<?php

if($_SESSION["perfil"] == "Limitado"){
    echo '<script>
        window.location = "inicio";
    </script>';
    return;
}

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Histórico de Movimientos
            <small>Trazabilidad completa del stock en tránsito</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="stock-transito"><i class="fa fa-truck"></i> Stock en Tránsito</a></li>
            <li class="active">Histórico</li>
        </ol>
    </section>

    <section class="content">

        <!-- ROW DE FILTROS Y ESTADÍSTICAS -->
        <div class="row">
            
            <!-- FILTROS AVANZADOS -->
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-filter"></i> Filtros de Búsqueda
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        
                        <div class="row">
                            
                            <!-- FILTROS PRINCIPALES -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Rango de Fechas:</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="fechaDesdeHistorico" 
                                               value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                                        <span class="input-group-addon">hasta</span>
                                        <input type="date" class="form-control" id="fechaHastaHistorico" 
                                               value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tipo de Movimiento:</label>
                                    <select class="form-control" id="filtroTipoMovimiento">
                                        <option value="">Todos los tipos</option>
                                        <option value="cargue">Cargue de Productos</option>
                                        <option value="descarga">Descarga Confirmada</option>
                                        <option value="solicitud_descarga">Solicitud de Descarga</option>
                                        <option value="rechazo_descarga">Solicitud Rechazada</option>
                                        <option value="descarga_forzada">Descarga Forzada</option>
                                        <option value="transferencia">Transferencia</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Transportador:</label>
                                    <select class="form-control" id="filtroTransportadorHistorico">
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
                        
                        <div class="row">
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Código de Producto:</label>
                                    <input type="text" class="form-control" id="filtroCodigoProducto" 
                                           placeholder="Ej: PROD001">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Sucursal:</label>
                                    <input type="text" class="form-control" id="filtroSucursal" 
                                           placeholder="Nombre de sucursal">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Usuario:</label>
                                    <input type="text" class="form-control" id="filtroUsuario" 
                                           placeholder="Nombre del usuario">
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" onclick="aplicarFiltrosHistorico()">
                                        <i class="fa fa-search"></i> Aplicar Filtros
                                    </button>
                                    <button type="button" class="btn btn-default" onclick="limpiarFiltrosHistorico()">
                                        <i class="fa fa-eraser"></i> Limpiar
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="filtrosRapidos('hoy')">
                                        <i class="fa fa-calendar-o"></i> Hoy
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="filtrosRapidos('semana')">
                                        <i class="fa fa-calendar"></i> Esta Semana
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="filtrosRapidos('mes')">
                                        <i class="fa fa-calendar-plus-o"></i> Este Mes
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            <!-- ESTADÍSTICAS RÁPIDAS -->
            <div class="col-md-4">
                
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Movimientos</span>
                        <span class="info-box-number" id="totalMovimientos">0</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">En el período seleccionado</span>
                    </div>
                </div>

                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-download"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Descargas Exitosas</span>
                        <span class="info-box-number" id="descargasExitosas">0</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">Productos descargados</span>
                    </div>
                </div>

                <div class="info-box bg-yellow">
                    <span class="info-box-icon"><i class="fa fa-truck"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Productos Cargados</span>
                        <span class="info-box-number" id="productosCargados">0</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">En tránsito</span>
                    </div>
                </div>

                <div class="info-box bg-red">
                    <span class="info-box-icon"><i class="fa fa-ban"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Solicitudes Rechazadas</span>
                        <span class="info-box-number" id="solicitudesRechazadas">0</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">En el período</span>
                    </div>
                </div>
                
            </div>
            
        </div>

        <!-- TABLA PRINCIPAL -->
        <div class="box">
            
            <!-- HEADER CON CONTROLES -->
            <div class="box-header with-border">
                
                <div class="row">
                    
                    <div class="col-md-6">
                        <h3 class="box-title">
                            <i class="fa fa-history"></i> Histórico de Movimientos
                        </h3>
                        <span class="label label-info" id="contadorRegistros">0 registros</span>
                    </div>
                    
                    <div class="col-md-6">
                        
                        <div class="pull-right">
                            
                            <!-- BOTÓN ACTUALIZAR -->
                            <button type="button" class="btn btn-primary" onclick="actualizarHistorico()">
                                <i class="fa fa-refresh"></i> Actualizar
                            </button>

                            <!-- BOTONES DE EXPORTACIÓN -->
                            <?php if($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Transportador"): ?>
                            <div class="btn-group" style="margin-left: 10px;">
                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-download"></i> Exportar <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-right">
                                    <li>
                                        <a href="#" onclick="exportarHistoricoPDF()">
                                            <i class="fa fa-file-pdf-o text-red"></i> Exportar a PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" onclick="exportarHistoricoExcel()">
                                            <i class="fa fa-file-excel-o text-green"></i> Exportar a Excel
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="#" onclick="exportarResumenEjecutivo()">
                                            <i class="fa fa-pie-chart text-blue"></i> Resumen Ejecutivo
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>

                        </div>
                        
                    </div>
                    
                </div>
                
            </div>

            <!-- TABLA DE HISTÓRICO -->
            <div class="box-body">
                <table class="table table-bordered table-striped dt-responsive tablaHistoricoTransito" width="100%">
                    <thead>
                        <tr>
                            <th style="width:10px">#</th>
                            <th>Fecha/Hora</th>
                            <th>Tipo Movimiento</th>
                            <th>Código Producto</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Transportador</th>
                            <th>Origen → Destino</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            
        </div>

        <!-- ANÁLISIS Y GRÁFICOS -->
        <?php if($_SESSION["perfil"] == "Administrador"): ?>
        <div class="row">
            
            <!-- GRÁFICO DE MOVIMIENTOS POR TIPO -->
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-pie-chart"></i> Movimientos por Tipo
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="graficoTiposMovimiento" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- GRÁFICO DE ACTIVIDAD POR TRANSPORTADOR -->
            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-bar-chart"></i> Actividad por Transportador
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="graficoTransportadores" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- LÍNEA DE TIEMPO DE ACTIVIDAD -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-line-chart"></i> Línea de Tiempo de Actividad
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <canvas id="graficoLineaTiempo" style="height: 200px;"></canvas>
            </div>
        </div>
        <?php endif; ?>
        
    </section>
</div>

<!-- MODAL VER DETALLES COMPLETOS DE MOVIMIENTO -->
<div class="modal fade" id="modalDetallesMovimiento" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title text-white">
                    <i class="fa fa-info-circle"></i> Detalles Completos del Movimiento
                </h4>
            </div>
            <div class="modal-body">
                
                <!-- TAB DE NAVEGACIÓN -->
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#tabInformacionGeneral" role="tab" data-toggle="tab">
                            <i class="fa fa-info"></i> Información General
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tabDetalleProducto" role="tab" data-toggle="tab">
                            <i class="fa fa-cube"></i> Detalle del Producto
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#tabTrazabilidad" role="tab" data-toggle="tab">
                            <i class="fa fa-route"></i> Trazabilidad
                        </a>
                    </li>
                </ul>

                <!-- CONTENIDO DE TABS -->
                <div class="tab-content" style="margin-top: 20px;">
                    
                    <!-- TAB 1: INFORMACIÓN GENERAL -->
                    <div role="tabpanel" class="tab-pane fade in active" id="tabInformacionGeneral">
                        <div class="row">
                            
                            <div class="col-md-6">
                                <div class="box box-solid">
                                    <div class="box-header">
                                        <h4 class="box-title">Datos del Movimiento</h4>
                                    </div>
                                    <div class="box-body">
                                        <table class="table table-condensed">
                                            <tr>
                                                <td><strong>ID Movimiento:</strong></td>
                                                <td id="detalleIdMovimiento">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Fecha y Hora:</strong></td>
                                                <td id="detalleFechaMovimiento">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tipo:</strong></td>
                                                <td id="detalleTipoMovimiento">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Cantidad:</strong></td>
                                                <td id="detalleCantidadMovimiento">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="box box-solid">
                                    <div class="box-header">
                                        <h4 class="box-title">Participantes</h4>
                                    </div>
                                    <div class="box-body">
                                        <table class="table table-condensed">
                                            <tr>
                                                <td><strong>Transportador:</strong></td>
                                                <td id="detalleTransportadorMovimiento">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Usuario Origen:</strong></td>
                                                <td id="detalleUsuarioOrigen">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Usuario Destino:</strong></td>
                                                <td id="detalleUsuarioDestino">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box box-solid">
                                    <div class="box-header">
                                        <h4 class="box-title">Ubicaciones</h4>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Sucursal Origen:</strong><br>
                                                <span id="detalleSucursalOrigen" class="text-primary">-</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Sucursal Destino:</strong><br>
                                                <span id="detalleSucursalDestino" class="text-success">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- OBSERVACIONES -->
                        <div id="detalleObservacionesContainer" class="row" style="display: none;">
                            <div class="col-md-12">
                                <div class="box box-warning">
                                    <div class="box-header">
                                        <h4 class="box-title">
                                            <i class="fa fa-comment"></i> Observaciones
                                        </h4>
                                    </div>
                                    <div class="box-body">
                                        <p id="detalleObservaciones"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: DETALLE DEL PRODUCTO -->
                    <div role="tabpanel" class="tab-pane fade" id="tabDetalleProducto">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box box-primary">
                                    <div class="box-header">
                                        <h4 class="box-title">Información del Producto</h4>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-condensed">
                                                    <tr>
                                                        <td><strong>Código:</strong></td>
                                                        <td><code id="detalleCodigoProducto">-</code></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Descripción:</strong></td>
                                                        <td id="detalleDescripcionProducto">-</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <div id="detalleDespachoInfo" style="display: none;">
                                                    <strong>Despacho Relacionado:</strong><br>
                                                    <span id="detalleNumeroDespacho" class="label label-primary">-</span>
                                                </div>
                                                <div id="detalleSolicitudInfo" style="display: none;">
                                                    <strong>Solicitud Relacionada:</strong><br>
                                                    <span id="detalleIdSolicitud" class="label label-warning">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: TRAZABILIDAD -->
                    <div role="tabpanel" class="tab-pane fade" id="tabTrazabilidad">
                        <div class="timeline" id="timelineTrazabilidad">
                            <!-- Timeline se carga dinámicamente -->
                        </div>
                    </div>

                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ESTILOS CSS -->
<style>
.movimiento-cargue {
    border-left: 4px solid #3c8dbc;
}

.movimiento-descarga {
    border-left: 4px solid #00a65a;
}

.movimiento-solicitud {
    border-left: 4px solid #f39c12;
}

.movimiento-rechazo {
    border-left: 4px solid #dd4b39;
}

.movimiento-forzada {
    border-left: 4px solid #605ca8;
}

.tipo-movimiento-badge {
    font-size: 11px;
    padding: 4px 8px;
}

.timeline-condensed {
    max-height: 400px;
    overflow-y: auto;
}

.info-box-content .progress {
    margin: 5px -10px 5px -10px;
}

.info-box-content .progress .progress-bar {
    background: rgba(255,255,255,0.2);
}

#contadorRegistros {
    margin-left: 10px;
    font-size: 12px;
}

.badge-tipo-movimiento {
    display: inline-block;
    min-width: 10px;
    padding: 3px 7px;
    font-size: 10px;
    font-weight: bold;
    line-height: 1;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 10px;
}

.badge-cargue { background-color: #3c8dbc; }
.badge-descarga { background-color: #00a65a; }
.badge-solicitud { background-color: #f39c12; }
.badge-rechazo { background-color: #dd4b39; }
.badge-forzada { background-color: #605ca8; }
.badge-transferencia { background-color: #00c0ef; }
</style>

<?php
// EJECUTAR CONTROLADOR
require_once "controladores/historico-transito.controlador.php";

$historicoTransito = new ControladorHistoricoTransito();
?>