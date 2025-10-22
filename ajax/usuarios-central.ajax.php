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
            'success' => false,
            'error' => 'Sin permisos para acceder a esta información'
        ]);
        exit;
    }
    
    // Obtener acción
    $accion = $_POST["accion"] ?? $_GET["accion"] ?? "";
    
    switch($accion) {
        
        case "obtener_estadisticas":
            $estadisticas = ControladorUsuariosCentral::ctrObtenerEstadisticasSincronizacion();
            echo json_encode([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);
            break;
            
        case "obtener_usuarios_centrales":
            $usuarios = ControladorUsuariosCentral::ctrObtenerUsuariosCentral();
            echo json_encode([
                'success' => true,
                'usuarios' => $usuarios
            ]);
            break;
            
        case "obtener_usuarios_sucursales":
            $usuarios = ControladorUsuariosCentral::ctrConsultarUsuariosSucursales();
            echo json_encode([
                'success' => true,
                'usuarios' => $usuarios
            ]);
            break;
            
        case "crear_usuario_central":
            // Decodificar datos JSON enviados desde JavaScript
            $datosJson = json_decode($_POST['datos'], true);
            $datos = [
                'nombre' => $datosJson['nombre'] ?? '',
                'usuario' => $datosJson['usuario'] ?? '',
                'password' => $datosJson['password'] ?? '',
                'perfil' => $datosJson['perfil'] ?? '',
                'telefono' => $datosJson['telefono'] ?? ''
            ];
            
            $resultado = ControladorUsuariosCentral::ctrCrearUsuarioCentral($datos);
            echo json_encode($resultado);
            break;
            
        case "sincronizar_usuarios":
            $resultado = ControladorUsuariosCentral::ctrSincronizarUsuariosCentral();
            echo json_encode($resultado);
            break;
            
        case "importar_usuarios_sucursales":
            $resultado = ControladorUsuariosCentral::ctrImportarUsuariosSucursales();
            echo json_encode($resultado);
            break;
            
        case "importar_usuario_individual":
            $usuario = json_decode($_POST['usuario'], true);
            $resultado = ControladorUsuariosCentral::ctrImportarUsuarioIndividual($usuario);
            echo json_encode($resultado);
            break;
            
        case "editar_usuario_central":
            // Decodificar datos JSON enviados desde JavaScript
            $datosJson = json_decode($_POST['datos'], true);
            $datos = [
                'id' => $datosJson['id'] ?? '',
                'nombre' => $datosJson['nombre'] ?? '',
                'usuario' => $datosJson['usuario'] ?? '',
                'password' => $datosJson['password'] ?? '',
                'perfil' => $datosJson['perfil'] ?? '',
                'telefono' => $datosJson['telefono'] ?? '',
                'activo' => $datosJson['activo'] ?? 1
            ];
            
            $resultado = ControladorUsuariosCentral::ctrEditarUsuarioCentral($datos);
            echo json_encode($resultado);
            break;
            
        case "eliminar_usuario_central":
            $id = $_POST['id'];
            $resultado = ControladorUsuariosCentral::ctrEliminarUsuarioCentral($id);
            echo json_encode($resultado);
            break;
            
        case "obtener_sucursales_disponibles":
            $sucursales = ControladorUsuariosCentral::ctrObtenerSucursalesDisponibles();
            echo json_encode([
                'success' => true,
                'sucursales' => $sucursales
            ]);
            break;
            
        case "sincronizar_usuarios_sucursales":
            $sucursales = json_decode($_POST['sucursales'], true);
            $resultado = ControladorUsuariosCentral::ctrSincronizarUsuariosSucursales($sucursales);
            echo json_encode($resultado);
            break;
            
        case "asignar_sucursales_usuario":
            error_log("AJAX: Asignando sucursales - Usuario ID: " . $_POST['usuario_id'] . ", Sucursales: " . $_POST['sucursales']);
            $usuario_id = $_POST['usuario_id'];
            $sucursales = json_decode($_POST['sucursales'], true);
            error_log("AJAX: Datos decodificados - Usuario ID: $usuario_id, Sucursales: " . json_encode($sucursales));
            $resultado = ControladorUsuariosCentral::ctrAsignarSucursalesUsuario($usuario_id, $sucursales);
            error_log("AJAX: Resultado: " . json_encode($resultado));
            echo json_encode($resultado);
            break;
            
        case "sincronizar_todos_usuarios":
            // Limpiar cualquier output previo
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Iniciar nuevo buffer
            ob_start();
            
            // Deshabilitar error_log temporalmente
            $old_error_log = ini_set('log_errors', 0);
            $old_display_errors = ini_set('display_errors', 0);
            
            $resultado = ControladorUsuariosCentral::ctrSincronizarTodosUsuarios();
            
            // Restaurar configuraciones
            ini_set('log_errors', $old_error_log);
            ini_set('display_errors', $old_display_errors);
            
            // Limpiar buffer y enviar solo JSON
            ob_clean();
            
            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit;
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en usuarios-central.ajax.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>
