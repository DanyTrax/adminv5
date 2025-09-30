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

// CREAR TABLA DIRECTAMENTE AQUÍ - SIN DEPENDER DE ARCHIVOS EXTERNOS
$(document).ready(function() {
    
    console.log("🚛 Inicializando DataTable Stock Tránsito directamente...");
    
    // Destruir tabla existente si existe
    if ($.fn.DataTable.isDataTable('#tablaStockTransito')) {
        $('#tablaStockTransito').DataTable().destroy();
    }
    
    // Crear nuevo DataTable
    var tablaStockTransito = $('#tablaStockTransito').DataTable({
        "ajax": {
            "url": "ajax/datatable-stock-transito.ajax.php",
            "type": "POST",
            "data": {
                "tabla": "stock-transito"
            },
            "error": function(xhr, error, code) {
                console.error("❌ Error AJAX:", xhr.status, error);
                console.error("Respuesta completa:", xhr.responseText);
            },
            "success": function(data) {
                console.log("✅ AJAX exitoso - datos recibidos:", data);
            }
        },
        "processing": true,
        "deferRender": true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron productos en tránsito",
            "sEmptyTable": "No hay productos en stock de tránsito",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            }
        },
        "columnDefs": [
            {
                "targets": [0, 3, 7, 8], // #, Cantidad, Solicitudes, Acciones
                "orderable": false
            },
            {
                "targets": [3, 7, 8], // Cantidad, Solicitudes, Acciones
                "className": "text-center"
            }
        ],
        "drawCallback": function() {
            console.log("✅ DataTable Stock Tránsito dibujado exitosamente");
            var info = this.api().page.info();
            console.log("📊 Registros totales:", info.recordsTotal);
            console.log("📊 Registros mostrados:", info.recordsDisplay);
            
            // Configurar eventos después de dibujar
            configurarEventosStockTransito();
        },
        "initComplete": function() {
            console.log("🎯 DataTable Stock Tránsito inicializado completamente");
        }
    });
    
    // Función para actualizar tabla
    window.actualizarTablaStockTransito = function() {
        if (tablaStockTransito) {
            tablaStockTransito.ajax.reload(function() {
                console.log("🔄 Tabla actualizada");
            });
        }
    };
    
});

// CONFIGURAR EVENTOS DE LOS BOTONES
function configurarEventosStockTransito() {
    
    console.log("🔗 Configurando eventos de Stock Tránsito...");
    
    // Botón Ver Historial
    $(document).off('click', '.btnVerHistorialProducto').on('click', '.btnVerHistorialProducto', function(e) {
        e.preventDefault();
        
        var codigo = $(this).attr('codigoProducto');
        var transportadorId = $(this).attr('transportadorId');
        
        console.log("📋 Ver historial de producto:", codigo);
        
        // Aquí puedes agregar la lógica para mostrar el historial
        alert("Ver historial del producto: " + codigo);
    });
    
    // Botón Solicitar Descarga
    $(document).off('click', '.btnSolicitarDescarga').on('click', '.btnSolicitarDescarga', function(e) {
        e.preventDefault();
        
        var id = $(this).attr('idStockTransito');
        var codigo = $(this).attr('codigoProducto');
        var descripcion = $(this).attr('descripcionProducto');
        var cantidad = $(this).attr('cantidadDisponible');
        var transportador = $(this).attr('nombreTransportador');
        var origen = $(this).attr('sucursalOrigen');
        
        console.log("💼 Solicitar descarga de:", codigo, "Cantidad:", cantidad);
        
        // Aquí puedes agregar la lógica para solicitar descarga
        alert("Solicitar descarga de: " + codigo + " (Cantidad: " + cantidad + ")");
    });
    
    // Botón Ver Solicitudes Pendientes
    $(document).off('click', '.btnVerSolicitudesPendientes').on('click', '.btnVerSolicitudesPendientes', function(e) {
        e.preventDefault();
        
        var transportadorId = $(this).attr('transportadorId');
        
        console.log("🔔 Ver solicitudes pendientes del transportador:", transportadorId);
        
        alert("Ver solicitudes pendientes");
    });
    
    // Botón Forzar Descarga (Admin)
    $(document).off('click', '.btnForzarDescarga').on('click', '.btnForzarDescarga', function(e) {
        e.preventDefault();
        
        var id = $(this).attr('idStockTransito');
        var codigo = $(this).attr('codigoProducto');
        
        console.log("⚡ Forzar descarga (Admin):", codigo);
        
        if (confirm("¿Está seguro de forzar la descarga de este producto?")) {
            alert("Descarga forzada del producto: " + codigo);
        }
    });
    
    console.log("✅ Eventos de Stock Tránsito configurados");
}

// Función para actualizar tabla desde botón
function actualizarTabla() {
    if (typeof window.actualizarTablaStockTransito === 'function') {
        window.actualizarTablaStockTransito();
    } else {
        location.reload();
    }
}

console.log("✅ Script Stock Tránsito cargado completamente");
</script>