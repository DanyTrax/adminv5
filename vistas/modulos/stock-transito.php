<div class="content-wrapper">
    
    <section class="content-header">
        
        <h1>
            Stock en Tránsito
            <small>Gestión de productos en tránsito</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Stock en Tránsito</li>
        </ol>

    </section>

    <section class="content">

        <div class="box">
            
            <div class="box-header with-border">
                
                <h3 class="box-title">
                    <i class="fa fa-truck"></i> 
                    Lista de productos en tránsito
                </h3>
                
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" onclick="actualizarTabla()">
                        <i class="fa fa-refresh"></i> Actualizar
                    </button>
                </div>

            </div>

            <div class="box-body">
                
            <table id="tablaStockTransito" class="table table-bordered table-striped dt-responsive" width="100%">
                
                <thead>
                    <tr>
                        <th style="width:10px">#</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Transportador</th>
                        <th>Origen</th>
                        <th>Fecha Carga</th>
                        <th>Solicitudes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

            </table>

            </div>

        </div>

    </section>

</div>

<!--=====================================
MODAL SOLICITAR DESCARGA
======================================-->
<div id="modalSolicitarDescarga" class="modal fade" role="dialog">
    
    <div class="modal-dialog">

        <div class="modal-content">

            <form role="form" method="post" id="formSolicitarDescarga">

                <div class="modal-header" style="background:#3c8dbc; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-download"></i> Solicitar Descarga
                    </h4>
                </div>

                <div class="modal-body">

                    <div class="box-body">

                        <!-- INFO PRODUCTO -->
                        <div class="alert alert-info">
                            <h4><i class="fa fa-info-circle"></i> Información del Producto</h4>
                            <p><strong>Código:</strong> <span id="infoCodigo"></span></p>
                            <p><strong>Descripción:</strong> <span id="infoDescripcion"></span></p>
                            <p><strong>Transportador:</strong> <span id="infoTransportador"></span></p>
                            <p><strong>Origen:</strong> <span id="infoOrigen"></span></p>
                        </div>

                        <!-- FORM -->
                        <div class="row">
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cantidad disponible:</label>
                                    <input type="text" id="cantidadDisponible" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cantidad a solicitar: *</label>
                                    <input type="number" 
                                           name="cantidadSolicitada" 
                                           id="cantidadSolicitada"
                                           class="form-control" 
                                           min="1" 
                                           required>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sucursal destino: *</label>
                                    <select name="sucursalDestino" class="form-control" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="Sucursal Principal">Sucursal Principal</option>
                                        <option value="Sucursal Norte">Sucursal Norte</option>
                                        <option value="Sucursal Sur">Sucursal Sur</option>
                                        <option value="Bodega Central">Bodega Central</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha límite:</label>
                                    <input type="date" name="fechaLimite" class="form-control">
                                </div>
                            </div>

                        </div>

                        <div class="form-group">
                            <label>Observaciones:</label>
                            <textarea name="observaciones" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Observaciones adicionales..."></textarea>
                        </div>

                        <!-- CAMPOS OCULTOS -->
                        <input type="hidden" name="idStockTransito" id="idStockTransito">
                        <input type="hidden" name="codigoProducto" id="codigoProducto">

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-paper-plane"></i> Enviar Solicitud
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

<!--=====================================
MODAL VER HISTORIAL
======================================-->
<div id="modalHistorial" class="modal fade" role="dialog">
    
    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header" style="background:#3c8dbc; color:white">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-history"></i> Historial del Producto: <span id="historialCodigo"></span>
                </h4>
            </div>

            <div class="modal-body">
                <div id="contenidoHistorial">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando historial...</p>
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

<script>
// Definir variables globales desde PHP
window.perfilUsuario = '<?php echo $_SESSION["perfil"] ?? "Invitado"; ?>';
window.idUsuario = <?php echo $_SESSION["id"] ?? 0; ?>;

console.log("🔧 Variables PHP definidas:");
console.log("- perfilUsuario:", window.perfilUsuario);
console.log("- idUsuario:", window.idUsuario);
</script>
<!-- Forzar carga de archivos sin caché -->
<script>
// Verificar que no hay conflictos
if (typeof perfilUsuario === 'undefined') {
    console.log("✅ perfilUsuario no está definido globalmente - OK");
} else {
    console.log("⚠️ perfilUsuario YA existe:", perfilUsuario);
}
</script>