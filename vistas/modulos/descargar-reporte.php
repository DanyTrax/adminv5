<?php
/*=============================================
DESCARGAR REPORTE GENERAL
=============================================*/

// Verificar permisos
if($_SESSION["perfil"] != "Administrador" && $_SESSION["perfil"] != "Vendedor" && $_SESSION["perfil"] != "Contador") {
    echo '<script>window.location = "inicio";</script>';
    return;
}

// Obtener parámetros
$tipoReporte = isset($_GET["reporte"]) ? $_GET["reporte"] : "reporte";
$fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
$fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;

// Incluir controladores necesarios
require_once "controladores/reportes.controlador.php";
require_once "modelos/reportes.modelo.php";
require_once "controladores/contabilidad.controlador.php";
require_once "modelos/contabilidad.modelo.php";
require_once "controladores/ventas.controlador.php";
require_once "modelos/ventas.modelo.php";

try {
    // Obtener datos del reporte
    $fechaInicialSql = $fechaInicial ? $fechaInicial . " 00:00:00" : null;
    $fechaFinalSql = $fechaFinal ? $fechaFinal . " 23:59:59" : null;
    
    // Obtener resumen de ventas
    $resumenVentas = ControladorVentas::ctrSumaTotalVentasGeneral($fechaInicial, $fechaFinal);
    $resumenEntradas = ControladorContabilidad::ctrSumaTotalEntradas($fechaInicial, $fechaFinal);
    $resumenDeuda = ControladorVentas::ctrSumaTotalDeuda($fechaInicial, $fechaFinal);
    
    // Configurar headers para descarga
    $filename = "Reporte_General_" . date('Y-m-d_H-i-s') . ".csv";
    if ($fechaInicial && $fechaFinal) {
        $filename = "Reporte_General_" . $fechaInicial . "_a_" . $fechaFinal . "_" . date('Y-m-d_H-i-s') . ".csv";
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    
    // Crear archivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados
    fputcsv($output, ['REPORTE GENERAL DE VENTAS']);
    fputcsv($output, ['Generado por: ' . $_SESSION["nombre"]]);
    fputcsv($output, ['Fecha: ' . date('d/m/Y H:i:s')]);
    fputcsv($output, ['Perfil: ' . $_SESSION["perfil"]]);
    
    if ($fechaInicial && $fechaFinal) {
        fputcsv($output, ['Período: ' . $fechaInicial . ' a ' . $fechaFinal]);
    } else {
        fputcsv($output, ['Período: Todos los registros']);
    }
    
    fputcsv($output, []); // Línea vacía
    
    // Resumen financiero
    fputcsv($output, ['RESUMEN FINANCIERO']);
    fputcsv($output, ['Concepto', 'Valor']);
    fputcsv($output, ['Total Ventas', '$' . number_format($resumenVentas["total_ventas"] ?? 0, 2)]);
    fputcsv($output, ['Total Entradas', '$' . number_format($resumenEntradas["total"] ?? 0, 2)]);
    fputcsv($output, ['Total Deuda', '$' . number_format($resumenDeuda["total_deuda"] ?? 0, 2)]);
    
    fputcsv($output, []); // Línea vacía
    
    // Nota
    fputcsv($output, ['NOTA: Este reporte contiene un resumen general de las operaciones del período seleccionado.']);
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    echo "Error al generar el reporte: " . $e->getMessage();
}

?>
