<?php
// Vista móvil dedicada para Transportador - Solo perfil Transportador
if ($_SESSION["perfil"] != "Transportador") {
    header("Location: inicio");
    exit;
}
?>
<div class="content-wrapper transportador-movil-wrapper">
    <section class="content-header" style="padding: 10px 15px;">
        <h1 style="font-size: 18px; margin: 0;">
            <i class="fa fa-truck"></i> Transportador: <?php echo htmlspecialchars($_SESSION["nombre"]); ?>
        </h1>
    </section>

    <section class="content" style="padding: 10px; padding-bottom: 80px;">
        
        <!-- Tabs de navegación - Fijas para móvil -->
        <ul class="nav nav-tabs nav-justified transportador-movil-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#tabInicio" data-toggle="tab" class="tab-movil">
                    <i class="fa fa-home"></i><br><small>Inicio</small>
                </a>
            </li>
            <li role="presentation">
                <a href="#tabSolicitudes" data-toggle="tab" class="tab-movil">
                    <i class="fa fa-clipboard"></i><br><small>Solicitudes</small>
                    <span class="badge badge-solicitudes" id="badgeSolicitudes" style="display:none;">0</span>
                </a>
            </li>
            <li role="presentation">
                <a href="#tabDespachos" data-toggle="tab" class="tab-movil">
                    <i class="fa fa-list-alt"></i><br><small>Despachos</small>
                    <span class="badge badge-despachos" id="badgeDespachos" style="display:none;">0</span>
                </a>
            </li>
            <li role="presentation">
                <a href="#tabStock" data-toggle="tab" class="tab-movil">
                    <i class="fa fa-cubes"></i><br><small>En Camión</small>
                </a>
            </li>
            <li role="presentation">
                <a href="#tabDescargas" data-toggle="tab" class="tab-movil">
                    <i class="fa fa-download"></i><br><small>Mis Descargas</small>
                </a>
            </li>
        </ul>

        <div class="tab-content transportador-movil-content">
            
            <!-- TAB INICIO -->
            <div role="tabpanel" class="tab-pane active" id="tabInicio">
                <div id="contenidoInicio">
                    <div class="text-center" style="padding: 30px;">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted">Cargando resumen...</p>
                    </div>
                </div>
            </div>

            <!-- TAB SOLICITUDES -->
            <div role="tabpanel" class="tab-pane" id="tabSolicitudes">
                <div id="contenidoSolicitudes">
                    <div class="text-center" style="padding: 30px;">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted">Cargando solicitudes...</p>
                    </div>
                </div>
            </div>

            <!-- TAB DESPACHOS -->
            <div role="tabpanel" class="tab-pane" id="tabDespachos">
                <div id="contenidoDespachos">
                    <div class="text-center" style="padding: 30px;">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted">Cargando despachos...</p>
                    </div>
                </div>
            </div>

            <!-- TAB EN CAMIÓN -->
            <div role="tabpanel" class="tab-pane" id="tabStock">
                <div id="contenidoStock">
                    <div class="text-center" style="padding: 30px;">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted">Cargando productos en camión...</p>
                    </div>
                </div>
            </div>

            <!-- TAB MIS DESCARGAS -->
            <div role="tabpanel" class="tab-pane" id="tabDescargas">
                <div id="contenidoDescargas">
                    <div class="text-center" style="padding: 30px;">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted">Cargando mis descargas...</p>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Modal Detalle Despacho - Productos del despacho -->
<div class="modal fade" id="modalDetalleDespachoMovil" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-truck"></i> <span id="modalDespachoTitulo">Detalle del Despacho</span></h4>
            </div>
            <div class="modal-body">
                <p id="modalDespachoMeta" class="text-muted" style="margin-bottom:15px;"></p>
                <div id="modalDespachoDetalleAdicional" style="display:none; margin-bottom:15px;">
                    <strong><i class="fa fa-comment"></i> Detalle Adicional:</strong>
                    <p id="modalDespachoDetalleAdicionalTexto" class="text-muted" style="margin:5px 0 0 0; padding:8px; background:#f8f9fa; border-radius:4px;"></p>
                </div>
                <div id="modalDespachoProductos" class="table-responsive">
                    <table class="table table-bordered table-condensed">
                        <thead><tr><th>Código</th><th>Descripción</th><th class="text-center">Cant.</th><th>Observación</th></tr></thead>
                        <tbody id="modalDespachoProductosBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle Solicitud - Productos y Stock Sucursales -->
