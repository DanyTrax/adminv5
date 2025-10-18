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

<!--=====================================
MODAL DESCARGA DIRECTA
======================================-->
<div id="modalDescargaDirecta" class="modal fade" role="dialog">
    
    <div class="modal-dialog">

        <div class="modal-content">

            <form role="form" method="post" id="formDescargaDirecta">

                <div class="modal-header" style="background:#28a745; color:white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-download"></i> Descargar Producto
                    </h4>
                </div>

                <div class="modal-body">

                    <div class="box-body">

                        <!-- INFO PRODUCTO -->
                        <div class="alert alert-info">
                            <h4><i class="fa fa-info-circle"></i> Información del Producto</h4>
                            <p><strong>Código:</strong> <span id="descargaCodigo"></span></p>
                            <p><strong>Descripción:</strong> <span id="descargaDescripcion"></span></p>
                            <p><strong>Transportador:</strong> <span id="descargaTransportador"></span></p>
                            <p><strong>Origen:</strong> <span id="descargaOrigen"></span></p>
                            <p><strong>Despacho:</strong> <span id="descargaDespacho"></span></p>
                        </div>

                        <!-- FORM -->
                        <div class="row">
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cantidad disponible:</label>
                                    <input type="text" id="descargaCantidadDisponible" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cantidad a descargar: *</label>
                                    <input type="number" 
                                           name="cantidadDescargar" 
                                           id="cantidadDescargar"
                                           class="form-control" 
                                           min="1" 
                                           required>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Observaciones:</label>
                                    <textarea name="observacionesDescarga" 
                                              id="observacionesDescarga"
                                              class="form-control" 
                                              rows="3" 
                                              placeholder="Observaciones sobre la descarga..."></textarea>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Descargar</button>
                </div>

            </form>

        </div>

    </div>

</div>

<!--=====================================
INCLUIR JAVASCRIPT
======================================-->
<script src="vistas/js/stock-transito.js"></script>

<!--=====================================
SCRIPT DE PRUEBA
======================================-->
<script>
// Variable global para almacenar el ID del stock seleccionado
var stockSeleccionado = null;

console.log("🔍 Script de prueba cargado");
console.log("🔍 jQuery disponible:", typeof $ !== 'undefined');
console.log("🔍 Modal existe:", $("#modalDescargaDirecta").length > 0);

// Evento de prueba simple
$(document).ready(function() {
    console.log("🔍 Document ready ejecutado");
    
    // Evento de prueba para cualquier botón
    $(document).on("click", "button", function() {
        console.log("🔍 Botón clickeado:", $(this).attr("class"));
        
        if($(this).hasClass("btnDescargaDirecta")) {
            console.log("🎯 Botón de descarga detectado!");
            
            // Obtener datos del botón
            var idStockTransito = $(this).data("id-stock");
            var codigoProducto = $(this).data("codigo");
            var descripcionProducto = $(this).data("descripcion");
            var cantidadDisponible = $(this).data("cantidad");
            var nombreTransportador = $(this).data("transportador");
            var sucursalOrigen = $(this).data("origen");
            var numeroDespacho = $(this).data("despacho");
            
            console.log("📥 Datos obtenidos:", {
                idStockTransito,
                codigoProducto,
                descripcionProducto,
                cantidadDisponible,
                nombreTransportador,
                sucursalOrigen,
                numeroDespacho
            });
            
            // Guardar ID del stock seleccionado
            stockSeleccionado = idStockTransito;
            
            // Llenar información del producto
            $("#descargaCodigo").text(codigoProducto || "N/A");
            $("#descargaDescripcion").text(descripcionProducto || "N/A");
            $("#descargaTransportador").text(nombreTransportador || "N/A");
            $("#descargaOrigen").text(sucursalOrigen || "N/A");
            $("#descargaDespacho").text(numeroDespacho || "N/A");
            $("#descargaCantidadDisponible").val(cantidadDisponible || 0);
            
            // Configurar máximo en el input
            $("#cantidadDescargar").attr("max", cantidadDisponible || 0);
            $("#cantidadDescargar").val("");
            $("#observacionesDescarga").val("");
            
            // Mostrar modal
            $("#modalDescargaDirecta").modal("show");
        }
    });
    
    // Manejar envío del formulario de descarga
    $(document).on("submit", "#formDescargaDirecta", function(e) {
        e.preventDefault();
        
        console.log("📤 Formulario de descarga enviado");
        
        var cantidadDescargar = $("#cantidadDescargar").val();
        var observaciones = $("#observacionesDescarga").val();
        
        console.log("📥 Datos del formulario:", {
            cantidadDescargar,
            observaciones
        });
        
        if(!cantidadDescargar || cantidadDescargar <= 0) {
            alert("Debe ingresar una cantidad válida");
            return;
        }
        
        // Usar el ID del stock guardado globalmente
        var idStockTransito = stockSeleccionado;
        
        console.log("📤 Enviando descarga AJAX:", {
            idStockTransito,
            cantidadDescargar,
            observaciones
        });
        
        // Enviar datos por AJAX
        var datos = new FormData();
        datos.append("descargarStockDirecto", true);
        datos.append("idStockTransito", idStockTransito);
        datos.append("cantidadDescargar", cantidadDescargar);
        datos.append("observaciones", observaciones);
        
        $.ajax({
            url: "ajax/stock-transito.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta) {
                console.log("📨 Respuesta descarga:", respuesta);
                
                if(respuesta.success) {
                    swal({
                        title: "¡Descarga exitosa!",
                        text: respuesta.message,
                        type: "success",
                        confirmButtonText: "Cerrar"
                    }).then(function() {
                        $("#modalDescargaDirecta").modal("hide");
                        // Recargar la tabla
                        if(typeof tablaStockTransito !== 'undefined') {
                            tablaStockTransito.ajax.reload();
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    swal("Error", respuesta.error || "No se pudo procesar la descarga", "error");
                }
            },
            error: function(xhr, status, error) {
                console.error("❌ Error AJAX descarga:", error);
                swal("Error", "Error al procesar la descarga", "error");
            }
        });
    });
}

// Llamar a configurarEventos cuando el documento esté listo
$(document).ready(function() {
    configurarEventos();
});
</script>