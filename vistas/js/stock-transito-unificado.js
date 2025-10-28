/*=============================================
STOCK EN TRÁNSITO - JAVASCRIPT UNIFICADO
=============================================*/

// Variable global para almacenar el stock seleccionado
var stockSeleccionado = null;
var timeoutBusqueda;

// Función para buscar productos
function buscarProductos(termino = "", transportadorId = null) {
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
} else {
$("#listaProductos").html("<div class='alert alert-warning'>No se encontraron productos</div>");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
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

    // Debug: Log de cronología original
    console.log("🔍 CRONOLOGÍA ORIGINAL:", cronologia);
    console.log("📊 Total elementos:", cronologia.length);
    
    // Filtrar elementos válidos de la cronología
    var cronologiaValida = cronologia.filter(function(entrada) {
        var esValida = entrada && 
               entrada.despacho && 
               entrada.sucursal_origen && 
               entrada.cantidad_agregada && 
               entrada.fecha &&
               entrada.despacho !== 'undefined' &&
               entrada.sucursal_origen !== 'undefined' &&
               entrada.cantidad_agregada !== 'undefined' &&
               entrada.fecha !== 'undefined';
        
        if (!esValida) {
            console.log("❌ Elemento inválido filtrado:", entrada);
        }
        
        return esValida;
    });
    
    console.log("✅ CRONOLOGÍA VÁLIDA:", cronologiaValida);
    console.log("📊 Elementos válidos:", cronologiaValida.length);

    cronologiaValida.forEach(function(entrada, index) {
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
var cantidadDescargar = $("#cantidadDescargar").val();
    var observaciones = $("#observacionesDescarga").val();
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
// Obtener datos del usuario actual via AJAX
                var usuarioId = "0";
                var usuarioNombre = "Usuario";

                // Obtener usuario actual (con fallback si no hay sesión)
                $.ajax({
                    url: "ajax/obtener-usuario-actual.ajax.php",
                    method: "GET",
                    dataType: "json",
                    async: false, // Síncrono para obtener datos antes de continuar
                    success: function(respuesta) {
                        if(respuesta.success) {
                            usuarioId = respuesta.usuario.id;
                            usuarioNombre = respuesta.usuario.nombre;
                        }
                    },
                    error: function() {
                        // Fallback: usar datos por defecto si falla
                        usuarioId = "999";
                        usuarioNombre = "Usuario Sistema";
}
                });

                // Obtener datos de la sucursal actual desde BD local
                var sucursalId = "1";
                var sucursalNombre = "Local Pruebas";

                // Obtener datos reales de la sucursal
                $.ajax({
                    url: "ajax/obtener-sucursal-actual.ajax.php",
                    method: "GET",
                    data: { accion: "obtener_sucursal_actual" },
                    dataType: "json",
                    async: false, // Síncrono para obtener datos antes de continuar
                    success: function(respuesta) {
                        if(respuesta.success) {
                            sucursalId = respuesta.sucursal.id;
                            sucursalNombre = respuesta.sucursal.nombre;
}
                    },
                    error: function() {
}
                });

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
} else {
}
                    },
                    error: function(xhr, status, error) {
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
REGISTRAR DESCARGA OPTIMIZADA - NUEVA VERSIÓN
=============================================*/
function registrarDescargaOptimizada(codigoProducto, cantidad, observaciones) {
// Datos por defecto que funcionan (basados en probar-registro-desde-sucursal.php)
    var datosRegistro = {
        accion: "registrar_descarga",
        codigo_producto: codigoProducto,
        descripcion_producto: "Producto desde JavaScript Optimizado",
        cantidad_descargada: cantidad,
        usuario_id: "999", // Usuario por defecto que funciona
        usuario_nombre: "Usuario Sistema", // Nombre por defecto que funciona
        sucursal_id: "1", // Sucursal 2 (ID: 1) que funciona
        sucursal_nombre: "Sucursal 2", // Nombre que funciona
        transportador_id: "0",
        transportador_nombre: "Transportador Sistema",
        numero_despacho: "",
        observaciones: observaciones || "Registro desde JavaScript - " + new Date().toLocaleString()
    };

    // Obtener datos reales de sucursal (si está disponible)
    $.ajax({
        url: "ajax/obtener-sucursal-actual-sin-sesion.ajax.php",
        method: "GET",
        data: { accion: "obtener_sucursal_actual" },
        dataType: "json",
        async: false, // Síncrono para obtener datos antes de continuar
        success: function(respuesta) {
            if(respuesta.success && respuesta.sucursal) {
                datosRegistro.sucursal_id = respuesta.sucursal.id;
                datosRegistro.sucursal_nombre = respuesta.sucursal.nombre;
} else {
}
        },
        error: function() {
}
    });

    // Obtener datos reales de usuario (si está disponible)
    $.ajax({
        url: "ajax/obtener-usuario-actual-sin-sesion.ajax.php",
        method: "GET",
        dataType: "json",
        async: false, // Síncrono para obtener datos antes de continuar
        success: function(respuesta) {
            if(respuesta.success && respuesta.usuario) {
                datosRegistro.usuario_id = respuesta.usuario.id;
                datosRegistro.usuario_nombre = respuesta.usuario.nombre;
} else {
}
        },
        error: function() {
}
    });
// Enviar registro por AJAX
    $.ajax({
        url: "ajax/registro-descargas-simple.ajax.php",
        method: "POST",
        data: datosRegistro,
        dataType: "json",
        success: function(respuesta) {
if(respuesta.success) {
// Mostrar mensaje de éxito
                swal({
                    type: "success",
                    title: "¡Éxito!",
                    text: "Descarga registrada correctamente",
                    showConfirmButton: false,
                    timer: 2000
                });

            } else {
// Mostrar mensaje de error
                swal({
                    type: "error",
                    title: "Error",
                    text: "No se pudo registrar la descarga: " + respuesta.error,
                    showConfirmButton: true
                });
            }
        },
        error: function(xhr, status, error) {
// Mostrar mensaje de error
            swal({
                type: "error",
                title: "Error de Conexión",
                text: "No se pudo conectar al servidor: " + error,
                showConfirmButton: true
            });
        }
    });
}

/*=============================================
REEMPLAZAR FUNCIONES EXISTENTES
=============================================*/
// Reemplazar función existente
function registrarDescargaSimple(codigoProducto, cantidad, observaciones) {
    registrarDescargaOptimizada(codigoProducto, cantidad, observaciones);
}

// Reemplazar función existente
function registrarDescargaDirecta(codigoProducto, cantidad, observaciones) {
    registrarDescargaOptimizada(codigoProducto, cantidad, observaciones);
}
