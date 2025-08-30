<?php

// --- Se incluyen los controladores necesarios para las tarjetas ---
require_once "controladores/ventas.controlador.php";
require_once "modelos/ventas.modelo.php";
require_once "controladores/contabilidad.controlador.php";
require_once "modelos/contabilidad.modelo.php";
require_once "controladores/reportes.controlador.php";
require_once "modelos/reportes.modelo.php";
require_once "controladores/usuarios.controlador.php";
require_once "modelos/usuarios.modelo.php";


if($_SESSION["perfil"] == "Especial" || $_SESSION["perfil"] == "Vendedor"){

  echo '<script>

    window.location = "inicio";

  </script>';

  return;

}

?>
<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Reportes de ventas
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Reportes de ventas</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <div class="input-group">

          <!-- Se usa un ID único para este botón para evitar conflictos -->
          <button type="button" class="btn btn-default" id="daterange-btn-reportes">
           
            <span>
              <i class="fa fa-calendar"></i> 

              <?php

                if(isset($_GET["fechaInicial"]) && !empty($_GET["fechaInicial"])){

                  echo $_GET["fechaInicial"]." - ".$_GET["fechaFinal"];
                
                }else{
                 
                  echo 'Rango de fecha';

                }

              ?>
            </span>

            <i class="fa fa-caret-down"></i>

          </button>

        </div>

        <div class="box-tools pull-right">
            <?php
                $urlDescarga = "vistas/modulos/descargar-reporte.php?reporte=reporte";
                if (isset($_GET["fechaInicial"])) {
                    $urlDescarga .= "&fechaInicial=" . $_GET["fechaInicial"] . "&fechaFinal=" . $_GET["fechaFinal"];
                }
            ?>
            <a href="<?= $urlDescarga ?>">
                <button class="btn btn-success" style="margin-top:5px">Descargar reporte en Excel</button>
            </a>
        </div>
        
      </div>
      
      <div class="box-body">

        <?php
            
            $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
            $fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;

            // Se preparan las fechas para las consultas SQL
            $fechaInicialSql = $fechaInicial ? $fechaInicial . " 00:00:00" : null;
            $fechaFinalSql = $fechaFinal ? $fechaFinal . " 23:59:59" : null;
    
            // --- CÁLCULOS PARA TODAS LAS TARJETAS Y TABLAS ---
            $entradasResult = ControladorContabilidad::ctrSumaTotalEntradas($fechaInicial, $fechaFinal);
            $deudaResult = ControladorVentas::ctrSumaTotalDeuda($fechaInicial, $fechaFinal);
            $vendidoResult = ControladorVentas::ctrSumaTotalVentasGeneral($fechaInicial, $fechaFinal);
            $totalDescuentos = ControladorReportes::ctrGetTotalDescuentos($fechaInicialSql, $fechaFinalSql);
            $totalesEntradas = ControladorContabilidad::ctrSumaEntradasPorMedioPago($fechaInicial, $fechaFinal);
            $entradasEfectivo = ControladorContabilidad::ctrSumaTotalPorTipoYMedio("Entrada", "Efectivo", $fechaInicial, $fechaFinal);
            $gastosEfectivo = ControladorContabilidad::ctrSumaTotalPorTipoYMedio("Gasto", "Efectivo", $fechaInicial, $fechaFinal);
            $totalGastos = ControladorContabilidad::ctrSumaTotalGastos($fechaInicial, $fechaFinal);
            $gastosPorMedioPago = ControladorContabilidad::ctrSumaGastosPorMedioPago($fechaInicial, $fechaFinal);
            $ventasPorVendedor = ControladorVentas::ctrSumaVentasPorVendedor($fechaInicial, $fechaFinal);
    
            // --- Asignación a variables ---
            $totalEntradas = $entradasResult["total"] ?? 0;
            $totalDeuda = $deudaResult["total_deuda"] ?? 0;
            $totalVendido = $vendidoResult["total_ventas"] ?? 0;
            $arqueoDeEfectivo = ($entradasEfectivo["total"] ?? 0) - ($gastosEfectivo["total"] ?? 0);
    
        ?>

        <!--=====================================
        SECCIÓN 1: RESUMEN GENERAL
        ======================================-->
        <section class="content">
            <div class="row">
                <h2>Resumen General</h2>
                <hr>
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h3>$<?= number_format($totalEntradas, 2) ?></h3>
                            <p>Total Entradas (Dinero Ingresado)</p>
                        </div>
                        <div class="icon"><i class="ion ion-arrow-up-a"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h3>$<?= number_format($totalDeuda, 2) ?></h3>
                            <p>Total por Cobrar (Deuda)</p>
                        </div>
                        <div class="icon"><i class="ion ion-alert-circled"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>$<?= number_format($totalVendido, 2) ?></h3>
                            <p>Total Vendido (Generado en Ventas)</p>
                        </div>
                        <div class="icon"><i class="ion ion-social-usd"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-orange">
                        <div class="inner">
                            <h3>$<?= number_format($totalDescuentos, 2) ?></h3>
                            <p>Total Descuentos</p>
                        </div>
                        <div class="icon"><i class="ion ion-arrow-graph-down-right"></i></div>
                    </div>
                </div>
            </div>
        </section>

        <!--===============================================
        SECCIÓN 2: TOTAL DE ENTRADAS POR MEDIO DE PAGO
        ================================================-->
        <section class="content">
            <div class="row">
                <h2>Total de Entradas por Medio de Pago</h2>
                <hr>
                <?php
                    $colores = ["bg-aqua", "bg-green", "bg-yellow", "bg-red", "bg-blue", "bg-purple", "bg-teal", "bg-maroon", "bg-gray"];
                    $colorIndex = 0;
                    if (!empty($totalesEntradas)) {
                        foreach ($totalesEntradas as $key => $value) {
                            if($value["total_entradas"] > 0){
                                echo '<div class="col-lg-3 col-xs-6">
                                        <div class="small-box ' . $colores[$colorIndex] . '">
                                            <div class="inner">
                                                <h3>$' . number_format($value["total_entradas"], 2) . '</h3>
                                                <p>' . $value["medio_pago"] . '</p>
                                            </div>
                                            <div class="icon"><i class="ion ion-social-usd"></i></div>
                                        </div>
                                      </div>';
                                $colorIndex = ($colorIndex + 1) % count($colores);
                            }
                        }
                    } else {
                        echo '<div class="col-xs-12"><p class="text-center">No hay datos de entradas para el período seleccionado.</p></div>';
                    }
                ?>
            </div>
        </section>

        <!--===============================================
        SECCIÓN 3: ARQUEO Y RESUMEN DE GASTOS
        ================================================-->
        <section class="content">
            <hr>
            <div class="row">
                <h2>Arqueo y Resumen de Gastos</h2>
                <hr>
                <div class="col-lg-6 col-xs-12">
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>$<?= number_format($arqueoDeEfectivo, 2) ?></h3>
                            <p>Arqueo de Efectivo (Entradas Efectivo - Gastos Efectivo)</p>
                        </div>
                        <div class="icon"><i class="ion ion-calculator"></i></div>
                    </div>
                </div>
                <div class="col-lg-6 col-xs-12">
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h3>$<?= number_format($totalGastos["total"] ?? 0, 2) ?></h3>
                            <p>Total de Gastos (Todos los medios de pago)</p>
                        </div>
                        <div class="icon"><i class="ion ion-arrow-graph-down-right"></i></div>
                    </div>
                </div>
            </div>
        </section>

        <!--===============================================
        SECCIÓN 4: DESGLOSE DE GASTOS POR MEDIO DE PAGO
        ================================================-->
        <section class="content">
            <div class="row">
                <h3>Desglose de Gastos por Medio de Pago</h3>
                <hr>
                <?php
                    $coloresGastos = ["bg-maroon", "bg-purple", "bg-orange", "bg-blue-active", "bg-green-active"];
                    $colorIndexGastos = 0;
                    if (!empty($gastosPorMedioPago)) {
                        foreach ($gastosPorMedioPago as $key => $value) {
                            if($value["total_gastos"] > 0){
                                echo '<div class="col-lg-3 col-xs-6">
                                        <div class="small-box ' . $coloresGastos[$colorIndexGastos] . '">
                                            <div class="inner">
                                                <h3>$' . number_format($value["total_gastos"], 2) . '</h3>
                                                <p>' . $value["medio_pago"] . '</p>
                                            </div>
                                            <div class="icon"><i class="ion ion-pie-graph"></i></div>
                                        </div>
                                      </div>';
                                $colorIndexGastos = ($colorIndexGastos + 1) % count($coloresGastos);
                            }
                        }
                    } else {
                        echo '<div class="col-xs-12"><p class="text-center">No hay datos de gastos para el período seleccionado.</p></div>';
                    }
                ?>
            </div>
        </section>

        <!--===============================================
        SECCIÓN 5: TOTAL DE VENTAS POR VENDEDOR
        ================================================-->
        <section class="content">
            <br>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Total de Ventas por Vendedor</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Vendedor</th>
                                <th>Total Vendido</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if (!empty($ventasPorVendedor)) {
                                    foreach ($ventasPorVendedor as $key => $value) {
                                        echo '<tr>
                                                <td>'.($key + 1).'</td>
                                                <td>'.$value["vendedor"].'</td>
                                                <td>$ '.number_format($value["total_vendido"], 2).'</td>
                                              </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="text-center">No hay datos de ventas para el período seleccionado.</td></tr>';
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

      <div class="box-body">
        
        <div class="row">

          <div class="col-xs-12">
            
            <?php

            include "reportes/grafico-ventas.php";

            ?>

          </div>

      </div>
      
    </div>

  </section>
 
</div>