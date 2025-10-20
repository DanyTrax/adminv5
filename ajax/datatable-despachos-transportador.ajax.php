<?php
session_start();

require_once "../controladores/despachos.controlador.php";

// Obtener despachos para el transportador actual
$despachos = ControladorDespachos::ctrMostrarDespachosTransportador($_SESSION["id"]);

$data = array();

foreach($despachos as $despacho) {
    
    // Generar botones de acción específicos para transportador
    $botones = generarBotonesAccionTransportador($despacho);
    
    $data[] = array(
        "numero_despacho" => $despacho["numero_despacho"],
        "sucursal_origen" => $despacho["sucursal_origen"],
        "total_productos" => $despacho["total_productos"],
        "total_cantidad" => $despacho["total_cantidad"],
        "estado" => generarEstadoDespacho($despacho),
        "fecha_creacion" => date("d/m/Y H:i", strtotime($despacho["fecha_creacion"])),
        "acciones" => $botones
    );
}

echo json_encode(array("data" => $data));

function generarBotonesAccionTransportador($despacho) {
    
    $botones = "";
    
    // Botón de detalles (siempre visible)
    $botones .= '<button class="btn btn-info btn-xs btnVerDetalle" 
                        data-id="' . $despacho["id"] . '"
                        data-toggle="tooltip" 
                        title="Ver detalles">
                    <i class="fa fa-eye"></i>
                </button> ';
    
    // Botón de aceptar (solo para pendientes)
    if($despacho["estado"] == "pendiente") {
        $botones .= '<button class="btn btn-success btn-xs btnAceptarDespacho" 
                            data-id="' . $despacho["id"] . '"
                            data-toggle="tooltip" 
                            title="Aceptar despacho">
                        <i class="fa fa-check"></i>
                    </button> ';
    }
    
    // Botón de cancelar (solo para pendientes)
    if($despacho["estado"] == "pendiente") {
        $botones .= '<button class="btn btn-warning btn-xs btnCancelarDespacho" 
                            data-id="' . $despacho["id"] . '"
                            data-toggle="tooltip" 
                            title="Cancelar despacho">
                        <i class="fa fa-times"></i>
                    </button> ';
    }
    
    // Botón de stock en tránsito (solo para en_transito asignados a él)
    if($despacho["estado"] == "en_transito" && $despacho["transportador_id"] == $_SESSION["id"]) {
        $botones .= '<a href="stock-transito" class="btn btn-primary btn-xs" 
                        data-toggle="tooltip" 
                        title="Ver stock en tránsito">
                        <i class="fa fa-truck"></i>
                    </a> ';
    }
    
    return $botones;
}

function generarEstadoDespacho($despacho) {
    
    $estado = $despacho["estado"];
    $transportadorId = $despacho["transportador_id"];
    $usuarioId = $_SESSION["id"];
    
    $html = "";
    
    switch($estado) {
        case "pendiente":
            $html = '<span class="label label-warning">
                        <i class="fa fa-clock-o"></i> Pendiente de Aceptación
                     </span>';
            break;
            
        case "en_transito":
            if($transportadorId == $usuarioId) {
                $html = '<span class="label label-info">
                            <i class="fa fa-truck"></i> En Mi Poder
                         </span>';
            } else {
                $html = '<span class="label label-default">
                            <i class="fa fa-user"></i> Asignado a Otro
                         </span>';
            }
            break;
            
        case "entregado":
            $html = '<span class="label label-success">
                        <i class="fa fa-check"></i> Entregado
                     </span>';
            break;
            
        case "cancelado":
            $html = '<span class="label label-danger">
                        <i class="fa fa-times"></i> Cancelado
                     </span>';
            break;
            
        default:
            $html = '<span class="label label-default">' . ucfirst($estado) . '</span>';
    }
    
    return $html;
}
?>
