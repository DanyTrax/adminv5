<?php
header('Content-Type: application/json');
require_once "conexion-central.php";
$stmt = ConexionCentral::conectar()->prepare("SELECT * FROM stock_transito WHERE cantidad > 0");
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));