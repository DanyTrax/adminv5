<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

try {
    $stmt = ConexionCentral::conectar()->prepare(
        "SELECT * FROM recepciones ORDER BY fecha_recepcion DESC"
    );
    $stmt->execute();
    $recepciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Envolvemos la respuesta en el formato { "data": [...] } que espera DataTables
    echo json_encode(["data" => $recepciones]);

} catch (PDOException $e) {
    echo json_encode(["data" => [], "error" => 'Error: ' . $e->getMessage()]);
}