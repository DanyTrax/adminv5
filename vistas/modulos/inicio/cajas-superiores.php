<?php
// =================================================================
// INCLUSIÓN DE ARCHIVOS NECESARIOS
// =================================================================
// (Se asume que tu plantilla principal ya carga estos archivos)
require_once "controladores/reportes.controlador.php";
require_once "modelos/reportes.modelo.php";
require_once "controladores/contabilidad.controlador.php";
require_once "modelos/contabilidad.modelo.php";
require_once "controladores/ventas.controlador.php";
require_once "modelos/ventas.modelo.php";

// =================================================================
// OBTENCIÓN DE DATOS PARA LAS TARJETAS DE RESUMEN
// =================================================================

// --- CÁLCULOS PARA EL DÍA DE HOY ---
$fechaHoyInicial = date("Y-m-d") . " 00:00:00";
$fechaHoyFinal   = date("Y-m-d") . " 23:59:59";

// Se usan las funciones centralizadas en ControladorReportes para consistencia
$totalEntradasHoy = ControladorReportes::ctrDashboardTotalEntradasHoy();
$totalEntradasEfectivoHoy = ControladorReportes::ctrDashboardTotalEntradasEfectivoHoy();
$totalVendidoGeneral = ControladorReportes::ctrDashboardSumaTotalVentasGeneral();
$totalDescuentosHoy = ControladorReportes::ctrGetTotalDescuentos($fechaHoyInicial, $fechaHoyFinal) ?? 0;

// --- INICIO DE LA CORRECCIÓN: Se usan las funciones correctas para los gastos ---
$totalGastosHoy = ControladorReportes::ctrDashboardTotalGastosHoy();
$totalGastosEfectivoHoy = ControladorReportes::ctrDashboardTotalGastosEfectivoHoy();
// --- FIN DE LA CORRECCIÓN ---

$arqueoEfectivoHoy = $totalEntradasEfectivoHoy - $totalGastosEfectivoHoy;

?>

<!-- INICIO DE LA SECCIÓN DE CONTENIDO HTML -->
<section class="content">

    <!-- Fila 1 de Tarjetas de Reporte (Ahora con 4 tarjetas) -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>$<?= number_format($totalEntradasHoy, 0) ?></h3>
                    <p>Total Entradas (Hoy)</p>
                </div>
                <div class="icon"><i class="ion ion-arrow-up-a"></i></div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>$<?= number_format($totalEntradasEfectivoHoy, 0) ?></h3>
                    <p>Total Entradas Efectivo (Hoy)</p>
                </div>
                <div class="icon"><i class="ion ion-cash"></i></div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>$<?= number_format($totalVendidoGeneral, 0) ?></h3>
                    <p>Total Vendido (General)</p>
                </div>
                <div class="icon"><i class="ion ion-social-usd"></i></div>
            </div>
        </div>
        
        <!-- NUEVA TARJETA: TOTAL DESCUENTOS -->
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3>$<?= number_format($totalDescuentosHoy, 0) ?></h3>
                    <p>Total Descuentos (Hoy)</p>
                </div>
                <div class="icon"><i class="ion ion-arrow-graph-down-right"></i></div>
            </div>
        </div>
    </div>

    <!-- Fila 2 de Tarjetas de Reporte (Se mantiene igual) -->
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>$<?= number_format($totalGastosHoy, 0) ?></h3>
                    <p>Total Gastos (Hoy)</p>
                </div>
                <div class="icon"><i class="ion ion-arrow-down-a"></i></div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
            <div class="small-box bg-maroon">
                <div class="inner">
                    <h3>$<?= number_format($totalGastosEfectivoHoy, 0) ?></h3>
                    <p>Total Gastos en Efectivo (Hoy)</p>
                </div>
                <div class="icon"><i class="ion ion-ios-paperplane-outline"></i></div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>$<?= number_format($arqueoEfectivoHoy, 0) ?></h3>
                    <p>Arqueo de Efectivo (Hoy)</p>
                </div>
                <div class="icon"><i class="ion ion-calculator"></i></div>
            </div>
        </div>
    </div>