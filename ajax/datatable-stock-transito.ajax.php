<?php

session_start();

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

class TablaStockTransito {

    public function mostrarTablaStockTransito() {

        try {
            // Si es transportador, solo mostrar su stock
            $filtroTransportador = "";
            if($_SESSION["perfil"] == "Transportador") {
                $filtroTransportador = "AND st.transportador_id = " . $_SESSION["id"];
            }

            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    st.*,
                    COALESCE(SUM(sd.cantidad_solicitada), 0) as cantidad_solicitada_pendiente,
                    COUNT(sd.id) as solicitudes_pendientes
                FROM stock_transito st
                LEFT JOIN solicitudes_descarga sd ON st.id = sd.id_stock_transito 
                    AND sd.estado = 'pendiente'
                WHERE st.cantidad_disponible > 0 
                {$filtroTransportador}
                GROUP BY st.id
                ORDER BY st.nombre_transportador ASC, st.codigo_producto ASC
            ");

            $stmt->execute();
            $stockTransito = $stmt->fetchAll();

            $data = [];

            foreach($stockTransito as $key => $value) {

                $botones = $this->generarBotonesAccion($value);
                $cantidadDisponible = $this->formatearCantidadConIndicadores($value);
                $solicitudesPendientes = $this->formatearSolicitudesPendientes($value);
                $fechaCargue = date('d/m/Y H:i', strtotime($value["fecha_carga"]));

                // USAR ARRAY EN LUGAR DE JSON MANUAL
                $data[] = [
                    ($key + 1),
                    $value["codigo_producto"],
                    $this->truncarTexto($value["descripcion_producto"], 40),
                    $cantidadDisponible,
                    $value["nombre_transportador"],
                    $value["sucursal_origen"],
                    $fechaCargue,
                    $solicitudesPendientes,
                    $botones
                ];
            }

            // USAR json_encode() PARA EVITAR PROBLEMAS DE COMILLAS
            header('Content-Type: application/json');
            echo json_encode(["data" => $data]);

        } catch(Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(["data" => [], "error" => $e->getMessage()]);
        }
    }

    private function generarBotonesAccion($stock) {

        $botones = '<div class="btn-group">';

        // BOTÓN VER HISTORIAL - TODOS LOS PERFILES
        $botones .= '<button class="btn btn-info btn-xs btnVerHistorialProducto" 
                            data-toggle="tooltip" 
                            title="Ver historial del producto" 
                            codigoProducto="' . htmlspecialchars($stock["codigo_producto"]) . '"
                            transportadorId="' . $stock["transportador_id"] . '">
                        <i class="fa fa-history"></i>
                    </button>';

        // BOTONES SEGÚN PERFIL
        if($_SESSION["perfil"] == "Transportador") {
            
            if($stock["transportador_id"] == $_SESSION["id"]) {
                if($stock["solicitudes_pendientes"] > 0) {
                    $botones .= '<button class="btn btn-warning btn-xs btnVerSolicitudesPendientes" 
                                        data-toggle="tooltip" 
                                        title="Ver solicitudes pendientes" 
                                        transportadorId="' . $stock["transportador_id"] . '">
                                    <i class="fa fa-bell"></i>
                                    <span class="badge">' . $stock["solicitudes_pendientes"] . '</span>
                                </button>';
                }
            }

        } else {
            
            $cantidadDisponibleReal = $stock["cantidad_disponible"] - $stock["cantidad_solicitada_pendiente"];
            
            if($cantidadDisponibleReal > 0) {
                $botones .= '<button class="btn btn-success btn-xs btnSolicitarDescarga" 
                                    data-toggle="tooltip" 
                                    title="Solicitar descarga" 
                                    idStockTransito="' . $stock["id"] . '"
                                    codigoProducto="' . htmlspecialchars($stock["codigo_producto"]) . '"
                                    descripcionProducto="' . htmlspecialchars($stock["descripcion_producto"]) . '"
                                    cantidadDisponible="' . $cantidadDisponibleReal . '"
                                    transportadorId="' . $stock["transportador_id"] . '"
                                    nombreTransportador="' . htmlspecialchars($stock["nombre_transportador"]) . '"
                                    sucursalOrigen="' . htmlspecialchars($stock["sucursal_origen"]) . '">
                                <i class="fa fa-download"></i>
                            </button>';
            } else {
                $botones .= '<button class="btn btn-default btn-xs" 
                                    data-toggle="tooltip" 
                                    title="Sin stock disponible" disabled>
                                <i class="fa fa-ban"></i>
                            </button>';
            }

            if($_SESSION["perfil"] == "Administrador" && $stock["cantidad_disponible"] > 0) {
                $botones .= '<button class="btn btn-danger btn-xs btnForzarDescarga" 
                                    data-toggle="tooltip" 
                                    title="Forzar descarga (Admin)" 
                                    idStockTransito="' . $stock["id"] . '"
                                    codigoProducto="' . htmlspecialchars($stock["codigo_producto"]) . '"
                                    descripcionProducto="' . htmlspecialchars($stock["descripcion_producto"]) . '"
                                    cantidadDisponible="' . $stock["cantidad_disponible"] . '"
                                    transportadorNombre="' . htmlspecialchars($stock["nombre_transportador"]) . '">
                                <i class="fa fa-exclamation-triangle"></i>
                            </button>';
            }
        }

        $botones .= '</div>';
        return $botones;
    }

