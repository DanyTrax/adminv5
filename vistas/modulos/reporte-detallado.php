<?php
// --- INICIO DE LA CORRECCIÓN: Se añaden los 'require_once' ---
require_once "controladores/reportes.controlador.php";
require_once "modelos/reportes.modelo.php";
require_once "controladores/contabilidad.controlador.php";
require_once "modelos/contabilidad.modelo.php";
require_once "controladores/ventas.controlador.php";
require_once "modelos/ventas.modelo.php";
// --- FIN DE LA CORRECCIÓN ---

// --- Control de Acceso ---
if ($_SESSION["perfil"] != "Administrador") {
    echo '<script>window.location = "inicio";</script>';
    return;
}

// 1. Leemos las fechas de la URL para pasarlas a todas las funciones
$fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
$fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;

// --- OBTENER DATOS PARA LOS RESÚMENES ---
$entradasResult = ControladorContabilidad::ctrSumaTotalEntradas($fechaInicial, $fechaFinal);
$deudaResult = ControladorVentas::ctrSumaTotalDeuda($fechaInicial, $fechaFinal);
$vendidoResult = ControladorVentas::ctrSumaTotalVentasGeneral($fechaInicial, $fechaFinal);
$entradasPorMedioPago = ControladorContabilidad::ctrSumaEntradasPorMedioPago($fechaInicial, $fechaFinal);

$fechaInicialSql = $fechaInicial ? $fechaInicial . " 00:00:00" : null;
$fechaFinalSql = $fechaFinal ? $fechaFinal . " 23:59:59" : null;
$totalDescuentos = ControladorReportes::ctrGetTotalDescuentos($fechaInicialSql, $fechaFinalSql);

$totalEntradas = $entradasResult["total"] ?? 0;
$totalDeuda = $deudaResult["total_deuda"] ?? 0;
$totalVendido = $vendidoResult["total_ventas"] ?? 0;

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Reporte Detallado de Ventas</h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Reporte Detallado</li>
        </ol>
    </section>
    
    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3>Ventas por Producto</h3>
                <div class="box-tools pull-right">
                    <?php
                        $urlDescarga = "vistas/modulos/descargar-reporte-detallado.php";
                        if (isset($_GET["fechaInicial"])) {
                            $urlDescarga .= "?fechaInicial=" . $_GET["fechaInicial"] . "&fechaFinal=" . $_GET["fechaFinal"];
                        }
                    ?> 	
                    <a href="<?= $urlDescarga ?>" style="margin-left:10px;">
                        <button class="btn btn-success" style="margin-right: 15px;">Descargar reporte en Excel</button>
                    </a>
                </div>
                <button type="button" class="btn btn-default pull-right" id="daterange-btn-detallado">
                    <span>
                        <i class="fa fa-calendar"></i> 
                        <?php
                            if ($fechaInicial) {
                                echo $fechaInicial . " - " . $fechaFinal;
                            } else {
                                echo 'Rango de fecha';
                            }
                        ?>
                    </span>
                    <i class="fa fa-caret-down"></i>
                </button>
            </div>
            <div class="box-body">
                <table id="tablaReporteDetallado" class="table table-bordered table-striped dt-responsive" width="100%">
                    <thead>
                        <tr>
                            <th style="width:10px">#</th>
                            <th>Fecha</th>
                            <th>Factura</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>Descripción del Producto</th>
                            <th>Cantidad</th>
                            <th>Total Producto</th>
                            <th>Medio de Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div> 
    </section> 

    <section class="content">
        <div class="row">
            <h2>Resumen Financiero del Periodo</h2>
            <hr>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>$<?= number_format($totalVendido, 0) ?></h3>
                        <p>Total Vendido (Generado)</p>
                    </div>
                    <div class="icon"><i class="ion ion-social-usd"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>$<?= number_format($totalEntradas, 0) ?></h3>
                        <p>Total Entradas (Ingresado)</p>
                    </div>
                    <div class="icon"><i class="ion ion-arrow-up-a"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>$<?= number_format($totalDeuda, 0) ?></h3>
                        <p>Total por Cobrar (Deuda)</p>
                    </div>
                    <div class="icon"><i class="ion ion-alert-circled"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-orange">
                    <div class="inner">
                        <h3>$<?= number_format($totalDescuentos, 0) ?></h3>
                        <p>Total Descuentos</p>
                    </div>
                    <div class="icon"><i class="ion ion-arrow-graph-down-right"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <h2>Desglose de Entradas por Medio de Pago</h2>
            <hr>
            <?php
                $colores = ["bg-aqua", "bg-green", "bg-yellow", "bg-red", "bg-blue", "bg-purple"];
                $colorIndex = 0;
                foreach ($entradasPorMedioPago as $key => $value) {
                    if($value["total_entradas"] > 0){
                        echo '<div class="col-lg-3 col-xs-6">
                                <div class="small-box ' . $colores[$colorIndex] . '">
                                    <div class="inner">
                                        <h3>$' . number_format($value["total_entradas"], 0) . '</h3>
                                        <p>' . $value["medio_pago"] . '</p>
                                    </div>
                                    <div class="icon"><i class="fa fa-credit-card"></i></div>
                                </div>
                              </div>';
                        $colorIndex = ($colorIndex + 1) % count($colores);
                    }
                }
            ?>
        </div>
    </section>
</div> 

<script>
$(document).ready(function() {
    var tabla = $('#tablaReporteDetallado').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/datatable-reporte-detallado.php",
            "type": "POST",
            "data": function(d) {
                d.fechaInicial = "<?php echo $fechaInicial; ?>";
                d.fechaFinal = "<?php echo $fechaFinal; ?>";
            }
        },
        "columns": [
            { "data": "contador", "searchable": false, "orderable": false },
            { "data": "fecha_venta" },
            { "data": "codigo_factura" },
            { "data": "vendedor" },
            { "data": "cliente" },
            { "data": "producto_descripcion" },
            { "data": "producto_cantidad" },
            { "data": "producto_total" },
            { "data": "medio_pago" }
        ],
        "columnDefs": [
            {
                "targets": 1,
                 "render": function(data, type, row) {
                    if (type === 'display' && data) {
                        var date = new Date(data);
                        if (!isNaN(date.getTime())) {
                            var day = ('0' + date.getDate()).slice(-2);
                            var month = ('0' + (date.getMonth() + 1)).slice(-2);
                            var year = date.getFullYear().toString().slice(-2);
                            var hours = ('0' + date.getHours()).slice(-2);
                            var minutes = ('0' + date.getMinutes()).slice(-2);
                            return `${day}/${month}/${year} ${hours}:${minutes}`;
                        }
                    }
                    return 'Fecha inválida';
                }
            },
            { "targets": 6, "className": "dt-center" },
            {
                "targets": 7,
                "render": function(data, type, row) {
                    if (type === 'display' && data) {
                        var number = parseFloat(data);
                        return '$ ' + number.toLocaleString('es-CO', { maximumFractionDigits: 0 });
                    }
                    return data;
                }
            }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
        },
        "responsive": true,
        "autoWidth": false,
        "order": [[ 1, "desc" ]],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
    });

    $('#daterange-btn-detallado').on('apply.daterangepicker', function(ev, picker) {
        var fechaInicial = picker.startDate.format('YYYY-MM-DD');
        var fechaFinal = picker.endDate.format('YYYY-MM-DD');
        window.location = "index.php?ruta=reporte-detallado&fechaInicial=" + fechaInicial + "&fechaFinal=" + fechaFinal;
    });
});
</script>