<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

$productos_recibidos_json = $_POST['productos_recibidos'] ?? '[]';
$usuario_recibe = $_POST['usuario_recibe'] ?? '';
$sucursal_destino = $_POST['sucursal_destino'] ?? '';
$usuario_transporte = $_POST['usuario_transporte'] ?? '';

if(empty($usuario_recibe) || empty($sucursal_destino)){
    echo json_encode(['status'=>'error', 'message'=>'Faltan datos del receptor.']);
    exit();
}

try {
    $stmt = ConexionCentral::conectar()->prepare(
        "INSERT INTO recepciones (sucursal_destino, usuario_recibe, usuario_transporte, productos_recibidos)
         VALUES (:sucursal, :recibe, :transporte, :productos)"
    );
    $stmt->bindParam(":sucursal", $sucursal_destino, PDO::PARAM_STR);
    $stmt->bindParam(":recibe", $usuario_recibe, PDO::PARAM_STR);
    $stmt->bindParam(":transporte", $usuario_transporte, PDO::PARAM_STR);
    $stmt->bindParam(":productos", $productos_recibidos_json, PDO::PARAM_STR);
    
    $stmt->execute();
    
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}