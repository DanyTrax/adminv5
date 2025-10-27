<?php
/**
 * API para obtener sucursales activas desde conexion-central
 * Utilizado por el instalador para mostrar sucursales disponibles
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Incluir conexion-central
    require_once __DIR__ . '/conexion-central.php';
    
    // Obtener sucursales activas desde conexion-central
    $sucursales = [];
    
    // Obtener todas las sucursales configuradas
    foreach ($sucursales_configuracion as $codigo => $config) {
        if ($config['activa']) {
            $sucursales[] = [
                'id' => $codigo,
                'codigo_sucursal' => $codigo,
                'nombre' => $config['nombre'] ?? $codigo,
                'direccion' => $config['direccion'] ?? '',
                'telefono' => $config['telefono'] ?? '',
                'email' => $config['email'] ?? '',
                'url_base' => $config['url_base'] ?? '',
                'url_api' => $config['url_api'] ?? '',
                'activo' => 1,
                'fecha_registro' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Sucursales activas obtenidas exitosamente',
        'total' => count($sucursales),
        'sucursales' => $sucursales
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en obtener-sucursales-activas.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error general: ' . $e->getMessage(),
        'sucursales' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>
