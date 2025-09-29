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

            $data = [];

            foreach($stockTransito as $key => $value) {

                $botones = '<div class="btn-group">';
                
                $botones .= '<button class="btn btn-info btn-xs btnVerHistorial" 
                                    data-codigo="'.$value["codigo_producto"].'" 
                                    title="Ver historial">
                                <i class="fa fa-history"></i>
                            </button>';
                
                if($perfilUsuario != "Transportador") {
                    $descripcionLimpia = str_replace('"', '&quot;', $value["descripcion_producto"]);
                    $transportadorLimpio = str_replace('"', '&quot;', $value["nombre_transportador"]);
                    $origenLimpio = str_replace('"', '&quot;', $value["sucursal_origen"]);
                    
                    $botones .= '<button class="btn btn-success btn-xs btnSolicitarDescarga" 
                                        data-id="'.$value["id"].'"
                                        data-codigo="'.$value["codigo_producto"].'"
                                        data-descripcion="'.$descripcionLimpia.'"
                                        data-cantidad="'.$value["cantidad_disponible"].'"
                                        data-transportador="'.$transportadorLimpio.'"
                                        data-origen="'.$origenLimpio.'"
                                        title="Solicitar descarga">
                                    <i class="fa fa-download"></i>
                                </button>';
                }
                
                $botones .= '</div>';

                $fechaCarga = date('d/m/Y H:i', strtotime($value["fecha_carga"]));

                $data[] = [
                    ($key+1),
                    $value["codigo_producto"],
                    substr($value["descripcion_producto"], 0, 40)."...",
                    '<span class="label label-primary">'.$value["cantidad_disponible"].'</span>',
                    $value["nombre_transportador"],
                    $value["sucursal_origen"],
                    $fechaCarga,
                    $botones
                ];
            }

            header('Content-Type: application/json');
            echo json_encode(["data" => $data]);
            
        } catch(Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(["data" => [], "error" => $e->getMessage()]);
        }
    }
}

$activarStock = new TablaStockTransito();
$activarStock->mostrarTablaStockTransito();

?>