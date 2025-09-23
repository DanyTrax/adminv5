<?php

require_once "../controladores/solicitudes-stock.controlador.php";
require_once "../modelos/solicitudes-stock.modelo.php";

class TablaSolicitudesStock {

    /*=============================================
    MOSTRAR LA TABLA DE SOLICITUDES DE STOCK
    =============================================*/
    public function mostrarTablaSolicitudesStock(){
        
        $solicitudes = ControladorSolicitudesStock::ctrMostrarSolicitudesCompletas();
        
        if(count($solicitudes) == 0){
            echo '{"data": []}';
            return;
        }

        $datosJson = '{
            "data": [';

        for($i = 0; $i < count($solicitudes); $i++){
            
            /*=============================================
            DETERMINAR COLOR DEL ESTADO
            =============================================*/
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

            /*=============================================
            TIPO DE SOLICITUD CON ICONO
            =============================================*/
            $tipoIcono = $solicitudes[$i]["tipo_solicitud"] == 'stock' ? 'fa-cubes' : 'fa-file-text-o';
            $tipoTexto = $solicitudes[$i]["tipo_solicitud"] == 'stock' ? 'Stock' : 'Remisión';
            $tipo = "<i class='fa ".$tipoIcono."'></i> ".$tipoTexto;

            /*=============================================
            INFORMACIÓN DE PRODUCTOS
            =============================================*/
            $totalProductos = $solicitudes[$i]["total_productos"];
            $totalCantidad = $solicitudes[$i]["total_cantidad"];
            $productos = "<span class='badge bg-blue'>".$totalProductos." prod.</span><br>".
                        "<small class='text-muted'>".$totalCantidad." unidades</small>";

            /*=============================================
            FECHAS FORMATEADAS
            =============================================*/
            $fechaSolicitud = date('d/m/Y H:i', strtotime($solicitudes[$i]["fecha_solicitud"]));
            
            $aprobadoPor = 'N/A';
            $fechaAprobacion = 'N/A';
            
            if($solicitudes[$i]["fecha_aprobacion"]) {
                $fechaAprobacion = date('d/m/Y H:i', strtotime($solicitudes[$i]["fecha_aprobacion"]));
                $aprobadoPor = $solicitudes[$i]["nombre_usuario_aprobacion"] ?: 'Usuario eliminado';
            }

            /*=============================================
            BOTONES DE ACCIONES
            =============================================*/
            $acciones = "<div class='btn-group'>";

            // Botón VER - Para todos
            $acciones .= "<button class='btn btn-info btn-xs btnVerSolicitud' ".
                        "idSolicitud='".$solicitudes[$i]["id"]."' ".
                        "data-toggle='modal' data-target='#modalVerSolicitud' ".
                        "title='Ver detalles'>".
                        "<i class='fa fa-eye'></i>".
                        "</button>";

            // Botones APROBAR y CANCELAR - Solo para Transportador y Administrador
            if(($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador") && 
               $solicitudes[$i]["estado"] == "pendiente") {
                
                $acciones .= "<button class='btn btn-success btn-xs btnAprobarSolicitud' ".
                            "idSolicitud='".$solicitudes[$i]["id"]."' ".
                            "title='Aprobar solicitud'>".
                            "<i class='fa fa-check'></i>".
                            "</button>";
                
                $acciones .= "<button class='btn btn-warning btn-xs btnCancelarSolicitud' ".
                            "idSolicitud='".$solicitudes[$i]["id"]."' ".
                            "title='Cancelar solicitud'>".
                            "<i class='fa fa-times'></i>".
                            "</button>";
            }

            // Botón ELIMINAR - Solo para Administrador
            if($_SESSION["perfil"] == "Administrador") {
                $acciones .= "<button class='btn btn-danger btn-xs btnEliminarSolicitud' ".
                            "idSolicitud='".$solicitudes[$i]["id"]."' ".
                            "title='Eliminar solicitud'>".
                            "<i class='fa fa-trash'></i>".
                            "</button>";
            }

            $acciones .= "</div>";

            $datosJson .='[
                "'.($i+1).'",
                "'.$solicitudes[$i]["numero_solicitud"].'",
                "'.$solicitudes[$i]["nombre_sucursal_solicitante"].'",
                "'.$solicitudes[$i]["nombre_usuario_solicitante"].'",
                "'.$tipo.'",
                "'.$productos.'",
                "'.$estado.'",
                "'.$fechaSolicitud.'",
                "'.$aprobadoPor.'",
                "'.$acciones.'"
            ],';
        }

        $datosJson = substr($datosJson, 0, -1);
        $datosJson .= '] }';
        
        echo $datosJson;
    }
}

/*=============================================
ACTIVAR TABLA DE SOLICITUDES DE STOCK
=============================================*/
$activarSolicitudesStock = new TablaSolicitudesStock();
$activarSolicitudesStock->mostrarTablaSolicitudesStock();