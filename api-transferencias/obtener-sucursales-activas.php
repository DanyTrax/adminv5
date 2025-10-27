<?php
/**
 * API para obtener sucursales activas desde BD central
 * Utilizado por el instalador para mostrar sucursales disponibles
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Incluir conexion-central para conectar a BD central
    require_once __DIR__ . '/conexion-central.php';
    
    // Conectar a la base de datos central
    $pdo = ConexionCentral::conectar();
    
    // Obtener sucursales activas desde la tabla sucursales del central
    $stmt = $pdo->prepare("
        SELECT 
            id,
            codigo_sucursal,
            nombre,
            direccion,
            telefono,
            email,
            url_base,
            url_api,
            activo,
            fecha_registro
        FROM sucursales
        WHERE activo = 1
        ORDER BY nombre ASC
    ");
    
    $stmt->execute();
    $sucursales = $stmt->fetchAll();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Sucursales activas obtenidas exitosamente',
        'total' => count($sucursales),
        'sucursales' => $sucursales
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Error en obtener-sucursales-activas.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage(),
        'sucursales' => []
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error general en obtener-sucursales-activas.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error general: ' . $e->getMessage(),
        'sucursales' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>
