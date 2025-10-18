<?php

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

class TablaStockTransito {

    public function mostrarTablaStockTransito() {

        try {
            // Valor por defecto si no hay sesión
            $perfilUsuario = isset($_SESSION["perfil"]) ? $_SESSION["perfil"] : "Administrador";
            $idUsuario = isset($_SESSION["id"]) ? $_SESSION["id"] : 1;
            
            // Si es transportador, solo mostrar su stock
            $filtroTransportador = "";
            if($perfilUsuario == "Transportador") {
                $filtroTransportador = "AND st.transportador_id = " . $idUsuario;
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

                $botones = $this->generarBotonesAccion($value, $perfilUsuario, $idUsuario);
                $cantidadDisponible = $this->formatearCantidadConIndicadores($value);
                $solicitudesPendientes = $this->formatearSolicitudesPendientes($value);
                $fechaCargue = date('d/m/Y H:i', strtotime($value["fecha_carga"]));

                // CREAR FILA DE DATOS
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

            // RESPUESTA JSON LIMPIA
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["data" => $data], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        } catch(Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "data" => [], 
                "error" => $e->getMessage()
            ]);
        }
    }

private function generarBotonesAccion($stock, $perfilUsuario, $idUsuario) {

    $botones = '<div class="btn-group">';

    // BOTÓN VER HISTORIAL - TODOS LOS PERFILES
    $botones .= '<button class="btn btn-info btn-xs btnVerHistorialProducto" 
                        data-toggle="tooltip" 
                        title="Ver historial del producto" 
                        codigoProducto="' . htmlspecialchars($stock["codigo_producto"]) . '"
                        transportadorId="' . $stock["transportador_id"] . '">
                    <i class="fa fa-history"></i>
                </button>';

    // BOTÓN RECIBIR - SOLO USUARIOS (NO ADMIN, NO TRANSPORTADOR)
    if($perfilUsuario != "Administrador" && $perfilUsuario != "Transportador") {
        
        $cantidadDisponible = intval($stock["cantidad_disponible"]);
        
        if($cantidadDisponible > 0) {
            $botones .= '<button class="btn btn-success btn-xs btnRecibirStock" 
                                data-toggle="tooltip" 
                                title="Recibir en mi sucursal" 
                                idStockTransito="' . $stock["id"] . '"
                                codigoProducto="' . htmlspecialchars($stock["codigo_producto"]) . '"
                                descripcionProducto="' . htmlspecialchars($stock["descripcion_producto"]) . '"
                                cantidadDisponible="' . $cantidadDisponible . '"
                                transportadorId="' . $stock["transportador_id"] . '"
                                nombreTransportador="' . htmlspecialchars($stock["nombre_transportador"]) . '"
                                sucursalOrigen="' . htmlspecialchars($stock["sucursal_origen"]) . '">
                            <i class="fa fa-check-circle"></i>
                        </button>';
        }
    }

    // BOTÓN DESCARGA DIRECTA - USUARIOS NO TRANSPORTADOR
    if($perfilUsuario != "Transportador") {
        
        $cantidadDisponible = intval($stock["cantidad_disponible"]);
        
        if($cantidadDisponible > 0) {
            $botones .= '<button class="btn btn-warning btn-xs btnDescargaDirecta" 
                                data-toggle="tooltip" 
                                title="Descargar cantidad específica" 
                                idStockTransito="' . $stock["id"] . '"
                                codigoProducto="' . htmlspecialchars($stock["codigo_producto"]) . '"
                                descripcionProducto="' . htmlspecialchars($stock["descripcion_producto"]) . '"
                                cantidadDisponible="' . $cantidadDisponible . '"
                                transportadorId="' . $stock["transportador_id"] . '"
                                nombreTransportador="' . htmlspecialchars($stock["nombre_transportador"]) . '"
                                sucursalOrigen="' . htmlspecialchars($stock["sucursal_origen"]) . '"
                                idDespacho="' . $stock["id_despacho"] . '"
                                numeroDespacho="' . htmlspecialchars($stock["numero_despacho"]) . '">
                            <i class="fa fa-download"></i>
                        </button>';
        }
    }

    // BOTÓN ELIMINAR - SOLO ADMINISTRADOR
    if($perfilUsuario == "Administrador") {
        $botones .= '<button class="btn btn-danger btn-xs btnEliminarStock" 
                            data-toggle="tooltip" 
                            title="Eliminar del stock en tránsito" 
                            idStockTransito="' . $stock["id"] . '"
                            codigoProducto="' . htmlspecialchars($stock["codigo_producto"]) . '"
                            descripcionProducto="' . htmlspecialchars($stock["descripcion_producto"]) . '"
                            cantidadDisponible="' . $stock["cantidad_disponible"] . '">
                        <i class="fa fa-trash"></i>
                    </button>';
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
        return $texto;
    }
}

// EJECUTAR DIRECTAMENTE SIN VERIFICAR PARÁMETROS
$activarStock = new TablaStockTransito();
$activarStock->mostrarTablaStockTransito();
?>