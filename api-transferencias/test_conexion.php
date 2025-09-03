<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'message' => 'API disponible',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '5.0',
    'server' => $_SERVER['SERVER_NAME'] ?? 'localhost'
]);
?>