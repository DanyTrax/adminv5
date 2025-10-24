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
    
    // Llenar cronología con tooltips de productos del despacho
    var cronologiaHtml = "";
    cronologia.forEach(function(entrada, index) {
        // Crear tooltip con información del despacho y productos
        var numeroDespacho = entrada.despacho || 'N/A';
        var sucursal = entrada.sucursal_origen || 'N/A';
        var cantidad = entrada.cantidad_agregada || entrada.total_cantidad || 'N/A';
        var fecha = new Date(entrada.fecha).toLocaleString() || 'N/A';
        
        cronologiaHtml += `
            <div class="timeline-item" 
                 data-toggle="tooltip" 
                 data-placement="top" 
                 data-html="true"
                 data-despacho="${numeroDespacho}"
                 title="<div class='tooltip-productos-despacho'>
                            <h6 style='margin: 0 0 8px 0; color: #333; font-weight: bold;'>Productos del Despacho ${numeroDespacho}</h6>
                            <div id='productos-${numeroDespacho.replace(/[^a-zA-Z0-9]/g, '')}' style='max-height: 200px; overflow-y: auto;'>
                                <p style='margin: 0; color: #666; font-size: 10px; text-align: center;'>Cargando productos del despacho...</p>
                            </div>
                        </div>"
                 style="cursor: pointer;">
                <div class="timeline-marker bg-blue"></div>
                <div class="timeline-content">
                    <div class="timeline-info-table">
                        <table class="table table-condensed table-bordered" style="margin: 0; font-size: 11px;">
                            <tr>
                                <td style="background-color: #f5f5f5; font-weight: bold; width: 25%;">Carga:</td>
                                <td>#${entrada.orden_carga || (index + 1)}</td>
                            </tr>
                            <tr>
                                <td style="background-color: #f5f5f5; font-weight: bold;">Despacho:</td>
                                <td>${entrada.despacho}</td>
                            </tr>
                            <tr>
                                <td style="background-color: #f5f5f5; font-weight: bold;">Sucursal:</td>
                                <td>${entrada.sucursal_origen}</td>
                            </tr>
                            <tr>
                                <td style="background-color: #f5f5f5; font-weight: bold;">Cantidad:</td>
                                <td>${entrada.cantidad_agregada || entrada.total_cantidad}</td>
                            </tr>
                            <tr>
                                <td style="background-color: #f5f5f5; font-weight: bold;">Fecha:</td>
                                <td>${new Date(entrada.fecha).toLocaleString()}</td>
                            </tr>
                        </table>
                    </div>
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
        
        // Cargar productos del despacho cuando se muestre el tooltip
        $('[data-toggle="tooltip"]').on('show.bs.tooltip', function() {
            var numeroDespacho = $(this).data('despacho');
            if (numeroDespacho && numeroDespacho !== 'N/A') {
                cargarProductosDespachoTooltip(numeroDespacho);
            }
        });
    }, 100);
    
    // Mostrar modal
    $("#modalDetalleProducto").modal("show");
});

/*=============================================
CARGAR PRODUCTOS DEL DESPACHO PARA TOOLTIP
=============================================*/
function cargarProductosDespachoTooltip(numeroDespacho) {
    
    var containerId = 'productos-' + numeroDespacho.replace(/[^a-zA-Z0-9]/g, '');
    
    // Verificar si ya se cargaron los productos
    if ($('#' + containerId).data('loaded')) {
        return;
    }
    
    $.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: {
            accion: "obtener_productos_despacho",
            numero_despacho: numeroDespacho
        },
        dataType: "json",
        success: function(respuesta) {
            
            if (respuesta.success && respuesta.productos) {
                
                var productosHtml = `
                    <h6 style='margin: 0 0 8px 0; color: #333; font-weight: bold;'>Productos del Despacho</h6>
                    <table style='margin: 0; font-size: 10px; border-collapse: collapse; width: 100%;'>
                        <thead>
                            <tr style='background-color: #f5f5f5;'>
                                <th style='padding: 4px 6px; border: 1px solid #ddd; color: #333; font-weight: bold;'>Código</th>
                                <th style='padding: 4px 6px; border: 1px solid #ddd; color: #333; font-weight: bold;'>Descripción</th>
                                <th style='padding: 4px 6px; border: 1px solid #ddd; color: #333; font-weight: bold;'>Cantidad</th>
                                <th style='padding: 4px 6px; border: 1px solid #ddd; color: #333; font-weight: bold;'>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                respuesta.productos.forEach(function(producto, index) {
                    productosHtml += `
                        <tr style='background-color: ${index % 2 === 0 ? '#f9f9f9' : 'white'};'>
                            <td style='padding: 3px 6px; border: 1px solid #ddd; color: #333; font-weight: bold;'>${producto.codigo || 'N/A'}</td>
                            <td style='padding: 3px 6px; border: 1px solid #ddd; color: #333;'>${producto.descripcion || 'N/A'}</td>
                            <td style='padding: 3px 6px; border: 1px solid #ddd; color: #333; text-align: center;'>${producto.cantidad || 'N/A'}</td>
                            <td style='padding: 3px 6px; border: 1px solid #ddd; color: #333;'>${producto.observaciones || '-'}</td>
                        </tr>
                    `;
                });
                
                productosHtml += `
                        </tbody>
                    </table>
                `;
                
                $('#' + containerId).html(productosHtml).data('loaded', true);
                
            } else {
                $('#' + containerId).html('<p style="margin: 0; color: #666; font-size: 10px; text-align: center;">No se encontraron productos</p>').data('loaded', true);
            }
            
        },
        error: function() {
            $('#' + containerId).html('<p style="margin: 0; color: #d32f2f; font-size: 10px; text-align: center;">Error al cargar productos</p>').data('loaded', true);
        }
    });
}

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
