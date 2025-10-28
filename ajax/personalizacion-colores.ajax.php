<?php
/*=============================================
AJAX PERSONALIZACIÓN DE COLORES
=============================================*/

session_start();

require_once "../controladores/personalizacion-colores.controlador.php";
require_once "../modelos/personalizacion-colores.modelo.php";

if(isset($_POST["accion"])) {
    
    switch($_POST["accion"]) {
        
        case "obtenerConfiguracionActiva":
            $configuracion = ControladorPersonalizacionColores::ctrMostrarConfiguracionActiva();
            echo json_encode([
                "success" => true,
                "configuracion" => $configuracion
            ]);
            break;
            
        case "actualizarConfiguracion":
            if(isset($_POST["datos"])) {
                $datos = json_decode($_POST["datos"], true);
                
                // Validar campos requeridos
                $camposRequeridos = [
                    'nombre_configuracion',
                    'navbar_color',
                    'navbar_text_color',
                    'sidebar_color',
                    'sidebar_text_color',
                    'logo_mini_color',
                    'logo_lg_color',
                    'icon_color',
                    'login_gradient_start',
                    'login_gradient_end',
                    'login_logo_color',
                    'login_text_color'
                ];
                
                $valido = true;
                $mensaje = "";
                
                foreach ($camposRequeridos as $campo) {
                    if (empty($datos[$campo])) {
                        $valido = false;
                        $mensaje = "El campo '$campo' es requerido";
                        break;
                    }
                }
                
                if ($valido) {
                    // Validar formato de colores hexadecimales
                    $patronColor = '/^#[0-9A-Fa-f]{6}$/';
                    foreach ($camposRequeridos as $campo) {
                        if ($campo !== 'nombre_configuracion' && !preg_match($patronColor, $datos[$campo])) {
                            $valido = false;
                            $mensaje = "El campo '$campo' debe tener un formato de color hexadecimal válido (#RRGGBB)";
                            break;
                        }
                    }
                }
                
                if ($valido) {
                    $resultado = ModeloPersonalizacionColores::mdlActualizarConfiguracion($datos);
                    echo json_encode($resultado);
                } else {
                    echo json_encode([
                        "success" => false,
                        "error" => $mensaje
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "error" => "No se recibieron datos"
                ]);
            }
            break;
            
        case "activarConfiguracion":
            if(isset($_POST["id"])) {
                $id = intval($_POST["id"]);
                $resultado = ModeloPersonalizacionColores::mdlActivarConfiguracion($id);
                echo json_encode($resultado);
            } else {
                echo json_encode([
                    "success" => false,
                    "error" => "ID de configuración requerido"
                ]);
            }
            break;
            
        case "eliminarConfiguracion":
            if(isset($_POST["id"])) {
                $id = intval($_POST["id"]);
                $resultado = ModeloPersonalizacionColores::mdlEliminarConfiguracion($id);
                echo json_encode($resultado);
            } else {
                echo json_encode([
                    "success" => false,
                    "error" => "ID de configuración requerido"
                ]);
            }
            break;
            
        case "obtenerTodasConfiguraciones":
            $configuraciones = ControladorPersonalizacionColores::ctrObtenerTodasConfiguraciones();
            echo json_encode([
                "success" => true,
                "configuraciones" => $configuraciones
            ]);
            break;
            
        case "aplicarEstilos":
            $estilos = ControladorPersonalizacionColores::ctrAplicarConfiguracionEstilos();
            echo json_encode([
                "success" => true,
                "estilos" => $estilos
            ]);
            break;
            
        default:
            echo json_encode([
                "success" => false,
                "error" => "Acción no válida"
            ]);
            break;
    }
} else {
    echo json_encode([
        "success" => false,
        "error" => "No se especificó una acción"
    ]);
}
?>
