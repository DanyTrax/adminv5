<?php
/*=============================================
CONTROLADOR REGISTRO DE DESCARGAS SIMPLE - FUNCIONAL
=============================================*/

// Incluir modelo si no existe
if (!class_exists('ModeloRegistroDescargasSimple')) {
    require_once __DIR__ . "/../modelos/registro-descargas-simple.modelo.php";
}

class ControladorRegistroDescargasSimple {
    
    /*=============================================
    REGISTRAR DESCARGA
    =============================================*/
    public function ctrRegistrarDescarga() {
        // Verificar que se recibieron los datos necesarios
        if(isset($_POST["codigo_producto"]) && isset($_POST["cantidad_descargada"])) {
            $datos = array(
                "codigo_producto" => $_POST["codigo_producto"],
                "descripcion_producto" => isset($_POST["descripcion_producto"]) ? $_POST["descripcion_producto"] : "",
                "cantidad_descargada" => $_POST["cantidad_descargada"],
                "usuario_id" => isset($_POST["usuario_id"]) ? $_POST["usuario_id"] : "0",
                "usuario_nombre" => isset($_POST["usuario_nombre"]) ? $_POST["usuario_nombre"] : "Usuario",
                "sucursal_id" => isset($_POST["sucursal_id"]) ? $_POST["sucursal_id"] : "1",
                "sucursal_nombre" => isset($_POST["sucursal_nombre"]) ? $_POST["sucursal_nombre"] : "Local Pruebas",
                "transportador_id" => isset($_POST["transportador_id"]) ? $_POST["transportador_id"] : "0",
                "transportador_nombre" => isset($_POST["transportador_nombre"]) ? $_POST["transportador_nombre"] : "",
                "numero_despacho" => isset($_POST["numero_despacho"]) ? $_POST["numero_despacho"] : "",
                "observaciones" => isset($_POST["observaciones"]) ? $_POST["observaciones"] : ""
            );
            
            $respuesta = ModeloRegistroDescargasSimple::mdlRegistrarDescarga($datos);
            
            if($respuesta == "ok"){
                return ["success" => true, "message" => "Descarga registrada correctamente"];
            } else {
                return ["success" => false, "error" => $respuesta];
            }
        }
        
        return ["success" => false, "error" => "Datos incompletos: código_producto y cantidad_descargada son requeridos"];
    }
    
    /*=============================================
    OBTENER REGISTRO
    =============================================*/
    public function ctrObtenerRegistro($filtros = []) {
        $respuesta = ModeloRegistroDescargasSimple::mdlObtenerRegistro($filtros);
        return $respuesta;
    }
    
    /*=============================================
    OBTENER ESTADÍSTICAS
    =============================================*/
    public function ctrObtenerEstadisticas($filtros = []) {
        $respuesta = ModeloRegistroDescargasSimple::mdlObtenerEstadisticas($filtros);
        return $respuesta;
    }
}
?>