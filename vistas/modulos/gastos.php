<?php

if ($_SESSION["perfil"] == "Especial") {

  echo '<script>

    window.location = "inicio";

  </script>';

  return;
}

$xml = ControladorVentas::ctrDescargarXML();

if ($xml) {

  rename($_GET["xml"] . ".xml", "xml/" . $_GET["xml"] . ".xml");

  echo '<a class="btn btn-block btn-success abrirXML" archivo="xml/' . $_GET["xml"] . '.xml" href="ventas">Se ha creado correctamente el archivo XML <span class="fa fa-times pull-right"></span></a>';
}

?>
<div class="content-wrapper">

  <section class="content-header">

    <h1>

      Gastos

    </h1>

    <ol class="breadcrumb">

      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>

      <li class="active">Gastos</li>

    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <a href="crear-gastos">

          <button class="btn btn-primary">

            Agregar gasto

          </button>

        </a>
        
        <?php

        if (count($_GET) === 1) {
        ?>
          <a class="btn btn-success" href="vistas/modulos/reporte-gastos.php">
            Reporte Excel
          </a>
        <?php
        }

        if (isset($_GET["fechaInicial"])) {
        ?>
          <a class="btn btn-success" href="vistas/modulos/reporte-gastos.php?fechaInicial=<?= $_GET["fechaInicial"] ?>&fechaFinal=<?= $_GET["fechaFinal"] ?>">
            Reporte Excel
          </a>
        <?php
        }

        if (isset($_GET["medioPago"])) {
        ?>
          <a class="btn btn-success" href="vistas/modulos/reporte-gastos.php?medioPago=<?= $_GET["medioPago"] ?>">
            Reporte Excel
          </a>
        <?php
        }

        ?>
        
        <?php 
          include "componentes/filtro-medio-pago.php"; 
        ?>

        <!-- =================================================================== -->
        <!-- CORRECCIÓN 1: Se cambia el ID del botón a uno único para esta página -->
        <!-- =================================================================== -->
        <button type="button" class="btn btn-default pull-right" id="daterange-btn-gastos">
          <span>
            <i class="fa fa-calendar"></i>
            <?php
            if (isset($_GET["fechaInicial"])) {
              echo $_GET["fechaInicial"] . " - " . $_GET["fechaFinal"];
            } else {
              echo 'Rango de fecha';
            }
            ?>
          </span>
          <i class="fa fa-caret-down"></i>
        </button>


      </div>

      <div class="box-body">

        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">

          <thead>

            <tr>

              <th style="width:3px">#</th>
              <th>Vendedor</th>
              <th>Detalle</th>
              <th>Valor</th>
              <th>Medio Pago</th>
              <th>Fecha</th>
              <th>Acciones</th>

            </tr>

          </thead>

          <tbody>
            <?php
              $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
              $fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;
              $medioPago = isset($_GET["medioPago"]) ? $_GET["medioPago"] : null;

              $respuesta = ControladorContabilidad::filterBy($fechaInicial, $fechaFinal, $medioPago, 'Gasto');

              foreach ($respuesta as $key => $value) {

                $vendedor = ControladorUsuarios::ctrMostrarUsuarios('id', $value["id_vendedor"]);

                echo '<tr>
                        <td>' . ($key + 1) . '</td>
                        <td>' . $vendedor["nombre"] . '</td>
                        <td>' . $value["detalle"] . '</td>
                        <td>$ ' . number_format((float)$value["valor"], 2, ',', '.') . '</td>
                        <td>' . $value["medio_pago"] . '</td>
                        
                        <td>' . date('Y-m-d H:i:s', strtotime($value["fecha"])) . '</td>
                        
                        <td>';
                
                if ($_SESSION["perfil"] == "Administrador") {
                  echo '<div class="btn-group">
                          <button class="btn btn-warning btn-xs btnEditarGasto" idGasto="'.$value["id"].'"><i class="fa fa-pencil"></i></button>
                          <button class="btn btn-danger btn-xs btnEliminarGasto" idGasto="'.$value["id"].'"><i class="fa fa-times"></i></button>
                        </div>';
                }

                echo '  </td>
                      </tr>';
              }
            ?>
          </tbody>
        </table>
        <?php
        ControladorContabilidad::deleteGasto();
        ?>
      </div>
    </div>
  </section>
</div>

<!-- (El código de tu MODAL va aquí sin cambios) -->

<!-- =================================================================== -->
<!-- CORRECCIÓN 2: Se añade el script local para el filtro de fecha -->
<!-- =================================================================== -->
<script>
$(document).ready(function() {
    // Se apunta al ID único 'daterange-btn-gastos'
    $('#daterange-btn-gastos').daterangepicker(
      {
        ranges: {
          'Hoy': [moment(), moment()], 'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Últimos 7 días': [moment().subtract(6, 'days'), moment()], 'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
          'Este mes': [moment().startOf('month'), moment().endOf('month')],
          'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment(), endDate: moment()
      },
      function (start, end) {
        $('#daterange-btn-gastos span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        var fechaInicial = start.format('YYYY-MM-DD');
        var fechaFinal = end.format('YYYY-MM-DD');
        // Se recarga la página con la ruta correcta: 'gastos'
        window.location = "index.php?ruta=gastos&fechaInicial=" + fechaInicial + "&fechaFinal=" + fechaFinal;
      }
    );

    // Se añade la lógica para el botón "Cancelar"
    $('#daterange-btn-gastos').on('cancel.daterangepicker', function(ev, picker) {
        window.location = "gastos";
    });
});
</script>
