<?php
/*=============================================
CONTROLADOR REGISTRO DE DESCARGAS SIMPLE
=============================================*/

// Cargar modelo solo si no está cargado
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
                "transportador_id" => $_POST["transportador_id"] ?? null,
                "transportador_nombre" => $_POST["transportador_nombre"] ?? null,
                "numero_despacho" => $_POST["numero_despacho"] ?? null,
                "observaciones" => $_POST["observaciones"] ?? "",
                "ip_usuario" => $_SERVER["REMOTE_ADDR"] ?? "",
                "user_agent" => $_SERVER["HTTP_USER_AGENT"] ?? ""
            );

            $respuesta = ModeloRegistroDescargasSimple::mdlRegistrarDescarga($datos);

            if($respuesta == "ok") {
                echo json_encode(["success" => true, "message" => "Descarga registrada exitosamente"]);
            } else {
                echo json_encode(["success" => false, "error" => "Error al registrar la descarga"]);
            }
        }
    }

    /*=============================================
    OBTENER REGISTRO DE DESCARGAS
    =============================================*/
    public function ctrObtenerRegistro($filtros = []) {
        $respuesta = ModeloRegistroDescargasSimple::mdlObtenerTodasDescargas($filtros);
        return $respuesta;
    }

    /*=============================================
    OBTENER ESTADÍSTICAS
    =============================================*/
    public function ctrObtenerEstadisticas($filtros = []) {
        $respuesta = ModeloRegistroDescargasSimple::mdlObtenerEstadisticasDescargas($filtros);
        return $respuesta;
    }
}
?>
