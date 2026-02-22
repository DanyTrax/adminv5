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
    $disabled = array_map('trim', explode(',', ini_get('disable_functions') ?: ''));
    $salida = '';
    $returnCode = -1;

    $runExec = function_exists('exec') && !in_array('exec', $disabled);
    $runShellExec = function_exists('shell_exec') && !in_array('shell_exec', $disabled);
    $runProcOpen = function_exists('proc_open') && !in_array('proc_open', $disabled);
    $runPopen = function_exists('popen') && !in_array('popen', $disabled);

    if ($runExec) {
        $output = [];
        @exec("cd " . escapeshellarg($rutaBase) . " && git pull 2>&1", $output, $returnCode);
        $salida = implode("\n", $output);
    } elseif ($runShellExec) {
        $salida = @shell_exec("cd " . escapeshellarg($rutaBase) . " && git pull 2>&1") ?: '';
        $returnCode = (strpos($salida, 'Already up to date') !== false || strpos($salida, 'Updating') !== false || strpos($salida, 'Fast-forward') !== false) ? 0 : (preg_match('/error|fatal|failed/i', $salida) ? 1 : 0);
    } elseif ($runProcOpen) {
        $descriptorspec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = @proc_open('git pull 2>&1', $descriptorspec, $pipes, $rutaBase);
        if (is_resource($process)) {
            fclose($pipes[0]);
            $salida = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $returnCode = proc_close($process);
        } else {
            $salida = 'proc_open falló';
            $returnCode = 1;
        }
    } elseif ($runPopen) {
        $handle = @popen("cd " . escapeshellarg($rutaBase) . " && git pull 2>&1", 'r');
        if ($handle) {
            $salida = stream_get_contents($handle);
            pclose($handle);
            $returnCode = preg_match('/error|fatal|failed/i', $salida) ? 1 : 0;
        } else {
            $salida = 'popen falló';
            $returnCode = 1;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'exec, shell_exec, proc_open y popen están deshabilitados en este servidor. Contacta al hosting para habilitar al menos uno.']);
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
