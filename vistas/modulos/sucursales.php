<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      Administrar Sucursales
      <small>Panel de Control</small>
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

        <button class="btn btn-success pull-right btnSincronizarTodas" id="btnSincronizarTodas">
          <i class="fa fa-refresh"></i> Sincronizar Todas las Sucursales
        </button>

      </div>

      <div class="box-body">
        
        <!-- Widget de Estadísticas -->
        <div class="row" id="estadisticasSucursales">
          
          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3 id="totalSucursales">0</h3>
                <p>Total Sucursales</p>
              </div>
              <div class="icon">
                <i class="fa fa-building"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
              <div class="inner">
                <h3 id="sucursalesActivas">0</h3>
                <p>Sucursales Activas</p>
              </div>
              <div class="icon">
                <i class="fa fa-check-circle"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3 id="sucursalesInactivas">0</h3>
                <p>Sucursales Inactivas</p>
              </div>
              <div class="icon">
                <i class="fa fa-times-circle"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
              <div class="inner">
                <h3 id="ultimaSincronizacion">Nunca</h3>
                <p>Última Sincronización</p>
              </div>
              <div class="icon">
                <i class="fa fa-refresh"></i>
              </div>
            </div>
          </div>

        </div>

        <!-- DataTable -->
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

<!--=====================================
MODAL AGREGAR SUCURSAL
======================================-->

<div id="modalAgregarSucursal" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Agregar Sucursal</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
              <li role="presentation" class="active">
                <a href="#datosGenerales" aria-controls="datosGenerales" role="tab" data-toggle="tab">
                  <i class="fa fa-info-circle"></i> Información General
                </a>
              </li>
              <li role="presentation">
                <a href="#configuracionApi" aria-controls="configuracionApi" role="tab" data-toggle="tab">
                  <i class="fa fa-cog"></i> Configuración API
                </a>
              </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content" style="padding-top: 20px;">
              
              <!-- DATOS GENERALES -->
              <div role="tabpanel" class="tab-pane active" id="datosGenerales">

                <div class="row">

                  <!-- CÓDIGO SUCURSAL -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-code"></i></span> 
                        <input type="text" class="form-control input-lg" name="nuevoCodigo" id="nuevoCodigo" placeholder="Código Sucursal" required>
                        <span class="input-group-btn">
                          <button class="btn btn-default btn-lg" type="button" id="btnGenerarCodigo" title="Generar código automático">
                            <i class="fa fa-magic"></i>
                          </button>
                        </span>
                      </div>
                      <small class="text-muted">Ejemplo: SUC001, SUC002, etc.</small>
                    </div>
                  </div>

                  <!-- NOMBRE SUCURSAL -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span> 
                        <input type="text" class="form-control input-lg" name="nuevoNombre" placeholder="Nombre de la Sucursal" required>
                      </div>
                    </div>
                  </div>

                </div>

                <div class="row">

                  <!-- DIRECCIÓN -->
                  <div class="col-md-12">
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> 
                        <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Dirección">
                      </div>
                    </div>
                  </div>

                </div>

                <div class="row">

                  <!-- TELÉFONO -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-phone"></i></span> 
                        <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Teléfono">
                      </div>
                    </div>
                  </div>

                  <!-- EMAIL -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span> 
                        <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Email">
                      </div>
                    </div>
                  </div>

                </div>

                <!-- LOGO -->
                <div class="form-group">
                  <div class="panel panel-default">
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-6">
                          <label>Logo de la Sucursal:</label>
                          <input type="file" class="nuevoLogo" name="nuevoLogo" accept="image/*">
                          <p class="help-block">Peso máximo de la foto 2MB. Formatos permitidos: JPG, PNG, GIF</p>
                        </div>
                        <div class="col-md-6">
                          <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- OBSERVACIONES -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-comment"></i></span> 
                    <textarea class="form-control input-lg" name="nuevasObservaciones" placeholder="Observaciones" rows="3"></textarea>
                  </div>
                </div>

              </div>

              <!-- CONFIGURACIÓN API -->
              <div role="tabpanel" class="tab-pane" id="configuracionApi">

                <div class="row">

                  <!-- URL BASE -->
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>URL Base de la Sucursal: <span class="text-red">*</span></label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-globe"></i></span> 
                        <input type="url" class="form-control input-lg" name="nuevaUrlBase" placeholder="https://sucursal.empresa.com/" required>
                      </div>
                      <small class="text-muted">URL principal donde está instalado el sistema en la sucursal</small>
                    </div>
                  </div>

                </div>

                <div class="row">

                  <!-- API URL -->
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>API URL: <span class="text-red">*</span></label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-code"></i></span> 
                        <input type="url" class="form-control input-lg" name="nuevaApiUrl" placeholder="https://sucursal.empresa.com/api-transferencias/" required>
                        <span class="input-group-btn">
                          <button class="btn btn-info btn-lg" type="button" id="btnAutocompletarApi" title="Autocompletar desde URL Base">
                            <i class="fa fa-magic"></i>
                          </button>
                        </span>
                      </div>
                      <small class="text-muted">URL del API de transferencias de la sucursal</small>
                    </div>
                  </div>

                </div>

                <div class="row">

                  <!-- CHECKBOXES -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>
                        <input type="checkbox" name="nuevaActiva" checked> Sucursal Activa
                      </label>
                      <br>
                      <small class="text-muted">Si está marcado, la sucursal estará disponible para sincronización</small>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>
                        <input type="checkbox" name="nuevaPrincipal"> Sucursal Principal
                      </label>
                      <br>
                      <small class="text-muted">Marcar solo si es la sucursal principal de la empresa</small>
                    </div>
                  </div>

                </div>

                <!-- BOTÓN PROBAR CONEXIÓN -->
                <div class="form-group">
                  <button type="button" class="btn btn-warning btn-lg btn-block" id="btnProbarConexionNueva">
                    <i class="fa fa-wifi"></i> Probar Conexión con API
                  </button>
                </div>

              </div>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar Sucursal</button>

        </div>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR SUCURSAL
