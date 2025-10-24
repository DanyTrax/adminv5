<?php
/*=============================================
DESCARGAR REPORTE DETALLADO
=============================================*/

// Verificar permisos
if($_SESSION["perfil"] != "Administrador" && $_SESSION["perfil"] != "Vendedor" && $_SESSION["perfil"] != "Contador") {
    echo '<script>window.location = "inicio";</script>';
    return;
}

// Obtener fechas
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
    
    // Obtener ventas detalladas
    $ventas = ModeloReportes::mdlObtenerVentasDetalladas($fechaInicialSql, $fechaFinalSql);
    
    // Configurar headers para descarga
    $filename = "Reporte_Detallado_" . date('Y-m-d_H-i-s') . ".csv";
    if ($fechaInicial && $fechaFinal) {
        $filename = "Reporte_Detallado_" . $fechaInicial . "_a_" . $fechaFinal . "_" . date('Y-m-d_H-i-s') . ".csv";
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
    fputcsv($output, [
        'FECHA',
        'FACTURA',
        'VENDEDOR',
        'CLIENTE',
        'PRODUCTO',
        'CANTIDAD',
        'TOTAL',
        'MEDIO DE PAGO'
    ]);
    
    // Datos
    foreach ($ventas as $venta) {
        fputcsv($output, [
            $venta['fecha_venta'],
            $venta['codigo_factura'],
            $venta['nombre_vendedor'],
            $venta['nombre_cliente'],
            $venta['producto_descripcion'],
            $venta['producto_cantidad'],
            $venta['producto_total'],
            $venta['medio_pago']
        ]);
    }
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    echo "Error al generar el reporte: " . $e->getMessage();
}

?>
