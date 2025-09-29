<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "api-transferencias/conexion-central.php";

class TablaStockTransito {

    public function mostrarTablaStockTransito() {
        
        try {
            $perfilUsuario = $_SESSION["perfil"] ?? "Invitado";
            $idUsuario = $_SESSION["id"] ?? 0;
            
            // Consulta simple que funciona con tu JS actual
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT * FROM stock_transito 
                WHERE cantidad_disponible > 0 
                ORDER BY fecha_carga DESC
            ");
            
            $stmt->execute();
            $stockTransito = $stmt->fetchAll();

            if(count($stockTransito) == 0) {
                echo '{"data": []}';
                return;
            }

            $datosJson = '{"data": [';

            foreach($stockTransito as $key => $value) {

                // Botones compatibles con tu JS
                $botones = '<div class="btn-group">';
                $botones .= '<button class="btn btn-info btn-xs btnVerHistorialProducto" 
                                    codigoProducto="'.$value["codigo_producto"].'"
                                    transportadorId="'.$value["transportador_id"].'">
                                <i class="fa fa-history"></i>
                            </button>';
                
                if($perfilUsuario != "Transportador") {
                    $botones .= '<button class="btn btn-success btn-xs btnSolicitarDescarga" 
                                        idStockTransito="'.$value["id"].'"
                                        codigoProducto="'.$value["codigo_producto"].'"
                                        descripcionProducto="'.htmlspecialchars($value["descripcion_producto"]).'"
                                        cantidadDisponible="'.$value["cantidad_disponible"].'"
                                        transportadorId="'.$value["transportador_id"].'"
                                        nombreTransportador="'.$value["nombre_transportador"].'"
                                        sucursalOrigen="'.$value["sucursal_origen"].'">
                                    <i class="fa fa-download"></i>
                                </button>';
                }
                $botones .= '</div>';

                $fechaCarga = date('d/m/Y H:i', strtotime($value["fecha_carga"]));

                $datosJson .= '[
                    "'.($key+1).'",
                    "'.$value["codigo_producto"].'",
                    "'.substr($value["descripcion_producto"], 0, 40).'...",
                    "<div class=\"text-center\"><span class=\"stock-disponible\">'.$value["cantidad_disponible"].'</span></div>",
                    "'.$value["nombre_transportador"].'",
                    "'.$value["sucursal_origen"].'",
                    "'.$fechaCarga.'",
                    "-",
                    "'.$botones.'"
                ],';
            }

            $datosJson = substr($datosJson, 0, -1);
            $datosJson .= ']}';

            echo $datosJson;
            
        } catch(Exception $e) {
            echo '{"data": [], "error": "' . $e->getMessage() . '"}';
        }
    }
}

// Ejecutar directamente - compatible con tu JS
$activarStock = new TablaStockTransito();
$activarStock->mostrarTablaStockTransito();

?>