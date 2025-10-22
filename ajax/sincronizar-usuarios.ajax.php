<?php
// Incluir archivos necesarios
require_once __DIR__ . "/../controladores/usuarios-central.controlador.php";

// Verificar sesión
session_start();

if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
    echo json_encode([
        'success' => false,
        'error' => 'Sin permisos para acceder a esta información'
    ]);
    exit;
}

// Limpiar cualquier output previo
while (ob_get_level()) {
    ob_end_clean();
}

// Redirigir error_log a un archivo temporal
$log_file = tempnam(sys_get_temp_dir(), 'sync_log_');
ini_set('log_errors', 1);
ini_set('error_log', $log_file);
ini_set('display_errors', 0);

try {
    $resultado = ControladorUsuariosCentral::ctrSincronizarTodosUsuarios();
    
    // Limpiar archivo de log temporal
    if (file_exists($log_file)) {
        unlink($log_file);
    }
    
    // Enviar respuesta JSON
    header('Content-Type: application/json');
    echo json_encode($resultado);
    
} catch (Exception $e) {
    // Limpiar archivo de log temporal
    if (file_exists($log_file)) {
        unlink($log_file);
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>
