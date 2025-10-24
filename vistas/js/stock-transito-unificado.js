/*=============================================
STOCK EN TRÁNSITO - JAVASCRIPT UNIFICADO
=============================================*/

// Variable global para almacenar el stock seleccionado
var stockSeleccionado = null;
var timeoutBusqueda;

// Función para buscar productos
function buscarProductos(termino = "", transportadorId = null) {
    console.log("🔍 Buscando productos:", { termino, transportadorId });
    
    $.ajax({
        url: "ajax/buscar-stock-transito.ajax.php",
        method: "POST",
        data: {
            buscarProductos: true,
            termino: termino,
            transportador: transportadorId
        },
        dataType: "json",
        success: function(respuesta) {
            if(respuesta.success) {
                $("#listaProductos").html(respuesta.html);
                console.log("✅ Productos cargados correctamente");
            } else {
                console.error("❌ Error al buscar productos:", respuesta.error);
                $("#listaProductos").html("<div class='alert alert-warning'>No se encontraron productos</div>");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("❌ Error AJAX al buscar productos:", textStatus, errorThrown);
            $("#listaProductos").html("<div class='alert alert-danger'>Error al cargar productos</div>");
        }
    });
}

// Función para filtrar por transportador
function filtrarPorTransportador() {
    var transportadorId = document.getElementById('filtroTransportador').value;
    var terminoBusqueda = document.getElementById('buscarProducto').value;
    buscarProductos(terminoBusqueda, transportadorId);
}

// Función para limpiar búsqueda
function limpiarBusqueda() {
    document.getElementById('buscarProducto').value = '';
    var transportadorId = document.getElementById('filtroTransportador').value;
    buscarProductos('', transportadorId);
}

// Función para limpiar filtros
function limpiarFiltros() {
    document.getElementById('filtroTransportador').value = '';
    document.getElementById('buscarProducto').value = '';
    buscarProductos('', '');
}

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    console.log("🚀 Inicializando stock en tránsito");
    
    // Búsqueda en tiempo real con debounce
    $("#buscarProducto").on("input", function() {
        var termino = $(this).val();
        var transportadorId = $("#filtroTransportador").val();
        
        // Limpiar timeout anterior
        if(timeoutBusqueda) {
            clearTimeout(timeoutBusqueda);
        }
        
        // Establecer nuevo timeout (500ms de delay)
        timeoutBusqueda = setTimeout(function() {
            buscarProductos(termino, transportadorId);
        }, 500);
    });
    
    // Event listener para cambio de transportador
    $("#filtroTransportador").on("change", function() {
        var transportadorId = $(this).val();
        var termino = $("#buscarProducto").val();
        buscarProductos(termino, transportadorId);
    });
    
    // Cargar productos iniciales
    buscarProductos();
});

