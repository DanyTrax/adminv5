<?php

class ControladorHistoricoTransito {

    /*=============================================
    MOSTRAR HISTÓRICO DE MOVIMIENTOS
    =============================================*/
    static public function ctrMostrarHistoricoTransito($item, $valor, $filtros = []) {
        
        $tabla = "historico_transito";
        $respuesta = ModeloHistoricoTransito::mdlMostrarHistoricoTransito($tabla, $item, $valor, $filtros);
        return $respuesta;
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DEL HISTÓRICO
    =============================================*/
    static public function ctrObtenerEstadisticasHistorico($filtros = []) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            // Aplicar filtro de perfil
            $filtroTransportador = "";
            $parametros = [];
            
            if($_SESSION["perfil"] == "Transportador") {
                $filtroTransportador = "AND transportador_id = :transportador_sesion";
                $parametros[':transportador_sesion'] = $_SESSION["id"];
            }
            
            // Aplicar filtros adicionales
            $filtrosSQL = "";
            
            if(!empty($filtros['fecha_desde'])) {
                $filtrosSQL .= " AND DATE(fecha_movimiento) >= :fecha_desde";
                $parametros[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if(!empty($filtros['fecha_hasta'])) {
                $filtrosSQL .= " AND DATE(fecha_movimiento) <= :fecha_hasta";
                $parametros[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            if(!empty($filtros['transportador'])) {
                $filtrosSQL .= " AND transportador_id = :transportador_filtro";
                $parametros[':transportador_filtro'] = $filtros['transportador'];
            }
            
            if(!empty($filtros['tipo_movimiento'])) {
                $filtrosSQL .= " AND tipo_movimiento = :tipo_movimiento";
                $parametros[':tipo_movimiento'] = $filtros['tipo_movimiento'];
            }
            
            // Consulta de estadísticas generales
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_movimientos,
                    SUM(CASE WHEN tipo_movimiento = 'descarga' THEN cantidad ELSE 0 END) as descargas_exitosas,
                    SUM(CASE WHEN tipo_movimiento = 'cargue' THEN cantidad ELSE 0 END) as productos_cargados,
                    COUNT(CASE WHEN tipo_movimiento = 'rechazo_descarga' THEN 1 END) as solicitudes_rechazadas,
                    COUNT(CASE WHEN tipo_movimiento = 'solicitud_descarga' THEN 1 END) as solicitudes_creadas,
                    COUNT(CASE WHEN tipo_movimiento = 'descarga_forzada' THEN 1 END) as descargas_forzadas
                FROM historico_transito 
                WHERE 1=1 {$filtroTransportador} {$filtrosSQL}
            ");
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            $estadisticas = $stmt->fetch();
            
            // Consulta por tipos de movimiento
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    tipo_movimiento,
                    COUNT(*) as cantidad,
                    SUM(cantidad) as unidades_totales
                FROM historico_transito 
                WHERE 1=1 {$filtroTransportador} {$filtrosSQL}
                GROUP BY tipo_movimiento
                ORDER BY cantidad DESC
            ");
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            $tiposMovimiento = $stmt->fetchAll();
            
            // Consulta por transportadores (solo para administradores)
            $transportadores = [];
            if($_SESSION["perfil"] == "Administrador") {
                $stmt = ConexionCentral::conectar()->prepare("
                    SELECT 
                        nombre_transportador,
                        COUNT(*) as cantidad_movimientos,
                        SUM(cantidad) as unidades_totales
                    FROM historico_transito 
                    WHERE 1=1 {$filtrosSQL}
                    GROUP BY transportador_id, nombre_transportador
                    ORDER BY cantidad_movimientos DESC
                    LIMIT 10
                ");
                
                // Filtrar parámetros para no incluir transportador_sesion
                $parametrosFiltrados = array_filter($parametros, function($key) {
                    return $key !== ':transportador_sesion';
                }, ARRAY_FILTER_USE_KEY);
                
                foreach($parametrosFiltrados as $key => $valor) {
                    $stmt->bindValue($key, $valor);
                }
                
                $stmt->execute();
                $transportadores = $stmt->fetchAll();
            }
            
            return [
                "estadisticas_generales" => $estadisticas,
                "tipos_movimiento" => $tiposMovimiento,
                "transportadores" => $transportadores
            ];
            
        } catch(Exception $e) {
            return [
                "estadisticas_generales" => [
                    "total_movimientos" => 0,
                    "descargas_exitosas" => 0,
                    "productos_cargados" => 0,
                    "solicitudes_rechazadas" => 0,
                    "solicitudes_creadas" => 0,
                    "descargas_forzadas" => 0
                ],
                "tipos_movimiento" => [],
                "transportadores" => []
            ];
        }
    }

    /*=============================================
    OBTENER DATOS PARA GRÁFICO DE LÍNEA DE TIEMPO
    =============================================*/
    static public function ctrObtenerDatosLineaTiempo($filtros = []) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $filtroTransportador = "";
            $parametros = [];
            
            if($_SESSION["perfil"] == "Transportador") {
                $filtroTransportador = "AND transportador_id = :transportador_sesion";
                $parametros[':transportador_sesion'] = $_SESSION["id"];
            }
            
            // Aplicar filtros de fecha (últimos 30 días por defecto)
            $fechaDesde = $filtros['fecha_desde'] ?? date('Y-m-d', strtotime('-30 days'));
            $fechaHasta = $filtros['fecha_hasta'] ?? date('Y-m-d');
            
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    DATE(fecha_movimiento) as fecha,
                    tipo_movimiento,
                    COUNT(*) as cantidad_movimientos,
                    SUM(cantidad) as cantidad_unidades
                FROM historico_transito 
                WHERE DATE(fecha_movimiento) BETWEEN :fecha_desde AND :fecha_hasta
                {$filtroTransportador}
                GROUP BY DATE(fecha_movimiento), tipo_movimiento
                ORDER BY fecha ASC
            ");
            
            $stmt->bindParam(":fecha_desde", $fechaDesde, PDO::PARAM_STR);
            $stmt->bindParam(":fecha_hasta", $fechaHasta, PDO::PARAM_STR);
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            $datos = $stmt->fetchAll();
            
            // Procesar datos para el gráfico
            $fechas = [];
            $series = [];
            $tipos = ['cargue', 'descarga', 'solicitud_descarga', 'rechazo_descarga'];
            
            // Inicializar series
            foreach($tipos as $tipo) {
                $series[$tipo] = [];
            }
            
            // Obtener todas las fechas del rango
            $fechaActual = new DateTime($fechaDesde);
            $fechaFinal = new DateTime($fechaHasta);
            
            while($fechaActual <= $fechaFinal) {
                $fechas[] = $fechaActual->format('Y-m-d');
                
                // Inicializar con 0 para cada tipo
                foreach($tipos as $tipo) {
                    $series[$tipo][] = 0;
                }
                
                $fechaActual->add(new DateInterval('P1D'));
            }
            
            // Llenar con datos reales
            foreach($datos as $registro) {
                $indiceFecha = array_search($registro['fecha'], $fechas);
                if($indiceFecha !== false && isset($series[$registro['tipo_movimiento']])) {
                    $series[$registro['tipo_movimiento']][$indiceFecha] = intval($registro['cantidad_movimientos']);
                }
            }
            
            return [
                "fechas" => $fechas,
                "series" => $series
            ];
            
        } catch(Exception $e) {
            return [
                "fechas" => [],
                "series" => []
            ];
        }
    }

    /*=============================================
    OBTENER TRAZABILIDAD COMPLETA DE UN PRODUCTO
    =============================================*/
    static public function ctrObtenerTrazabilidadProducto($codigoProducto, $transportadorId = null) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $sql = "SELECT * FROM historico_transito 
                    WHERE codigo_producto = :codigo_producto";
            
            $parametros = [":codigo_producto" => $codigoProducto];
            
            // Si es transportador o se especifica, filtrar por transportador
            if($_SESSION["perfil"] == "Transportador" || $transportadorId) {
                $sql .= " AND transportador_id = :transportador_id";
                $parametros[":transportador_id"] = $transportadorId ?? $_SESSION["id"];
            }
            
            $sql .= " ORDER BY fecha_movimiento DESC";
            
            $stmt = ConexionCentral::conectar()->prepare($sql);
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            $trazabilidad = $stmt->fetchAll();
            
            return $trazabilidad;
            
        } catch(Exception $e) {
            return [];
        }
    }

