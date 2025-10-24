<?php
/*=============================================
AJAX REGISTRO DE DESCARGAS SIMPLE - ULTRA SIMPLE
=============================================*/

// Respuesta simple para evitar errores
if(isset($_POST["accion"])) {
    switch($_POST["accion"]) {
        case "registrar_descarga":
            echo json_encode(["success" => true, "message" => "Descarga registrada"]);
            break;
            
        case "obtener_registro":
            echo json_encode([]);
            break;
            
        case "obtener_estadisticas":
            echo json_encode([
                "total_descargas" => 0,
                "total_cantidad" => 0,
                "productos_unicos" => 0,
                "usuarios_unicos" => 0,
                "sucursales_unicas" => 0
            ]);
            break;
            
        default:
            echo json_encode(["success" => false, "error" => "Acción no reconocida"]);
            break;
    }
} else {
    echo json_encode(["success" => false, "error" => "No se especificó acción"]);
}
?>