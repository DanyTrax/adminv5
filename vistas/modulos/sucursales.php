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
    
    <!-- INFORMACIÓN DE SUCURSAL ACTUAL -->
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">
              <i class="fa fa-building"></i> Configuración de Esta Sucursal
            </h3>
            <div class="box-tools pull-right">
              <button class="btn btn-info btn-sm" id="btnEditarSucursalLocal">
                <i class="fa fa-edit"></i> Editar Configuración
              </button>
            </div>
          </div>
          <div class="box-body">
            <div class="row" id="infoSucursalActual">
              <div class="col-md-3">
                <strong>Código:</strong> 
                <span id="codigoActual" class="label label-primary">No configurado</span>
              </div>
              <div class="col-md-3">
                <strong>Nombre:</strong> 
                <span id="nombreActual">No configurado</span>
              </div>
              <div class="col-md-3">
                <strong>Estado:</strong>
                <span id="estadoActual" class="label label-warning">No registrada</span>
              </div>
              <div class="col-md-3">
                <div class="pull-right">
                  <button class="btn btn-success btn-sm" id="btnRegistrarEsta" style="display:none;">
                    <i class="fa fa-plus-circle"></i> Registrar Esta Sucursal
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DIRECTORIO DE SUCURSALES -->
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">
          <i class="fa fa-list"></i> Directorio de Sucursales Registradas
        </h3>
        <div class="box-tools pull-right">
          <button class="btn btn-success btn-sm" id="btnSincronizarCatalogo">
            <i class="fa fa-refresh"></i> Sincronizar Catálogo Maestro
          </button>
        </div>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablaSucursales" width="100%">
          <thead>
            <tr>
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

<!--=====================================
MODAL CONFIGURAR SUCURSAL LOCAL
======================================-->
<div id="modalConfigurarLocal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form role="form" method="post" id="formConfigurarLocal">
        
        <div class="modal-header" style="background:#17a2b8; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">
            <i class="fa fa-cog"></i> Configurar Esta Sucursal
          </h4>
        </div>

        <div class="modal-body">
          <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> 
            Configure los datos de esta instalación. Estos datos se usarán para identificar esta sucursal en el sistema.
          </div>

          <div class="row">
            <!-- CÓDIGO SUCURSAL -->
            <div class="col-md-6">
              <div class="form-group">
                <label>Código de Sucursal: <span class="text-red">*</span></label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-code"></i></span>
                  <input type="text" class="form-control" name="codigoLocal" id="codigoLocal" placeholder="SUC001" required>
                  <span class="input-group-btn">
                    <button class="btn btn-info" type="button" id="btnGenerarCodigo">
                      <i class="fa fa-magic"></i>
                    </button>
                  </span>
                </div>
                <small class="text-muted">Se generará automáticamente si está vacío</small>
              </div>
            </div>

            <!-- NOMBRE SUCURSAL -->
            <div class="col-md-6">
              <div class="form-group">
                <label>Nombre de la Sucursal: <span class="text-red">*</span></label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-building"></i></span>
                  <input type="text" class="form-control" name="nombreLocal" id="nombreLocal" placeholder="Nombre de la sucursal" required>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- DIRECCIÓN -->
            <div class="col-md-12">
              <div class="form-group">
                <label>Dirección:</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                  <input type="text" class="form-control" name="direccionLocal" id="direccionLocal" placeholder="Dirección de la sucursal">
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- TELÉFONO -->
            <div class="col-md-6">
              <div class="form-group">
                <label>Teléfono:</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                  <input type="text" class="form-control" name="telefonoLocal" id="telefonoLocal" placeholder="(000) 000-0000">
                </div>
              </div>
            </div>

            <!-- EMAIL -->
            <div class="col-md-6">
              <div class="form-group">
                <label>Email:</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                  <input type="email" class="form-control" name="emailLocal" id="emailLocal" placeholder="sucursal@empresa.com">
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- URL BASE -->
            <div class="col-md-6">
              <div class="form-group">
                <label>URL Base: <span class="text-red">*</span></label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-globe"></i></span>
                  <input type="url" class="form-control" name="urlBaseLocal" id="urlBaseLocal" required>
                  <span class="input-group-btn">
                    <button class="btn btn-success" type="button" id="btnDetectarURL">
                      <i class="fa fa-search"></i>
                    </button>
                  </span>
                </div>
                <small class="text-muted">URL donde está instalado este sistema</small>
              </div>
            </div>

            <!-- URL API -->
            <div class="col-md-6">
              <div class="form-group">
                <label>URL API: <span class="text-red">*</span></label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-code"></i></span>
                  <input type="url" class="form-control" name="urlApiLocal" id="urlApiLocal" required>
                  <span class="input-group-btn">
                    <button class="btn btn-info" type="button" id="btnAutoAPI">
                      <i class="fa fa-magic"></i>
                    </button>
                  </span>
                </div>
                <small class="text-muted">Se genera automáticamente desde URL Base</small>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>
                  <input type="checkbox" name="esPrincipal" id="esPrincipal"> 
                  Esta es la sucursal principal
                </label>
                <br>
                <small class="text-muted">Marque solo si esta es la sucursal principal de la empresa</small>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Guardar Configuración
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR SUCURSAL
======================================-->
<div id="modalEditarSucursal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" id="formEditarSucursal">
        
        <div class="modal-header" style="background:#f39c12; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">
            <i class="fa fa-edit"></i> Editar Sucursal
          </h4>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Código:</label>
                <input type="text" class="form-control" name="editarCodigo" id="editarCodigo" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Nombre:</label>
                <input type="text" class="form-control" name="editarNombre" id="editarNombre" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Dirección:</label>
            <input type="text" class="form-control" name="editarDireccion" id="editarDireccion">
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Teléfono:</label>
                <input type="text" class="form-control" name="editarTelefono" id="editarTelefono">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Email:</label>
                <input type="email" class="form-control" name="editarEmail" id="editarEmail">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>URL Base:</label>
                <input type="url" class="form-control" name="editarUrlBase" id="editarUrlBase" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>URL API:</label>
                <input type="url" class="form-control" name="editarUrlApi" id="editarUrlApi" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>
              <input type="checkbox" name="editarActivo" id="editarActivo"> Sucursal Activa
            </label>
          </div>

          <input type="hidden" name="editarId" id="editarId">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Actualizar
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<?php
  // Controladores para procesar formularios
  $configurarLocal = new ControladorSucursales();
  $configurarLocal->ctrConfigurarSucursalLocal();

  $registrarSucursal = new ControladorSucursales();
  $registrarSucursal->ctrRegistrarSucursal();

  $actualizarSucursal = new ControladorSucursales();
  $actualizarSucursal->ctrActualizarSucursal();

  $eliminarSucursal = new ControladorSucursales();
  $eliminarSucursal->ctrEliminarSucursal();
?>

<script src="vistas/js/sucursales.js"></script>