// Event listener para botón de detalle de stock-transito
$(document).on("click", ".btnVerDetalleStockTransito", function(e) {
    e.preventDefault();
    
    var codigo = $(this).data("codigo");
    var descripcion = $(this).data("descripcion");
    var detalles = $(this).data("detalles");
    var cronologia = $(this).data("cronologia");
    var cantidadTotal = $(this).data("cantidad-total");
    var transportador = $(this).data("transportador");
    
    console.log("🔍 DEBUG: Mostrando detalle del producto:", {
        codigo, descripcion, cantidadTotal, transportador, detalles, cronologia
    });
    
    // Llenar información básica
    $("#detalleCodigoProducto").text(codigo);
    $("#detalleDescripcionProducto").text(descripcion);
    $("#detalleTransportador").text(transportador);
    $("#detalleCantidadTotal").text(cantidadTotal);
    
    // Llenar tabla de despachos
    var tablaHtml = "";
    detalles.forEach(function(detalle, index) {
        tablaHtml += `
            <tr>
                <td><span class="badge bg-blue">${detalle.orden_carga || (index + 1)}</span></td>
                <td><span class="label label-default">${detalle.numero_despacho}</span></td>
                <td><i class="fa fa-building"></i> ${detalle.sucursal_origen}</td>
                <td><span class="badge bg-green">${detalle.cantidad}</span></td>
                <td>${new Date(detalle.fecha_carga).toLocaleString()}</td>
            </tr>
        `;
    });
    $("#detalleTablaDespachos").html(tablaHtml);
    
    // Llenar cronología con tooltips
    var cronologiaHtml = "";
    cronologia.forEach(function(entrada, index) {
        // Crear datos para el tooltip
        var tooltipData = {
            despacho: entrada.despacho || 'N/A',
            sucursal: entrada.sucursal_origen || 'N/A',
            cantidad: entrada.cantidad_agregada || entrada.total_cantidad || 'N/A',
            fecha: new Date(entrada.fecha).toLocaleString() || 'N/A'
        };
        
        cronologiaHtml += `
            <div class="timeline-item" 
                 data-toggle="tooltip" 
                 data-placement="top" 
                 data-html="true"
                 title="<div class='tooltip-despacho'>
                            <table class='table table-condensed table-bordered' style='margin:0; font-size:12px;'>
                                <tr><td><strong>Despacho:</strong></td><td>${tooltipData.despacho}</td></tr>
                                <tr><td><strong>Sucursal:</strong></td><td>${tooltipData.sucursal}</td></tr>
                                <tr><td><strong>Cantidad:</strong></td><td>${tooltipData.cantidad}</td></tr>
                                <tr><td><strong>Fecha:</strong></td><td>${tooltipData.fecha}</td></tr>
                            </table>
                        </div>"
                 style="cursor: pointer;">
                <div class="timeline-marker bg-blue"></div>
                <div class="timeline-content">
                    <h6 class="timeline-title">Carga #${entrada.orden_carga || (index + 1)}</h6>
                    <p><strong>Despacho:</strong> ${entrada.despacho}</p>
                    <p><strong>Sucursal:</strong> ${entrada.sucursal_origen}</p>
                    <p><strong>Cantidad:</strong> ${entrada.cantidad_agregada || entrada.total_cantidad}</p>
                    <p><strong>Fecha:</strong> ${new Date(entrada.fecha).toLocaleString()}</p>
                </div>
            </div>
        `;
    });
    $("#detalleCronologia").html(cronologiaHtml);
    
    // Inicializar tooltips después de agregar el HTML
    setTimeout(function() {
        $('[data-toggle="tooltip"]').tooltip({
            html: true,
            container: 'body',
            delay: { "show": 300, "hide": 100 }
        });
    }, 100);
    
    // Mostrar modal
    $("#modalDetalleProducto").modal("show");
});

// Event listener para botón de descarga desde detalle
$(document).on("click", "#btnDescargarDesdeDetalle", function(e) {
    e.preventDefault();
    $("#modalDetalleProducto").modal("hide");
    
    // Buscar el botón de descarga correspondiente y hacer clic
    var codigo = $("#detalleCodigoProducto").text();
    $(".btnDescargaDirecta[data-codigo='" + codigo + "']").click();
});

// Event listener para botones de descarga
$(document).on("click", ".btnDescargaDirecta", function(e) {
    e.preventDefault();
    
    var codigo = $(this).data('codigo');
    var descripcion = $(this).data('descripcion');
    var cantidad = $(this).data('cantidad');
    var transportador = $(this).data('transportador');
    var detalles = $(this).data('detalles');
    
    console.log("🔍 DEBUG: Iniciando descarga consolidada:", {
        codigo, descripcion, cantidad, transportador, detalles
    });
    
    // Guardar código globalmente
    stockSeleccionado = codigo;
    
    // Llenar modal
    $("#descargaCodigo").text(codigo);
    $("#descargaDescripcion").text(descripcion);
    $("#descargaTransportador").text(transportador);
    $("#descargaOrigen").text("Múltiples sucursales");
    $("#descargaDespacho").text(detalles ? detalles.length + " despachos" : "N/A");
    $("#descargaCantidadDisponible").val(cantidad);
    
    // Configurar máximo en el input
    $("#cantidadDescargar").attr("max", cantidad);
    $("#cantidadDescargar").val("");
    $("#observacionesDescarga").val("");
    
    // Mostrar modal
    $("#modalDescargaDirecta").modal("show");
});

