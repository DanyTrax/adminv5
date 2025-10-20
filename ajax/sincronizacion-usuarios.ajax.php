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

require_once __DIR__ . "/../controladores/sincronizacion-usuarios.controlador.php";

try {
    
    // Verificar permisos
    if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
        echo json_encode([
            "success" => false,
            "error" => "No tienes permisos para realizar esta acción"
        ]);
        exit;
    }
    
    // Procesar diferentes tipos de peticiones
    if(isset($_POST["sincronizarUsuarioCentral"])) {
        $usuarioId = $_POST["usuario_id"];
        $resultado = ControladorSincronizacionUsuarios::ctrSincronizarUsuarioCentral($usuarioId);
        echo json_encode($resultado);
        
    } elseif(isset($_POST["sincronizarTodosUsuariosCentrales"])) {
        $resultado = ControladorSincronizacionUsuarios::ctrSincronizarTodosUsuariosCentrales();
        echo json_encode($resultado);
        
    } elseif(isset($_POST["importarUsuariosSucursal"])) {
        $sucursalId = $_POST["sucursal_id"];
        $resultado = ControladorSincronizacionUsuarios::ctrImportarUsuariosSucursal($sucursalId);
        echo json_encode($resultado);
        
    } elseif(isset($_POST["consultarUsuariosTodasSucursales"])) {
        $resultado = ControladorSincronizacionUsuarios::ctrConsultarUsuariosTodasSucursales();
        echo json_encode([
            "success" => true,
            "data" => $resultado
        ]);
        
    } elseif(isset($_POST["obtenerEstadoSincronizacion"])) {
        $resultado = ControladorSincronizacionUsuarios::ctrObtenerEstadoSincronizacion();
        echo json_encode([
            "success" => true,
            "data" => $resultado
        ]);
        
    } elseif(isset($_POST["eliminarUsuarioBidireccional"])) {
        $usuarioId = $_POST["usuario_id"];
        $tipo = $_POST["tipo"];
        $sucursalId = $_POST["sucursal_id"] ?? null;
        
        $resultado = ControladorSincronizacionUsuarios::ctrEliminarUsuarioBidireccional($usuarioId, $tipo, $sucursalId);
        echo json_encode($resultado);
        
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Acción no válida"
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en sincronizacion-usuarios.ajax.php: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
