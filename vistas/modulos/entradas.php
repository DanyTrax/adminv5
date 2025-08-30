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

      Entradas

    </h1>

    <ol class="breadcrumb">

      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>

      <li class="active">Entradas</li>

    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <a href="crear-entradas">

          <button class="btn btn-primary">

            Agregar entrada

          </button>

        </a>



        <?php
            // 1. Se construye la URL base
            $urlDescarga = "vistas/modulos/reporte-entradas.php";
            $filtros = [];
        
            // 2. Se añade cada filtro a la URL si existe
            if (isset($_GET["fechaInicial"]) && !empty($_GET["fechaInicial"])) {
                $filtros['fechaInicial'] = $_GET["fechaInicial"];
                $filtros['fechaFinal'] = $_GET["fechaFinal"];
            }
            if (isset($_GET["medioPago"]) && !empty($_GET["medioPago"])) {
                $filtros['medioPago'] = $_GET["medioPago"];
            }
            
            // 3. Se añaden los filtros a la URL si hay alguno
            if (!empty($filtros)) {
                $urlDescarga .= "?" . http_build_query($filtros);
            }
        ?>
        
        <a href="<?= $urlDescarga ?>">
            <button class="btn btn-success">Reporte Excel</button>
        </a>


        
        <?php 
            // Asegúrate de que la ruta sea correcta desde el archivo donde lo uses
            // (ej: ventas.php, gastos.php, etc.)
            include "componentes/filtro-medio-pago.php"; 
        ?>
        <button type="button" class="btn btn-default pull-right" id="daterange-btn-entrada">
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

    <?php
        // CORRECCIÓN: Definimos las variables ANTES de usarlas en la tabla.
        $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : '';
        $fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : '';
        $medioPago = isset($_GET["medioPago"]) ? $_GET["medioPago"] : '';
    ?>

    <table class="table table-bordered table-striped dt-responsive tablaEntradas" width="100%" 
           data-fecha-inicial="<?= htmlspecialchars($fechaInicial) ?>" 
           data-fecha-final="<?= htmlspecialchars($fechaFinal) ?>" 
           data-medio-pago="<?= htmlspecialchars($medioPago) ?>">

        <thead>
            <tr>
                <th style="width:3px">#</th>
                <th>Empresa</th>
                <th>Factura</th>
                <th>Fecha</th>
                <th>Descipción</th>
                <th>Valor</th>
                <th>Medio Pago</th>
                <th>Forma Pago</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            </tbody>
    </table>
    
    <?php
        ControladorContabilidad::deleteEntrada();
    ?>

</div>
    </div>
  </section>
</div>
<script>
<script>
$(document).ready(function() {
    // Se apunta al ID único 'daterange-btn-gastos'
    $('#daterange-btn3').daterangepicker(
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
        $('#daterange-btn3 span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        var fechaInicial = start.format('YYYY-MM-DD');
        var fechaFinal = end.format('YYYY-MM-DD');
        // Se recarga la página con la ruta correcta: 'gastos'
        window.location = "index.php?ruta=entradas&fechaInicial=" + fechaInicial + "&fechaFinal=" + fechaFinal;
      }
    );

    // Se añade la lógica para el botón "Cancelar"
    $('#daterange-btn3').on('cancel.daterangepicker', function(ev, picker) {
        window.location = "entradas";
    });
});
</script>