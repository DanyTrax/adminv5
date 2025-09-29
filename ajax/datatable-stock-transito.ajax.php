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

                $botones = '<div class="btn-group">';
                
                // Ver historial
                $botones .= '<button class="btn btn-info btn-xs btnVerHistorial" 
                                    data-codigo="'.$value["codigo_producto"].'"
                                    data-transportador="'.$value["transportador_id"].'" 
                                    title="Ver historial">
                                <i class="fa fa-history"></i>
                            </button>';
                
                // Solicitar descarga
                if($perfilUsuario != "Transportador") {
                    $botones .= '<button class="btn btn-success btn-xs btnSolicitarDescarga" 
                                        data-id="'.$value["id"].'"
                                        data-codigo="'.$value["codigo_producto"].'"
                                        data-descripcion="'.htmlspecialchars($value["descripcion_producto"]).'"
                                        data-cantidad="'.$value["cantidad_disponible"].'"
                                        data-transportador="'.$value["nombre_transportador"].'"
                                        data-origen="'.$value["sucursal_origen"].'"
                                        title="Solicitar descarga">
                                    <i class="fa fa-download"></i>
                                </button>';
                }
                
                $botones .= '</div>';

                $fechaCarga = date('d/m/Y H:i', strtotime($value["fecha_carga"]));

                $datosJson .= '[
                    "'.($key+1).'",
                    "'.$value["codigo_producto"].'",
                    "'.substr($value["descripcion_producto"], 0, 40).'...",
                    "<span class=\"label label-primary\">'.$value["cantidad_disponible"].'</span>",
                    "'.$value["nombre_transportador"].'",
                    "'.$value["sucursal_origen"].'",
                    "'.$fechaCarga.'",
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

$activarStock = new TablaStockTransito();
$activarStock->mostrarTablaStockTransito();

?>