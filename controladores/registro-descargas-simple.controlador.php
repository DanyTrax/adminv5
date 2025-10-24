<?php
/*=============================================
CONTROLADOR REGISTRO DE DESCARGAS SIMPLE - FUNCIONAL
=============================================*/

// Incluir modelo si no existe
if (!class_exists('ModeloRegistroDescargasSimple')) {
    require_once "modelos/registro-descargas-simple.modelo.php";
}

class ControladorRegistroDescargasSimple {
    
    /*=============================================
    REGISTRAR DESCARGA
    =============================================*/
    public function ctrRegistrarDescarga() {
        if(isset($_POST["registrarDescarga"])) {
            $datos = array(
                "codigo_producto" => $_POST["codigo_producto"],
                "descripcion_producto" => $_POST["descripcion_producto"],
                "cantidad_descargada" => $_POST["cantidad_descargada"],
                "usuario_id" => $_POST["usuario_id"],
                "usuario_nombre" => $_POST["usuario_nombre"],
                "sucursal_id" => $_POST["sucursal_id"],
                "sucursal_nombre" => $_POST["sucursal_nombre"],
                "transportador_id" => $_POST["transportador_id"],
                "transportador_nombre" => $_POST["transportador_nombre"],
                "numero_despacho" => $_POST["numero_despacho"],
                "observaciones" => $_POST["observaciones"]
            );
            
            $respuesta = ModeloRegistroDescargasSimple::mdlRegistrarDescarga($datos);
            
            if($respuesta == "ok"){
                return ["success" => true, "message" => "Descarga registrada correctamente"];
            } else {
                return ["success" => false, "error" => $respuesta];
            }
        }
        
        return ["success" => false, "error" => "Datos incompletos"];
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