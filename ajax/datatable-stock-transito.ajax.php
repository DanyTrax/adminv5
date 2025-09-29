<?php

session_start();

require_once "api-transferencias/conexion-central.php";

class TablaStockTransito {

    /*=============================================
    MOSTRAR LA TABLA DE STOCK EN TRÁNSITO
    =============================================*/
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

                /*=============================================
                BOTONES DE ACCIONES SEGÚN PERFIL
                =============================================*/
                $botones = $this->generarBotonesAccion($value);

                /*=============================================
                FECHA FORMATEADA
                =============================================*/
                $fechaCarga = date('d/m/Y H:i', strtotime($value["fecha_carga"]));

                /*=============================================
                CONSTRUIR FILA JSON - IGUAL QUE DESPACHOS
                =============================================*/
                $datosJson .= '[
                    "' . ($key + 1) . '",
                    "' . htmlspecialchars($value["codigo_producto"]) . '",
                    "' . htmlspecialchars(substr($value["descripcion_producto"], 0, 40) . "...") . '",
                    "' . $value["cantidad_disponible"] . '",
                    "' . htmlspecialchars($value["nombre_transportador"]) . '",
                    "' . htmlspecialchars($value["sucursal_origen"]) . '",
                    "' . $fechaCarga . '",
                    "' . $botones . '"
                ],';
            }

            $datosJson = substr($datosJson, 0, -1);
            $datosJson .= ']}';

            echo $datosJson;

        } catch(Exception $e) {
            echo '{"data": [], "error": "' . $e->getMessage() . '"}';
        }
    }

    /*=============================================
    GENERAR BOTONES DE ACCIÓN - IGUAL QUE DESPACHOS
    =============================================*/
    private function generarBotonesAccion($stock) {
        
        $botones = '';
        $perfil = $_SESSION["perfil"];
        
        // Botón Ver historial - TODOS
        $botones .= '<button class="btn btn-info btn-xs btnVerHistorial" codigoProducto="' . $stock["codigo_producto"] . '" title="Ver historial"><i class="fa fa-history"></i></button>';
        
        // Botón Solicitar descarga - Solo NO transportadores
        if($perfil != "Transportador") {
            $botones .= ' <button class="btn btn-success btn-xs btnSolicitarDescarga" 
                                idStockTransito="' . $stock["id"] . '"
                                codigoProducto="' . htmlspecialchars($stock["codigo_producto"]) . '"
                                descripcionProducto="' . htmlspecialchars($stock["descripcion_producto"]) . '"
                                cantidadDisponible="' . $stock["cantidad_disponible"] . '"
                                nombreTransportador="' . htmlspecialchars($stock["nombre_transportador"]) . '"
                                sucursalOrigen="' . htmlspecialchars($stock["sucursal_origen"]) . '"
                                title="Solicitar descarga">
                            <i class="fa fa-download"></i>
                        </button>';
        }
        
        return $botones;
    }
}

/*=============================================
INSTANCIAR CLASE Y MOSTRAR TABLA - IGUAL QUE DESPACHOS
=============================================*/
$tablaStockTransito = new TablaStockTransito();
$tablaStockTransito->mostrarTablaStockTransito();

?>