// Event listener para el formulario de descarga
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
        swal({
            type: "error",
            title: "Cantidad inválida",
            text: "Debe ingresar una cantidad mayor a 0",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    // Usar el código de producto guardado globalmente
    var codigoProducto = stockSeleccionado;
    
    console.log("📤 Enviando descarga AJAX:", {
        codigoProducto,
        cantidadDescargar,
        observaciones
    });
    
    // Enviar datos por AJAX
    var datos = new FormData();
    datos.append("descargarStockDirecto", true);
    datos.append("codigoProducto", codigoProducto);
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
            if(respuesta.success) {
                swal({
                    type: "success",
                    title: "¡Descarga Exitosa!",
                    text: respuesta.message,
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result) {
                    if(result.value) {
                        $("#modalDescargaDirecta").modal("hide");
                        // Recargar la página o actualizar la tabla
                        location.reload(); 
                    }
                });
            } else {
                swal({
                    type: "error",
                    title: "Error en la descarga",
                    text: respuesta.error,
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error AJAX:", textStatus, errorThrown, jqXHR.responseText);
            swal({
                type: "error",
                title: "Error de conexión",
                text: "No se pudo procesar la descarga. Intente nuevamente.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        }
    });
});

// Event listener para botón de eliminar stock
$(document).on("click", ".btnEliminarStock", function(e) {
    e.preventDefault();
    
    var codigo = $(this).data('codigo');
    var descripcion = $(this).data('descripcion');
    var cantidad = $(this).data('cantidad');
    var transportador = $(this).data('transportador');
    
    console.log("🗑️ DEBUG: Iniciando eliminación de stock:", {
        codigo, descripcion, cantidad, transportador
    });
    
    // Llenar modal de eliminación
    $("#eliminarCodigo").text(codigo);
    $("#eliminarDescripcion").text(descripcion);
    $("#eliminarTransportador").text(transportador);
    $("#eliminarCantidad").text(cantidad);
    $("#eliminarCodigoProducto").val(codigo);
    $("#motivoEliminacion").val("");
    
    // Mostrar modal
    $("#modalEliminarStock").modal("show");
});

// Event listener para confirmar eliminación
$(document).on("click", "#btnConfirmarEliminarStock", function(e) {
    e.preventDefault();
    
    var codigoProducto = $("#eliminarCodigoProducto").val();
    var motivo = $("#motivoEliminacion").val();
    
    if(!motivo.trim()) {
        swal({
            type: "error",
            title: "Motivo requerido",
            text: "Debe escribir un motivo para la eliminación",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    console.log("🗑️ DEBUG: Confirmando eliminación:", {
        codigoProducto, motivo
    });
    
    // Enviar datos por AJAX
    var datos = new FormData();
    datos.append("eliminarStockTransito", true);
    datos.append("codigoProducto", codigoProducto);
    datos.append("motivoEliminacion", motivo);
    
    $.ajax({
        url: "ajax/stock-transito.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            if(respuesta.success) {
                swal({
                    type: "success",
                    title: "¡Stock Eliminado!",
                    text: respuesta.message,
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result) {
                    if(result.value) {
                        $("#modalEliminarStock").modal("hide");
                        // Recargar la página para actualizar la lista
                        location.reload(); 
                    }
                });
            } else {
                swal({
                    type: "error",
                    title: "Error al eliminar",
                    text: respuesta.error,
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error AJAX:", textStatus, errorThrown, jqXHR.responseText);
            swal({
                type: "error",
                title: "Error de conexión",
                text: "No se pudo procesar la eliminación. Intente nuevamente.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        }
    });
});
