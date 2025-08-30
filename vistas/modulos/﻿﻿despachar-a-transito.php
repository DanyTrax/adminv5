<div class="content-wrapper">
  <section class="content-header">
    <h1>Despachar Mercancía a Tránsito</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Despachar a Tránsito</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">

      <div class="col-lg-5 col-xs-12">
        <div class="box box-primary">
          <div class="box-header with-border"></div>
          <form role="form" method="post" id="formularioDespacho">
            <div class="box-body">
              <div class="box">
                
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-truck"></i></span>
                    <select class="form-control" name="seleccionarTransportador" required>
                      <option value="">Seleccionar Transportador</option>
                      <?php
                        // Obtenemos los usuarios con el rol de Transportador
                        $item = "perfil";
                        $valor = "Transportador";
                        $transportadores = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
                        foreach ($transportadores as $key => $value) {
                          echo '<option value="' . $value["nombre"] . '">' . $value["nombre"] . '</option>';
                        }
                      ?>
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
                  <tbody class="productosDespacho">
                    </tbody>
                </table>

              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary pull-right">Iniciar Cargue y Notificar</button>
            </div>
          </form>
        </div>
      </div>

      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">
        <div class="box box-warning">
          <div class="box-header with-border"></div>
          <div class="box-body">
            <table class="table table-bordered table-striped dt-responsive tablaCatalogoDespacho" width="100%">
               <thead>
                 <tr>
                  <th style="width: 10px">#</th>
                  <th>Imagen</th>
                  <th>Código</th>
                  <th>Descripción</th>
                  <th>Stock Actual</th>
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