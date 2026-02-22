<?php
/**
 * Endpoint remoto para ejecutar git pull
 * Debe desplegarse en cada sucursal. Se llama desde el panel central con token.
 */
header('Content-Type: application/json; charset=utf-8');

// Capturar errores para evitar 500 y devolver JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    $tokenRecibido = $_POST['token'] ?? $_GET['token'] ?? '';
    $tokenEsperado = 'adminv5_git_pull_2025';
    $configPath = dirname(__DIR__) . '/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
        $tokenEsperado = defined('GIT_PULL_TOKEN') ? GIT_PULL_TOKEN : $tokenEsperado;
    }

    if ($tokenRecibido !== $tokenEsperado || empty($tokenRecibido)) {
        echo json_encode(['success' => false, 'error' => 'Token inválido']);
        exit;
    }

    $rutaBase = dirname(__DIR__);
    $comando = "cd " . escapeshellarg($rutaBase) . " && git pull 2>&1";

    $salida = '';
    $returnCode = -1;

    if (function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions') ?: '')))) {
        $output = [];
        @exec($comando, $output, $returnCode);
        $salida = implode("\n", $output);
    } elseif (function_exists('shell_exec')) {
        $salida = @shell_exec($comando) ?: '';
        $returnCode = (strpos($salida, 'Already up to date') !== false || strpos($salida, 'Updating') !== false || strpos($salida, 'Fast-forward') !== false) ? 0 : (preg_match('/error|fatal|failed/i', $salida) ? 1 : 0);
    } else {
        echo json_encode(['success' => false, 'error' => 'exec y shell_exec están deshabilitados en este servidor']);
        exit;
    }

    if ($returnCode === 0) {
        echo json_encode(['success' => true, 'message' => $salida ?: 'Git pull ejecutado correctamente']);
    } else {
        echo json_encode(['success' => false, 'error' => $salida ?: "Código de salida: $returnCode"]);
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
