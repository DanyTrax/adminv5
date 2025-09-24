<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../api-transferencias/conexion-central.php";
require_once "../modelos/solicitudes-stock.modelo.php";

class TablaSolicitudesStock {

    public function mostrarTablaSolicitudesStock(){
        
        try {
            // ✅ OBTENER SOLICITUDES DIRECTAMENTE DEL MODELO (NO DEL CONTROLADOR)
            $solicitudes = ModeloSolicitudesStock::mdlMostrarSolicitudesCompletas("solicitudes_stock");
            
            if(count($solicitudes) == 0){
                echo '{"data": []}';
                return;
            }

            $datosJson = '{"data": [';

            for($i = 0; $i < count($solicitudes); $i++){
                
                // Estados con colores
                $estadoColor = '';
                $estadoTexto = '';
                
                switch($solicitudes[$i]["estado"]) {
                    case 'pendiente':
                        $estadoColor = 'label-warning';
                        $estadoTexto = 'Pendiente';
                        break;
                    case 'aprobado':
                        $estadoColor = 'label-success';
                        $estadoTexto = 'Aprobado';
                        break;
                    case 'cancelado':
                        $estadoColor = 'label-danger';
                        $estadoTexto = 'Cancelado';
                        break;
                }
                
                $estado = "<span class='label ".$estadoColor."'>".$estadoTexto."</span>";

                // Tipo de solicitud
                $tipoIcono = $solicitudes[$i]["tipo_solicitud"] == 'stock' ? 'fa-cubes' : 'fa-file-text-o';
                $tipoTexto = $solicitudes[$i]["tipo_solicitud"] == 'stock' ? 'Stock' : 'Remisión';
                $tipo = "<i class='fa ".$tipoIcono."'></i> ".$tipoTexto;

                // Información de productos
                $totalProductos = $solicitudes[$i]["total_productos"];
                $totalCantidad = $solicitudes[$i]["total_cantidad"];
                $productos = "<span class='badge bg-blue'>".$totalProductos." prod.</span><br>".
                            "<small class='text-muted'>".$totalCantidad." unidades</small>";

                // Fechas
                $fechaSolicitud = date('d/m/Y H:i', strtotime($solicitudes[$i]["fecha_solicitud"]));
                
                $aprobadoPor = 'N/A';
                if($solicitudes[$i]["nombre_usuario_aprobacion"]) {
                    $aprobadoPor = $solicitudes[$i]["nombre_usuario_aprobacion"];
                }

                // Botones de acciones
                $acciones = "<div class='btn-group'>";

                // Botón VER
                $acciones .= "<button class='btn btn-info btn-xs btnVerSolicitud' ".
                            "idSolicitud='".$solicitudes[$i]["id"]."' ".
                            "title='Ver detalles'>".
                            "<i class='fa fa-eye'></i>".
                            "</button>";

                // Botones según perfil
                if(($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador") && 
                   $solicitudes[$i]["estado"] == "pendiente") {
                    
                    $acciones .= "<button class='btn btn-success btn-xs btnAprobarSolicitud' ".
                                "idSolicitud='".$solicitudes[$i]["id"]."' ".
                                "title='Aprobar'>".
                                "<i class='fa fa-check'></i>".
                                "</button>";
                    
                    $acciones .= "<button class='btn btn-warning btn-xs btnCancelarSolicitud' ".
                                "idSolicitud='".$solicitudes[$i]["id"]."' ".
                                "title='Cancelar'>".
                                "<i class='fa fa-times'></i>".
                                "</button>";
                }

                if($_SESSION["perfil"] == "Administrador") {
                    $acciones .= "<button class='btn btn-danger btn-xs btnEliminarSolicitud' ".
                                "idSolicitud='".$solicitudes[$i]["id"]."' ".
                                "title='Eliminar'>".
                                "<i class='fa fa-trash'></i>".
                                "</button>";
                }

                $acciones .= "</div>";

                $datosJson .='[
                    "'.($i+1).'",
                    "'.addslashes($solicitudes[$i]["numero_solicitud"]).'",
                    "'.addslashes($solicitudes[$i]["nombre_sucursal_solicitante"]).'",
                    "'.addslashes($solicitudes[$i]["nombre_usuario_solicitante"]).'",
                    "'.$tipo.'",
                    "'.$productos.'",
                    "'.$estado.'",
                    "'.$fechaSolicitud.'",
                    "'.addslashes($aprobadoPor).'",
                    "'.$acciones.'"
                ],';
            }

            $datosJson = substr($datosJson, 0, -1);
            $datosJson .= '] }';
            
            echo $datosJson;
            
        } catch (Exception $e) {
            echo json_encode([
                "error" => "Error en DataTable: " . $e->getMessage()
            ]);
        }
    }
}

// ✅ NO VERIFICAR USUARIOS - EJECUTAR DIRECTAMENTE
$activarSolicitudesStock = new TablaSolicitudesStock();
$activarSolicitudesStock->mostrarTablaSolicitudesStock();