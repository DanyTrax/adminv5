<?php

session_start();

require_once "../api-transferencias/conexion-central.php";

class AjaxSolicitudesStockBuscar {

    /*=============================================
    BUSCAR SOLICITUD POR NÚMERO
    =============================================*/
    public function ajaxBuscarSolicitudPorNumero() {
        
        if(isset($_POST["numeroSolicitud"])) {
            
            try {
                $numeroSolicitud = $_POST["numeroSolicitud"];
                
                $stmt = ConexionCentral::conectar()->prepare("
                    SELECT * FROM solicitudes_stock 
                    WHERE numero_solicitud = :numero_solicitud 
                    AND estado = 'pendiente'
                    ORDER BY fecha_solicitud DESC 
                    LIMIT 1
                ");
                
                $stmt->bindParam(":numero_solicitud", $numeroSolicitud, PDO::PARAM_STR);
                $stmt->execute();
                
                $solicitud = $stmt->fetch();
                
                if($solicitud) {
                    echo json_encode($solicitud);
                } else {
                    echo json_encode(["error" => "Solicitud no encontrada o no está pendiente"]);
                }
                
            } catch(Exception $e) {
                echo json_encode(["error" => "Error en la búsqueda: " . $e->getMessage()]);
            }
        }
    }

    /*=============================================
    BUSCAR SOLICITUDES RECIENTES
    =============================================*/
    public function ajaxBuscarSolicitudesRecientes() {
        
        if(isset($_POST["buscarRecientes"])) {
            
            try {
                $stmt = ConexionCentral::conectar()->prepare("
                    SELECT 
                        numero_solicitud, 
                        nombre_sucursal_solicitante,
                        total_productos,
                        total_cantidad,
                        fecha_solicitud
                    FROM solicitudes_stock 
                    WHERE estado = 'pendiente'
                    ORDER BY fecha_solicitud DESC 
                    LIMIT 20
                ");
                
                $stmt->execute();
                $solicitudes = $stmt->fetchAll();
                
                echo json_encode($solicitudes);
                
            } catch(Exception $e) {
                echo json_encode([]);
            }
        }
    }
}

/*=============================================
PROCESAR PETICIONES
=============================================*/
if(isset($_POST["numeroSolicitud"])) {
    $buscarSolicitud = new AjaxSolicitudesStockBuscar();
    $buscarSolicitud->ajaxBuscarSolicitudPorNumero();
}

if(isset($_POST["buscarRecientes"])) {
    $buscarRecientes = new AjaxSolicitudesStockBuscar();
    $buscarRecientes->ajaxBuscarSolicitudesRecientes();
}

?>