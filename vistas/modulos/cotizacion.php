<?php

if ($_SESSION["perfil"] == "Especial") {

  echo '<script>

    window.location = "inicio";

  </script>';

  return;
}

$listCotizaciones = ControladorCotizaciones::all();
?>

<div class="content-wrapper">

  <section class="content-header">

    <h1>

      Administrar cotizaciones

    </h1>

    <ol class="breadcrumb">

      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>

      <li class="active">Administrar cotizaciones</li>

    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">

        <a href="crear-cotizacion">

          <button class="btn btn-primary">

            Agregar cotizacion

          </button>

        </a>

      </div>

      <div class="box-body">

        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">

          <thead>

            <tr>

              <th style="width:3px">#</th>
              <th style="width:20px">C&oacute;digo cotizaci&oacute;n</th>
              <th>Cliente</th>
              <th style="width:3px">Emp</th>
              <th>Vendedor</th>
              <!-- <th>V_Abono</th> -->
              <!-- <th style="width:80px">Forma de pago</th> -->
              <th>Neto</th>
              <th>Total</th>
              <!-- <th>Detalle</th> -->
              <th>Fecha Venta</th>
              <!-- <th>Abono</th> -->
              <!-- <th>Ult_Abono</th> -->
              <!-- <th>Pago</th> -->
              <!-- <th>Medio Pago</th> -->
              <th>Acciones</th>

            </tr>

          </thead>

          <tbody>

            <?php
            foreach ($listCotizaciones as $key => $value) {
              echo '<tr>
              <td>' . ($key + 1) . '</td>
              <td>' . ($value["id"] + 1000) . '</td>';

              $respuestaCliente = ControladorClientes::ctrMostrarClientes("id", $value["id_cliente"]);
              echo '<td>' . $respuestaCliente["nombre"] . '</td>';
              $respuestaUsuario = ControladorUsuarios::ctrMostrarUsuarios("id", $value["id_vendedor"]);
              $respuestaUsuario_ab = ControladorUsuarios::ctrMostrarUsuarios("id", $value["id_vend_abono"]);

              if ($value["abono"] != 0) {
                $botones = '<div class="btn-group">
                  
                  <button class="btn btn-info btn-xs btnImprimirCotizacion" codigoVenta="' . $value["id"] . '">
                    <i class="fa fa-print"></i>
                  </button>';
                if ($_SESSION["perfil"] == "Administrador") {
                  $botones .= '<button class="btn btn-warning btn-xs btnEditarCotizacion" idVenta="' . $value["id"] . '"><i class="fa fa-pencil"></i></button>';
                  $botones .= '
                    <button class="btn btn-danger btn-xs btnEliminarCotizacion" idCotizacion="' . $value["id"] . '"><i class="fa fa-times"></i></button>';
                }
              } else {
                $botones = '<div class="btn-group">
                  
                  <button class="btn btn-info btn-xs btnImprimirCotizacion" codigoVenta="' . $value["id"] . '">
                    <i class="fa fa-print"></i>
                  </button>';
                if ($_SESSION["perfil"] == "Administrador") {
                  $botones .= '<button class="btn btn-warning btn-xs btnEditarCotizacion" idVenta="' . $value["id"] . '"><i class="fa fa-pencil"></i></button>';
                  $botones .= '
                    <button class="btn btn-danger btn-xs btnEliminarCotizacion" idCotizacion="' . $value["id"] . '"><i class="fa fa-times"></i></button>';
                }
                $botones .= '</div>';
              }

echo '<td>' . (isset($respuestaUsuario_ab["empresa"]) ? $respuestaUsuario_ab["empresa"] : '') . '</td>
<td>' . (isset($respuestaUsuario["nombre"]) ? $respuestaUsuario["nombre"] : '') . '</td>
<td>$ ' . number_format($value["neto"], 2, ",", ".") . '</td>
<td>$ ' . number_format($value["total"], 2, ",", ".") . '</td>
<td>' . $value["fecha_abono"] . '</td>
<td>' . $botones . '</td>
</tr>';
            }
            ?>
          </tbody>
        </table>
        <?php
        ControladorCotizaciones::delete();
        ?>
      </div>
    </div>
  </section>
</div>

<!-- MODAL AGREGAR ABONO -->
<div id="modalAbonar" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Abono</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="form-group">
              <div class="input-group col-xs-12">
                <label>Dinero Restante</label>
                <input type="text" class="form-control dinRestante" disabled>
              </div>
            </div>
            <input type="hidden" class="form-control idUsuarioAbo" name="idUsuarioAbo">
            <input type="hidden" class="form-control idVentaAbo" name="idVentaAbo">
            <div class="form-group">
              <div class="input-group col-xs-12">
                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                <input type="text" class="form-control nuevoAbono" name="nuevoAbono" placeholder="Ingrese Nuevo Abono" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar Abono</button>
        </div>
        <?php
        $crearAbono = new ControladorVentas();
        $crearAbono->ctrCrearAbono();
        ?>
      </form>
    </div>
  </div>
</div>