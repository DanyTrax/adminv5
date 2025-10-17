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
                    <button type="button" class="btn btn-primary btn-sm" onclick="actualizarTablaStock()">
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
// EVITAR CONFLICTOS - NO DECLARAR VARIABLES GLOBALES
(function() {
    
    console.log("🚛 Inicializando Stock Tránsito...");
    
    var tablaStock = null;
    
    // Esperar a que todo se cargue
    $(document).ready(function() {
        
        setTimeout(function() {
            
            console.log("⚡ Creando DataTable...");
            
            // Limpiar tabla anterior
            if ($.fn.DataTable.isDataTable('#tablaStockTransito')) {
                $('#tablaStockTransito').DataTable().destroy();
            }
            
            // Crear nueva tabla
            tablaStock = $('#tablaStockTransito').DataTable({
                "ajax": {
                    "url": "ajax/datatable-stock-transito.ajax.php",
                    "type": "POST",
                    "error": function(xhr, error, thrown) {
                        console.error("❌ Error AJAX:", error);
                        console.error("Respuesta:", xhr.responseText);
                    }
                },
                "processing": true,
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
                    { "targets": [0, 3, 7, 8], "orderable": false },
                    { "targets": [3, 7, 8], "className": "text-center" }
                ],
                "drawCallback": function() {
                    var info = this.api().page.info();
                    console.log("✅ Tabla cargada:", info.recordsTotal, "registros");
                    
                    // Configurar eventos
                    configurarEventos();
                }
            });
            
            // Función global para actualizar
            window.actualizarTablaStock = function() {
                if (tablaStock) {
                    tablaStock.ajax.reload();
                    console.log("🔄 Tabla actualizada");
                }
            };
            
        }, 2000); // Esperar 2 segundos
        
    });
    
// Configurar eventos
function configurarEventos() {
    
    // Ver historial
    $(document).off('click', '.btnVerHistorialProducto').on('click', '.btnVerHistorialProducto', function() {
        var codigo = $(this).attr('codigoProducto');
        console.log("📋 Ver historial:", codigo);
        
        $("#historialCodigo").text(codigo);
        $("#modalHistorial").modal("show");
        $("#contenidoHistorial").html('<p class="text-center">Historial del producto ' + codigo + '</p>');
    });
    
    // Recibir stock (usuarios)
    $(document).off('click', '.btnRecibirStock').on('click', '.btnRecibirStock', function() {
        var datos = {
            id: $(this).attr('idStockTransito'),
            codigo: $(this).attr('codigoProducto'),
            descripcion: $(this).attr('descripcionProducto'),
            cantidad: $(this).attr('cantidadDisponible'),
            transportador: $(this).attr('nombreTransportador'),
            origen: $(this).attr('sucursalOrigen')
        };
        
        console.log("📦 Recibir stock:", datos.codigo);
        
        // Llenar modal
        $("#recibirCodigo").text(datos.codigo);
        $("#recibirDescripcion").text(datos.descripcion);
        $("#recibirTransportador").text(datos.transportador);
        $("#recibirOrigen").text(datos.origen);
        $("#recibirDisponible").text(datos.cantidad);
        $("#cantidadRecibir").attr("max", datos.cantidad).val(1);
        $("#recibirIdStock").val(datos.id);
        
        $("#modalRecibirStock").modal("show");
    });
    
    // Eliminar stock (admin)
    $(document).off('click', '.btnEliminarStock').on('click', '.btnEliminarStock', function() {
        var datos = {
            id: $(this).attr('idStockTransito'),
            codigo: $(this).attr('codigoProducto'),
            cantidad: $(this).attr('cantidadDisponible')
        };
        
        console.log("🗑️ Eliminar stock:", datos.codigo);
        
        // Llenar modal
        $("#eliminarCodigo").text(datos.codigo);
        $("#eliminarCantidad").text(datos.cantidad);
        $("#eliminarIdStock").val(datos.id);
        
        $("#modalEliminarStock").modal("show");
    });
    
    console.log("🔗 Eventos configurados");
}

// Procesar formulario de recepción
$(document).on('submit', '#formRecibirStock', function(e) {
    e.preventDefault();
    
    console.log("📤 Procesando recepción...");
    
    $.ajax({
        url: 'ajax/procesar-recepcion-stock.ajax.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        beforeSend: function() {
            $("#formRecibirStock button[type=submit]").prop("disabled", true);
        },
        success: function(response) {
            if (response.success) {
                $("#modalRecibirStock").modal("hide");
                
                if (typeof swal === 'function') {
                    swal("¡Recibido!", response.message, "success");
                } else {
                    alert("Recepción procesada exitosamente");
                }
                
                // Actualizar tabla
                if (tablaStock) {
                    tablaStock.ajax.reload();
                }
            } else {
                if (typeof swal === 'function') {
                    swal("Error", response.error, "error");
                } else {
                    alert("Error: " + response.error);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("Error AJAX:", error);
            if (typeof swal === 'function') {
                swal("Error", "Error al procesar la recepción", "error");
            } else {
                alert("Error al procesar la recepción");
            }
        },
        complete: function() {
            $("#formRecibirStock button[type=submit]").prop("disabled", false);
        }
    });
});
})();

console.log("✅ Módulo cargado");
</script>