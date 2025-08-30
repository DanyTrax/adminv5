<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

$sucursal_origen = $_POST['sucursal_origen'] ?? '';
$usuario_despacho = $_POST['usuario_despacho'] ?? '';
$usuario_transporte = $_POST['usuario_transporte'] ?? '';
$productos_json = $_POST['productos'] ?? '[]';

if (empty($sucursal_origen) || empty($usuario_despacho) || empty($usuario_transporte) || empty($productos_json)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
    exit();
}

$pdo = ConexionCentral::conectar();
try {
    $pdo->beginTransaction();

    // 1. Creamos el registro de la transferencia
    $stmt_transferencia = $pdo->prepare(
        "INSERT INTO transferencias (sucursal_origen, sucursal_destino, usuario_despacho, usuario_transporte, estado) 
         VALUES (:origen, 'Stock en Transito', :despacho, :transporte, 'pendiente_cargue')"
    );
    $stmt_transferencia->execute([
        ':origen' => $sucursal_origen,
        ':despacho' => $usuario_despacho,
        ':transporte' => $usuario_transporte
    ]);
    $id_transferencia = $pdo->lastInsertId();

    // 2. Insertamos los productos en el manifiesto
    $productos = json_decode($productos_json, true);
    $stmt_items = $pdo->prepare(
        "INSERT INTO transferencia_items (id_transferencia, id_producto_origen, descripcion, cantidad_enviada)
         VALUES (:id_transferencia, :id_producto, :descripcion, :cantidad)"
    );
    foreach($productos as $producto){
        $stmt_items->execute([
            ':id_transferencia' => $id_transferencia,
            ':id_producto' => $producto['id'],
            ':descripcion' => $producto['descripcion'],
            ':cantidad' => $producto['cantidad']
        ]);
    }
    
    // 3. Creamos el primer registro en el log
    $accion_log = "Preparo el cargue para " . $usuario_transporte;
    $stmt_log = $pdo->prepare(
        "INSERT INTO log_transferencia (id_transferencia, usuario_accion, sucursal_accion, accion)
         VALUES (:id_transferencia, :usuario, :sucursal, :accion)"
    );
    $stmt_log->execute([
        ':id_transferencia' => $id_transferencia,
        ':usuario' => $usuario_despacho,
        ':sucursal' => $sucursal_origen,
        ':accion' => $accion_log
    ]);

    $pdo->commit();
    echo json_encode(['status' => 'ok', 'message' => 'Cargue iniciado.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al iniciar el cargue: ' . $e->getMessage()]);
}