======================================-->

<div id="modalEditarSucursal" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Editar Sucursal</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
              <li role="presentation" class="active">
                <a href="#editarDatosGenerales" aria-controls="editarDatosGenerales" role="tab" data-toggle="tab">
                  <i class="fa fa-info-circle"></i> Información General
                </a>
              </li>
              <li role="presentation">
                <a href="#editarConfiguracionApi" aria-controls="editarConfiguracionApi" role="tab" data-toggle="tab">
                  <i class="fa fa-cog"></i> Configuración API
                </a>
              </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content" style="padding-top: 20px;">
              
              <!-- DATOS GENERALES -->
              <div role="tabpanel" class="tab-pane active" id="editarDatosGenerales">

                <div class="row">

                  <!-- CÓDIGO SUCURSAL -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-code"></i></span> 
                        <input type="text" class="form-control input-lg" name="editarCodigo" id="editarCodigo" placeholder="Código Sucursal" required readonly>
                      </div>
                      <small class="text-muted">El código no se puede modificar</small>
                    </div>
                  </div>

                  <!-- NOMBRE SUCURSAL -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-building"></i></span> 
                        <input type="text" class="form-control input-lg" name="editarNombre" id="editarNombre" placeholder="Nombre de la Sucursal" required>
                      </div>
                    </div>
                  </div>

                </div>

                <div class="row">

                  <!-- DIRECCIÓN -->
                  <div class="col-md-12">
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> 
                        <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion" placeholder="Dirección">
                      </div>
                    </div>
                  </div>

                </div>

                <div class="row">

                  <!-- TELÉFONO -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-phone"></i></span> 
                        <input type="text" class="form-control input-lg" name="editarTelefono" id="editarTelefono" placeholder="Teléfono">
                      </div>
                    </div>
                  </div>

                  <!-- EMAIL -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span> 
                        <input type="email" class="form-control input-lg" name="editarEmail" id="editarEmail" placeholder="Email">
                      </div>
                    </div>
                  </div>

                </div>

                <!-- LOGO -->
                <div class="form-group">
                  <div class="panel panel-default">
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-6">
                          <label>Logo de la Sucursal:</label>
                          <input type="file" class="nuevaImagen" name="editarLogo" accept="image/*">
                          <p class="help-block">Peso máximo de la foto 2MB</p>
                          <input type="hidden" name="logoActual" id="logoActual">
                        </div>
                        <div class="col-md-6">
                          <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- OBSERVACIONES -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-comment"></i></span> 
                    <textarea class="form-control input-lg" name="editarObservaciones" id="editarObservaciones" placeholder="Observaciones" rows="3"></textarea>
                  </div>
                </div>

              </div>

              <!-- CONFIGURACIÓN API -->
              <div role="tabpanel" class="tab-pane" id="editarConfiguracionApi">

                <div class="row">

                  <!-- URL BASE -->
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>URL Base de la Sucursal: <span class="text-red">*</span></label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-globe"></i></span> 
                        <input type="url" class="form-control input-lg" name="editarUrlBase" id="editarUrlBase" placeholder="https://sucursal.empresa.com/" required>
                      </div>
                    </div>
                  </div>

                </div>

                <div class="row">

                  <!-- API URL -->
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>API URL: <span class="text-red">*</span></label>
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-code"></i></span> 
                        <input type="url" class="form-control input-lg" name="editarApiUrl" id="editarApiUrl" placeholder="https://sucursal.empresa.com/api-transferencias/" required>
                        <span class="input-group-btn">
                          <button class="btn btn-success btn-lg btnProbarConexionEditar" type="button" title="Probar Conexión">
                            <i class="fa fa-wifi"></i>
                          </button>
                        </span>
                      </div>
                    </div>
                  </div>

                </div>

                <div class="row">

                  <!-- CHECKBOXES -->
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>
                        <input type="checkbox" name="editarActiva" id="editarActiva"> Sucursal Activa
                      </label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label>
                        <input type="checkbox" name="editarPrincipal" id="editarPrincipal"> Sucursal Principal
                      </label>
                    </div>
                  </div>

                </div>

              </div>

            </div>

            <!-- CAMPOS OCULTOS -->
            <input type="hidden" name="editarId" id="editarId">

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar Cambios</button>

        </div>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL SINCRONIZACIÓN SELECTIVA
======================================-->

<div id="modalSincronizacionSelectiva" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#00a65a; color:white">

        <button type="button" class="close" data-dismiss="modal">&times;</button>

        <h4 class="modal-title">
          <i class="fa fa-refresh"></i> Sincronización Selectiva
        </h4>

      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <p>Selecciona las sucursales con las que deseas sincronizar el catálogo:</p>

        <div id="listaSucursalesSync">
          <!-- Se carga dinámicamente -->
        </div>

      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->

      <div class="modal-footer">

        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>

        <button type="button" class="btn btn-success" id="btnEjecutarSincronizacionSelectiva">
          <i class="fa fa-refresh"></i> Sincronizar Seleccionadas
        </button>

      </div>

    </div>

  </div>

</div>

<?php

  $crearSucursal = new ControladorSucursales();
  $crearSucursal -> ctrCrearSucursal();

  $actualizarSucursal = new ControladorSucursales();
  $actualizarSucursal -> ctrActualizarSucursal();

  $eliminarSucursal = new ControladorSucursales();
  $eliminarSucursal -> ctrEliminarSucursal();

?>

<script src="vistas/js/sucursales.js"></script>