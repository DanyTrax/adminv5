<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

try {
    // CORRECCIÓN: Quitamos el "WHERE estado..." para traer el historial completo
    $stmt = ConexionCentral::conectar()->prepare(
        "SELECT * FROM transferencias ORDER BY fecha_despacho DESC"
    );
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}