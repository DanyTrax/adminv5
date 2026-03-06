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
          <a href="crear-solicitud-stock" class="btn btn-primary">
            <i class="fa fa-plus"></i>
            Nueva Solicitud de Stock
          </a>
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
            // Mostrar estadísticas básicas si existe el método
            if(method_exists('ControladorSolicitudesStock', 'ctrObtenerEstadisticas')):
              $estadisticas = ControladorSolicitudesStock::ctrObtenerEstadisticas();
              if($estadisticas):
            ?>
            <div class="info-box-content">
              <span class="info-box-text">Pendientes</span>
              <span class="info-box-number text-yellow"><?php echo $estadisticas["pendientes"]; ?></span>
            </div>
            <?php 
              endif;
            endif; 
            ?>
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

<!-- ✅ MODAL VER DETALLES DE SOLICITUD - CON ORDEN DE TABS CAMBIADO -->
<div class="modal fade" id="modalVerSolicitud" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      
      <!-- HEADER -->
      <div class="modal-header bg-primary">
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title text-white">
          <i class="fa fa-file-text-o"></i> Detalles de la Solicitud
          <span id="numeroSolicitudModal" class="label label-default"></span>
        </h4>
      </div>

      <!-- BODY -->
      <div class="modal-body" style="padding: 0;">
        
        <!-- ✅ TABS DE NAVEGACIÓN - ORDEN CAMBIADO: PRODUCTOS PRIMERO -->
        <ul class="nav nav-tabs" role="tablist" style="margin: 0; background: #f4f4f4;">
          <li role="presentation" class="active">
            <a href="#tabProductos" role="tab" data-toggle="tab">
              <i class="fa fa-list"></i> Productos Solicitados
            </a>
          </li>
          <li role="presentation">
            <a href="#tabInformacion" role="tab" data-toggle="tab">
              <i class="fa fa-info-circle"></i> Información General
            </a>
          </li>
          <li role="presentation">
            <a href="#tabHistorial" role="tab" data-toggle="tab">
              <i class="fa fa-history"></i> Historial
            </a>
          </li>
          <li role="presentation">
            <a href="#tabStockSucursales" role="tab" data-toggle="tab">
              <i class="fa fa-warehouse"></i> Stock Sucursales
            </a>
          </li>
        </ul>

        <!-- ✅ CONTENIDO DE LOS TABS - ORDEN CAMBIADO -->
        <div class="tab-content" style="padding: 20px;">
          
          <!-- ✅ TAB 1: PRODUCTOS SOLICITADOS - AHORA ES EL PRIMERO Y ACTIVO -->
          <div role="tabpanel" class="tab-pane fade in active" id="tabProductos">
            
            <div class="table-responsive">
              <table class="table table-bordered table-striped" id="tablaProductosSolicitados">
                <thead class="bg-primary">
                  <tr>
                    <th width="80px">#</th>
                    <th width="120px">Código</th>
                    <th>Descripción del Producto</th>
                    <th width="100px" class="text-center">Cantidad</th>
                    <th>Observaciones</th>
                  </tr>
                </thead>
                <tbody id="productosModalBody">
                  <!-- Productos se cargan dinámicamente -->
                </tbody>
                <tfoot>
                  <tr class="bg-light">
                    <td colspan="3"><strong>TOTAL:</strong></td>
                    <td class="text-center"><strong id="totalCantidadProductos">0</strong></td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>

          </div>

          <!-- ✅ TAB 2: INFORMACIÓN GENERAL - AHORA ES EL SEGUNDO -->
          <div role="tabpanel" class="tab-pane fade" id="tabInformacion">
            
            <div class="row">
              
              <!-- COLUMNA IZQUIERDA -->
              <div class="col-md-6">
                
                <div class="info-box">
                  <span class="info-box-icon bg-blue">
                    <i class="fa fa-building"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Sucursal Solicitante</span>
                    <span class="info-box-number" id="sucursalSolicitante">-</span>
                  </div>
                </div>

                <div class="info-box">
                  <span class="info-box-icon bg-green">
                    <i class="fa fa-user"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Usuario Solicitante</span>
                    <span class="info-box-number" id="usuarioSolicitante">-</span>
                  </div>
                </div>

                <div class="info-box">
                  <span class="info-box-icon bg-yellow">
                    <i class="fa fa-calendar"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Fecha de Solicitud</span>
                    <span class="info-box-number" id="fechaSolicitud">-</span>
                  </div>
                </div>

              </div>

              <!-- COLUMNA DERECHA -->
              <div class="col-md-6">
                
                <div class="info-box">
                  <span class="info-box-icon bg-purple">
                    <i class="fa fa-tag"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Tipo de Solicitud</span>
                    <span class="info-box-number" id="tipoSolicitud">-</span>
                  </div>
                </div>

                <div class="info-box">
                  <span class="info-box-icon bg-orange">
                    <i class="fa fa-cubes"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Total de Productos</span>
                    <span class="info-box-number" id="totalProductos">-</span>
                  </div>
                </div>

                <div class="info-box">
                  <span class="info-box-icon bg-red" id="estadoIcon">
                    <i class="fa fa-clock-o"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Estado</span>
                    <span class="info-box-number" id="estadoSolicitud">-</span>
                  </div>
                </div>

              </div>

            </div>

            <!-- INFORMACIÓN ADICIONAL -->
            <div class="row" style="margin-top: 20px;">
              
              <!-- REMISIÓN (si aplica) -->
              <div class="col-md-12" id="infoRemision" style="display: none;">
                <div class="box box-info">
                  <div class="box-header with-border">
                    <h4 class="box-title">
                      <i class="fa fa-file-text"></i> Información de Remisión
                    </h4>
                  </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-md-6">
                        <strong>Código de Remisión:</strong>
                        <span id="codigoRemision">-</span>
                      </div>
                      <div class="col-md-6">
                        <strong>Cliente:</strong>
                        <span id="clienteRemision">-</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- DETALLE ADICIONAL -->
              <div class="col-md-12" id="detalleAdicionalContainer" style="display: none;">
                <div class="box box-success">
                  <div class="box-header with-border">
                    <h4 class="box-title">
                      <i class="fa fa-comment"></i> Detalle Adicional
                    </h4>
                  </div>
                  <div class="box-body">
                    <p id="detalleAdicional" class="text-justify"></p>
                  </div>
                </div>
              </div>

            </div>

          </div>

          <!-- ✅ TAB 3: HISTORIAL - AHORA ES EL TERCERO -->
          <div role="tabpanel" class="tab-pane fade" id="tabHistorial">
            
            <div class="timeline">
              
              <!-- CREACIÓN -->
              <div class="time-label">
                <span class="bg-blue" id="fechaCreacion">
                  <i class="fa fa-plus-circle"></i> Creación
                </span>
              </div>
              <div>
                <i class="fa fa-file-o bg-blue"></i>
                <div class="timeline-item">
                  <span class="time">
                    <i class="fa fa-clock-o"></i> <span id="horaCreacion">-</span>
                  </span>
                  <h3 class="timeline-header">
                    Solicitud creada por <span id="usuarioCreacion">-</span>
                  </h3>
                  <div class="timeline-body">
                    Solicitud <span id="numeroCreacion">-</span> creada exitosamente.
                  </div>
                </div>
              </div>

              <!-- APROBACIÓN/CANCELACIÓN -->
              <div id="timelineAprobacion" style="display: none;">
                <div class="time-label">
                  <span id="labelAprobacion" class="bg-green">
                    <i class="fa fa-check"></i> Aprobación
                  </span>
                </div>
                <div>
                  <i id="iconAprobacion" class="fa fa-check bg-green"></i>
                  <div class="timeline-item">
                    <span class="time">
                      <i class="fa fa-clock-o"></i> <span id="horaAprobacion">-</span>
                    </span>
                    <h3 class="timeline-header">
                      <span id="accionAprobacion">Aprobada</span> por <span id="usuarioAprobacion">-</span>
                    </h3>
                    <div class="timeline-body" id="motivoContainer" style="display: none;">
                      <strong>Motivo:</strong> <span id="motivoAprobacion">-</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- FIN TIMELINE -->
              <div>
                <i class="fa fa-clock-o bg-gray"></i>
              </div>

            </div>

          </div>

          <!-- ✅ TAB 4: STOCK SUCURSALES - NUEVA PESTAÑA -->
          <div role="tabpanel" class="tab-pane fade" id="tabStockSucursales">
            
            <div class="box box-info">
              <div class="box-header with-border">
                <h4 class="box-title">
                  <i class="fa fa-warehouse"></i> Stock Disponible por Sucursal
                </h4>
                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="box-body">
                <div class="alert alert-info">
                  <i class="fa fa-info-circle"></i>
                  <strong>Información:</strong> Esta tabla muestra el stock disponible de los productos solicitados en cada sucursal. Es solo para consulta.
                </div>
                
                <!-- Tabla de stock por sucursales -->
                <div class="table-responsive">
                  <table class="table table-bordered table-striped" id="tablaStockSucursales">
                    <thead>
                      <tr>
                        <th style="width: 10px;">#</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Cantidad Solicitada</th>
                        <th id="sucursalesHeader">Sucursales</th>
                      </tr>
                    </thead>
                    <tbody id="tbodyStockSucursales">
                      <tr>
                        <td colspan="5" class="text-center">
                          <i class="fa fa-spinner fa-spin"></i> Cargando stock disponible...
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>

        </div>

      </div>

      <!-- FOOTER CON BOTONES -->
      <div class="modal-footer">
        <input type="hidden" id="solicitudIdParaExportar" value="">
        <button type="button" class="btn btn-success btnExportarPDFSolicitud" id="btnExportarPDF" title="Exportar a PDF">
          <i class="fa fa-file-pdf-o"></i> Exportar a PDF
        </button>

        <button type="button" class="btn btn-default" data-dismiss="modal">
          <i class="fa fa-times"></i> Cerrar
        </button>

      </div>

    </div>
  </div>
