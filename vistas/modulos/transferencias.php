<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Administrar Transferencias
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Transferencias</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
<a href="crear-transferencia">
  <button class="btn btn-primary">
    Solicitar Productos
  </button>
</a>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablaTransferencias" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Solicita</th>
              <th>Usuario</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
<div id="modalVerProductos" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#3c8dbc; color:white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Productos Solicitados</h4>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-striped" width="100%">
          <thead>
            <tr>
              <th>Descripción</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody id="listaProductosModal">
            </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