    private function formatearCantidadConIndicadores($stock) {
        
        $cantidadDisponible = intval($stock["cantidad_disponible"]);
        $cantidadSolicitada = intval($stock["cantidad_solicitada_pendiente"]);
        $cantidadReal = $cantidadDisponible - $cantidadSolicitada;

        $html = '<div class="text-center">';
        $html .= '<span class="stock-disponible">' . $cantidadDisponible . '</span>';
        
        if($cantidadSolicitada > 0) {
            $html .= '<br><small class="stock-solicitado">(' . $cantidadSolicitada . ' solicitado)</small>';
            $html .= '<br><small class="text-success"><strong>' . $cantidadReal . ' disponible</strong></small>';
        }
        
        $html .= '</div>';
        return $html;
    }

    private function formatearSolicitudesPendientes($stock) {
        
        $solicitudesPendientes = intval($stock["solicitudes_pendientes"]);
        
        if($solicitudesPendientes == 0) {
            return '<span class="text-muted">-</span>';
        }

        $colorClass = 'label-warning';
        if($solicitudesPendientes >= 3) {
            $colorClass = 'label-danger';
        }

        return '<span class="label ' . $colorClass . '">' . $solicitudesPendientes . ' pendiente' . ($solicitudesPendientes != 1 ? 's' : '') . '</span>';
    }

    private function truncarTexto($texto, $limite) {
        if(strlen($texto) > $limite) {
            return substr($texto, 0, $limite) . '...';
        }
        return $texto;
    }
}

/*=============================================
DETERMINAR QUÉ FUNCIÓN EJECUTAR
=============================================*/

// SI NO HAY PARÁMETROS, MOSTRAR TABLA (DEFAULT)
if(empty($_POST)) {
    $activarStock = new TablaStockTransito();
    $activarStock->mostrarTablaStockTransito();
    exit;
}

// SI SE SOLICITA TABLA ESPECÍFICAMENTE
if(isset($_POST["tabla"]) && $_POST["tabla"] == "stock-transito") {
    $activarStock = new TablaStockTransito();
    $activarStock->mostrarTablaStockTransito();
    exit;
}

// SI SE SOLICITA RESUMEN (para dashboard)
if(isset($_POST["resumen"]) && $_POST["resumen"] == "dashboard") {
    // Aquí puedes agregar la función de resumen si la necesitas
    echo json_encode([
        "total_productos" => 0,
        "total_unidades" => 0,
        "total_transportadores" => 0,
        "solicitudes_pendientes" => 0
    ]);
    exit;
}

// DEFAULT: mostrar tabla
if(empty($_POST)) {
    $activarStock = new TablaStockTransito();
    $activarStock->mostrarTablaStockTransito();
    exit;
}
?>