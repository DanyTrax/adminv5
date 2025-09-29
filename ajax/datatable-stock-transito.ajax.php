<?php

session_start();

require_once "api-transferencias/conexion-central.php";

class TablaStockTransito {

    public function mostrarTablaStockTransito() {

        try {
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

            $datosJson = '{
                "data": [';

            foreach($stockTransito as $key => $value) {

                $botones = $this->generarBotonesAccion($value);
                $fechaCarga = date('d/m/Y H:i', strtotime($value["fecha_carga"]));

                // ESCAPAR CORRECTAMENTE TODAS LAS COMILLAS
                $codigo = addslashes($value["codigo_producto"]);
                $descripcion = addslashes(substr($value["descripcion_producto"], 0, 40) . "...");
                $transportador = addslashes($value["nombre_transportador"]);
                $origen = addslashes($value["sucursal_origen"]);
                $botonesEscapados = addslashes($botones);

                $datosJson .= '[
                    "' . ($key + 1) . '",
                    "' . $codigo . '",
                    "' . $descripcion . '",
                    "' . $value["cantidad_disponible"] . '",
                    "' . $transportador . '",
                    "' . $origen . '",
                    "' . $fechaCarga . '",
                    "' . $botonesEscapados . '"
                ],';
            }

            $datosJson = substr($datosJson, 0, -1);
            $datosJson .= ']}';

            echo $datosJson;

        } catch(Exception $e) {
            echo '{"data": [], "error": "' . addslashes($e->getMessage()) . '"}';
        }
    }

    private function generarBotonesAccion($stock) {
        
        $perfil = $_SESSION["perfil"];
        
        $botones = '<button class=\'btn btn-info btn-xs btnVerHistorial\' codigoProducto=\'' . $stock["codigo_producto"] . '\' title=\'Ver historial\'><i class=\'fa fa-history\'></i></button>';
        
        if($perfil != "Transportador") {
            $botones .= ' <button class=\'btn btn-success btn-xs btnSolicitarDescarga\' 
                                idStockTransito=\'' . $stock["id"] . '\'
                                codigoProducto=\'' . $stock["codigo_producto"] . '\'
                                descripcionProducto=\'' . str_replace("'", "&#39;", $stock["descripcion_producto"]) . '\'
                                cantidadDisponible=\'' . $stock["cantidad_disponible"] . '\'
                                nombreTransportador=\'' . str_replace("'", "&#39;", $stock["nombre_transportador"]) . '\'
                                sucursalOrigen=\'' . str_replace("'", "&#39;", $stock["sucursal_origen"]) . '\'
                                title=\'Solicitar descarga\'>
                            <i class=\'fa fa-download\'></i>
                        </button>';
        }
        
        return $botones;
    }
}

$tablaStockTransito = new TablaStockTransito();
$tablaStockTransito->mostrarTablaStockTransito();

?>