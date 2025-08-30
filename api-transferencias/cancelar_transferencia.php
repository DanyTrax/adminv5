<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once "conexion-central.php";
$id_transferencia = $_POST['id_transferencia'] ?? 0;

if($id_transferencia > 0){
    $stmt = ConexionCentral::conectar()->prepare("UPDATE transferencias SET estado = 'cancelada', fecha_actualizacion_estado = NOW() WHERE id = :id");
    $stmt->bindParam(":id", $id_transferencia, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error']);
}