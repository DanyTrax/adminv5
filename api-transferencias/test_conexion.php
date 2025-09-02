<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Este archivo debe estar en cada sucursal para probar conectividad

try {
    
    echo json_encode([
        'success' => true,
        'message' => 'API de transferencias funcionando correctamente',
        'servidor' => $_SERVER['SERVER_NAME'] ?? 'Desconocido',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => 'AdminV5 API v1.0'
    ]);
    
} catch (Exception $e) {
    
    echo json_encode([
        'success' => false,
        'message' => 'Error en test de conexión: ' . $e->getMessage()
    ]);
}
?>