<?php

session_start();

require_once "../controladores/personalizacion-cotizaciones.controlador.php";

if (isset($_POST["accion"])) {
    
    switch ($_POST["accion"]) {
        
        case "obtener_configuracion":
            
            if (isset($_POST["id"])) {
                $configuracion = ControladorPersonalizacionCotizaciones::ctrObtenerConfiguracion($_POST["id"]);
                
                if ($configuracion) {
                    echo json_encode([
                        "success" => true,
                        "configuracion" => $configuracion
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "mensaje" => "Configuración no encontrada"
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "ID no proporcionado"
                ]);
            }
            
            break;
            
        case "obtener_sucursales":
            
            $sucursales = ControladorPersonalizacionCotizaciones::ctrObtenerSucursales();
            
            echo json_encode([
                "success" => true,
                "sucursales" => $sucursales
            ]);
            
            break;
            
        default:
            echo json_encode([
                "success" => false,
                "mensaje" => "Acción no válida"
            ]);
            break;
    }
    
} else {
    echo json_encode([
        "success" => false,
        "mensaje" => "No se especificó una acción"
    ]);
}

?>

