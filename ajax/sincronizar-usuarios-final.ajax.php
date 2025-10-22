<?php
// Incluir archivos necesarios
require_once __DIR__ . "/../controladores/usuarios-central.controlador.php";

// Verificar sesión
session_start();

if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Sin permisos para acceder a esta información'
    ]);
    exit;
}

// Deshabilitar completamente el error_log
ini_set('log_errors', 0);
ini_set('display_errors', 0);

// Limpiar cualquier output previo
while (ob_get_level()) {
    ob_end_clean();
}

// Iniciar nuevo buffer
ob_start();

try {
    $resultado = ControladorUsuariosCentral::ctrSincronizarTodosUsuarios();
    
    // Limpiar buffer completamente
    ob_clean();
    
    // Enviar solo JSON
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    
    // Terminar script inmediatamente
    exit;
    
} catch (Exception $e) {
    // Limpiar buffer completamente
    ob_clean();
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    
    // Terminar script inmediatamente
    exit;
}
?>
