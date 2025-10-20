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

require_once "../modelos/usuarios-central.modelo.php";

try {
    
    // Verificar permisos
    if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
        echo json_encode([
            'success' => false,
            'message' => 'Sin permisos para acceder a esta información'
        ]);
        return;
    }
    
    // Obtener usuarios pendientes de sincronización
    $usuariosPendientes = ModeloUsuariosCentral::mdlObtenerUsuariosPendientesSincronizacion();
    
    echo json_encode([
        'success' => true,
        'data' => $usuariosPendientes
    ]);
    
} catch (Exception $e) {
    error_log("Error en detalles-sincronizacion.ajax.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener detalles: ' . $e->getMessage()
    ]);
}
?>
