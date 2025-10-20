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

require_once __DIR__ . "/../controladores/usuarios-central.controlador.php";

try {
    
    // Verificar permisos
    if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
        echo json_encode([
            "success" => false,
            "error" => "No tienes permisos para realizar esta acción"
        ]);
        exit;
    }
    
    // Obtener parámetros
    $sucursalId = $_POST["sucursal_id"] ?? null;
    $tipoConsulta = $_POST["tipo_consulta"] ?? "todas";
    
    $resultado = [];
    
    if($tipoConsulta === "todas" || $tipoConsulta === "remotas") {
        // Consultar usuarios de sucursales remotas
        $usuariosSucursales = ControladorUsuariosCentral::ctrConsultarUsuariosSucursales($sucursalId);
        $resultado["sucursales"] = $usuariosSucursales;
    }
    
    if($tipoConsulta === "todas" || $tipoConsulta === "local") {
        // Consultar usuarios de sucursal local
        $usuariosLocal = ControladorUsuariosCentral::ctrObtenerUsuariosLocal();
        $resultado["local"] = $usuariosLocal;
    }
    
    // Calcular estadísticas
    $totalUsuarios = 0;
    $sucursalesConectadas = 0;
    $sucursalesError = 0;
    
    if(isset($resultado["local"])) {
        $totalUsuarios += count($resultado["local"]);
    }
    
    if(isset($resultado["sucursales"])) {
        foreach($resultado["sucursales"] as $sucursal) {
            $totalUsuarios += $sucursal["total_usuarios"];
            if($sucursal["estado_conexion"] == "conectado") {
                $sucursalesConectadas++;
            } else {
                $sucursalesError++;
            }
        }
    }
    
    $resultado["estadisticas"] = [
        "total_usuarios" => $totalUsuarios,
        "sucursales_conectadas" => $sucursalesConectadas,
        "sucursales_error" => $sucursalesError,
        "total_sucursales" => count($resultado["sucursales"] ?? [])
    ];
    
    echo json_encode([
        "success" => true,
        "data" => $resultado
    ]);
    
} catch (Exception $e) {
    error_log("Error en consultar-usuarios-sucursales.ajax.php: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
