<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

$id_transferencia = $_POST['id_transferencia'] ?? 0;
$usuario_confirmador = $_POST['usuario_confirmador'] ?? ''; 

if(empty($id_transferencia) || empty($usuario_confirmador)){
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
    exit();
}

$pdo = ConexionCentral::conectar();
try {
    $pdo->beginTransaction();

    // 1. Obtenemos la información de la transferencia
    $stmt_get_info = $pdo->prepare("SELECT * FROM transferencias WHERE id = :id");
    $stmt_get_info->bindParam(":id", $id_transferencia, PDO::PARAM_INT);
    $stmt_get_info->execute();
    $transferencia = $stmt_get_info->fetch(PDO::FETCH_ASSOC);

    if (!$transferencia) {
        throw new Exception("No se encontró la transferencia.");
    }

    // 2. Actualizamos el estado de la transferencia a 'en_transito'
    $stmt_update = $pdo->prepare("UPDATE transferencias SET estado = 'en_transito', fecha_actualizacion_estado = NOW() WHERE id = :id AND estado = 'pendiente_cargue'");
    $stmt_update->bindParam(":id", $id_transferencia, PDO::PARAM_INT);
    $stmt_update->execute();
    
    // 3. Obtenemos los productos del manifiesto de esta transferencia
    $stmt_get_items = $pdo->prepare("SELECT * FROM transferencia_items WHERE id_transferencia = :id");
    $stmt_get_items->bindParam(":id", $id_transferencia, PDO::PARAM_INT);
    $stmt_get_items->execute();
    $items = $stmt_get_items->fetchAll(PDO::FETCH_ASSOC);

    // 4. AÑADIMOS CADA PRODUCTO AL STOCK EN TRÁNSITO
    $stmt_stock_upsert = $pdo->prepare(
        "INSERT INTO stock_transito (id_producto_origen, descripcion, cantidad, usuario_transporte)
         VALUES (:id_producto, :descripcion, :cantidad, :transporte)
         ON DUPLICATE KEY UPDATE cantidad = cantidad + :cantidad"
    );
    foreach($items as $item){
        $stmt_stock_upsert->execute([
            ':id_producto' => $item['id_producto_origen'],
            ':descripcion' => $item['descripcion'],
            ':cantidad' => $item['cantidad_enviada'],
            ':transporte' => $transferencia['usuario_transporte']
        ]);
    }

    // 5. Creamos el registro en el log con la confirmación bidireccional
    $accion_log = "Confirmó cargue y recibió para tránsito.";
    $stmt_log = $pdo->prepare(
        "INSERT INTO log_transferencia (id_transferencia, usuario_accion, sucursal_accion, accion, usuario_contraparte)
         VALUES (:id_transferencia, :usuario_accion, :sucursal_accion, :accion, :usuario_contraparte)"
    );
    $stmt_log->execute([
        ':id_transferencia' => $id_transferencia,
        ':usuario_accion' => $transferencia['usuario_despacho'],
        ':sucursal_accion' => $transferencia['sucursal_origen'],
        ':accion' => $accion_log,
        ':usuario_contraparte' => $usuario_confirmador
    ]);
    
    $pdo->commit();
    echo json_encode(['status' => 'ok', 'message' => 'Cargue confirmado y puesto en tránsito.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al confirmar el cargue: ' . $e->getMessage()]);
}