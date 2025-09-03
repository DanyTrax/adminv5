<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Administrar Sucursales
      <small>Directorio Central de Sucursales</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar Sucursales</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarSucursal">
          <i class="fa fa-plus"></i> Agregar Sucursal
        </button>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablaSucursales" width="100%">
          <thead>
            <tr>
              <th style="width:60px">Logo</th>
              <th style="width:80px">Código</th>
              <th>Nombre</th>
              <th style="width:200px">Dirección</th>
              <th style="width:120px">Teléfono</th>
              <th style="width:80px">Estado</th>
              <th style="width:150px">Última Sync</th>
              <th style="width:120px">Acciones</th>
            </tr> 
          </thead>
        </table>
      </div>
    </div>
  </section>
</div>

<?php
  // Estos llamados se mantienen para procesar los formularios
  $crearSucursal = new ControladorSucursales();
  $crearSucursal->ctrCrearSucursal();

  $actualizarSucursal = new ControladorSucursales();
  $actualizarSucursal->ctrActualizarSucursal();

  $eliminarSucursal = new ControladorSucursales();
  $eliminarSucursal->ctrEliminarSucursal();
?>