</div>

<!-- ✅ ESTILOS CSS NECESARIOS -->
<style>
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

.estado-finalizado {
    background-color: #95a5a6 !important;
    color: white;
}

.bg-primary {
    background-color: #3c8dbc !important;
}

.info-box-number {
    font-size: 14px !important;
    font-weight: normal !important;
}

.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}

.timeline > div {
    margin-bottom: 15px;
    position: relative;
}

.timeline > div > .timeline-item {
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    border-radius: 3px;
    margin-top: 0;
    background: #fff;
    color: #444;
    margin-left: 60px;
    margin-right: 15px;
    padding: 0;
    position: relative;
}

.timeline > div > .fa,
.timeline > div > .glyphicon,
.timeline > div > .ion {
    width: 30px;
    height: 30px;
    font-size: 15px;
    line-height: 30px;
    position: absolute;
    color: #666;
    background: #d2d6de;
    border-radius: 50%;
    text-align: center;
    left: 18px;
    top: 0;
}

.timeline > .time-label > span {
    font-weight: 600;
    color: #fff;
    border-radius: 4px;
    display: inline-block;
    padding: 5px 10px;
}

.timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px;
    font-weight: 600;
    font-size: 16px;
}

.timeline-body,
.timeline-footer {
    padding: 10px;
}

.time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
}
</style>

<!-- ✅ INCLUIR CONTROLADORES NECESARIOS -->
<?php
// Solo incluir si existen los métodos
if(method_exists('ControladorSolicitudesStock', 'ctrAprobarSolicitud')):
$aprobarSolicitud = new ControladorSolicitudesStock();
$aprobarSolicitud->ctrAprobarSolicitud();
endif;

if(method_exists('ControladorSolicitudesStock', 'ctrCancelarSolicitud')):
$cancelarSolicitud = new ControladorSolicitudesStock();
$cancelarSolicitud->ctrCancelarSolicitud();
endif;

if(method_exists('ControladorSolicitudesStock', 'ctrEliminarSolicitud')):
$eliminarSolicitud = new ControladorSolicitudesStock();
$eliminarSolicitud->ctrEliminarSolicitud();
endif;
?>