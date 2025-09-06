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
<!-- Modal Ver Manifiesto -->
<div class="modal fade" id="modalVerManifiesto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">📋 Manifiesto de Transferencia</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th width="20%">Código</th>
                                <th width="60%">Descripción</th>
                                <th width="20%" class="text-center">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody id="listaProductosManifiesto">
                            <!-- Los datos se cargan aquí dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>