<div class="modal fade" id="modalDetalleSolicitudMovil" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-list"></i> <span id="modalSolicitudTitulo">Detalle de Solicitud</span></h4>
            </div>
            <div class="modal-body">
                <p id="modalSolicitudMeta" class="text-muted" style="margin-bottom:15px;"></p>
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#tabProductosSolicitudMovil" role="tab" data-toggle="tab"><i class="fa fa-cubes"></i> Productos</a>
                    </li>
                    <li role="presentation">
                        <a href="#tabStockSucursalesMovil" role="tab" data-toggle="tab"><i class="fa fa-warehouse"></i> Stock Sucursales</a>
                    </li>
                </ul>
                <div class="tab-content" style="padding-top:15px;">
                    <div role="tabpanel" class="tab-pane active" id="tabProductosSolicitudMovil">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead><tr><th>Código</th><th>Descripción</th><th class="text-center">Cant.</th></tr></thead>
                                <tbody id="modalSolicitudProductosBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="tabStockSucursalesMovil">
                        <p class="text-info"><i class="fa fa-info-circle"></i> Stock disponible de los productos solicitados en cada sucursal (para saber si hay y de dónde sacarla).</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-condensed" id="tablaStockSucursalesMovil">
                                <thead><tr id="theadStockSucursalesMovil"><th>#</th><th>Código</th><th>Descripción</th><th>Cant. Sol.</th><th id="sucursalesHeaderMovil">Sucursales</th></tr></thead>
                                <tbody id="tbodyStockSucursalesMovil">
                                    <tr><td colspan="5" class="text-center text-muted">Seleccione la pestaña después de cargar los productos</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Transportador móvil - responsive */
.transportador-movil-wrapper { max-width: 100%; }
.transportador-movil-tabs {
    background: #fff;
    border-bottom: 2px solid #ddd;
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0;
    padding: 0;
    list-style: none;
    margin-left: 0;
}
.transportador-movil-tabs li {
    border-right: 1px solid #eee;
    border-bottom: 1px solid #eee;
    margin: 0;
}
.transportador-movil-tabs li:nth-child(2n) { border-right: none; }
.transportador-movil-tabs li a {
    position: relative;
    padding: 10px 5px !important;
    font-size: 11px;
    color: #666;
    min-height: 55px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.transportador-movil-tabs li a i { font-size: 18px; margin-bottom: 4px; }
.transportador-movil-tabs li.active a { color: #3c8dbc; font-weight: bold; background: #f8f9fa; }
.transportador-movil-tabs li.active { border-bottom: 2px solid #3c8dbc; }
.transportador-movil-tabs .badge { position: absolute; top: 2px; right: 2px; font-size: 10px; min-width: 18px; }
@media (min-width: 768px) {
    .transportador-movil-tabs { grid-template-columns: repeat(5, 1fr); }
    .transportador-movil-tabs li:nth-child(2n) { border-right: 1px solid #eee; }
    .transportador-movil-tabs li:last-child { border-right: none; }
}

/* Tarjetas móviles */
.card-movil {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 12px;
    padding: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.card-movil .card-title { font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #333; }
.card-movil .card-meta { font-size: 12px; color: #777; margin-bottom: 10px; }
.btn-movil { min-height: 44px; padding: 10px 16px; font-size: 14px; }
.btn-movil-block { width: 100%; margin-bottom: 8px; }

/* Contadores resumen */
.resumen-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
.resumen-item {
    flex: 1 1 45%;
    min-width: 120px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    border: 1px solid #e9ecef;
}
.resumen-item .num { font-size: 24px; font-weight: bold; color: #3c8dbc; }
.resumen-item .label { font-size: 11px; color: #666; }
</style>
