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
    
    // Llenar cronología como tabla horizontal
    var cronologiaHtml = `
        <div class="cronologia-tabla">
            <table class="table table-striped table-bordered" style="margin: 0; font-size: 12px;">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th style="text-align: center; font-weight: bold;">Carga</th>
                        <th style="text-align: center; font-weight: bold;">Despacho</th>
                        <th style="text-align: center; font-weight: bold;">Sucursal</th>
                        <th style="text-align: center; font-weight: bold;">Cantidad</th>
                        <th style="text-align: center; font-weight: bold;">Fecha</th>
                        <th style="text-align: center; font-weight: bold;">Productos</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    cronologia.forEach(function(entrada, index) {
        var numeroDespacho = entrada.despacho || 'N/A';
        cronologiaHtml += `
            <tr class="cronologia-fila" 
                data-despacho="${numeroDespacho}"
                style="cursor: pointer;">
                <td style="text-align: center; font-weight: bold;">#${entrada.orden_carga || (index + 1)}</td>
                <td style="text-align: center;">${entrada.despacho}</td>
                <td style="text-align: center;">${entrada.sucursal_origen}</td>
                <td style="text-align: center; font-weight: bold;">${entrada.cantidad_agregada || entrada.total_cantidad}</td>
                <td style="text-align: center;">${new Date(entrada.fecha).toLocaleString()}</td>
                <td style="text-align: center;">
                    <button class="btn btn-info btn-xs btnVerProductos" 
                            data-despacho="${numeroDespacho}"
                            style="padding: 2px 8px; font-size: 10px;">
                        <i class="fa fa-list"></i> Ver
                    </button>
                </td>
            </tr>
        `;
    });
    
    cronologiaHtml += `
                </tbody>
            </table>
        </div>
    `;
    $("#detalleCronologia").html(cronologiaHtml);
    
    // Event listener para botón de ver productos
    $(document).on("click", ".btnVerProductos", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var numeroDespacho = $(this).data('despacho');
        if (numeroDespacho && numeroDespacho !== 'N/A') {
            mostrarProductosDespacho(numeroDespacho);
        }
    });
    
    // Event listener para cerrar modal al hacer click fuera
    $(document).on("click", function(e) {
        if (!$(e.target).closest('#modalProductosDespacho, .btnVerProductos').length) {
            $('#modalProductosDespacho').modal('hide');
        }
    });
    
    // Mostrar modal
    $("#modalDetalleProducto").modal("show");
});

/*=============================================
MOSTRAR PRODUCTOS DEL DESPACHO EN MODAL GRANDE
=============================================*/
function mostrarProductosDespacho(numeroDespacho) {
    
    // Actualizar título del modal
    $('#modalProductosDespacho .modal-title').html(`<i class="fa fa-list"></i> Productos del Despacho ${numeroDespacho}`);
    
    // Mostrar loading
    $('#tablaProductosDespacho').html(`
        <div class="text-center" style="padding: 20px;">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <p style="margin-top: 10px;">Cargando productos del despacho...</p>
        </div>
    `);
    
    // Mostrar modal
    $('#modalProductosDespacho').modal('show');
    
    // Cargar productos
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
                    <table class="table table-striped table-bordered" style="margin: 0; font-size: 13px;">
                        <thead style="background-color: #f5f5f5;">
                            <tr>
                                <th style="text-align: center; font-weight: bold; width: 15%;">#</th>
                                <th style="text-align: center; font-weight: bold; width: 15%;">Código</th>
                                <th style="text-align: center; font-weight: bold; width: 40%;">Descripción del Producto</th>
                                <th style="text-align: center; font-weight: bold; width: 15%;">Cantidad</th>
                                <th style="text-align: center; font-weight: bold; width: 15%;">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                respuesta.productos.forEach(function(producto, index) {
                    productosHtml += `
                        <tr>
                            <td style="text-align: center; font-weight: bold;">${index + 1}</td>
                            <td style="text-align: center; font-weight: bold;">${producto.codigo || 'N/A'}</td>
                            <td>${producto.descripcion || 'N/A'}</td>
                            <td style="text-align: center; font-weight: bold;">${producto.cantidad || 'N/A'}</td>
                            <td style="text-align: center;">${producto.observaciones || '-'}</td>
                        </tr>
                    `;
                });
                
                productosHtml += `
                        </tbody>
                    </table>
                `;
                
                $('#tablaProductosDespacho').html(productosHtml);
                
            } else {
                $('#tablaProductosDespacho').html(`
                    <div class="alert alert-warning text-center" style="margin: 20px;">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>No se encontraron productos</strong>
                        <p>No hay productos registrados para este despacho.</p>
                    </div>
                `);
            }
            
        },
        error: function() {
            $('#tablaProductosDespacho').html(`
                <div class="alert alert-danger text-center" style="margin: 20px;">
                    <i class="fa fa-exclamation-circle"></i>
                    <strong>Error al cargar productos</strong>
                    <p>No se pudieron cargar los productos del despacho. Intente nuevamente.</p>
                </div>
            `);
        }
    });
}

// Event listener para botón de descarga desde detalle
$(document).on("click", "#btnDescargarDesdeDetalle", function(e) {
    e.preventDefault();
    $("#modalDetalleProducto").modal("hide");
    
    // Obtener datos del producto desde el modal de detalle
    var codigo = $("#detalleCodigoProducto").text();
    var descripcion = $("#detalleDescripcionProducto").text();
    var cantidad = $("#detalleCantidadTotal").text();
    var transportador = $("#detalleTransportador").text();
    
    console.log("🔍 DEBUG: Descarga desde detalle:", {
        codigo, descripcion, cantidad, transportador
    });
    
    // Verificar que el código no esté vacío
    if(!codigo || codigo === '-' || codigo.trim() === '') {
        swal({
            type: "error",
            title: "Error",
            text: "No se pudo obtener el código del producto",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    // Establecer datos globalmente y mostrar modal de descarga
    stockSeleccionado = {
        codigo: codigo,
        descripcion: descripcion,
        transportador: transportador,
        detalles: detalles
    };
    
    // Llenar modal de descarga
    $("#descargaCodigo").text(codigo);
    $("#descargaDescripcion").text(descripcion);
    $("#descargaTransportador").text(transportador);
    $("#descargaOrigen").text("Múltiples sucursales");
    $("#descargaDespacho").text("Varios despachos");
    $("#descargaCantidadDisponible").val(cantidad);
    
    // Configurar máximo en el input
    $("#cantidadDescargar").attr("max", cantidad);
    $("#cantidadDescargar").val("");
    $("#observacionesDescarga").val("");
    
    // Mostrar modal
    $("#modalDescargaDirecta").modal("show");
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
    
    // Guardar datos globalmente
    stockSeleccionado = {
        codigo: codigo,
        descripcion: descripcion,
        transportador: transportador,
        detalles: detalles
    };
    
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
    var codigoProducto = stockSeleccionado.codigo;
    
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
                // 🔗 REGISTRO DIRECTO - Registrar descarga en la tabla
                console.log("🔗 Registrando descarga directamente:", codigoProducto, cantidadDescargar);
                
                // Obtener datos del usuario actual
                var usuarioId = sessionStorage.getItem("id") || "0";
                var usuarioNombre = sessionStorage.getItem("nombre") || "Usuario";
                
                // Obtener datos de la sucursal
                var sucursalId = "1";
                var sucursalNombre = "Local Pruebas";
                
                // Obtener datos del producto y transportador desde stockSeleccionado
                var descripcionProducto = "";
                var transportadorNombre = "";
                var transportadorId = "0";
                var numeroDespacho = "";
                
                if (typeof stockSeleccionado === "object" && stockSeleccionado !== null) {
                    descripcionProducto = stockSeleccionado.descripcion || "";
                    transportadorNombre = stockSeleccionado.transportador || "";
                    
                    if (stockSeleccionado.detalles) {
                        var detalles = stockSeleccionado.detalles;
                        if (detalles.transportador_id) {
                            transportadorId = detalles.transportador_id;
                        }
                        if (detalles.numero_despacho) {
                            numeroDespacho = detalles.numero_despacho;
                        }
                    }
                }
                
                // Hacer petición AJAX para registrar la descarga
                $.ajax({
                    url: "ajax/registro-descargas-simple.ajax.php",
                    method: "POST",
                    data: {
                        accion: "registrar_descarga",
                        codigo_producto: codigoProducto,
                        descripcion_producto: descripcionProducto,
                        cantidad_descargada: cantidadDescargar,
                        usuario_id: usuarioId,
                        usuario_nombre: usuarioNombre,
                        sucursal_id: sucursalId,
                        sucursal_nombre: sucursalNombre,
                        transportador_id: transportadorId,
                        transportador_nombre: transportadorNombre,
                        numero_despacho: numeroDespacho,
                        observaciones: observaciones
                    },
                    dataType: "json",
                    success: function(respuestaRegistro) {
                        if(respuestaRegistro.success) {
                            console.log("✅ Descarga registrada en la tabla:", codigoProducto);
                        } else {
                            console.error("❌ Error al registrar descarga:", respuestaRegistro.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("❌ Error AJAX al registrar descarga:", error);
                    }
                });
                
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

/*=============================================
REGISTRAR DESCARGA SIMPLE
=============================================*/
function registrarDescargaSimple(codigoProducto, cantidad, observaciones) {
    // Obtener datos del usuario actual
    var usuarioId = $("#usuarioId").val() || 1;
    var usuarioNombre = $("#usuarioNombre").val() || "Usuario";
    var sucursalId = $("#sucursalId").val() || 1;
    var sucursalNombre = $("#sucursalNombre").val() || "Sucursal";
    
    // Obtener información del producto desde stockSeleccionado
    var descripcionProducto = "";
    var transportadorId = null;
    var transportadorNombre = null;
    var numeroDespacho = null;
    
    if (typeof stockSeleccionado === 'object' && stockSeleccionado !== null) {
        descripcionProducto = stockSeleccionado.descripcion || "";
        transportadorId = stockSeleccionado.transportador_id || null;
        transportadorNombre = stockSeleccionado.transportador_nombre || null;
        numeroDespacho = stockSeleccionado.numero_despacho_origen || null;
    }
    
    // Enviar registro por AJAX
    $.ajax({
        url: "ajax/registro-descargas-simple.ajax.php",
        method: "POST",
        data: {
            accion: "registrar_descarga",
            codigo_producto: codigoProducto,
            descripcion_producto: descripcionProducto,
            cantidad_descargada: cantidad,
            usuario_id: usuarioId,
            usuario_nombre: usuarioNombre,
            sucursal_id: sucursalId,
            sucursal_nombre: sucursalNombre,
            transportador_id: transportadorId,
            transportador_nombre: transportadorNombre,
            numero_despacho: numeroDespacho,
            observaciones: observaciones
        },
        dataType: "json",
        success: function(respuesta) {
            if(respuesta.success) {
                console.log("✅ Descarga registrada en el sistema");
            } else {
                console.error("❌ Error al registrar descarga:", respuesta.error);
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error AJAX al registrar descarga:", error);
        }
    });
}


/*=============================================
REGISTRAR DESCARGA DIRECTA
=============================================*/
function registrarDescargaDirecta(codigoProducto, cantidad, observaciones) {
    // Obtener datos del usuario actual desde la sesión
    var usuarioId = 1; // Valor por defecto
    var usuarioNombre = "Usuario";
    var sucursalId = 1;
    var sucursalNombre = "Sucursal";
    
    // Intentar obtener datos del DOM
    if ($("#usuarioId").length > 0) usuarioId = $("#usuarioId").val() || 1;
    if ($("#usuarioNombre").length > 0) usuarioNombre = $("#usuarioNombre").val() || "Usuario";
    if ($("#sucursalId").length > 0) sucursalId = $("#sucursalId").val() || 1;
    if ($("#sucursalNombre").length > 0) sucursalNombre = $("#sucursalNombre").val() || "Sucursal";
    
    // Obtener información del producto desde stockSeleccionado
    var descripcionProducto = "";
    var transportadorId = null;
    var transportadorNombre = null;
    var numeroDespacho = null;
    
    if (typeof stockSeleccionado === "object" && stockSeleccionado !== null) {
        descripcionProducto = stockSeleccionado.descripcion || "";
        transportadorNombre = stockSeleccionado.transportador || "";
        
        // Extraer transportador_id y numero_despacho de los detalles si están disponibles
        if (stockSeleccionado.detalles && Array.isArray(stockSeleccionado.detalles)) {
            // Buscar el primer detalle que tenga transportador_id
            for (var i = 0; i < stockSeleccionado.detalles.length; i++) {
                var detalle = stockSeleccionado.detalles[i];
                if (detalle.transportador_id) {
                    transportadorId = detalle.transportador_id;
                    break;
                }
            }
            
            // Buscar numero_despacho en los detalles
            for (var i = 0; i < stockSeleccionado.detalles.length; i++) {
                var detalle = stockSeleccionado.detalles[i];
                if (detalle.numero_despacho) {
                    numeroDespacho = detalle.numero_despacho;
                    break;
                }
            }
        }
    }
    
    // Enviar registro directo por AJAX
    $.ajax({
        url: "ajax/registro-descargas-simple.ajax.php",
        method: "POST",
        data: {
            accion: "registrar_descarga",
            codigo_producto: codigoProducto,
            descripcion_producto: descripcionProducto,
            cantidad_descargada: cantidad,
            usuario_id: usuarioId,
            usuario_nombre: usuarioNombre,
            sucursal_id: sucursalId,
            sucursal_nombre: sucursalNombre,
            transportador_id: transportadorId,
            transportador_nombre: transportadorNombre,
            numero_despacho: numeroDespacho,
            observaciones: observaciones
        },
        dataType: "json",
        success: function(respuesta) {
            if(respuesta.success) {
                console.log("✅ Descarga registrada en la tabla:", codigoProducto);
            } else {
                console.error("❌ Error al registrar descarga:", respuesta.error);
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error AJAX al registrar descarga:", error);
        }
    });
}