<div class="content-wrapper">
  <section class="content-header">
    <h1>Cargues Pendientes de Confirmación</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Cargues Pendientes</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablaCarguesPendientes" width="100%">
            <thead>
                <tr>
                  <th style="width:10px"># Transferencia</th>
                  <th>Sucursal Origen</th>
                  <th>Preparado por</th>
                  <th>Transportador Asignado</th>
                  <th>Fecha Preparacion</th>
                  <th>Fecha Estado</th><th>Estado</th> 
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
<div id="modalVerManifiesto" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#00c0ef; color:white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Manifiesto de Carga</h4>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <thead><tr><th>Descripción</th><th>Cantidad Enviada</th></tr></thead>
          <tbody id="listaProductosManifiesto"></tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>