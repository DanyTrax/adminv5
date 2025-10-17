<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar URL requerida
    if (empty($input['api_url'])) {
        echo json_encode(['success' => false, 'message' => 'URL de API requerida']);
        exit;
    }
    
    $apiUrl = rtrim($input['api_url'], '/') . '/';
    $testUrl = $apiUrl . 'test_conexion.php';
    
    // Crear contexto para la petición
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => [
                'User-Agent: AdminV5-Test-Connection/1.0',
                'Accept: application/json'
            ]
        ]
    ]);
    
    // Probar conexión
    $startTime = microtime(true);
    $response = @file_get_contents($testUrl, false, $context);
    $endTime = microtime(true);
    
    $responseTime = round(($endTime - $startTime) * 1000, 2); // ms
    
    if ($response === false) {
        
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo conectar con la sucursal',
            'url_probada' => $testUrl,
            'tiempo_respuesta' => null
        ]);
        
    } else {
        
        $responseData = json_decode($response, true);
        
        if ($responseData && isset($responseData['success']) && $responseData['success']) {
            
            echo json_encode([
                'success' => true,
                'message' => 'Conexión exitosa con la sucursal',
                'url_probada' => $testUrl,
                'tiempo_respuesta' => $responseTime . ' ms',
                'detalles' => $responseData
            ]);
            
        } else {
            
            echo json_encode([
                'success' => false,
                'message' => 'La sucursal respondió pero con formato incorrecto',
                'url_probada' => $testUrl,
                'tiempo_respuesta' => $responseTime . ' ms',
                'respuesta' => $response
            ]);
        }
    }
    
} catch (Exception $e) {
    
    error_log("Error en probar_conexion_sucursal.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>