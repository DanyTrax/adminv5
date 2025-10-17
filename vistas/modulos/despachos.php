<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Despachos
            <small>Gestión de despachos de mercancía</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Despachos</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            
            <!-- HEADER CON CONTROLES -->
            <div class="box-header with-border">
                
                <div class="row">
                    <div class="col-md-6">
                        <?php if($_SESSION["perfil"] != "Transportador" && $_SESSION["perfil"] != "Limitado"): ?>
                        <a href="crear-despacho" class="btn btn-primary">
                            <i class="fa fa-plus"></i>
                            Crear Nuevo Despacho
                        </a>
                        <?php endif; ?>
                        
                        <?php if($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Transportador"): ?>
                        <div class="btn-group" style="margin-left: 10px;">
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-download"></i> Exportar <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="#" onclick="exportarDespachosPDF()">
                                        <i class="fa fa-file-pdf-o text-red"></i> Exportar a PDF
                                    </a>
                                </li>
                                <li>
                                    <a href="#" onclick="exportarDespachosExcel()">
                                        <i class="fa fa-file-excel-o text-green"></i> Exportar a Excel
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6 text-right">
                        <!-- FILTROS RÁPIDOS -->
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btnFiltroEstado" data-estado="todos">
                                Todos
                            </button>
                            <button type="button" class="btn btn-warning btnFiltroEstado" data-estado="pendiente">
                                Pendientes
                            </button>
                            <button type="button" class="btn btn-success btnFiltroEstado" data-estado="aceptado">
                                Aceptados
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- TABLA DE DESPACHOS -->
            <div class="box-body">
                <table class="table table-bordered table-striped dt-responsive tablaDespachos" width="100%">
                    <thead>
                        <tr>
                            <th style="width:10px">#</th>
                            <th>N° Despacho</th>
                            <th>Sucursal Origen</th>
                            <th>Usuario Creador</th>
                            <th>Estado</th>
                            <th>Productos</th>
                            <th>Cantidad Total</th>
                            <th>Transportador</th>
                            <th>Fecha Creación</th>
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

<!-- MODAL VER DETALLES DE DESPACHO -->
<div class="modal fade" id="modalVerDespacho" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            
            <!-- HEADER -->
            <div class="modal-header bg-primary">
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title text-white">
                    <i class="fa fa-truck"></i> Detalles del Despacho
                    <span id="numeroDespachoModal" class="label label-default"></span>
                </h4>
            </div>

            <!-- BODY CON TABS -->
            <div class="modal-body" style="padding: 0;">
                
                <!-- TABS DE NAVEGACIÓN -->
                <ul class="nav nav-tabs" role="tablist" style="margin: 0; background: #f4f4f4;">
                    <li role="presentation" class="active">
                        <a href="#tabProductosDespacho" role="tab" data-toggle="tab">
                            <i class="fa fa-list"></i> Productos a Despachar
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tabInfoDespacho" role="tab" data-toggle="tab">
                            <i class="fa fa-info-circle"></i> Información General
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tabHistorialDespacho" role="tab" data-toggle="tab">
                            <i class="fa fa-history"></i> Historial
                        </a>
                    </li>
                </ul>

                <!-- CONTENIDO DE LOS TABS -->
                <div class="tab-content" style="padding: 20px;">
                    
                    <!-- TAB 1: PRODUCTOS A DESPACHAR -->
                    <div role="tabpanel" class="tab-pane fade in active" id="tabProductosDespacho">
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-primary">
                                    <tr>
                                        <th width="80px">#</th>
                                        <th width="120px">Código</th>
                                        <th>Descripción del Producto</th>
                                        <th width="100px" class="text-center">Cantidad</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody id="productosDespachoBody">
                                    <!-- Productos se cargan dinámicamente -->
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="3"><strong>TOTAL:</strong></td>
                                        <td class="text-center"><strong id="totalCantidadDespacho">0</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>

                    <!-- TAB 2: INFORMACIÓN GENERAL -->
                    <div role="tabpanel" class="tab-pane fade" id="tabInfoDespacho">
                        
                        <div class="row">
                            
                            <!-- INFORMACIÓN BÁSICA -->
                            <div class="col-md-6">
                                
                                <div class="info-box">
                                    <span class="info-box-icon bg-blue">
                                        <i class="fa fa-building"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Sucursal Origen</span>
                                        <span class="info-box-number" id="sucursalOrigenDespacho">-</span>
                                    </div>
                                </div>

                                <div class="info-box">
                                    <span class="info-box-icon bg-green">
                                        <i class="fa fa-user"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Creado por</span>
                                        <span class="info-box-number" id="usuarioCreadorDespacho">-</span>
                                    </div>
                                </div>

                                <div class="info-box">
                                    <span class="info-box-icon bg-yellow">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Fecha de Creación</span>
                                        <span class="info-box-number" id="fechaCreacionDespacho">-</span>
                                    </div>
                                </div>

                            </div>

                            <!-- INFORMACIÓN DE ESTADO -->
                            <div class="col-md-6">
                                
                                <div class="info-box">
                                    <span class="info-box-icon bg-purple">
                                        <i class="fa fa-truck"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Transportador Asignado</span>
                                        <span class="info-box-number" id="transportadorDespacho">Sin asignar</span>
                                    </div>
                                </div>

                                <div class="info-box">
                                    <span class="info-box-icon bg-orange">
                                        <i class="fa fa-cubes"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total de Productos</span>
                                        <span class="info-box-number" id="totalProductosDespacho">-</span>
                                    </div>
                                </div>

                                <div class="info-box">
                                    <span class="info-box-icon bg-red" id="estadoIconDespacho">
                                        <i class="fa fa-clock-o"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Estado</span>
                                        <span class="info-box-number" id="estadoDespacho">-</span>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- DETALLE ADICIONAL -->
                        <div class="row" style="margin-top: 20px;">
                            <div class="col-md-12" id="detalleAdicionalDespachoContainer" style="display: none;">
                                <div class="box box-success">
                                    <div class="box-header with-border">
                                        <h4 class="box-title">
                                            <i class="fa fa-comment"></i> Detalle Adicional
                                        </h4>
                                    </div>
                                    <div class="box-body">
                                        <p id="detalleAdicionalDespacho" class="text-justify"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 3: HISTORIAL -->
                    <div role="tabpanel" class="tab-pane fade" id="tabHistorialDespacho">
                        
                        <div class="timeline" id="timelineDespacho">
                            <!-- Timeline se carga dinámicamente -->
                        </div>

                    </div>

                </div>

            </div>

            <!-- FOOTER CON BOTONES -->
            <div class="modal-footer">
                
                <!-- BOTONES DE ACCIÓN SEGÚN ESTADO Y PERFIL -->
                <div id="botonesAccionDespacho">
                    <!-- Se muestran dinámicamente según permisos -->
                </div>

                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>

            </div>
                    </div>
    </div>
</div>

<!-- MODAL ACEPTAR DESPACHO (TRANSPORTADORES) -->
<?php if($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador"): ?>
<div class="modal fade" id="modalAceptarDespacho" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form role="form" method="post" id="formAceptarDespacho">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-check text-success"></i> Confirmar Aceptación de Despacho
                    </h4>
                </div>
                <div class="modal-body">
                    
                    <div class="alert alert-warning">
                        <i class="fa fa-warning"></i>
                        <strong>¡Importante!</strong> Al aceptar este despacho:
                        <ul style="margin-top: 10px;">
                            <li>Los productos serán <strong>descontados del stock local</strong></li>
                            <li>Los productos se <strong>agregarán a su stock en tránsito</strong></li>
                            <li>Esta acción <strong>no se puede deshacer</strong></li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Observaciones sobre la aceptación:</label>
                                <textarea class="form-control" name="observacionesAceptacion" rows="4" 
                                         placeholder="Escriba cualquier observación sobre este despacho (opcional)..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="idDespachoAceptar" id="idDespachoAceptar">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" name="aceptarDespacho" class="btn btn-success">
                        <i class="fa fa-check"></i> Aceptar Despacho
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL CANCELAR DESPACHO -->
<?php if($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Transportador"): ?>
<div class="modal fade" id="modalCancelarDespacho" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form role="form" method="post" id="formCancelarDespacho">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-ban text-danger"></i> Cancelar Despacho
                    </h4>
                </div>
                <div class="modal-body">
                    
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>¡Atención!</strong> Está a punto de cancelar este despacho.
                        <div id="alertaDevolucionStock" style="display: none; margin-top: 10px;">
                            <strong>Si el despacho ya fue aceptado:</strong>
                            <ul>
                                <li>Los productos serán <strong>devueltos al stock local</strong></li>
                                <li>Se <strong>eliminarán del stock en tránsito</strong></li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Motivo de cancelación <span class="text-danger">*</span>:</label>
                        <textarea class="form-control" name="motivoCancelacion" rows="4" 
                                 placeholder="Explique el motivo por el cual cancela este despacho..." required></textarea>
                    </div>
                    
                    <input type="hidden" name="idDespachoCancelar" id="idDespachoCancelar">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> No Cancelar
                    </button>
                    <button type="submit" name="cancelarDespacho" class="btn btn-danger">
                        <i class="fa fa-ban"></i> Cancelar Despacho
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL ELIMINAR DESPACHO (SOLO ADMINISTRADOR) -->
<?php if($_SESSION["perfil"] == "Administrador"): ?>
<div class="modal fade" id="modalEliminarDespacho" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-trash text-danger"></i> Eliminar Despacho
                </h4>
            </div>
            <div class="modal-body">
                
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>¡PELIGRO!</strong> Esta acción eliminará permanentemente el despacho.
                    <br><strong>Esta acción NO se puede deshacer.</strong>
                </div>

                <p>¿Está completamente seguro que desea <strong>eliminar</strong> este despacho?</p>
                
                <div class="form-group">
                    <label>Motivo de eliminación <span class="text-danger">*</span>:</label>
                    <textarea class="form-control" id="motivoEliminacion" rows="3" 
                             placeholder="Explique por qué elimina este despacho..." required></textarea>
                </div>
                
                <input type="hidden" id="idDespachoEliminar">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmarEliminacionDespacho()">
                    <i class="fa fa-trash"></i> Eliminar Definitivamente
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL FILTROS AVANZADOS PARA EXPORTAR -->
<div class="modal fade" id="modalFiltrosExportar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-filter"></i> Filtros para Exportación
                </h4>
            </div>
            <div class="modal-body">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Desde:</label>
                            <input type="date" class="form-control" id="fechaDesdeExport" 
                                   value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Hasta:</label>
                            <input type="date" class="form-control" id="fechaHastaExport" 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Estado:</label>
                            <select class="form-control" id="estadoExport">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendientes</option>
                                <option value="aceptado">Aceptados</option>
                                <option value="en_transito">En Tránsito</option>
                                <option value="finalizado">Finalizados</option>
                                <option value="cancelado">Cancelados</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Transportador:</label>
                            <select class="form-control" id="transportadorExport">
                                <option value="">Todos los transportadores</option>
                                <?php
                                $transportadores = ModeloDespachos::mdlObtenerTransportadores();
                                foreach($transportadores as $transportador):
                                ?>
                                <option value="<?php echo $transportador['id']; ?>">
                                    <?php echo $transportador['nombre']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="ejecutarExportacionPDF()">
                    <i class="fa fa-file-pdf-o"></i> Exportar PDF
                </button>
                <button type="button" class="btn btn-success" onclick="ejecutarExportacionExcel()">
                    <i class="fa fa-file-excel-o"></i> Exportar Excel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ESTILOS CSS -->
<style>
.estado-pendiente {
    background-color: #f39c12 !important;
    color: white;
}

.estado-aceptado {
    background-color: #00a65a !important;
    color: white;
}

.estado-en_transito {
    background-color: #3c8dbc !important;
    color: white;
}

.estado-finalizado {
    background-color: #666 !important;
    color: white;
}

.estado-cancelado {
    background-color: #dd4b39 !important;
    color: white;
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
.timeline > div > .glyphicon {
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

.info-box-number {
    font-size: 14px !important;
    font-weight: normal !important;
}

.btn-group .btnFiltroEstado.active {
    background-color: #337ab7;
    color: white;
    border-color: #2e6da4;
}
</style>

<?php
// EJECUTAR CONTROLADORES
$crearDespacho = new ControladorDespachos();
$crearDespacho->ctrCrearDespacho();

$aceptarDespacho = new ControladorDespachos();
$aceptarDespacho->ctrAceptarDespacho();

$cancelarDespacho = new ControladorDespachos();
$cancelarDespacho->ctrCancelarDespacho();

if($_SESSION["perfil"] == "Administrador") {
    $borrarDespacho = new ControladorDespachos();
    $borrarDespacho->ctrBorrarDespacho();
}
?>