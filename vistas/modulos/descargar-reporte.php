<?php
session_start();
require_once "../../controladores/ventas.controlador.php";
require_once "../../modelos/ventas.modelo.php";
require_once "../../controladores/contabilidad.controlador.php";
require_once "../../modelos/contabilidad.modelo.php";
require_once "../../controladores/usuarios.controlador.php";
require_once "../../modelos/usuarios.modelo.php";

// Se construye el nombre del archivo dinámicamente
$nombreArchivo = 'reporte-general';
if (isset($_GET["fechaInicial"]) && !empty($_GET["fechaInicial"])) {
    $nombreArchivo .= '_' . $_GET["fechaInicial"] . '_a_' . $_GET["fechaFinal"];
}
$nombreArchivo .= '.xls';
header('Expires: 0');
header('Cache-control: private');
header("Content-type: application/vnd.ms-excel; charset=utf-8");
header("Cache-Control: cache, must-revalidate");
header('Content-Description: File Transfer');
header('Last-Modified: ' . date('D, d M Y H:i:s'));
header("Pragma: public");
header('Content-Disposition:; filename="' . $nombreArchivo . '"');
header("Content-Transfer-Encoding: binary");

$fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
$fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;

// --- OBTENCIÓN DE TODOS LOS DATOS ---
$totalEntradas = ControladorContabilidad::ctrSumaTotalEntradas($fechaInicial, $fechaFinal);
$totalDeuda = ControladorVentas::ctrSumaTotalDeuda($fechaInicial, $fechaFinal);
$totalVendido = ControladorVentas::ctrSumaTotalVentasGeneral($fechaInicial, $fechaFinal);
$ventasPorVendedor = ControladorVentas::ctrSumaVentasPorVendedor($fechaInicial, $fechaFinal);
$entradasPorMedioPago = ControladorContabilidad::ctrSumaEntradasPorMedioPago($fechaInicial, $fechaFinal);
$arqueoEntradasEfectivo = ControladorContabilidad::ctrSumaTotalPorTipoYMedio("Entrada", "Efectivo", $fechaInicial, $fechaFinal);
$arqueoGastosEfectivo = ControladorContabilidad::ctrSumaTotalPorTipoYMedio("Gasto", "Efectivo", $fechaInicial, $fechaFinal);
$totalGastos = ControladorContabilidad::ctrSumaTotalGastos($fechaInicial, $fechaFinal);
$gastosPorMedioPago = ControladorContabilidad::ctrSumaGastosPorMedioPago($fechaInicial, $fechaFinal);

// --- CONSTRUCCIÓN DEL EXCEL ---
echo utf8_decode("
<table border='1'>
    <tr><td colspan='3' style='font-weight:bold; background-color:#3c8dbc; color:white;'>RESUMEN GENERAL</td></tr>
    <tr>
        <td style='font-weight:bold;'>Total Entradas (Dinero Ingresado)</td>
        <td style='font-weight:bold;'>Total por Cobrar (Deuda)</td>
        <td style='font-weight:bold;'>Total Vendido (Generado en Ventas)</td>
    </tr>
    <tr>
        <td>" . number_format($totalEntradas["total"] ?? 0, 2, ',', '') . "</td>
        <td>" . number_format($totalDeuda["total_deuda"] ?? 0, 2, ',', '') . "</td>
        <td>" . number_format($totalVendido["total_ventas"] ?? 0, 2, ',', '') . "</td>
    </tr>

    <tr></tr>
    <tr><td colspan='2' style='font-weight:bold; background-color:#00a65a; color:white;'>TOTAL DE ENTRADAS POR MEDIO DE PAGO</td></tr>
    <tr><td style='font-weight:bold;'>Medio de Pago</td><td style='font-weight:bold;'>Total Entradas</td></tr>
");
if (!empty($entradasPorMedioPago)) { foreach ($entradasPorMedioPago as $item) { echo "<tr><td>" . $item['medio_pago'] . "</td><td>" . number_format($item['total_entradas'], 2, ',', '') . "</td></tr>"; } }

echo utf8_decode("
    <tr></tr>
    <tr><td colspan='2' style='font-weight:bold; background-color:#f39c12; color:white;'>ARQUEO Y RESUMEN DE GASTOS</td></tr>
    <tr><td style='font-weight:bold;'>Concepto</td><td style='font-weight:bold;'>Total</td></tr>
    <tr><td>Arqueo de Efectivo</td><td>" . number_format(($arqueoEntradasEfectivo["total"] ?? 0) - ($arqueoGastosEfectivo["total"] ?? 0), 2, ',', '') . "</td></tr>
    <tr><td>Total de Gastos</td><td>" . number_format($totalGastos["total"] ?? 0, 2, ',', '') . "</td></tr>

    <tr></tr>
    <tr><td colspan='2' style='font-weight:bold; background-color:#dd4b39; color:white;'>DESGLOSE DE GASTOS POR MEDIO DE PAGO</td></tr>
    <tr><td style='font-weight:bold;'>Medio de Pago</td><td style='font-weight:bold;'>Total Gastos</td></tr>
");
if (!empty($gastosPorMedioPago)) { foreach ($gastosPorMedioPago as $item) { echo "<tr><td>" . $item['medio_pago'] . "</td><td>" . number_format($item['total_gastos'], 2, ',', '') . "</td></tr>"; } }

echo utf8_decode("
    <tr></tr>
    <tr><td colspan='2' style='font-weight:bold; background-color:#00c0ef; color:white;'>TOTAL DE VENTAS POR VENDEDOR</td></tr>
    <tr><td style='font-weight:bold;'>Vendedor</td><td style='font-weight:bold;'>Total Vendido</td></tr>
");
if (!empty($ventasPorVendedor)) { foreach ($ventasPorVendedor as $vendedor) { echo "<tr><td>" . $vendedor['vendedor'] . "</td><td>" . number_format($vendedor['total_vendido'], 2, ',', '') . "</td></tr>"; } }

echo "</table>";
?>