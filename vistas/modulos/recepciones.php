<div class="content-wrapper">
  <section class="content-header">
    <h1>Historial de Recepciones</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Recepciones</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablaRecepciones" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Sucursal Destino</th>
              <th>Recibido por</th>
              <th>Entregado por (Transportador)</th>
              <th>Fecha de Recepci¨®n</th>
              <th>Acciones</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </section>
</div>

<div id="modalVerDetalleRecepcion" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#00a65a; color:white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Detalle de la Recepci¨®n</h4>
      </div>
      <div class="modal-body">
        <table class="table">
          <thead>
            <tr>
              <th>Descripci¨®n</th>
              <th>Cantidad Recibida</th>
            </tr>
          </thead>
          <tbody id="detalleProductosRecibidos"></tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>