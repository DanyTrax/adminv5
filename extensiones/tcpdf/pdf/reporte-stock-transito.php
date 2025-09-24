<?php

session_start();

// Verificar permisos
if($_SESSION["perfil"] != "Administrador" && $_SESSION["perfil"] != "Transportador") {
    header('HTTP/1.0 403 Forbidden');
    exit('Sin permisos para generar este reporte');
}

require_once "../../../api-transferencias/conexion-central.php";
require_once "../tcpdf_include.php";

try {
    
    // Obtener filtros de la URL
    $transportador = $_GET['transportador'] ?? '';
    $fecha_desde = $_GET['fecha_desde'] ?? '';
    $fecha_hasta = $_GET['fecha_hasta'] ?? '';
    
    // Construir consulta
    $sql = "SELECT 
                st.codigo_producto,
                st.descripcion_producto,
                st.cantidad_disponible,
                st.nombre_transportador,
                st.sucursal_origen,
                st.fecha_carga,
                st.observaciones,
                COALESCE(SUM(sd.cantidad_solicitada), 0) as cantidad_solicitada_pendiente
            FROM stock_transito st
            LEFT JOIN solicitudes_descarga sd ON st.id = sd.id_stock_transito 
                AND sd.estado = 'pendiente'
            WHERE st.cantidad_disponible > 0";
    
    $parametros = [];
    
    // Si es transportador, solo su stock
    if($_SESSION["perfil"] == "Transportador") {
        $sql .= " AND st.transportador_id = :transportador_sesion";
        $parametros[':transportador_sesion'] = $_SESSION["id"];
    }
    // Si se especificó filtro de transportador
    else if(!empty($transportador)) {
        $sql .= " AND st.transportador_id = :transportador_filtro";
        $parametros[':transportador_filtro'] = $transportador;
    }
    
    // Filtros de fecha
    if(!empty($fecha_desde)) {
        $sql .= " AND DATE(st.fecha_carga) >= :fecha_desde";
        $parametros[':fecha_desde'] = $fecha_desde;
    }
    
    if(!empty($fecha_hasta)) {
        $sql .= " AND DATE(st.fecha_carga) <= :fecha_hasta";
        $parametros[':fecha_hasta'] = $fecha_hasta;
    }
    
    $sql .= " GROUP BY st.id ORDER BY st.nombre_transportador ASC, st.codigo_producto ASC";
    
    $stmt = ConexionCentral::conectar()->prepare($sql);
    
    foreach($parametros as $key => $valor) {
        $stmt->bindValue($key, $valor);
    }
    
    $stmt->execute();
    $stockTransito = $stmt->fetchAll();
    
    // Crear PDF
    $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false); // Landscape
    
    // Configuración del PDF
    $pdf->SetCreator('Sistema de Stock en Tránsito');
    $pdf->SetAuthor($_SESSION["nombre"]);
    $pdf->SetTitle('Reporte de Stock en Tránsito');
    $pdf->SetSubject('Productos en Movimiento');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    $pdf->AddPage();
    
    // TÍTULO
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(60, 141, 188);
    $pdf->Cell(0, 15, 'REPORTE DE STOCK EN TRÁNSITO', 0, 1, 'C');
    $pdf->Ln(3);
    
    // INFORMACIÓN DEL REPORTE
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    
    $pdf->Cell(60, 8, 'Generado por: ' . $_SESSION["nombre"], 0, 0, 'L');
    $pdf->Cell(60, 8, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 0, 'L');
    $pdf->Cell(60, 8, 'Perfil: ' . $_SESSION["perfil"], 0, 1, 'L');
    
    // Mostrar filtros aplicados
    if(!empty($transportador) || !empty($fecha_desde) || !empty($fecha_hasta)) {
        $pdf->Cell(0, 8, 'Filtros aplicados: ', 0, 1, 'L');
        
        if(!empty($transportador)) {
            // Obtener nombre del transportador
            $stmtT = ConexionCentral::conectar()->prepare("
                SELECT nombre FROM usuarios WHERE id = :id AND perfil = 'Transportador'
            ");
            $stmtT->bindParam(':id', $transportador, PDO::PARAM_INT);
            $stmtT->execute();
            $nombreT = $stmtT->fetch();
            
            $pdf->Cell(0, 6, '• Transportador: ' . ($nombreT['nombre'] ?? 'ID: ' . $transportador), 0, 1, 'L');
        }
        
        if(!empty($fecha_desde) || !empty($fecha_hasta)) {
            $rango = '';
            if(!empty($fecha_desde)) $rango .= 'Desde: ' . date('d/m/Y', strtotime($fecha_desde));
            if(!empty($fecha_hasta)) $rango .= (!empty($rango) ? ' - ' : '') . 'Hasta: ' . date('d/m/Y', strtotime($fecha_hasta));
            $pdf->Cell(0, 6, '• Fechas: ' . $rango, 0, 1, 'L');
        }
    }
    
    $pdf->Ln(5);
    
    // TABLA DE STOCK
    $html = '
    <table cellpadding="3" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background-color: #3c8dbc; color: white; font-weight: bold;">
                <th width="80px" style="text-align: center;">Código</th>
                <th width="140px">Descripción</th>
                <th width="35px" style="text-align: center;">Total</th>
                <th width="35px" style="text-align: center;">Solic.</th>
                <th width="35px" style="text-align: center;">Disp.</th>
                <th width="90px">Transportador</th>
                <th width="80px">Origen</th>
                <th width="60px" style="text-align: center;">Fecha</th>
            </tr>
        </thead>
        <tbody>';
    
    $totalUnidades = 0;
    $totalSolicitadas = 0;
    $totalDisponibles = 0;
    $transportadorActual = '';
    
    foreach($stockTransito as $stock) {
        $cantidadTotal = intval($stock["cantidad_disponible"]);
        $cantidadSolicitada = intval($stock["cantidad_solicitada_pendiente"]);
        $cantidadDisponible = $cantidadTotal - $cantidadSolicitada;
        
        $totalUnidades += $cantidadTotal;
        $totalSolicitadas += $cantidadSolicitada;
        $totalDisponibles += $cantidadDisponible;
        
        $fechaCargue = date('d/m/Y', strtotime($stock["fecha_carga"]));
        
        // Separador por transportador
        if($transportadorActual != $stock["nombre_transportador"]) {
            $transportadorActual = $stock["nombre_transportador"];
            if($transportadorActual != $stock["nombre_transportador"] && !empty($transportadorActual)) {
                $html .= '<tr style="background-color: #f0f0f0;"><td colspan="8" style="height: 5px;"></td></tr>';
            }
        }
        
        $html .= '
            <tr>
                <td style="font-size: 8px; text-align: center;">' . $stock["codigo_producto"] . '</td>
                <td style="font-size: 8px;">' . substr($stock["descripcion_producto"], 0, 25) . '</td>
                <td style="text-align: center; font-size: 8px; font-weight: bold;">' . $cantidadTotal . '</td>
                <td style="text-align: center; font-size: 8px; color: #f39c12;">' . ($cantidadSolicitada > 0 ? $cantidadSolicitada : '-') . '</td>
                <td style="text-align: center; font-size: 8px; color: #00a65a; font-weight: bold;">' . $cantidadDisponible . '</td>
                <td style="font-size: 8px;">' . $stock["nombre_transportador"] . '</td>
                <td style="font-size: 8px;">' . $stock["sucursal_origen"] . '</td>
                <td style="font-size: 8px; text-align: center;">' . $fechaCargue . '</td>
            </tr>';
    }
    
    $html .= '
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="2" style="text-align: center; font-size: 9px;">TOTALES:</td>
                <td style="text-align: center; font-size: 9px;">' . $totalUnidades . '</td>
                <td style="text-align: center; font-size: 9px;">' . $totalSolicitadas . '</td>
                <td style="text-align: center; font-size: 9px;">' . $totalDisponibles . '</td>
                <td colspan="3" style="text-align: center; font-size: 9px;">' . count($stockTransito) . ' productos</td>
            </tr>
        </tfoot>
    </table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // RESUMEN POR TRANSPORTADOR
    if(count($stockTransito) > 5) {
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'RESUMEN POR TRANSPORTADOR', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Calcular estadísticas
        $estadisticas = [];
        foreach($stockTransito as $stock) {
            $transportador = $stock["nombre_transportador"];
            if(!isset($estadisticas[$transportador])) {
                $estadisticas[$transportador] = [
                    'productos' => 0,
                    'unidades' => 0,
                    'solicitadas' => 0
                ];
            }
            $estadisticas[$transportador]['productos']++;
            $estadisticas[$transportador]['unidades'] += $stock["cantidad_disponible"];
            $estadisticas[$transportador]['solicitadas'] += $stock["cantidad_solicitada_pendiente"];
        }
        
        $htmlResumen = '
        <table cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse;">
            <thead>
                <tr style="background-color: #00a65a; color: white; font-weight: bold;">
                    <th width="150px">Transportador</th>
                    <th width="80px" style="text-align: center;">Productos</th>
                    <th width="80px" style="text-align: center;">Unidades</th>
                    <th width="80px" style="text-align: center;">Solicitadas</th>
                    <th width="80px" style="text-align: center;">Disponibles</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach($estadisticas as $transportador => $datos) {
            $disponibles = $datos['unidades'] - $datos['solicitadas'];
            $htmlResumen .= '
                <tr>
                    <td style="font-size: 9px;">' . $transportador . '</td>
                    <td style="text-align: center; font-size: 9px;">' . $datos['productos'] . '</td>
                    <td style="text-align: center; font-size: 9px;">' . $datos['unidades'] . '</td>
                    <td style="text-align: center; font-size: 9px;">' . $datos['solicitadas'] . '</td>
                    <td style="text-align: center; font-size: 9px; font-weight: bold;">' . $disponibles . '</td>
                </tr>';
        }
        
        $htmlResumen .= '
            </tbody>
        </table>';
        
        $pdf->writeHTML($htmlResumen, true, false, true, false, '');
    }
    
    // PIE DE PÁGINA
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(128, 128, 128);
    $pdf->Cell(0, 8, 'Total productos: ' . count($stockTransito) . ' | Total unidades: ' . $totalUnidades . ' | Disponibles: ' . $totalDisponibles, 0, 1, 'C');
    
    // Salida del PDF
    ob_end_clean();
    $nombreArchivo = 'Stock_Transito_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($nombreArchivo, 'I');
    
} catch(Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo "Error generando reporte: " . $e->getMessage();
}

?>