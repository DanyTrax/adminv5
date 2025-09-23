<?php

require_once "../controladores/solicitudes-stock.controlador.php";
require_once "../modelos/solicitudes-stock.modelo.php";

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