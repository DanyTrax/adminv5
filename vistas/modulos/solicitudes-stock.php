<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Solicitudes de Stock
      <small>Gestión de solicitudes entre sucursales</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Solicitudes de Stock</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      
      <!-- HEADER CON BOTÓN SOLICITAR -->
      <div class="box-header with-border">
        
        <div class="row">
          <div class="col-md-8">
            <?php
            // Verificar si el usuario puede crear solicitudes
            if($_SESSION["perfil"] != "Transportador"): 
            ?>
              <button class="btn btn-primary" data-toggle="modal" data-target="#modalSolicitarStock">
                <i class="fa fa-plus"></i>
                Nueva Solicitud de Stock
              </button>
            <?php 
            else: 
            ?>
              <div class="alert alert-info" style="margin-bottom: 0;">
                <i class="fa fa-info-circle"></i>
                <strong>Transportador:</strong> Puedes aprobar o cancelar solicitudes existentes.
              </div>
            <?php 
            endif; 
            ?>
          </div>
          
          <div class="col-md-4 text-right">
            <?php
            // Mostrar estadísticas básicas
            $estadisticas = ControladorSolicitudesStock::ctrObtenerEstadisticas();
            if($estadisticas):
            ?>
            <div class="info-box-content">
              <span class="info-box-text">Pendientes</span>
              <span class="info-box-number text-yellow"><?php echo $estadisticas["pendientes"]; ?></span>
            </div>
            <?php endif; ?>
          </div>
        </div>
        
      </div>

      <!-- TABLA DE SOLICITUDES -->
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablaSolicitudesStock" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>N° Solicitud</th>
              <th>Sucursal</th>
              <th>Usuario</th>
              <th>Tipo</th>
              <th>Productos</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th>Aprobado por</th>
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

