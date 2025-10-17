<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

$id_transferencia = $_GET['id_transferencia'] ?? 0;

if($id_transferencia > 0){
    $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM transferencia_items WHERE id_transferencia = :id");
    $stmt->bindParam(":id", $id_transferencia, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo json_encode([]);
}