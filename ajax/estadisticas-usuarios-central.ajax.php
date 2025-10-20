<?php

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Establecer headers JSON
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
}

require_once __DIR__ . "/../modelos/usuarios-central.modelo.php";

try {
    
    // Verificar permisos
    if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
        echo json_encode([
            'success' => false,
            'message' => 'Sin permisos para acceder a esta información'
        ]);
        return;
    }
    
    // Obtener estadísticas
    $estadisticas = ModeloUsuariosCentral::mdlObtenerEstadisticasSincronizacion();
    
    echo json_encode([
        'success' => true,
        'data' => $estadisticas
    ]);
    
} catch (Exception $e) {
    error_log("Error en estadisticas-usuarios-central.ajax.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
    ]);
}
?>