<!-- MODAL SOLICITAR STOCK -->
<div id="modalSolicitarStock" class="modal fade" role="dialog">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      
      <form role="form" method="post" class="formularioSolicitudStock">
        
        <!-- HEADER DEL MODAL -->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">
            <i class="fa fa-plus"></i>
            Nueva Solicitud de Stock
          </h4>
        </div>

        <!-- BODY DEL MODAL -->
        <div class="modal-body">
          
          <div class="row">
            
            <!-- PANEL IZQUIERDO - INFORMACIÓN DE SOLICITUD -->
            <div class="col-md-5">
              
              <!-- SUCURSAL SOLICITANTE -->
              <div class="form-group">
                <label><i class="fa fa-building"></i> Sucursal Solicitante</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-building"></i></span>
                  <?php
                  $sucursalLocal = ControladorSucursales::ctrObtenerConfiguracionLocal();
                  $nombreSucursal = $sucursalLocal ? $sucursalLocal['nombre'] : 'No configurada';
                  ?>
                  <input type="text" class="form-control" 
                         value="<?php echo htmlspecialchars($nombreSucursal); ?>" 
                         readonly style="background-color: #f4f4f4;">
                </div>
              </div>

              <!-- TIPO DE SOLICITUD -->
              <div class="form-group">
                <label><i class="fa fa-tags"></i> Tipo de Solicitud</label>
                <div class="row">
                  <div class="col-xs-6">
                    <label class="radio-inline">
                      <input type="radio" name="tipo_solicitud" value="stock" checked>
                      <i class="fa fa-cubes"></i> Por Stock
                    </label>
                  </div>
                  <div class="col-xs-6">
                    <label class="radio-inline">
                      <input type="radio" name="tipo_solicitud" value="remision">
                      <i class="fa fa-file-text"></i> Por Remisión
                    </label>
                  </div>
                </div>
              </div>

              <!-- CAMPO REMISIÓN (OCULTO INICIALMENTE) -->
              <div class="form-group campoRemision" style="display: none;">
                <label><i class="fa fa-search"></i> Buscar Remisión</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="buscarRemision" 
                         placeholder="Buscar por código o cliente...">
                  <span class="input-group-btn">
                    <button class="btn btn-info" type="button" id="btnBuscarRemision">
                      <i class="fa fa-search"></i>
                    </button>
                  </span>
                </div>
                <input type="hidden" name="codigo_remision" id="codigoRemisionSeleccionada">
                <input type="hidden" name="nombre_cliente_remision" id="nombreClienteRemision">
                
                <!-- RESULTADOS DE BÚSQUEDA -->
                <div id="resultadosRemision" class="list-group" style="max-height: 200px; overflow-y: auto; margin-top: 5px; display: none;"></div>
              </div>

              <!-- DETALLE ADICIONAL -->
              <div class="form-group">
                <label><i class="fa fa-comment"></i> Detalle Adicional</label>
                <textarea class="form-control" name="detalle_adicional" rows="4" 
                         placeholder="Observaciones, motivo de la solicitud, urgencia, etc..."></textarea>
              </div>

              <!-- PRODUCTOS SELECCIONADOS -->
              <div class="box box-success">
                <div class="box-header">
                  <h4 class="box-title">
                    <i class="fa fa-shopping-cart"></i>
                    Productos Solicitados
                    <span class="badge bg-green" id="contadorProductos">0</span>
                  </h4>
                </div>
                <div class="box-body" style="max-height: 300px; overflow-y: auto;">
                  <table class="table table-condensed">
                    <thead>
                      <tr>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>Acc.</th>
                      </tr>
                    </thead>
                    <tbody id="productosSeleccionados">
                      <tr id="sinProductos">
                        <td colspan="3" class="text-center text-muted">
                          <i class="fa fa-info-circle"></i>
                          No hay productos seleccionados
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

            </div>

            <!-- PANEL DERECHO - CATÁLOGO DE PRODUCTOS -->
            <div class="col-md-7">
              
              <div class="box box-warning">
                <div class="box-header">
                  <h4 class="box-title">
                    <i class="fa fa-cube"></i>
                    Catálogo de Productos
                  </h4>
                </div>
                <div class="box-body">
                  <table class="table table-bordered table-striped dt-responsive tablaProductosCatalogo" width="100%">
                    <thead>
                      <tr>
                        <th>Imagen</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Stock</th>
                        <th>Acción</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
              
            </div>
            
          </div>

        </div>

        <!-- FOOTER DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
            <i class="fa fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-primary pull-right" id="btnCrearSolicitud" disabled>
            <i class="fa fa-save"></i> Crear Solicitud
          </button>
        </div>

        <input type="hidden" name="productos_solicitados" id="productosJsonInput">

        <?php
        $crearSolicitud = new ControladorSolicitudesStock();
        $crearSolicitud->ctrCrearSolicitud();
        ?>

      </form>

    </div>
  </div>
</div>

<!-- Modal para ingresar cantidad de producto -->
<div class="modal fade" id="modalCantidadProducto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-plus-circle text-success"></i> Agregar Producto
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><strong>Producto:</strong></label>
                    <p id="nombreProductoModal" class="text-primary"></p>
                </div>
                
                <div class="form-group">
                    <label for="cantidadProductoModal">
                        <i class="fa fa-calculator"></i> Cantidad a solicitar: <span class="text-danger">*</span>
                    </label>
                    <input type="number" 
                           class="form-control text-center" 
                           id="cantidadProductoModal" 
                           min="1" 
                           max="9999" 
                           value="1" 
                           placeholder="Ingrese cantidad">
                    <small class="help-block text-muted">
                        <i class="fa fa-info-circle"></i> Cantidad debe ser entre 1 y 9,999
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="observacionProductoModal">
                        <i class="fa fa-comment"></i> Observación (opcional):
                    </label>
                    <textarea class="form-control" 
                              id="observacionProductoModal" 
                              rows="2" 
                              maxlength="200"
                              placeholder="Ej: Urgente, color específico, medidas especiales..."></textarea>
                    <small class="help-block text-muted">
                        <i class="fa fa-info-circle"></i> Máximo 200 caracteres
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="confirmarAgregarProducto">
                    <i class="fa fa-check"></i> Agregar a Solicitud
                </button>
            </div>
        </div>
    </div>
