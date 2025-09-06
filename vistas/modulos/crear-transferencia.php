<div class="content-wrapper">
  <section class="content-header">
    <h1>Crear Solicitud de Transferencia</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Crear Solicitud</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">

      <div class="col-lg-5 col-xs-12">
        <div class="box box-success">
          <div class="box-header with-border"></div>
          <form role="form" method="post" id="formularioTransferencia">
            <div class="box-body">
              <div class="box">
                
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                    <select class="form-control" name="seleccionarDestino" required>
                      <option value="">Seleccionar sucursal de destino</option>
                      <option value="Sucursal Principal">Sucursal Principal</option>
                      <option value="Sucursal Norte">Sucursal Norte</option>
                       </select>
                  </div>
                </div>

                <table class="table table-bordered" width="100%">
                  <thead>
                    <tr>
                      <th>Descripción</th>
                      <th>Cantidad</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="productosSolicitados">
                    </tbody>
                </table>

              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary pull-right">Crear Solicitud</button>
            </div>
          </form>
        </div>
      </div>

      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">
        <div class="box box-warning">
          <div class="box-header with-border"></div>
          <div class="box-body">
            <table class="table table-bordered table-striped dt-responsive tablaProductosTransferencia" width="100%">
               <thead>
                 <tr>
                  <th style="width: 10px">#</th>
                  <th>Imagen</th>
                  <th>Código</th>
                  <th>Descripción</th>
                  <th>Stock</th>
                  <th>Acciones</th>
                </tr>
              </thead>
               </table>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>