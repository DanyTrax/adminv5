<?php
header('Content-Type: application/json');
require_once "conexion-central.php";
$idStock = $_POST['id_stock_transito'] ?? 0;
$cantidad = $_POST['cantidad'] ?? 0;
$usuario_recibe = $_POST['usuario_recibe'] ?? '';
$sucursal_destino = $_POST['sucursal_destino'] ?? '';

if(empty($idStock) || empty($cantidad)){
     echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
     exit();
}

try {
    // Restamos la cantidad del inventario en tránsito
    $stmt = ConexionCentral::conectar()->prepare("UPDATE stock_transito SET cantidad = cantidad - :cantidad WHERE id = :id");
    $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
    $stmt->bindParam(":id", $idStock, PDO::PARAM_INT);
    $stmt->execute();
    
    // Aquí puedes añadir un registro al log de transferencias para auditar la descarga
    // ...
    
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}