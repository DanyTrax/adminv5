<?php
    session_start();

    // ✅ DEBUG: Agregar estas líneas para debug
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Verificar sesión
    if (!isset($_SESSION['perfil'])) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Sin sesión activa"
        ]);
        exit;
    }

    // Verificar archivos
    if (!file_exists("../api-transferencias/conexion-central.php")) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Archivo conexion-central.php no encontrado"
        ]);
        exit;
    }

    if (!file_exists("../controladores/solicitudes-stock.controlador.php")) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Controlador solicitudes-stock no encontrado"
        ]);
        exit;
    }


if (!file_exists("../modelos/solicitudes-stock.modelo.php")) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Modelo solicitudes-stock no encontrado"
    ]);
    exit;
}


require_once "../api-transferencias/conexion-central.php";
require_once "../controladores/solicitudes-stock.controlador.php";
require_once "../modelos/solicitudes-stock.modelo.php";

// Verificar conexión central
$conexionTest = ConexionCentral::conectar();
if (!$conexionTest) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "No se pudo conectar a la base de datos central"
    ]);
    exit;
}
}

// Verificar si existe la tabla
try {
    $stmt = $conexionTest->prepare("SHOW TABLES LIKE 'solicitudes_stock'");
    $stmt->execute();
    $tabla_existe = $stmt->fetch();

    if (!$tabla_existe) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "La tabla solicitudes_stock no existe en la base de datos central"
        ]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error verificando tabla: " . $e->getMessage()
    ]);
    exit;
}
class AjaxNotificacionesSolicitudes {

    /*=============================================
    OBTENER SOLICITUDES PENDIENTES PARA NOTIFICACIONES
    =============================================*/
    public function ajaxObtenerSolicitudesPendientes() {
        
        // Verificar permisos
        if($_SESSION["perfil"] != "Transportador" && $_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                "success" => false,
                "message" => "Sin permisos"
            ]);
            return;
        }
        
        try {
            // Obtener contador
            $contador = ControladorSolicitudesStock::ctrContarSolicitudesPendientes();
            
            // Obtener lista de solicitudes recientes
            $solicitudes = ControladorSolicitudesStock::ctrObtenerSolicitudesPendientes(8);
            
            echo json_encode([
                "success" => true,
                "data" => [
                    "contador" => intval($contador),
                    "solicitudes" => $solicitudes
                ]
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error al obtener notificaciones: " . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    MARCAR NOTIFICACIONES COMO VISTAS
    =============================================*/
    public function ajaxMarcarComoVistas() {
        
        // Verificar permisos
        if($_SESSION["perfil"] != "Transportador" && $_SESSION["perfil"] != "Administrador") {
            echo json_encode([
                "success" => false,
                "message" => "Sin permisos"
            ]);
            return;
        }
        
        try {
            // Obtener todas las solicitudes pendientes para marcarlas como vistas
            $solicitudes = ControladorSolicitudesStock::ctrObtenerSolicitudesPendientes(50);
            
            if($solicitudes && count($solicitudes) > 0) {
                $ids = array_column($solicitudes, 'id');
                $respuesta = ControladorSolicitudesStock::ctrMarcarSolicitudesComoVistas($ids);
                
                echo json_encode([
                    "success" => ($respuesta == "ok"),
                    "message" => ($respuesta == "ok") ? "Marcadas como vistas" : "Error al marcar como vistas"
                ]);
            } else {
                echo json_encode([
                    "success" => true,
                    "message" => "No hay solicitudes que marcar"
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    OBTENER ESTADÍSTICAS RÁPIDAS
    =============================================*/
    public function ajaxObtenerEstadisticas() {
        
        try {
            $estadisticas = ControladorSolicitudesStock::ctrObtenerEstadisticas();
            
            echo json_encode([
                "success" => true,
                "data" => $estadisticas
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error al obtener estadísticas: " . $e->getMessage()
            ]);
        }
    }
}

/*=============================================
PROCESAR ACCIONES AJAX
=============================================*/
if(isset($_POST["accion"])) {
    
    $notificaciones = new AjaxNotificacionesSolicitudes();
    
    switch($_POST["accion"]) {
        
        case "obtener_pendientes":
            $notificaciones->ajaxObtenerSolicitudesPendientes();
            break;
            
        case "marcar_como_vistas":
            $notificaciones->ajaxMarcarComoVistas();
            break;
            
        case "obtener_estadisticas":
            $notificaciones->ajaxObtenerEstadisticas();
            break;
            
        default:
            echo json_encode([
                "success" => false,
                "message" => "Acción no válida"
            ]);
            break;
    }
    
} else {
    echo json_encode([
        "success" => false,
        "message" => "No se especificó acción"
    ]);
}