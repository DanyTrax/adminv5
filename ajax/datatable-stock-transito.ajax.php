<?php

session_start();

require_once "../api-transferencias/conexion-central.php";

class TablaStockTransito {

    /*=============================================
    MOSTRAR LA TABLA DE STOCK EN TRÁNSITO
    =============================================*/
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
                CANTIDAD CON INDICADORES
                =============================================*/
                $cantidadDisponible = $this->formatearCantidadConIndicadores($value);

                /*=============================================
                SOLICITUDES PENDIENTES
                =============================================*/
                $solicitudesPendientes = $this->formatearSolicitudesPendientes($value);

                /*=============================================
                FECHA FORMATEADA
                =============================================*/
                $fechaCargue = date('d/m/Y H:i', strtotime($value["fecha_carga"]));

                /*=============================================
                CONSTRUIR FILA JSON
                =============================================*/
                $datosJson .= '[
                    "' . ($key + 1) . '",
                    "' . $value["codigo_producto"] . '",
                    "' . $this->truncarTexto($value["descripcion_producto"], 40) . '",
                    "' . $cantidadDisponible . '",
                    "' . $value["nombre_transportador"] . '",
                    "' . $value["sucursal_origen"] . '",
                    "' . $fechaCargue . '",
                    "' . $solicitudesPendientes . '",
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
    GENERAR BOTONES DE ACCIÓN SEGÚN PERFIL
    =============================================*/
    private function generarBotonesAccion($stock) {

        $botones = '<div class="btn-group">';

        // BOTÓN VER HISTORIAL - TODOS LOS PERFILES
        $botones .= '<button class="btn btn-info btn-xs btnVerHistorialProducto" 
                            data-toggle="tooltip" 
                            title="Ver historial del producto" 
                            codigoProducto="' . $stock["codigo_producto"] . '"
                            transportadorId="' . $stock["transportador_id"] . '">
                        <i class="fa fa-history"></i>
                    </button>';

        // BOTONES SEGÚN PERFIL
        if($_SESSION["perfil"] == "Transportador") {
            
            // SI ES EL TRANSPORTADOR DUEÑO DEL STOCK
            if($stock["transportador_id"] == $_SESSION["id"]) {
                
                // Verificar si hay solicitudes pendientes
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
            // USUARIOS NORMALES Y ADMINISTRADORES PUEDEN SOLICITAR DESCARGA
            
            $cantidadDisponibleReal = $stock["cantidad_disponible"] - $stock["cantidad_solicitada_pendiente"];
            
            if($cantidadDisponibleReal > 0) {
                $botones .= '<button class="btn btn-success btn-xs btnSolicitarDescarga" 
                                    data-toggle="tooltip" 
                                    title="Solicitar descarga" 
                                    idStockTransito="' . $stock["id"] . '"
                                    codigoProducto="' . $stock["codigo_producto"] . '"
                                    descripcionProducto="' . htmlspecialchars($stock["descripcion_producto"]) . '"
                                    cantidadDisponible="' . $cantidadDisponibleReal . '"
                                    transportadorId="' . $stock["transportador_id"] . '"
                                    nombreTransportador="' . $stock["nombre_transportador"] . '"
                                    sucursalOrigen="' . $stock["sucursal_origen"] . '">
                                <i class="fa fa-download"></i>
                            </button>';
            } else {
                $botones .= '<button class="btn btn-default btn-xs" 
                                    data-toggle="tooltip" 
                                    title="Sin stock disponible" disabled>
                                <i class="fa fa-ban"></i>
                            </button>';
            }

            // ADMINISTRADOR PUEDE FORZAR DESCARGA
            if($_SESSION["perfil"] == "Administrador" && $stock["cantidad_disponible"] > 0) {
                $botones .= '<button class="btn btn-danger btn-xs btnForzarDescarga" 
                                    data-toggle="tooltip" 
                                    title="Forzar descarga (Admin)" 
                                    idStockTransito="' . $stock["id"] . '"
                                    codigoProducto="' . $stock["codigo_producto"] . '"
                                    descripcionProducto="' . htmlspecialchars($stock["descripcion_producto"]) . '"
                                    cantidadDisponible="' . $stock["cantidad_disponible"] . '"
                                    transportadorNombre="' . $stock["nombre_transportador"] . '">
                                <i class="fa fa-exclamation-triangle"></i>
                            </button>';
            }
        }

        $botones .= '</div>';

        return $botones;
    }

    /*=============================================
    FORMATEAR CANTIDAD CON INDICADORES
    =============================================*/
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

    /*=============================================
    FORMATEAR SOLICITUDES PENDIENTES
    =============================================*/
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

    /*=============================================
    TRUNCAR TEXTO
    =============================================*/
    private function truncarTexto($texto, $limite) {
        if(strlen($texto) > $limite) {
            return substr($texto, 0, $limite) . '...';
        }
        return $texto;
    }

    /*=============================================
    OBTENER RESUMEN PARA DASHBOARD
    =============================================*/
    public function obtenerResumenDashboard() {
        
        try {
            $filtroTransportador = "";
            if($_SESSION["perfil"] == "Transportador") {
                $filtroTransportador = "AND transportador_id = " . $_SESSION["id"];
            }

            // Obtener totales de stock
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    COUNT(DISTINCT codigo_producto) as total_productos,
                    SUM(cantidad_disponible) as total_unidades,
                    COUNT(DISTINCT transportador_id) as total_transportadores
                FROM stock_transito 
                WHERE cantidad_disponible > 0 
                {$filtroTransportador}
            ");
            
            $stmt->execute();
            $resumen = $stmt->fetch();

            // Obtener solicitudes pendientes
            $filtroSolicitudes = "";
            if($_SESSION["perfil"] == "Transportador") {
                $filtroSolicitudes = "WHERE transportador_id = " . $_SESSION["id"];
            }

            $stmt = ConexionCentral::conectar()->prepare("
                SELECT COUNT(*) as solicitudes_pendientes
                FROM solicitudes_descarga 
                {$filtroSolicitudes}
                " . (!empty($filtroSolicitudes) ? "AND" : "WHERE") . " estado = 'pendiente'
            ");
            
            $stmt->execute();
            $solicitudes = $stmt->fetch();

            echo json_encode([
                "total_productos" => $resumen["total_productos"] ?? 0,
                "total_unidades" => $resumen["total_unidades"] ?? 0,
                "total_transportadores" => $_SESSION["perfil"] == "Transportador" ? 1 : ($resumen["total_transportadores"] ?? 0),
                "solicitudes_pendientes" => $solicitudes["solicitudes_pendientes"] ?? 0
            ]);

        } catch(Exception $e) {
            echo json_encode([
                "total_productos" => 0,
                "total_unidades" => 0,
                "total_transportadores" => 0,
                "solicitudes_pendientes" => 0,
                "error" => $e->getMessage()
            ]);
        }
    }
}

/*=============================================
ACTIVAR TABLA DE STOCK EN TRÁNSITO
=============================================*/
if(isset($_POST["tabla"]) && $_POST["tabla"] == "stock-transito") {
    $activarStock = new TablaStockTransito();
    $activarStock->mostrarTablaStockTransito();
}

if(isset($_POST["resumen"]) && $_POST["resumen"] == "dashboard") {
    $resumen = new TablaStockTransito();
    $resumen->obtenerResumenDashboard();
}

?>