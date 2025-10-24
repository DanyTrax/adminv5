<?php
/*=============================================
AJAX REGISTRO DE DESCARGAS SIMPLE - REGISTRO DIRECTO
=============================================*/

// Cargar controlador solo si no está cargado
if (!class_exists("ControladorRegistroDescargasSimple")) {
    require_once "../controladores/registro-descargas-simple.controlador.php";
}

$registroDescargas = new ControladorRegistroDescargasSimple();

if(isset($_POST["accion"])) {
    switch($_POST["accion"]) {
        case "registrar_descarga":
            // Registrar descarga directamente
            $datos = array(
                "codigo_producto" => $_POST["codigo_producto"],
                "descripcion_producto" => $_POST["descripcion_producto"] ?? "",
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
            break;
            
        case "obtener_registro":
            $filtros = [
                "fecha_desde" => $_POST["fecha_desde"] ?? "",
                "fecha_hasta" => $_POST["fecha_hasta"] ?? "",
                "usuario_id" => $_POST["usuario_id"] ?? "",
                "sucursal_id" => $_POST["sucursal_id"] ?? "",
                "codigo_producto" => $_POST["codigo_producto"] ?? "",
                "busqueda_general" => $_POST["busqueda_general"] ?? ""
            ];
            
            $registros = $registroDescargas->ctrObtenerRegistro($filtros);
            echo json_encode($registros);
            break;
            
        case "obtener_estadisticas":
            $filtros = [
                "fecha_desde" => $_POST["fecha_desde"] ?? "",
                "fecha_hasta" => $_POST["fecha_hasta"] ?? "",
                "usuario_id" => $_POST["usuario_id"] ?? "",
                "sucursal_id" => $_POST["sucursal_id"] ?? ""
            ];
            
            $estadisticas = $registroDescargas->ctrObtenerEstadisticas($filtros);
            echo json_encode($estadisticas);
            break;
            
        default:
            echo json_encode(["success" => false, "error" => "Acción no reconocida"]);
            break;
    }
} else {
    echo json_encode(["success" => false, "error" => "No se especificó acción"]);
}
?>