</div>
<!-- MODAL VER DETALLES DE SOLICITUD -->
<div id="modalVerSolicitud" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#3c8dbc; color:white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">
          <i class="fa fa-eye"></i>
          Detalles de Solicitud
        </h4>
      </div>
      <div class="modal-body">
        
        <!-- INFORMACIÓN BÁSICA -->
        <div class="row">
          <div class="col-md-6">
            <strong>N° Solicitud:</strong> <span id="modalNumeroSolicitud"></span><br>
            <strong>Sucursal:</strong> <span id="modalSucursal"></span><br>
            <strong>Usuario:</strong> <span id="modalUsuario"></span><br>
            <strong>Tipo:</strong> <span id="modalTipo"></span>
          </div>
          <div class="col-md-6">
            <strong>Estado:</strong> <span id="modalEstado"></span><br>
            <strong>Fecha Solicitud:</strong> <span id="modalFechaSolicitud"></span><br>
            <strong>Aprobado por:</strong> <span id="modalAprobadoPor"></span><br>
            <strong>Fecha Aprobación:</strong> <span id="modalFechaAprobacion"></span>
          </div>
        </div>

        <!-- REMISIÓN SI APLICA -->
        <div id="infoRemision" style="display: none;">
          <hr>
          <h4><i class="fa fa-file-text"></i> Información de Remisión</h4>
          <strong>Código:</strong> <span id="modalCodigoRemision"></span><br>
          <strong>Cliente:</strong> <span id="modalClienteRemision"></span>
        </div>

        <!-- DETALLE ADICIONAL -->
        <div id="detalleAdicional" style="display: none;">
          <hr>
          <h4><i class="fa fa-comment"></i> Detalle Adicional</h4>
          <p id="modalDetalleTexto" class="well"></p>
        </div>

        <hr>

        <!-- PRODUCTOS SOLICITADOS -->
        <h4><i class="fa fa-cubes"></i> Productos Solicitados</h4>
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Observación</th>
              </tr>
            </thead>
            <tbody id="modalProductosLista">
            </tbody>
          </table>
        </div>

        <!-- BOTONES DE ACCIÓN PARA TRANSPORTADOR/ADMIN -->
        <?php if($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador"): ?>
        <hr>
        <div class="text-center" id="botonesAccionModal">
          <button class="btn btn-success btn-lg" id="btnAprobarModal" style="margin-right: 10px;">
            <i class="fa fa-check"></i> Aprobar Solicitud
          </button>
          <button class="btn btn-warning btn-lg" id="btnCancelarModal">
            <i class="fa fa-times"></i> Cancelar Solicitud
          </button>
        </div>
        <?php endif; ?>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-info" onclick="imprimirSolicitud()">
          <i class="fa fa-print"></i> Imprimir
        </button>
        <button type="button" class="btn btn-success" onclick="exportarExcel()">
          <i class="fa fa-file-excel-o"></i> Excel
        </button>
        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">
          <i class="fa fa-times"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<?php
// Ejecutar controladores para acciones
$aprobarSolicitud = new ControladorSolicitudesStock();
$aprobarSolicitud->ctrAprobarSolicitud();

$cancelarSolicitud = new ControladorSolicitudesStock();
$cancelarSolicitud->ctrCancelarSolicitud();

$eliminarSolicitud = new ControladorSolicitudesStock();
$eliminarSolicitud->ctrEliminarSolicitud();
?>

<!-- ESTILOS CSS ESPECÍFICOS -->
<style>
.modal-xl {
    width: 95%;
    max-width: 1200px;
}

.radio-inline {
    margin-right: 20px;
}

#productosSeleccionados {
    min-height: 100px;
}

.producto-seleccionado {
    background-color: #f9f9f9;
}

#resultadosRemision .list-group-item {
    cursor: pointer;
    padding: 8px 12px;
}

#resultadosRemision .list-group-item:hover {
    background-color: #f5f5f5;
}

.table-condensed th,
.table-condensed td {
    padding: 5px;
    font-size: 12px;
}

.estado-pendiente {
    background-color: #f39c12 !important;
    color: white;
}

.estado-aprobado {
    background-color: #00a65a !important;
    color: white;
}

.estado-cancelado {
    background-color: #dd4b39 !important;
    color: white;
}
</style>