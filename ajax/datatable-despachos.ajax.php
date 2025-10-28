<?php

// Iniciar buffer de salida para capturar warnings
ob_start();

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Establecer headers JSON solo si no se han enviado headers aún
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
} else {
    // Si los headers ya se enviaron, limpiar el buffer y empezar de nuevo
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
}

require_once "../api-transferencias/conexion-central.php";

class AjaxTablaDespachos {

/*=============================================
MOSTRAR LA TABLA DE DESPACHOS - SIN JOIN A BD LOCAL
=============================================*/
public function mostrarTablaDespachos() {

    try {
        $stmt = ConexionCentral::conectar()->prepare("
            SELECT 
                d.*,
                CASE 
                    WHEN d.estado = 'pendiente' THEN 0
                    WHEN d.estado = 'aceptado' THEN 1
                    WHEN d.estado = 'en_transito' THEN 2
                    WHEN d.estado = 'finalizado' THEN 3
                    ELSE 4
                END as orden_estado
            FROM despachos d 
            ORDER BY orden_estado ASC, d.fecha_creacion DESC
        ");

        $stmt->execute();
        $despachos = $stmt->fetchAll();

        if(count($despachos) == 0) {
            ob_clean(); // Limpiar buffer de warnings
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
            NOMBRE DE SUCURSAL - OBTENIDO DE BD LOCAL
            =============================================*/
            $sucursalOrigen = $this->obtenerNombreSucursal($value["sucursal_origen"]);

            /*=============================================
            CONSTRUIR FILA JSON
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
        
        ob_clean(); // Limpiar buffer de warnings
        echo $datosJson;

    } catch(Exception $e) {
        ob_clean(); // Limpiar buffer de warnings
        echo '{"data": [], "error": "' . $e->getMessage() . '"}';
    }
}

/*=============================================
OBTENER NOMBRE DE SUCURSAL DESDE BD LOCAL
=============================================*/
private function obtenerNombreSucursal($codigoSucursal) {
    
    try {
        require_once "../modelos/conexion.php";
        
        $stmt = Conexion::conectar()->prepare("
            SELECT nombre_sucursal 
            FROM sucursal_local 
            WHERE codigo_sucursal = :codigo 
            LIMIT 1
        ");
        
        $stmt->bindParam(":codigo", $codigoSucursal);
        $stmt->execute();
        $sucursal = $stmt->fetch();
        
        if($sucursal) {
            return $sucursal["nombre_sucursal"];
        } else {
            // Si no se encuentra, usar el código como fallback
            return $codigoSucursal ?: 'Sucursal no especificada';
        }
        
    } catch(Exception $e) {
        // En caso de error, devolver el código original
        return $codigoSucursal ?: 'Sucursal no especificada';
    }
}

/*=============================================
GENERAR BOTONES DE ACCIÓN - VERSIÓN CORREGIDA
=============================================*/
private function generarBotonesAccion($despacho) {
    
    $botones = '';
    $perfil = isset($_SESSION["perfil"]) ? $_SESSION["perfil"] : 'Usuario';
    $estado = $despacho["estado"];
    
    // Botón Ver detalles
    $botones .= '<button class=\"btn btn-info btn-xs btnVerDespacho\" idDespacho=\"' . $despacho["id"] . '\" title=\"Ver detalles\"><i class=\"fa fa-eye\"></i></button>';
    
    // Botón Aceptar (para Transportadores y Administradores) - Solo pendientes
    if(($perfil == "Administrador" || $perfil == "Transportador") && $estado == "pendiente") {
        $botones .= ' <button class=\"btn btn-success btn-xs btnAceptarDespacho\" idDespacho=\"' . $despacho["id"] . '\" title=\"Aceptar despacho\"><i class=\"fa fa-check\"></i></button>';
    }
    
    // Botón Cancelar (para Administradores y Transportadores) - Solo pendientes
    if(($perfil == "Administrador" || $perfil == "Transportador") && $estado == "pendiente") {
        $botones .= ' <button class=\"btn btn-warning btn-xs btnCancelarDespacho\" idDespacho=\"' . $despacho["id"] . '\" estadoDespacho=\"' . $estado . '\" title=\"Cancelar despacho\"><i class=\"fa fa-ban\"></i></button>';
    }
    
    // Botón Editar (solo para Administrador, Especial, Contador) - Solo pendientes
    if(($perfil == "Administrador" || $perfil == "Especial" || $perfil == "Contador") && $estado == "pendiente") {
        $botones .= ' <button class=\"btn btn-warning btn-xs btnEditarDespacho\" idDespacho=\"' . $despacho["id"] . '\" title=\"Editar despacho\" onclick=\"editarDespacho(' . $despacho["id"] . ')\"><i class=\"fa fa-pencil\"></i></button>';
    }
    
    // Botón Eliminar (pendientes para Administrador/Especial/Contador, cualquier estado solo para Administrador)
    $puedeEliminar = (($perfil == "Administrador" || $perfil == "Especial" || $perfil == "Contador") && $estado == "pendiente") || ($perfil == "Administrador");
    
    if($puedeEliminar) {
        $textoEliminar = $estado == "pendiente" ? "Eliminar" : "Eliminar (Admin)";
        $claseBoton = $estado == "pendiente" ? "btn-danger" : "btn-warning";
        $titulo = $estado == "pendiente" ? "Eliminar despacho" : "Eliminar despacho (Admin)";
        
        $botones .= ' <button class=\"btn ' . $claseBoton . ' btn-xs btnEliminarDespacho\" idDespacho=\"' . $despacho["id"] . '\" numeroDespacho=\"' . $despacho["numero_despacho"] . '\" estadoDespacho=\"' . $estado . '\" title=\"' . $titulo . '\"><i class=\"fa fa-trash\"></i></button>';
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