    /*=============================================
    EXPORTAR HISTÓRICO A PDF
    =============================================*/
    static public function ctrExportarHistoricoPDF($filtros = []) {
        
        if($_SESSION["perfil"] != "Administrador" && $_SESSION["perfil"] != "Transportador") {
            return false;
        }
        
        require_once "extensiones/tcpdf/tcpdf_include.php";
        
        // Obtener datos del histórico
        $historico = self::obtenerHistoricoParaExportar($filtros);
        
        // Crear PDF
        $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false); // Landscape
        
        // Configuración del PDF
        $pdf->SetCreator('Sistema de Histórico de Movimientos');
        $pdf->SetAuthor($_SESSION["nombre"]);
        $pdf->SetTitle('Histórico de Movimientos - Trazabilidad Completa');
        $pdf->SetSubject('Movimientos de Stock en Tránsito');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 15, 10);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        $pdf->AddPage();
        
        // TÍTULO
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(60, 141, 188);
        $pdf->Cell(0, 15, 'HISTÓRICO DE MOVIMIENTOS - TRAZABILIDAD', 0, 1, 'C');
        $pdf->Ln(3);
        
        // INFORMACIÓN DEL REPORTE
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        
        $pdf->Cell(60, 8, 'Generado por: ' . $_SESSION["nombre"], 0, 0, 'L');
        $pdf->Cell(60, 8, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 0, 'L');
        $pdf->Cell(60, 8, 'Perfil: ' . $_SESSION["perfil"], 0, 1, 'L');
        
        // Mostrar filtros aplicados
        if(!empty($filtros)) {
            $pdf->Cell(0, 8, 'Filtros aplicados: ', 0, 1, 'L');
            
            if(!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta'])) {
                $rango = '';
                if(!empty($filtros['fecha_desde'])) $rango .= 'Desde: ' . date('d/m/Y', strtotime($filtros['fecha_desde']));
                if(!empty($filtros['fecha_hasta'])) $rango .= (!empty($rango) ? ' - ' : '') . 'Hasta: ' . date('d/m/Y', strtotime($filtros['fecha_hasta']));
                $pdf->Cell(0, 6, '• Fechas: ' . $rango, 0, 1, 'L');
            }
            
            if(!empty($filtros['tipo_movimiento'])) {
                $tiposTexto = [
                    'cargue' => 'Cargues',
                    'descarga' => 'Descargas',
                    'solicitud_descarga' => 'Solicitudes',
                    'rechazo_descarga' => 'Rechazos',
                    'descarga_forzada' => 'Descargas Forzadas'
                ];
                $pdf->Cell(0, 6, '• Tipo: ' . ($tiposTexto[$filtros['tipo_movimiento']] ?? $filtros['tipo_movimiento']), 0, 1, 'L');
            }
        }
        
        $pdf->Ln(5);
        
        // TABLA DE HISTÓRICO
        $html = '
        <table cellpadding="3" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">
            <thead>
                <tr style="background-color: #3c8dbc; color: white; font-weight: bold;">
                    <th width="60px" style="text-align: center;">Fecha</th>
                    <th width="50px" style="text-align: center;">Hora</th>
                    <th width="70px">Tipo</th>
                    <th width="60px">Código</th>
                    <th width="100px">Descripción</th>
                    <th width="35px">Cant.</th>
                    <th width="80px">Transportador</th>
                    <th width="60px">Origen</th>
                    <th width="60px">Destino</th>
                    <th width="80px">Usuario</th>
                </tr>
            </thead>
            <tbody>';
        
        $totalMovimientos = 0;
        $totalCantidad = 0;
        
        foreach($historico as $movimiento) {
            $totalMovimientos++;
            $totalCantidad += intval($movimiento["cantidad"]);
            
            $fecha = date('d/m/Y', strtotime($movimiento["fecha_movimiento"]));
            $hora = date('H:i', strtotime($movimiento["fecha_movimiento"]));
            
            $tipoTexto = self::obtenerTextoTipoMovimiento($movimiento["tipo_movimiento"]);
            $colorTipo = self::obtenerColorTipoMovimiento($movimiento["tipo_movimiento"]);
            
            $html .= '
                <tr>
                    <td style="font-size: 8px; text-align: center;">' . $fecha . '</td>
                    <td style="font-size: 8px; text-align: center;">' . $hora . '</td>
                    <td style="font-size: 7px; background-color: ' . $colorTipo . '; color: white;">' . $tipoTexto . '</td>
                    <td style="font-size: 8px;">' . $movimiento["codigo_producto"] . '</td>
                    <td style="font-size: 7px;">' . substr($movimiento["descripcion_producto"], 0, 20) . '</td>
                    <td style="font-size: 8px; text-align: center;">' . $movimiento["cantidad"] . '</td>
                    <td style="font-size: 7px;">' . $movimiento["nombre_transportador"] . '</td>
                    <td style="font-size: 7px;">' . ($movimiento["sucursal_origen"] ?: '-') . '</td>
                    <td style="font-size: 7px;">' . ($movimiento["sucursal_destino"] ?: '-') . '</td>
                    <td style="font-size: 7px;">' . $movimiento["nombre_usuario_origen"] . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
            <tfoot>
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="5" style="text-align: center; font-size: 9px;">TOTALES:</td>
                    <td style="text-align: center; font-size: 9px;">' . $totalCantidad . '</td>
                    <td colspan="4" style="text-align: center; font-size: 9px;">' . $totalMovimientos . ' movimientos</td>
                </tr>
            </tfoot>
        </table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // ESTADÍSTICAS RESUMEN
        if($totalMovimientos > 10) {
            $pdf->Ln(10);
            
            $estadisticas = self::ctrObtenerEstadisticasHistorico($filtros);
            $stats = $estadisticas["estadisticas_generales"];
            
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'RESUMEN ESTADÍSTICO', 0, 1, 'C');
            $pdf->Ln(5);
            
            $htmlStats = '
            <table cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #00a65a; color: white; font-weight: bold;">
                        <th width="100px">Concepto</th>
                        <th width="80px" style="text-align: center;">Cantidad</th>
                        <th width="200px">Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-size: 9px;">Total Movimientos</td>
                        <td style="text-align: center; font-size: 9px;">' . ($stats["total_movimientos"] ?? 0) . '</td>
                        <td style="font-size: 9px;">Todos los tipos de movimientos registrados</td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;">Descargas Exitosas</td>
                        <td style="text-align: center; font-size: 9px;">' . ($stats["descargas_exitosas"] ?? 0) . '</td>
                        <td style="font-size: 9px;">Unidades descargadas correctamente</td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;">Productos Cargados</td>
                        <td style="text-align: center; font-size: 9px;">' . ($stats["productos_cargados"] ?? 0) . '</td>
                        <td style="font-size: 9px;">Unidades subidas a vehículos</td>
                    </tr>
                    <tr>
                        <td style="font-size: 9px;">Solicitudes Rechazadas</td>
                        <td style="text-align: center; font-size: 9px;">' . ($stats["solicitudes_rechazadas"] ?? 0) . '</td>
                        <td style="font-size: 9px;">Solicitudes de descarga no aprobadas</td>
                    </tr>
                </tbody>
            </table>';
            
            $pdf->writeHTML($htmlStats, true, false, true, false, '');
        }
        
        // PIE DE PÁGINA
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell(0, 8, 'Reporte generado desde el sistema de trazabilidad | Total: ' . $totalMovimientos . ' movimientos | ' . $totalCantidad . ' unidades', 0, 1, 'C');
        
        // Salida del PDF
        ob_end_clean();
        $nombreArchivo = 'Historico_Movimientos_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($nombreArchivo, 'I');
    }

    /*=============================================
    OBTENER HISTÓRICO PARA EXPORTAR
    =============================================*/
    static private function obtenerHistoricoParaExportar($filtros) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $sql = "SELECT * FROM historico_transito WHERE 1=1";
            $parametros = [];
            
            // Si es transportador, solo su histórico
            if($_SESSION["perfil"] == "Transportador") {
                $sql .= " AND transportador_id = :transportador_sesion";
                $parametros[':transportador_sesion'] = $_SESSION["id"];
            }
            
            // Aplicar filtros
            if(!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(fecha_movimiento) >= :fecha_desde";
                $parametros[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if(!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(fecha_movimiento) <= :fecha_hasta";
                $parametros[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            if(!empty($filtros['transportador'])) {
                $sql .= " AND transportador_id = :transportador_filtro";
                $parametros[':transportador_filtro'] = $filtros['transportador'];
            }
            
            if(!empty($filtros['tipo_movimiento'])) {
                $sql .= " AND tipo_movimiento = :tipo_movimiento";
                $parametros[':tipo_movimiento'] = $filtros['tipo_movimiento'];
            }
            
            if(!empty($filtros['codigo_producto'])) {
                $sql .= " AND codigo_producto LIKE :codigo_producto";
                $parametros[':codigo_producto'] = '%' . $filtros['codigo_producto'] . '%';
            }
            
            if(!empty($filtros['sucursal'])) {
                $sql .= " AND (sucursal_origen LIKE :sucursal OR sucursal_destino LIKE :sucursal)";
                $parametros[':sucursal'] = '%' . $filtros['sucursal'] . '%';
            }
            
            if(!empty($filtros['usuario'])) {
                $sql .= " AND (nombre_usuario_origen LIKE :usuario OR nombre_usuario_destino LIKE :usuario)";
                $parametros[':usuario'] = '%' . $filtros['usuario'] . '%';
            }
            
            $sql .= " ORDER BY fecha_movimiento DESC LIMIT 1000"; // Limitar a 1000 registros para PDF
            
            $stmt = ConexionCentral::conectar()->prepare($sql);
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            return [];
        }
    }

    /*=============================================
    OBTENER TEXTO PARA TIPO DE MOVIMIENTO
    =============================================*/
    static private function obtenerTextoTipoMovimiento($tipo) {
        
        $tipos = [
            'cargue' => 'CARGUE',
            'descarga' => 'DESCARGA',
            'solicitud_descarga' => 'SOLICITUD',
            'rechazo_descarga' => 'RECHAZO',
            'descarga_forzada' => 'FORZADA',
            'transferencia' => 'TRANSFER'
        ];
        
        return $tipos[$tipo] ?? strtoupper($tipo);
    }

    /*=============================================
    OBTENER COLOR PARA TIPO DE MOVIMIENTO
    =============================================*/
    static private function obtenerColorTipoMovimiento($tipo) {
        
        $colores = [
            'cargue' => '#3c8dbc',
            'descarga' => '#00a65a',
            'solicitud_descarga' => '#f39c12',
            'rechazo_descarga' => '#dd4b39',
            'descarga_forzada' => '#605ca8',
            'transferencia' => '#00c0ef'
        ];
        
        return $colores[$tipo] ?? '#999999';
    }
}