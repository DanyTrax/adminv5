<?php

session_start();

require_once "../api-transferencias/conexion-central.php";

class AjaxTablaDespachos {

/*=============================================
MOSTRAR LA TABLA DE DESPACHOS - CON NOMBRE REAL DE SUCURSAL
=============================================*/
public function mostrarTablaDespachos() {

    try {
        // CONSULTA CON JOIN PARA OBTENER NOMBRE REAL DE SUCURSAL
        $stmt = ConexionCentral::conectar()->prepare("
            SELECT 
                d.*,
                COALESCE(s.nombre_sucursal, d.sucursal_origen) as nombre_sucursal_real,
                CASE 
                    WHEN d.estado = 'pendiente' THEN 0
                    WHEN d.estado = 'aceptado' THEN 1
                    WHEN d.estado = 'en_transito' THEN 2
                    WHEN d.estado = 'finalizado' THEN 3
                    ELSE 4
                END as orden_estado
            FROM despachos d 
            LEFT JOIN sucursal_local s ON s.codigo_sucursal = d.sucursal_origen
            ORDER BY orden_estado ASC, d.fecha_creacion DESC
        ");

        $stmt->execute();
        $despachos = $stmt->fetchAll();

        if(count($despachos) == 0) {
            echo '{"data": []}';
            return;
        }

        $datosJson = '{
            "data": [';

        foreach($despachos as $key => $value) {

            /*=============================================
            BOTONES DE ACCIONES SEGÚN PERFIL
            =============================================*/
            $botones = $this->generarBotonesAccion($value);

            /*=============================================
            ESTADO CON COLOR
            =============================================*/
            $estado = $this->formatearEstado($value["estado"]);

            /*=============================================
            FECHA FORMATEADA
            =============================================*/
            $fechaCreacion = date('d/m/Y H:i', strtotime($value["fecha_creacion"]));

            /*=============================================
            TRANSPORTADOR
            =============================================*/
            $transportador = $value["nombre_transportador"] ?: 'Sin asignar';

            /*=============================================
            NOMBRE REAL DE LA SUCURSAL
            =============================================*/
            $sucursalOrigen = $value["nombre_sucursal_real"] ?: 'Sucursal no especificada';

            /*=============================================
            CONSTRUIR FILA JSON - SIN SALTOS DE LÍNEA
            =============================================*/
            $datosJson .= '[
                "' . ($key + 1) . '",
                "' . htmlspecialchars($value["numero_despacho"]) . '",
                "' . htmlspecialchars($sucursalOrigen) . '",
                "' . htmlspecialchars($value["nombre_usuario_creador"]) . '",
                "' . $estado . '",
                "' . $value["total_productos"] . '",
                "' . number_format($value["total_cantidad"]) . '",
                "' . htmlspecialchars($transportador) . '",
                "' . $fechaCreacion . '",
                "' . $botones . '"
            ],';
        }

        $datosJson = substr($datosJson, 0, -1);
        $datosJson .= ']}';

        echo $datosJson;

    } catch(Exception $e) {
        echo '{"data": [], "error": "' . $e->getMessage() . '"}';
    }
}

/*=============================================
GENERAR BOTONES DE ACCIÓN - VERSIÓN CORREGIDA
=============================================*/
private function generarBotonesAccion($despacho) {
    
    $botones = '';
    $perfil = $_SESSION["perfil"];
    $estado = $despacho["estado"];
    
    // Botón Ver detalles
    $botones .= '<button class=\"btn btn-info btn-xs btnVerDespacho\" idDespacho=\"' . $despacho["id"] . '\" title=\"Ver detalles\"><i class=\"fa fa-eye\"></i></button>';
    
    if($perfil == "Administrador" || $perfil == "Transportador") {
        
        // Botón Aceptar (para Transportadores y Administradores) - Solo pendientes
        if($estado == "pendiente") {
            $botones .= ' <button class=\"btn btn-success btn-xs btnAceptarDespacho\" idDespacho=\"' . $despacho["id"] . '\" title=\"Aceptar despacho\"><i class=\"fa fa-check\"></i></button>';
        }
        
        // Botón Editar (solo si está pendiente)
        if($estado == "pendiente") {
            $botones .= ' <button class=\"btn btn-warning btn-xs btnEditarDespacho\" idDespacho=\"' . $despacho["id"] . '\" title=\"Editar despacho\"><i class=\"fa fa-pencil\"></i></button>';
        }
        
        // Botón Cancelar (solo si está pendiente)
        if($estado == "pendiente") {
            $botones .= ' <button class=\"btn btn-warning btn-xs btnCancelarDespacho\" idDespacho=\"' . $despacho["id"] . '\" estadoDespacho=\"' . $estado . '\" title=\"Cancelar despacho\"><i class=\"fa fa-ban\"></i></button>';
        }
        
        // Botón Eliminar (solo administrador y pendientes)
        if($perfil == "Administrador" && $estado == "pendiente") {
            $botones .= ' <button class=\"btn btn-danger btn-xs btnEliminarDespacho\" idDespacho=\"' . $despacho["id"] . '\" numeroDespacho=\"' . $despacho["numero_despacho"] . '\" title=\"Eliminar despacho\"><i class=\"fa fa-trash\"></i></button>';
        }
    }
    
    return $botones;
}
    
    /*=============================================
    FORMATEAR ESTADO CON COLOR
    =============================================*/
    private function formatearEstado($estado) {
        
        switch(strtolower($estado)) {
            case 'pendiente':
                return '<span class=\"label label-warning\">PENDIENTE</span>';
            case 'aceptado':
                return '<span class=\"label label-info\">ACEPTADO</span>';
            case 'en_transito':
                return '<span class=\"label label-primary\">EN TRÁNSITO</span>';
            case 'finalizado':
            case 'entregado':
                return '<span class=\"label label-success\">ENTREGADO</span>';
            case 'cancelado':
                return '<span class=\"label label-danger\">CANCELADO</span>';
            default:
                return '<span class=\"label label-default\">' . strtoupper($estado) . '</span>';
        }
    }
}

/*=============================================
INSTANCIAR CLASE Y MOSTRAR TABLA
=============================================*/
$tablaDespachos = new AjaxTablaDespachos();
$tablaDespachos->mostrarTablaDespachos();

?>