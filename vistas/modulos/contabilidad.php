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

      Contabilidad ventas

    </h1>

    <ol class="breadcrumb">

      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>

      <li class="active">Contabilidad ventas</li>

    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <!-- <---------------------------------------------------------------------- -->
        <!-- <---------------------BOTON EXPORTAR VENTAS------------------------ -->
        <!-- <---------------------------------------------------------------------- -->


      <div class="box-header with-border">

    <?php
        // LÃ³gica para construir la URL de descarga con los filtros activos
        $urlDescarga = "vistas/modulos/reporte-ventas.php?reporte=contabilidad";
        if (isset($_GET["fechaInicial"])) {
            $urlDescarga .= "&fechaInicial=" . $_GET["fechaInicial"] . "&fechaFinal=" . $_GET["fechaFinal"];
        }
        if (isset($_GET["medioPago"]) && !empty($_GET["medioPago"])) {
            $urlDescarga .= "&medioPago=" . $_GET["medioPago"];
        }
        if (isset($_GET["formaPago"]) && !empty($_GET["formaPago"])) {
            $urlDescarga .= "&formaPago=" . $_GET["formaPago"];
        }
    ?>

    <a href="<?= $urlDescarga ?>">
        <button class="btn btn-success">Reporte Excel</button>
    </a>

    <?php
        $formaPago = isset($_GET['formaPago']) ? $_GET['formaPago'] : null;
        ?>
        <select class="btn pull-right" name="filter-formaPago" id="filter-formaPago" style="margin-left: 10px;">
            <option value="" <?= $formaPago === null ? 'selected' : '' ?>>Forma de Pago</option>
            <option value="">Todos</option>
            <?php foreach (FormaPago::ALL as $value) : ?>
                <option value="<?= $value ?>" <?= $formaPago === $value ? 'selected' : '' ?>><?= $value ?></option>
            <?php endforeach; ?>
        </select>
        
        <?php 
            // Aseg¨²rate de que la ruta sea correcta desde el archivo donde lo uses
            // (ej: ventas.php, gastos.php, etc.)
            include "componentes/filtro-medio-pago.php"; 
        ?>
        <button type="button" class="btn btn-default pull-right" id="daterange-btn-contabilidad">
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





        <!-- <---------------------------------------------------------------------- -->

      </div>

<div class="box-body">
    <?php
        $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : '';
        $fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : '';
        $medioPago = isset($_GET["medioPago"]) ? $_GET["medioPago"] : '';
        $formaPago = isset($_GET["formaPago"]) ? $_GET["formaPago"] : '';
    ?>

    <table class="table table-bordered table-striped dt-responsive tablaContabilidad" width="100%" 
           data-fecha-inicial="<?= htmlspecialchars($fechaInicial) ?>" 
           data-fecha-final="<?= htmlspecialchars($fechaFinal) ?>" 
           data-medio-pago="<?= htmlspecialchars($medioPago) ?>"
           data-forma-pago="<?= htmlspecialchars($formaPago) ?>">
        <thead>
            <tr>
                <th style="width:10px">#</th>
                <th>Factura</th>
                <th>Cliente</th>
                <th>Emp</th>
                <th>Vendedor</th>
                <th>V_Abono</th>
                <th>Forma de pago</th>
                <th>Neto</th>
                <th>Total</th>
                <th>Fecha Venta</th>
                <th>Abono</th>
                <th>Ult_Abono</th>
                <th>Pago</th>
                <th>Medio Pago</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

        </div>
        <?php
        $crearAbono = new ControladorVentas();
        $crearAbono->ctrCrearAbono();
        ?>
      </form>
    </div>
  </div>
</div>