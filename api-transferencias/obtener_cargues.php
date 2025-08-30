<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

try {
    // Nos aseguramos de que ordene por el más reciente
    $stmt = ConexionCentral::conectar()->prepare(
        "SELECT * FROM transferencias ORDER BY id DESC"
    );
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e){
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}