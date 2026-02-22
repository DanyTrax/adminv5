<?php
/**
 * Endpoint remoto para ejecutar git pull
 * Debe desplegarse en cada sucursal. Se llama desde el panel central con token.
 */
header('Content-Type: application/json; charset=utf-8');

$tokenRecibido = $_POST['token'] ?? $_GET['token'] ?? '';
$tokenEsperado = defined('GIT_PULL_TOKEN') ? GIT_PULL_TOKEN : (getenv('GIT_PULL_TOKEN') ?: 'adminv5_git_pull_2025');

if ($tokenRecibido !== $tokenEsperado || empty($tokenRecibido)) {
    echo json_encode(['success' => false, 'error' => 'Token inválido']);
    exit;
}

$rutaBase = dirname(__DIR__);
$comando = "cd " . escapeshellarg($rutaBase) . " && git pull 2>&1";
$output = [];
exec($comando, $output, $returnCode);
$salida = implode("\n", $output);

if ($returnCode === 0) {
    echo json_encode(['success' => true, 'message' => $salida ?: 'Git pull ejecutado correctamente']);
} else {
    echo json_encode(['success' => false, 'error' => $salida ?: "Código de salida: $returnCode"]);
}
