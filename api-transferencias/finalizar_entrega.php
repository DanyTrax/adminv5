<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

$cantidadesRecibidas = $_POST['cantidadRecibida'] ?? [];
$usuario_recibe = $_POST['usuario_recibe'] ?? '';
$sucursal_destino = $_POST['sucursal_destino'] ?? '';

$pdo = ConexionCentral::conectar();
try {
    $pdo->beginTransaction();

    // Recorremos los productos recibidos
    $stmt_update = $pdo->prepare(
        "UPDATE stock_transito SET cantidad = cantidad - :cantidad WHERE id = :id_stock_transito"
    );
    
    foreach($cantidadesRecibidas as $idStockTransito => $cantidad){
        if($cantidad > 0){
            $stmt_update->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
            $stmt_update->bindParam(":id_stock_transito", $idStockTransito, PDO::PARAM_INT);
            $stmt_update->execute();
        }
    }

    // Aquí iría la lógica para crear un registro en la tabla de 'logs'
    // ...

    $pdo->commit();
    echo json_encode(['status' => 'ok', 'message' => 'Stock en tránsito actualizado.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}