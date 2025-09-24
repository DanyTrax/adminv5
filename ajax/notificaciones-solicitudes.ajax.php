<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar sesión
if (!isset($_SESSION['perfil'])) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Sin sesión activa",
        "data" => ["contador" => 0]
    ]);
    exit;
}

require_once "../api-transferencias/conexion-central.php";
require_once "../controladores/solicitudes-stock.controlador.php";
require_once "../modelos/solicitudes-stock.modelo.php";

class AjaxNotificacionesSolicitudes {

    /*=============================================
    OBTENER NOTIFICACIONES DE SOLICITUDES PENDIENTES
    =============================================*/
    public function ajaxObtenerNotificaciones() {
        
        try {
            // Verificar permisos
            if($_SESSION["perfil"] != "Transportador" && $_SESSION["perfil"] != "Administrador") {
                echo json_encode([
                    "success" => true,
                    "data" => [
                        "contador" => 0,
                        "solicitudes" => [],
                        "mensaje" => "Sin permisos para ver notificaciones"
                    ]
                ]);
                return;
            }

            // Obtener contador de solicitudes pendientes
            $contador = ControladorSolicitudesStock::ctrContarSolicitudesPendientes();
            
            // Obtener solicitudes pendientes recientes (máximo 8)
            $solicitudes = ControladorSolicitudesStock::ctrObtenerSolicitudesPendientes(8);

            // ✅ SIEMPRE RETORNAR ESTRUCTURA CONSISTENTE
            echo json_encode([
                "success" => true,
                "data" => [
                    "contador" => intval($contador),
                    "solicitudes" => $solicitudes,
                    "perfil" => $_SESSION["perfil"]
                ]
            ]);

        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error obteniendo notificaciones: " . $e->getMessage(),
                "data" => [
                    "contador" => 0,
                    "solicitudes" => []
                ]
            ]);
        }
    }

    /*=============================================
    MARCAR NOTIFICACIONES COMO VISTAS (OPCIONAL)
    =============================================*/
    public function ajaxMarcarComoVistas() {
        
        try {
            // Por ahora solo retornamos éxito
            // En el futuro se puede implementar un sistema de notificaciones vistas
            echo json_encode([
                "success" => true,
                "message" => "Notificaciones marcadas como vistas"
            ]);

        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
}

// ✅ PROCESAR PETICIONES
if(isset($_POST["accion"])) {
    
    $notificaciones = new AjaxNotificacionesSolicitudes();
    
    switch($_POST["accion"]) {
        case 'obtener_notificaciones':
        case 'obtener_pendientes': // ✅ AMBAS ACCIONES FUNCIONAN
            $notificaciones->ajaxObtenerNotificaciones();
            break;
            
        case 'marcar_como_vistas':
            $notificaciones->ajaxMarcarComoVistas();
            break;
            
        default:
            echo json_encode([
                "success" => false,
                "message" => "Acción no reconocida",
                "data" => ["contador" => 0, "solicitudes" => []]
            ]);
            break;
    }
    
} else {
    // ✅ RESPUESTA POR DEFECTO
    echo json_encode([
        "success" => true,
        "data" => [
            "contador" => 0,
            "solicitudes" => []
        ]
    ]);
}

?>