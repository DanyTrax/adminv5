<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

try {
    // Si nos pasan un ID, buscamos solo esa solicitud
    if(isset($_GET['id'])){
        $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM solicitudes_transferencia WHERE id = :id");
        $stmt->bindParam(":id", $_GET['id'], PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Si no, buscamos todas las que no estén completadas
        $stmt = ConexionCentral::conectar()->prepare(
            "SELECT * FROM solicitudes_transferencia WHERE estado NOT IN ('completada', 'rechazada') ORDER BY fecha_solicitud DESC"
        );
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode($resultado);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}