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
            Stock en Tránsito
            <small>Gestión de productos en movimiento</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Stock en Tránsito</li>
        </ol>
    </section>

    <section class="content">

        <!-- ROW DE RESUMEN -->
        <div class="row">
            
            <!-- TOTAL PRODUCTOS -->
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 id="totalProductosTransito">0</h3>
                        <p>Productos Diferentes</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-cubes"></i>
                    </div>
                </div>
            </div>

            <!-- TOTAL UNIDADES -->
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 id="totalUnidadesTransito">0</h3>
                        <p>Unidades en Tránsito</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-boxes"></i>
                    </div>
                </div>
            </div>

            <!-- TRANSPORTADORES ACTIVOS -->
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 id="totalTransportadores">0</h3>
                        <p>Transportadores Activos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-truck"></i>
                    </div>
                </div>
            </div>

            <!-- SOLICITUDES PENDIENTES -->
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3 id="solicitudesPendientes">0</h3>
                        <p>Solicitudes Pendientes</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-clock-o"></i>
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
                            <i class="fa fa-truck"></i> Productos en Tránsito
                        </h3>
                    </div>
                    
                    <div class="col-md-6">
                        
                        <div class="pull-right">
                            
                            <!-- FILTRO POR TRANSPORTADOR -->
                            <div class="btn-group" style="margin-right: 10px;">
                                <select class="form-control" id="filtroTransportador" onchange="filtrarPorTransportador()">
                                    <option value="">Todos los Transportadores</option>
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

                            <!-- BOTÓN REFRESCAR -->
                            <button type="button" class="btn btn-primary" onclick="actualizarStockTransito()">
                                <i class="fa fa-refresh"></i> Actualizar
                            </button>

                            <!-- BOTÓN EXPORTAR -->
                            <?php if($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Transportador"): ?>
                            <div class="btn-group" style="margin-left: 10px;">
                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-download"></i> Exportar <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-right">
                                    <li>
                                        <a href="#" onclick="exportarStockTransitoPDF()">
                                            <i class="fa fa-file-pdf-o text-red"></i> Exportar a PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" onclick="exportarStockTransitoExcel()">
                                            <i class="fa fa-file-excel-o text-green"></i> Exportar a Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>

                        </div>
                        
                    </div>
                    
                </div>
                
            </div>

            <!-- TABLA DE STOCK EN TRÁNSITO -->
            <div class="box-body">
                <table class="table table-bordered table-striped dt-responsive tablaStockTransito" width="100%">
                    <thead>
                        <tr>
                            <th style="width:10px">#</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Transportador</th>
                            <th>Sucursal Origen</th>
                            <th>Fecha Cargue</th>
                            <th>Solicitudes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            
        </div>

        <!-- SOLICITUDES PENDIENTES -->
        <?php if($_SESSION["perfil"] == "Transportador"): ?>
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-bell"></i> Solicitudes de Descarga Pendientes
                </h3>
                <span class="label label-warning pull-right" id="contadorSolicitudesPendientes">0</span>
            </div>
            <div class="box-body">
                <div id="contenedorSolicitudesPendientes">
                    <p class="text-center text-muted">
                        <i class="fa fa-info-circle"></i> No hay solicitudes pendientes
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </section>
</div>

<!-- MODAL SOLICITAR DESCARGA -->
<div class="modal fade" id="modalSolicitarDescarga" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form role="form" method="post" id="formSolicitarDescarga">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-download"></i> Solicitar Descarga de Producto
                    </h4>
                </div>
                <div class="modal-body">
                    
                    <!-- INFORMACIÓN DEL PRODUCTO -->
                    <div class="alert alert-info">
                        <h4><i class="fa fa-cube"></i> Producto Seleccionado</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Código:</strong> <span id="codigoProductoDescarga"></span><br>
                                <strong>Transportador:</strong> <span id="transportadorProductoDescarga"></span><br>
                            </div>
                            <div class="col-md-6">
                                <strong>Disponible:</strong> <span id="cantidadDisponibleDescarga" class="text-bold text-green"></span> unidades<br>
                                <strong>Origen:</strong> <span id="origenProductoDescarga"></span><br>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px;">
                            <div class="col-md-12">
                                <strong>Descripción:</strong><br>
                                <span id="descripcionProductoDescarga"></span>
                            </div>
                        </div>
                    </div>

                    <!-- DATOS DE LA SOLICITUD -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cantidad a Descargar <span class="text-danger">*</span>:</label>
                                <input type="number" class="form-control" name="cantidadDescargar" 
                                       id="cantidadDescargar" min="1" required
                                       placeholder="Cantidad de unidades">
                                <small class="help-block">
                                    <i class="fa fa-info-circle"></i> 
                                    Máximo: <span id="maximoPermitidoDescarga">0</span> unidades
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sucursal Destino:</label>
                                <input type="text" class="form-control" name="sucursalDestino" 
                                       value="<?php 
                                       $sucursalLocal = ControladorSucursales::ctrObtenerConfiguracionLocal();
                                       echo $sucursalLocal ? $sucursalLocal['nombre'] : 'Sucursal Local';
                                       ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Observaciones (opcional):</label>
                        <textarea class="form-control" name="observacionesDescarga" rows="3" 
                                 placeholder="Motivo o detalles de la solicitud de descarga..."></textarea>
                    </div>
                    
                    <!-- ALERTA DE VALIDACIÓN -->
                    <div id="alertaValidacionDescarga" class="alert alert-danger" style="display: none;">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span id="mensajeValidacionDescarga"></span>
                    </div>

                    <!-- CAMPOS OCULTOS -->
                    <input type="hidden" name="idStockTransito" id="idStockTransitoDescarga">
                    <input type="hidden" name="codigoProducto" id="codigoProductoDescargaHidden">
                    <input type="hidden" name="transportadorId" id="transportadorIdDescarga">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" name="solicitarDescarga" class="btn btn-primary" id="btnSolicitarDescarga">
                        <i class="fa fa-download"></i> Solicitar Descarga
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR DESCARGA (TRANSPORTADORES) -->
<?php if($_SESSION["perfil"] == "Transportador"): ?>
<div class="modal fade" id="modalConfirmarDescarga" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form role="form" method="post" id="formConfirmarDescarga">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-check text-success"></i> Confirmar Descarga
                    </h4>
                </div>
                <div class="modal-body">
                    
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>¡Importante!</strong> Al confirmar esta descarga:
                        <ul style="margin-top: 10px;">
                            <li>Los productos serán <strong>descontados de su stock en tránsito</strong></li>
                            <li>Los productos se <strong>agregarán al inventario local</strong> de la sucursal</li>
                            <li>Esta acción <strong>no se puede deshacer</strong></li>
                        </ul>
                    </div>

                    <!-- DETALLES DE LA SOLICITUD -->
                    <div class="box box-solid">
                        <div class="box-header">
                            <h4 class="box-title">Detalles de la Solicitud</h4>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Producto:</strong><br>
                                    <span id="productoConfirmarDescarga"></span><br><br>
                                    <strong>Solicitado por:</strong><br>
                                    <span id="usuarioSolicitanteConfirmar"></span><br>
                                </div>
                                <div class="col-md-6">
                                    <strong>Cantidad solicitada:</strong><br>
                                    <span id="cantidadSolicitadaConfirmar" class="text-bold text-blue"></span> unidades<br><br>
                                    <strong>Destino:</strong><br>
                                    <span id="destinoConfirmarDescarga"></span><br>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-12">
                                    <strong>Observaciones del solicitante:</strong><br>
                                    <span id="observacionesSolicitanteConfirmar" class="text-muted"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Observaciones de la confirmación (opcional):</label>
                        <textarea class="form-control" name="observacionesConfirmacion" rows="3" 
                                 placeholder="Observaciones sobre la descarga confirmada..."></textarea>
                    </div>
                    
                    <input type="hidden" name="idSolicitudDescarga" id="idSolicitudDescargaConfirmar">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="rechazarDescarga()">
                        <i class="fa fa-ban"></i> Rechazar
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                    <button type="submit" name="confirmarDescarga" class="btn btn-success">
                        <i class="fa fa-check"></i> Confirmar Descarga
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL RECHAZAR DESCARGA -->
<?php if($_SESSION["perfil"] == "Transportador"): ?>
<div class="modal fade" id="modalRechazarDescarga" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form role="form" method="post" id="formRechazarDescarga">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-ban text-danger"></i> Rechazar Solicitud de Descarga
                    </h4>
                </div>
                <div class="modal-body">
                    
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        Está a punto de <strong>rechazar</strong> esta solicitud de descarga.
                        El usuario solicitante será notificado del rechazo.
                    </div>

                    <div class="form-group">
                        <label>Motivo del rechazo <span class="text-danger">*</span>:</label>
                        <textarea class="form-control" name="motivoRechazo" rows="4" 
                                 placeholder="Explique por qué rechaza esta solicitud de descarga..." required></textarea>
                    </div>
                    
                    <input type="hidden" name="idSolicitudRechazar" id="idSolicitudRechazarHidden">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" name="rechazarDescarga" class="btn btn-danger">
                        <i class="fa fa-ban"></i> Rechazar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL VER HISTORIAL DE PRODUCTO -->
<div class="modal fade" id="modalHistorialProducto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-history"></i> Historial del Producto
                    <span id="codigoProductoHistorial" class="label label-primary"></span>
                </h4>
            </div>
            <div class="modal-body">
                
                <div class="timeline" id="timelineProductoHistorial">
                    <!-- Timeline se carga dinámicamente -->
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
.stock-disponible {
    font-weight: bold;
    color: #00a65a;
}

.stock-solicitado {
    font-weight: bold;
    color: #f39c12;
}

.solicitud-pendiente {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 15px;
}

.solicitud-pendiente .btn-group {
    margin-top: 10px;
}

.small-box .inner h3 {
    font-size: 2.2em;
}

.validacion-cantidad-error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.validacion-cantidad-ok {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

/* Timeline styles */
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

.timeline > div > .fa {
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

.timeline-body {
    padding: 10px;
}

.time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
}
</style>

<?php
// EJECUTAR CONTROLADORES
require_once "controladores/stock-transito.controlador.php";

$stockTransito = new ControladorStockTransito();
$stockTransito->ctrSolicitarDescarga();
$stockTransito->ctrConfirmarDescarga();
$stockTransito->ctrRechazarDescarga();
?>