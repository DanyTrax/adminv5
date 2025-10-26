<?php
/*=============================================
AJAX REGISTRO DE DESCARGAS SIMPLE - FUNCIONAL
=============================================*/

// Incluir controlador si no existe
if (!class_exists('ControladorRegistroDescargasSimple')) {
    require_once __DIR__ . "/../controladores/registro-descargas-simple.controlador.php";
}

// Incluir modelo si no existe
if (!class_exists('ModeloRegistroDescargasSimple')) {
    require_once __DIR__ . "/../modelos/registro-descargas-simple.modelo.php";
}

// Incluir conexión
require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../api-transferencias/conexion-central.php";

// Verificar que se especificó una acción
if(isset($_POST["accion"])) {
    try {
        switch($_POST["accion"]) {
            case "registrar_descarga":
                $controlador = new ControladorRegistroDescargasSimple();
                $resultado = $controlador->ctrRegistrarDescarga();
                echo json_encode($resultado);
                break;
                
            case "obtener_registro":
                $controlador = new ControladorRegistroDescargasSimple();
                $filtros = [
                    'producto' => isset($_POST['producto']) ? $_POST['producto'] : '',
                    'usuario' => isset($_POST['usuario']) ? $_POST['usuario'] : '',
                    'fecha_desde' => isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : '',
                    'fecha_hasta' => isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : ''
                ];
                $registros = $controlador->ctrObtenerRegistro($filtros);
                echo json_encode(["success" => true, "data" => $registros]);
                break;
                
            case "obtener_estadisticas":
                $controlador = new ControladorRegistroDescargasSimple();
                $estadisticas = $controlador->ctrObtenerEstadisticas();
                echo json_encode(["success" => true, "data" => $estadisticas]);
                break;
                
            default:
                echo json_encode(["success" => false, "error" => "Acción no reconocida"]);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "No se especificó acción"]);
}
?>