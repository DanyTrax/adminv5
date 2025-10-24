<?php
/*=============================================
AJAX REGISTRO DE DESCARGAS SIMPLE
=============================================*/

// Cargar controlador solo si no está cargado
if (!class_exists('ControladorRegistroDescargasSimple')) {
    require_once "../controladores/registro-descargas-simple.controlador.php";
}

$registroDescargas = new ControladorRegistroDescargasSimple();

if(isset($_POST["accion"])) {
    switch($_POST["accion"]) {
        case "registrar_descarga":
            $registroDescargas->ctrRegistrarDescarga();
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
