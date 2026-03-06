/*=============================================
VARIABLES GLOBALES
=============================================*/
var productosSeleccionados = [];

/*=============================================
CARGAR CUANDO EL DOM ESTÁ LISTO
=============================================*/
$(document).ready(function() {

    // Inicializar DataTable principal
    if($('.tablaSolicitudesStock').length > 0) {
        $('.tablaSolicitudesStock').DataTable({
            "ajax": "ajax/datatable-solicitudes-stock.ajax.php",
            "deferRender": true,
            "retrieve": true,
            "processing": true,
            "language": configuracionIdioma,
            "order": [[ 7, "desc" ]], // Ordenar por fecha (columna 7) descendente
            "columnDefs": [
                { "orderable": false, "targets": [0, 8] }, // Deshabilitar ordenamiento en columna # y Acciones
                { "type": "date", "targets": 7 } // Especificar que la columna 7 es fecha
            ],
            "drawCallback": function() {
                // Si hay ?ver=ID en la URL, abrir modal de esa solicitud (ej: desde despacho)
                if(window._solicitudVerAbierto) return;
                var urlParams = new URLSearchParams(window.location.search);
                var verId = urlParams.get('ver');
                if(verId) {
                    window._solicitudVerAbierto = true;
                    urlParams.delete('ver');
                    window.history.replaceState({}, '', window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : ''));
                    $.ajax({
                        url: 'ajax/solicitudes-stock.ajax.php',
                        type: 'POST',
                        data: { accion: 'ver_detalle', id_solicitud: verId },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) mostrarModalDetalleSolicitud(response.data);
                        }
                    });
                }
            }
        });
    }

    // Inicializar DataTable de catálogo de productos
    if($('.tablaProductosCatalogo').length > 0) {
        $('.tablaProductosCatalogo').DataTable({
            "ajax": "ajax/datatable-productos-catalogo.ajax.php",
            "deferRender": true,
            "retrieve": true,
            "processing": true,
            "language": configuracionIdioma,
            "columnDefs": [
                { "orderable": false, "targets": [0, 4] },
                { "width": "80px", "targets": 0 },
                { "width": "100px", "targets": 3 },
                { "width": "120px", "targets": 4 }
            ]
        });
    }

    // Eventos de tipo de solicitud
    $('input[name="tipo_solicitud"]').change(function() {
        if($(this).val() === 'remision') {
            $('.campoRemision').slideDown();
        } else {
            $('.campoRemision').slideUp();
            limpiarRemisionSeleccionada();
        }
    });

    // Búsqueda de remisiones
    $('#buscarRemision').on('input', function() {
        var busqueda = $(this).val().trim();
        if(busqueda.length >= 2) {
            buscarRemisiones(busqueda);
        } else {
            $('#resultadosRemision').hide();
        }
    });

    // ✅ EVITAR ENVÍO AUTOMÁTICO DEL FORMULARIO
    $('.formularioSolicitudStock').submit(function(e) {
        e.preventDefault(); // ✅ SIEMPRE PREVENIR ENVÍO AUTOMÁTICO

        if(productosSeleccionados.length === 0) {
            mostrarAlerta('warning', 'Debe seleccionar al menos un producto');
            return false;
        }

        // ✅ CONFIRMAR ANTES DE CREAR SOLICITUD
        swal({
            title: '¿Crear esta solicitud?',
            text: "Se creará la solicitud con " + productosSeleccionados.length + " productos",
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, crear solicitud',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                crearSolicitudAjax();
            }
        });

        return false;
    });

    // Confirmar agregar producto
    $('#confirmarAgregarProducto').click(function() {
        agregarProductoALista();
    });

    // Limpiar modal al cerrarlo
    $('#modalSolicitarStock').on('hidden.bs.modal', function() {
        limpiarFormularioSolicitud();
    });

    $('#modalCantidadProducto').on('hidden.bs.modal', function() {
        limpiarModalCantidad();
    });
});

/*=============================================
CREAR SOLICITUD VIA AJAX (SIN RECARGAR PÁGINA)
=============================================*/
function crearSolicitudAjax() {

    // Actualizar campo hidden con productos
    $('#productosJsonInput').val(JSON.stringify(productosSeleccionados));

    // Crear FormData
    var formData = new FormData($('.formularioSolicitudStock')[0]);

    // Mostrar loading
    var loadingAlert = mostrarAlerta('info', 'Creando solicitud, por favor espere...');

    $.ajax({
        url: 'index.php?ruta=solicitudes-stock',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // ✅ CERRAR MODAL Y LIMPIAR FORMULARIO
            $('#modalSolicitarStock').modal('hide');
            limpiarFormularioSolicitud();

            // ✅ RECARGAR TABLA SIN RECARGAR PÁGINA
            if($('.tablaSolicitudesStock').length > 0) {
                $('.tablaSolicitudesStock').DataTable().ajax.reload();
            }

            mostrarAlerta('success', '¡Solicitud creada correctamente!');
        },
        error: function(xhr, status, error) {
mostrarAlerta('error', 'Error al crear la solicitud. Intente nuevamente.');
        }
    });
}

/*=============================================
CONFIGURACIÓN DE IDIOMA PARA DATATABLES
=============================================*/
var configuracionIdioma = {
    "sProcessing": "Procesando...",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sZeroRecords": "No se encontraron resultados",
    "sEmptyTable": "Ningún dato disponible en esta tabla",
    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
    "sSearch": "Buscar:",
    "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
    }
};

/*=============================================
AGREGAR PRODUCTO DESDE CATÁLOGO
=============================================*/
$(document).on('click', '.btnAgregarProducto', function() {

    var idProducto = $(this).attr('idProducto');
    var codigoProducto = $(this).attr('codigoProducto');
    var descripcionProducto = $(this).attr('descripcionProducto');

    // Abrir modal para cantidad
    $('#nombreProductoModal').text(descripcionProducto);
    $('#cantidadProductoModal').val(1).removeAttr('max');
    $('#observacionProductoModal').val('');

    // Guardar datos temporales
    $('#modalCantidadProducto').data('producto', {
        id: idProducto,
        codigo: codigoProducto,
        descripcion: descripcionProducto
    });

    $('#modalCantidadProducto').modal('show');
});

/*=============================================
AGREGAR PRODUCTO A LA LISTA
=============================================*/
function agregarProductoALista() {

    var producto = $('#modalCantidadProducto').data('producto');
    var cantidad = parseInt($('#cantidadProductoModal').val());
    var observacion = $('#observacionProductoModal').val().trim();

    // ✅ VALIDACIONES BÁSICAS
    if(isNaN(cantidad) || cantidad <= 0) {
        mostrarAlerta('error', 'La cantidad debe ser un número mayor a 0');
        $('#cantidadProductoModal').focus();
        return;
    }

    if(cantidad > 9999) {
        mostrarAlerta('error', 'La cantidad máxima es 9,999');
        $('#cantidadProductoModal').val(9999).focus();
        return;
    }

    // ✅ VERIFICAR SI YA ESTÁ EN LA LISTA
    var yaSeleccionado = productosSeleccionados.find(p => p.codigo === producto.codigo);
    if(yaSeleccionado) {
        mostrarAlerta('warning', 'Este producto ya está en la lista. Si desea cambiar la cantidad, elimínelo primero.');
        return;
    }

    // ✅ AGREGAR PRODUCTO
    var nuevoProducto = {
        id: producto.id,
        codigo: producto.codigo,
        descripcion: producto.descripcion,
        cantidad: cantidad,
        observacion: observacion
    };

    productosSeleccionados.push(nuevoProducto);

    // Actualizar interfaz
    actualizarListaProductosSeleccionados();
    actualizarContadorProductos();
    habilitarBotonCrear();

    // Cerrar modal
    $('#modalCantidadProducto').modal('hide');

    mostrarAlerta('success', 'Producto agregado: ' + producto.codigo + ' (Cantidad: ' + cantidad + ')');
}

/*=============================================
BUSCAR REMISIONES (SOLO PARA REFERENCIA)
=============================================*/
function buscarRemisiones(busqueda) {

    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: {
            accion: 'buscar_ventas',
            busqueda: busqueda
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                mostrarResultadosRemision(response.data);
            } else {
                $('#resultadosRemision').hide();
                mostrarAlerta('warning', response.message);
            }
        },
        error: function(xhr, status, error) {
            $('#resultadosRemision').hide();
            mostrarAlerta('error', 'Error al buscar remisiones');
        }
    });
}

/*=============================================
MOSTRAR RESULTADOS DE REMISIÓN
=============================================*/
function mostrarResultadosRemision(ventas) {

    var html = '';

    if(ventas.length === 0) {
        html = '<div class="list-group-item text-center text-muted">' +
               '<i class="fa fa-search"></i> No se encontraron remisiones' +
               '</div>';
    } else {
        ventas.forEach(function(venta) {
            var cliente = venta.nombre_cliente || 'Cliente no especificado';
            var fecha = venta.fecha ? new Date(venta.fecha).toLocaleDateString() : 'Sin fecha';

            html += '<a href="#" class="list-group-item seleccionar-remision" ' +
                   'data-codigo="' + venta.codigo + '" ' +
                   'data-cliente="' + cliente + '">' +
                   '<strong>Remisión: ' + venta.codigo + '</strong><br>' +
                   '<small>Cliente: ' + cliente + ' | Total: $' +

                   parseFloat(venta.total || 0).toLocaleString() + ' | Fecha: ' + fecha + '</small>' +
                   '</a>';
        });
    }

    $('#resultadosRemision').html(html).show();
}

/*=============================================
SELECCIONAR REMISIÓN - SOLO INFORMATIVO
=============================================*/
$(document).on('click', '.seleccionar-remision', function(e) {
    e.preventDefault();

    var codigo = $(this).data('codigo');
    var cliente = $(this).data('cliente');

    // ✅ SOLO ACTUALIZAR CAMPOS INFORMATIVOS
    $('#buscarRemision').val('Remisión: ' + codigo + ' - ' + cliente);
    $('#codigoRemisionSeleccionada').val(codigo);
    $('#nombreClienteRemision').val(cliente);

    // Ocultar resultados
    $('#resultadosRemision').hide();

    // ✅ MENSAJE INFORMATIVO - NO CARGAR PRODUCTOS
    mostrarAlerta('info', 'Remisión seleccionada como referencia. Agregue manualmente los productos que necesita solicitar.');
});

/*=============================================
ACTUALIZAR LISTA DE PRODUCTOS SELECCIONADOS
=============================================*/
function actualizarListaProductosSeleccionados() {

    var html = '';

    if(productosSeleccionados.length === 0) {
        html = '<tr id="sinProductos">' +
               '<td colspan="3" class="text-center text-muted">' +
               '<i class="fa fa-info-circle"></i> No hay productos seleccionados' +
               '</td></tr>';
    } else {
        productosSeleccionados.forEach(function(producto, index) {
            html += '<tr>' +
                   '<td>' +
                   '<strong>' + producto.codigo + '</strong><br>' +
                   '<small>' + producto.descripcion + '</small>' +
                   (producto.observacion ? '<br><em class="text-info">' + producto.observacion + '</em>' : '') +
                   '</td>' +
                   '<td class="text-center">' +
                   '<span class="badge bg-blue">' + producto.cantidad + '</span>' +
                   '</td>' +
                   '<td class="text-center">' +
                   '<button class="btn btn-danger btn-xs" onclick="eliminarProducto(' + index + ')" title="Eliminar">' +
                   '<i class="fa fa-trash"></i>' +
                   '</button>' +
                   '</td>' +
                   '</tr>';
        });
    }

    $('#productosSeleccionados').html(html);
}

/*=============================================
ELIMINAR PRODUCTO DE LA LISTA
=============================================*/
function eliminarProducto(index) {

    productosSeleccionados.splice(index, 1);

    actualizarListaProductosSeleccionados();
    actualizarContadorProductos();
    habilitarBotonCrear();

    mostrarAlerta('info', 'Producto eliminado de la solicitud');
}

/*=============================================
ACTUALIZAR CONTADOR DE PRODUCTOS
=============================================*/
function actualizarContadorProductos() {
    $('#contadorProductos').text(productosSeleccionados.length);
}

/*=============================================
HABILITAR/DESHABILITAR BOTÓN CREAR
=============================================*/
function habilitarBotonCrear() {
    if(productosSeleccionados.length > 0) {
        $('#btnCrearSolicitud').prop('disabled', false);
    } else {
        $('#btnCrearSolicitud').prop('disabled', true);
    }
}

/*=============================================
LIMPIAR REMISIÓN SELECCIONADA
=============================================*/
function limpiarRemisionSeleccionada() {
    $('#buscarRemision').val('');
    $('#codigoRemisionSeleccionada').val('');
    $('#nombreClienteRemision').val('');
    $('#resultadosRemision').hide();
}

/*=============================================
LIMPIAR FORMULARIO DE SOLICITUD
=============================================*/
function limpiarFormularioSolicitud() {
    productosSeleccionados = [];
    actualizarListaProductosSeleccionados();
    actualizarContadorProductos();
    habilitarBotonCrear();

    $('#detalleAdicional').val('');
    $('input[name="tipo_solicitud"]').prop('checked', false);
    $('.campoRemision').hide();
    limpiarRemisionSeleccionada();
}

/*=============================================
LIMPIAR MODAL CANTIDAD
=============================================*/
function limpiarModalCantidad() {
    $('#nombreProductoModal').text('');
    $('#cantidadProductoModal').val(1);
    $('#observacionProductoModal').val('');
}

/*=============================================
MOSTRAR ALERTA
=============================================*/
function mostrarAlerta(tipo, mensaje) {
    var icono = 'fa-info-circle';
    var clase = 'alert-info';

    switch(tipo) {
        case 'success':
            icono = 'fa-check-circle';
            clase = 'alert-success';
            break;
        case 'warning':
            icono = 'fa-warning';
            clase = 'alert-warning';
            break;
        case 'error':
            icono = 'fa-times-circle';
            clase = 'alert-danger';
            break;
    }

    var alerta = '<div class="alert ' + clase + ' alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '<i class="fa ' + icono + '"></i> ' + mensaje +
                '</div>';

    // Remover alertas existentes
    $('.alert').remove();

    // Agregar nueva alerta al contenedor principal
    if($('.content-wrapper').length > 0) {
        $('.content-wrapper').prepend(alerta);
    } else {
        $('body').prepend(alerta);
    }

    // Auto-remover después de 5 segundos
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

// ✅ VALIDACIONES DE INPUT
$(document).on('input', '#cantidadProductoModal', function() {
    var valor = $(this).val().replace(/[^0-9]/g, '');
    $(this).val(valor);

    if(parseInt(valor) > 9999) {
        $(this).val(9999);
    }
});

// ELIMINADO: Event listener de keydown removido
// Se deja el comportamiento básico por defecto del navegador
// El campo puede usar atributos HTML como type="number" o pattern para validación

/*=============================================
CREAR DESPACHO DESDE SOLICITUD
=============================================*/
$(document).on('click', '.btnCrearDespachoDesdeSolicitud', function() {

    var idSolicitud = $(this).attr('idSolicitud');
    var numeroSolicitud = $(this).attr('numeroSolicitud');
// Mostrar loading
    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    // Obtener detalles de la solicitud
    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: {
            accion: 'ver_detalle',
            id_solicitud: idSolicitud
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                var solicitud = response.data;
                if(solicitud.estado === 'finalizado') {
                    swal({
                        title: 'Solicitud finalizada',
                        text: 'Esta solicitud ya fue completada. No se puede crear otro despacho.',
                        type: 'warning',
                        confirmButtonText: 'Cerrar'
                    });
                    return;
                }
                var productos = JSON.parse(solicitud.productos_solicitados || '[]');
                var url = 'crear-despacho?desde_solicitud=1&id_solicitud=' + idSolicitud + '&numero_solicitud=' + encodeURIComponent(numeroSolicitud);
                try {
                    mostrarModalSeleccionProductos(solicitud, productos, url);
                } catch(error) {}

            } else {
                swal({
                    title: 'Error',
                    text: response.message || 'No se pudieron obtener los detalles de la solicitud',
                    type: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        },
        error: function() {
            swal({
                title: 'Error',
                text: 'Error de conexión al obtener detalles de la solicitud',
                type: 'error',
                confirmButtonText: 'Cerrar'
            });
        },
        complete: function() {
            // Restaurar botón
            $('.btnCrearDespachoDesdeSolicitud[idSolicitud="' + idSolicitud + '"]')
                .prop('disabled', false)
                .html('<i class="fa fa-truck"></i>');
        }
    });
});
/*=============================================
MOSTRAR STOCK POR SUCURSALES
=============================================*/
function mostrarStockPorSucursales(idSolicitud, numeroSolicitud, productos) {

    // Mostrar loading
    swal({
        title: "Consultando stock disponible...",
        text: "Buscando en todas las sucursales",
        type: "info",
        showConfirmButton: false,
        allowOutsideClick: false
    });

    // Obtener stock de todas las sucursales
    $.ajax({
        url: 'ajax/stock-disponible-sucursales.ajax.php',
        type: 'POST',
        data: {
            accion: 'consultar_stock_sucursales',
            productos: JSON.stringify(productos)
        },
        dataType: 'json',
        success: function(response) {
if(response.success) {
mostrarModalSeleccionStock(response.data, idSolicitud, numeroSolicitud);
            } else {
swal({
                    title: 'Error',
                    text: response.message || 'No se pudo consultar el stock disponible',
                    type: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        },
        error: function(xhr, status, error) {
swal({
                title: 'Error',
                text: 'Error de conexión al consultar stock disponible: ' + error,
                type: 'error',
                confirmButtonText: 'Cerrar'
            });
        }
    });
}

/*=============================================
MOSTRAR MODAL DE SELECCIÓN DE STOCK
=============================================*/
function mostrarModalSeleccionStock(stockData, idSolicitud, numeroSolicitud) {
// Verificar que stockData sea válido
    if (!stockData || !Array.isArray(stockData) || stockData.length === 0) {
swal({
            title: 'Error',
            text: 'No se recibieron datos de stock válidos',
            type: 'error',
            confirmButtonText: 'Cerrar'
        });
        return;
    }

    // Obtener todas las sucursales únicas de todos los productos
    var sucursalesUnicas = [];
    var sucursalesMap = {};

    stockData.forEach(function(producto) {
        if (producto.sucursales && Array.isArray(producto.sucursales)) {
            producto.sucursales.forEach(function(sucursal) {
                if (!sucursalesMap[sucursal.id]) {
                    sucursalesMap[sucursal.id] = sucursal;
                    sucursalesUnicas.push(sucursal);
                }
            });
        }
    });
var html = '<div class="stock-seleccion-container">';
    html += '<div class="alert alert-info">';
    html += '<h5><i class="fa fa-info-circle"></i> Stock Disponible por Sucursal</h5>';
    html += '<p>Para cada producto, se muestra el stock disponible en cada sucursal.</p>';
    html += '</div>';

    html += '<div class="table-responsive" style="max-height: 500px; overflow-y: auto;">';
    html += '<table class="table table-bordered table-striped">';
    html += '<thead class="bg-primary">';
    html += '<tr>';
    html += '<th style="width: 80px;">Código</th>';
    html += '<th style="width: 200px;">Producto</th>';
    html += '<th style="width: 80px;">Solicitado</th>';

    // Agregar columnas para cada sucursal (más compactas)
    sucursalesUnicas.forEach(function(sucursal) {
        html += '<th style="width: 120px;">' + sucursal.nombre + '</th>';
    });

    html += '<th style="width: 80px;">Estado</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';

    // Procesar cada producto
    stockData.forEach(function(producto) {
        var cantidadSolicitada = producto.cantidad_solicitada || 0;
        var totalDisponible = 0;

        // Calcular total disponible sumando stock de todas las sucursales
        if (producto.sucursales && Array.isArray(producto.sucursales)) {
            producto.sucursales.forEach(function(sucursal) {
                totalDisponible += sucursal.stock_disponible || 0;
            });
        }

        html += '<tr data-producto="' + producto.codigo + '">';
        html += '<td><strong>' + producto.codigo + '</strong></td>';
        html += '<td><small>' + producto.descripcion + '</small></td>';
        html += '<td><span class="badge badge-info">' + cantidadSolicitada + '</span></td>';

        // Agregar columnas para cada sucursal
        sucursalesUnicas.forEach(function(sucursal) {
            var stockSucursal = 0;
            var puedeSatisfacer = false;

            // Buscar el stock de esta sucursal para este producto
            if (producto.sucursales && Array.isArray(producto.sucursales)) {
                var sucursalProducto = producto.sucursales.find(function(s) {
                    return s.id === sucursal.id;
                });

                if (sucursalProducto) {
                    stockSucursal = sucursalProducto.stock_disponible || 0;
                    puedeSatisfacer = sucursalProducto.puede_satisfacer || false;
                }
            }

            html += '<td class="text-center">';
            html += '<span class="badge ' + (puedeSatisfacer ? 'badge-success' : 'badge-danger') + '" style="font-size: 11px;">';
            html += stockSucursal;
            html += '</span>';
            html += '<br><small class="text-muted" style="font-size: 10px;">' + (puedeSatisfacer ? '✓' : '✗') + '</small>';
            html += '</td>';
        });

        // Estado del producto
        var estado = totalDisponible >= cantidadSolicitada ? 'Completo' : 'Parcial';
        var claseEstado = totalDisponible >= cantidadSolicitada ? 'badge-success' : 'badge-warning';

        html += '<td class="text-center"><span class="badge ' + claseEstado + '" style="font-size: 11px;">' + estado + '</span></td>';
        html += '</tr>';
    });

    html += '</tbody>';
    html += '</table>';
    html += '</div>';

    // Resumen compacto
    html += '<div class="alert alert-info mt-2" style="padding: 10px;">';
    html += '<div class="row">';

    var productosCompletos = 0;
    var productosParciales = 0;
    var productosSinStock = 0;

    stockData.forEach(function(producto) {
        var cantidadSolicitada = producto.cantidad_solicitada || 0;
        var totalDisponible = 0;

        if (producto.sucursales && Array.isArray(producto.sucursales)) {
            producto.sucursales.forEach(function(sucursal) {
                totalDisponible += sucursal.stock_disponible || 0;
            });
        }

        if (totalDisponible >= cantidadSolicitada) {
            productosCompletos++;
        } else if (totalDisponible > 0) {
            productosParciales++;
        } else {
            productosSinStock++;
        }
    });

    html += '<div class="col-md-4 text-center">';
    html += '<span class="badge badge-success" style="font-size: 12px;">Completos: ' + productosCompletos + '</span>';
    html += '</div>';
    html += '<div class="col-md-4 text-center">';
    html += '<span class="badge badge-warning" style="font-size: 12px;">Parciales: ' + productosParciales + '</span>';
    html += '</div>';
    html += '<div class="col-md-4 text-center">';
    html += '<span class="badge badge-danger" style="font-size: 12px;">Sin stock: ' + productosSinStock + '</span>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    html += '</div>';

    swal({
        title: 'Stock Disponible por Sucursal',
        html: html,
        width: '70%',
        showCancelButton: true,
        confirmButtonText: 'Crear Despachos',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3c8dbc',
        cancelButtonColor: '#d33',
        onOpen: function() {
            // Configurar eventos para los inputs
            $('.stock-input').on('input', function() {
                actualizarTotalesProducto($(this).data('producto'));
                actualizarResumen();
            });

            // Actualizar resumen inicial
            actualizarResumen();
        }
    }).then(function(result) {
        if(result.value) {
            crearDespachosPorSucursal(stockData, idSolicitud, numeroSolicitud);
        }
    });
}

/*=============================================
ACTUALIZAR TOTALES POR PRODUCTO
=============================================*/
function actualizarTotalesProducto(codigoProducto) {
    var total = 0;
    var solicitado = 0;

    // Obtener cantidad solicitada
    $('tr[data-producto="' + codigoProducto + '"]').find('.badge-info').each(function() {
        solicitado = parseInt($(this).text());
    });

    // Sumar todas las cantidades seleccionadas
    $('input[data-producto="' + codigoProducto + '"]').each(function() {
        var cantidad = parseInt($(this).val()) || 0;
        total += cantidad;
    });

    // Actualizar total seleccionado
    $('.total-seleccionado[data-producto="' + codigoProducto + '"]').text(total);

    // Actualizar estado
    var estado = $('.estado-producto[data-producto="' + codigoProducto + '"]');
    if(total == solicitado) {
        estado.removeClass('badge-warning badge-danger').addClass('badge-success').text('Completo');
    } else if(total > solicitado) {
        estado.removeClass('badge-warning badge-success').addClass('badge-danger').text('Exceso');
    } else if(total > 0) {
        estado.removeClass('badge-success badge-danger').addClass('badge-warning').text('Parcial');
    } else {
        estado.removeClass('badge-success badge-danger').addClass('badge-warning').text('Pendiente');
    }
}

/*=============================================
ACTUALIZAR RESUMEN GENERAL
=============================================*/
function actualizarResumen() {
    var productosCompletos = 0;
    var productosParciales = 0;
    var productosPendientes = 0;
    var productosConExceso = 0;

    $('.estado-producto').each(function() {
        var estado = $(this).text();
        if(estado == 'Completo') productosCompletos++;
        else if(estado == 'Parcial') productosParciales++;
        else if(estado == 'Pendiente') productosPendientes++;
        else if(estado == 'Exceso') productosConExceso++;
    });

    var html = '<ul class="mb-0">';
    html += '<li><strong>Productos completos:</strong> ' + productosCompletos + '</li>';
    html += '<li><strong>Productos parciales:</strong> ' + productosParciales + '</li>';
    html += '<li><strong>Productos pendientes:</strong> ' + productosPendientes + '</li>';
    if(productosConExceso > 0) {
        html += '<li><strong>Productos con exceso:</strong> ' + productosConExceso + '</li>';
    }
    html += '</ul>';

    $('#resumen-seleccion').html(html);
}

/*=============================================
CREAR DESPACHOS POR SUCURSAL
=============================================*/
function crearDespachosPorSucursal(stockData, idSolicitud, numeroSolicitud) {

    // Recopilar datos de despachos por sucursal
    var despachosData = {};

    $('.stock-input').each(function() {
        var sucursal = $(this).data('sucursal');
        var producto = $(this).data('producto');
        var cantidad = parseInt($(this).val()) || 0;

        if(cantidad > 0) {
            if(!despachosData[sucursal]) {
                despachosData[sucursal] = {
                    id_solicitud_origen: idSolicitud,
                    productos: []
                };
            }

            // Buscar descripción del producto
            var descripcion = '';
            stockData.productos_solicitud.forEach(function(p) {
                if(p.codigo == producto) {
                    descripcion = p.descripcion;
                }
            });

            despachosData[sucursal].productos.push({
                codigo: producto,
                descripcion: descripcion,
                cantidad: cantidad
            });
        }
    });

    // Verificar que hay despachos para crear
    if(Object.keys(despachosData).length == 0) {
        swal({
            title: 'Sin selección',
            text: 'No se seleccionaron cantidades para crear despachos',
            type: 'warning',
            confirmButtonText: 'Cerrar'
        });
        return;
    }

    // Mostrar loading
    swal({
        title: "Creando despachos...",
        text: "Procesando " + Object.keys(despachosData).length + " despachos",
        type: "info",
        showConfirmButton: false,
        allowOutsideClick: false
    });

    // Enviar datos para crear despachos
    $.ajax({
        url: 'ajax/crear-despachos-sucursales.ajax.php',
        type: 'POST',
        data: {
            accion: 'crear_despachos_sucursales',
            despachos_data: JSON.stringify(despachosData)
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                mostrarResultadoDespachos(response);
            } else {
                swal({
                    title: 'Error',
                    text: response.message || 'No se pudieron crear los despachos',
                    type: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        },
        error: function() {
            swal({
                title: 'Error',
                text: 'Error de conexión al crear despachos',
                type: 'error',
                confirmButtonText: 'Cerrar'
            });
        }
    });
}

/*=============================================
MOSTRAR RESULTADO DE DESPACHOS
=============================================*/
function mostrarResultadoDespachos(response) {

    var html = '<div class="alert alert-success">';
    html += '<h5><i class="fa fa-check-circle"></i> Despachos Creados Exitosamente</h5>';
    html += '<p><strong>Total despachos creados:</strong> ' + response.total_despachos + '</p>';
    html += '</div>';

    if(response.despachos_creados.length > 0) {
        html += '<div class="table-responsive">';
        html += '<table class="table table-bordered table-striped">';
        html += '<thead class="bg-success">';
        html += '<tr><th>Sucursal</th><th>Productos</th><th>Cantidad Total</th></tr>';
        html += '</thead>';
        html += '<tbody>';

        response.despachos_creados.forEach(function(despacho) {
            html += '<tr>';
            html += '<td><strong>' + despacho.sucursal + '</strong></td>';
            html += '<td><span class="badge badge-primary">' + despacho.productos + '</span></td>';
            html += '<td><span class="badge badge-success">' + despacho.cantidad_total + '</span></td>';
            html += '</tr>';
        });

        html += '</tbody>';
        html += '</table>';
        html += '</div>';
    }

    if(response.errores.length > 0) {
        html += '<div class="alert alert-danger">';
        html += '<h5><i class="fa fa-exclamation-triangle"></i> Errores Encontrados</h5>';
        html += '<ul>';
        response.errores.forEach(function(error) {
            html += '<li>' + error + '</li>';
        });
        html += '</ul>';
        html += '</div>';
    }

    swal({
        title: 'Despachos Creados',
        html: html,
        width: '70%',
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#3c8dbc'
    }).then(function() {
        // Recargar la página para mostrar los nuevos despachos
        location.reload();
    });
}

/*=============================================
VER DETALLES DE SOLICITUD
=============================================*/
$(document).on('click', '.btnVerSolicitud', function() {

    var idSolicitud = $(this).attr('idSolicitud');
// ✅ MOSTRAR LOADING
    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    // ✅ OBTENER DETALLES VIA AJAX
    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: {
            accion: 'ver_detalle',
            id_solicitud: idSolicitud
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                mostrarModalDetalleSolicitud(response.data);
            } else {
                swal({
                    title: 'Error',
                    text: response.message,
                    type: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        },
        error: function() {
            swal({
                title: 'Error',
                text: 'Error de conexión al obtener detalles',
                type: 'error',
                confirmButtonText: 'Cerrar'
            });
        },
        complete: function() {
            // ✅ RESTAURAR BOTÓN
            $('.btnVerSolicitud[idSolicitud="' + idSolicitud + '"]')
                .prop('disabled', false)
                .html('<i class="fa fa-eye"></i>');
        }
    });
});

/*=============================================
APROBAR SOLICITUD
=============================================*/
$(document).on('click', '.btnAprobarSolicitud', function() {

    var idSolicitud = $(this).attr('idSolicitud');
swal({
        title: '¿Aprobar esta solicitud?',
        text: "La solicitud será marcada como aprobada y lista para procesar",
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            aprobarSolicitud(idSolicitud);
        }
    });
});

/*=============================================
CANCELAR SOLICITUD
=============================================*/
$(document).on('click', '.btnCancelarSolicitud', function() {

    var idSolicitud = $(this).attr('idSolicitud');
swal({
        title: '¿Cancelar esta solicitud?',
        text: "La solicitud será marcada como cancelada",
        type: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Escriba el motivo de la cancelación...',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No cancelar',
        inputValidator: (value) => {
            if (!value || value.trim().length < 5) {
                return 'Debe escribir un motivo de al menos 5 caracteres';
            }
        }
    }).then((result) => {
        if (result.value) {
            cancelarSolicitud(idSolicitud, result.value);
        }
    });
});

/*=============================================
ELIMINAR SOLICITUD
=============================================*/
$(document).on('click', '.btnEliminarSolicitud', function() {

    var idSolicitud = $(this).attr('idSolicitud');
swal({
        title: '¿Eliminar esta solicitud?',
        text: "¡Esta acción no se puede deshacer!",
        type: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            eliminarSolicitud(idSolicitud);
        }
    });
});

/*=============================================
FUNCIÓN PARA APROBAR SOLICITUD
=============================================*/
function aprobarSolicitud(idSolicitud) {

    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: {
            accion: 'aprobar',
            id_solicitud: idSolicitud
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                swal({
                    title: '¡Solicitud aprobada!',
                    text: response.message,
                    type: 'success',
                    confirmButtonText: 'Cerrar'
                }).then(() => {
                    // Recargar tabla
                    $('.tablaSolicitudesStock').DataTable().ajax.reload();
                });
            } else {
                swal({
                    title: 'Error',
                    text: response.message,
                    type: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        },
        error: function() {
            swal({
                title: 'Error',
                text: 'Error de conexión al aprobar solicitud',
                type: 'error',
                confirmButtonText: 'Cerrar'
            });
        }
    });
}

/*=============================================
FUNCIÓN PARA CANCELAR SOLICITUD
=============================================*/
function cancelarSolicitud(idSolicitud, motivo) {

    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: {
            accion: 'cancelar',
            id_solicitud: idSolicitud,
            motivo: motivo
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                swal({
                    title: '¡Solicitud cancelada!',
                    text: response.message,
                    type: 'success',
                    confirmButtonText: 'Cerrar'
                }).then(() => {
                    // Recargar tabla
                    $('.tablaSolicitudesStock').DataTable().ajax.reload();
                });
            } else {
                swal({
                    title: 'Error',
                    text: response.message,
                    type: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        },
        error: function() {
            swal({
                title: 'Error',
                text: 'Error de conexión al cancelar solicitud',
                type: 'error',
                confirmButtonText: 'Cerrar'
            });
        }
    });
}

/*=============================================
FUNCIÓN PARA ELIMINAR SOLICITUD
=============================================*/
function eliminarSolicitud(idSolicitud) {

    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: {
            accion: 'eliminar',
            id_solicitud: idSolicitud
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                swal({
                    title: '¡Solicitud eliminada!',
                    text: response.message,
                    type: 'success',
                    confirmButtonText: 'Cerrar'
                }).then(() => {
                    // Recargar tabla
                    $('.tablaSolicitudesStock').DataTable().ajax.reload();
                });
            } else {
                swal({
                    title: 'Error',
                    text: response.message,
                    type: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        },
        error: function() {
            swal({
                title: 'Error',
                text: 'Error de conexión al eliminar solicitud',
                type: 'error',
                confirmButtonText: 'Cerrar'
            });
        }
    });
}

/*=============================================
MOSTRAR MODAL CON DETALLES DE SOLICITUD - VERSIÓN CORREGIDA
=============================================*/
function mostrarModalDetalleSolicitud(solicitud) {

// ✅ VERIFICAR QUE EXISTAN LOS ELEMENTOS DEL MODAL
    if($('#modalVerSolicitud').length === 0) {
swal({
            title: 'Error',
            text: 'El modal de detalles no está disponible',
            type: 'error',
            confirmButtonText: 'Cerrar'
        });
        return;
    }

    try {

        // ✅ INFORMACIÓN GENERAL - con verificaciones
        if($('#numeroSolicitudModal').length) {
            $('#numeroSolicitudModal').text(solicitud.numero_solicitud || 'N/A');
        }

        if($('#sucursalSolicitante').length) {
            $('#sucursalSolicitante').text(solicitud.nombre_sucursal_solicitante || 'N/A');
        }

        if($('#usuarioSolicitante').length) {
            $('#usuarioSolicitante').text(solicitud.nombre_usuario_solicitante || 'N/A');
        }

        if($('#fechaSolicitud').length) {
            $('#fechaSolicitud').text(formatearFecha(solicitud.fecha_solicitud) || 'N/A');
        }

        if($('#tipoSolicitud').length) {
            $('#tipoSolicitud').text((solicitud.tipo_solicitud || 'N/A').toUpperCase());
        }

        if($('#totalProductos').length) {
            $('#totalProductos').text((solicitud.total_productos || 0) + ' productos');
        }

        // ✅ ESTADO CON COLOR
        if($('#estadoSolicitud').length) {
            var estadoTexto = (solicitud.estado || 'desconocido').charAt(0).toUpperCase() + (solicitud.estado || 'desconocido').slice(1);
            $('#estadoSolicitud').text(estadoTexto);
        }

        // ✅ Cambiar color del icono según estado
        if($('#estadoIcon').length) {
            var $estadoIcon = $('#estadoIcon');
            $estadoIcon.removeClass('bg-red bg-green bg-yellow bg-gray');

            switch(solicitud.estado) {
                case 'pendiente':
                    $estadoIcon.addClass('bg-yellow').find('i').removeClass().addClass('fa fa-clock-o');
                    break;
                case 'aprobado':
                    $estadoIcon.addClass('bg-green').find('i').removeClass().addClass('fa fa-check');
                    break;
                case 'finalizado':
                    $estadoIcon.addClass('bg-gray').find('i').removeClass().addClass('fa fa-flag-checkered');
                    break;
                case 'cancelado':
                    $estadoIcon.addClass('bg-red').find('i').removeClass().addClass('fa fa-times');
                    break;
                default:
                    $estadoIcon.addClass('bg-gray').find('i').removeClass().addClass('fa fa-question');
            }
        }

        // ✅ INFORMACIÓN DE REMISIÓN (si aplica)
        if(solicitud.tipo_solicitud === 'remision' && solicitud.codigo_remision) {
            $('#codigoRemision').text(solicitud.codigo_remision || 'N/A');
            $('#clienteRemision').text(solicitud.nombre_cliente_remision || 'No especificado');
            $('#infoRemision').show();
        } else {
            $('#infoRemision').hide();
        }

        // ✅ DETALLE ADICIONAL (si aplica)
        if(solicitud.detalle_adicional && solicitud.detalle_adicional.trim() !== '') {
            $('#detalleAdicional').text(solicitud.detalle_adicional);
            $('#detalleAdicionalContainer').show();
        } else {
            $('#detalleAdicionalContainer').hide();
        }

        // ✅ CARGAR PRODUCTOS
        cargarProductosEnModal(solicitud.productos_solicitados);

        // ✅ CARGAR HISTORIAL
        cargarHistorialEnModal(solicitud);

        // ✅ CARGAR STOCK SUCURSALES
        cargarStockSucursalesEnModal(solicitud.productos_solicitados);

        // ✅ CONFIGURAR BOTONES DE EXPORTACIÓN
        configurarBotonesExportacion(solicitud);

        // ✅ MOSTRAR MODAL
        $('#modalVerSolicitud').modal('show');
} catch(error) {
swal({
            title: 'Error',
            text: 'Error al cargar los detalles de la solicitud',
            type: 'error',
            confirmButtonText: 'Cerrar'
        });
    }
}

/*=============================================
CARGAR PRODUCTOS EN EL MODAL - VERSIÓN CORREGIDA
=============================================*/
function cargarProductosEnModal(productosJson) {

try {
        var productos = [];

        // ✅ PARSEAR JSON si es string
        if(typeof productosJson === 'string') {
            productos = JSON.parse(productosJson);
        } else if(Array.isArray(productosJson)) {
            productos = productosJson;
        } else {
productos = [];
        }
var html = '';
        var totalCantidad = 0;

        if(productos && productos.length > 0) {
            productos.forEach(function(producto, index) {
                var cantidad = parseInt(producto.cantidad) || 0;
                totalCantidad += cantidad;

                html += '<tr>';
                html += '<td class="text-center"><strong>' + (index + 1) + '</strong></td>';
                html += '<td><code>' + (producto.codigo || 'N/A') + '</code></td>';
                html += '<td>' + (producto.descripcion || 'Sin descripción') + '</td>';
                html += '<td class="text-center">';
                html += '<span class="badge bg-blue">' + cantidad + '</span>';
                html += '</td>';
                html += '<td>';
                if(producto.observacion && producto.observacion.trim() !== '') {
                    html += '<small class="text-muted"><i class="fa fa-comment"></i> ' + producto.observacion + '</small>';
                } else {
                    html += '<small class="text-muted">Sin observaciones</small>';
                }
                html += '</td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="5" class="text-center text-muted">No hay productos registrados</td></tr>';
        }

        $('#productosModalBody').html(html);
        $('#totalCantidadProductos').text(totalCantidad);
} catch(e) {
$('#productosModalBody').html('<tr><td colspan="5" class="text-center text-danger">Error cargando productos: ' + e.message + '</td></tr>');
    }
}

/*=============================================
CARGAR HISTORIAL EN EL MODAL - VERSIÓN CORREGIDA
=============================================*/
function cargarHistorialEnModal(solicitud) {
try {
        // ✅ CREACIÓN
        $('#fechaCreacion').html('<i class="fa fa-plus-circle"></i> ' + formatearFecha(solicitud.fecha_solicitud, true));
        $('#horaCreacion').text(formatearHora(solicitud.fecha_solicitud));
        $('#usuarioCreacion').text(solicitud.nombre_usuario_solicitante || 'N/A');
        $('#numeroCreacion').text(solicitud.numero_solicitud || 'N/A');

        // ✅ APROBACIÓN/CANCELACIÓN
        if(solicitud.estado !== 'pendiente' && solicitud.fecha_aprobacion) {
            $('#timelineAprobacion').show();

            var $labelAprobacion = $('#labelAprobacion');
            var $iconAprobacion = $('#iconAprobacion');
            var $accionAprobacion = $('#accionAprobacion');

            if(solicitud.estado === 'aprobado') {
                $labelAprobacion.removeClass('bg-red bg-yellow').addClass('bg-green')
                    .html('<i class="fa fa-check"></i> Aprobación');
                $iconAprobacion.removeClass('fa-times bg-red').addClass('fa-check bg-green');
                $accionAprobacion.text('Aprobada');
            } else if(solicitud.estado === 'cancelado') {
                $labelAprobacion.removeClass('bg-green bg-yellow').addClass('bg-red')
                    .html('<i class="fa fa-times"></i> Cancelación');
                $iconAprobacion.removeClass('fa-check bg-green').addClass('fa-times bg-red');
                $accionAprobacion.text('Cancelada');
            }

            $('#horaAprobacion').text(formatearHora(solicitud.fecha_aprobacion));
            $('#usuarioAprobacion').text(solicitud.nombre_usuario_aprobacion || 'Sistema');

            // Mostrar motivo si es cancelación
            if(solicitud.estado === 'cancelado' && solicitud.motivo_cancelacion) {
                $('#motivoAprobacion').text(solicitud.motivo_cancelacion);
                $('#motivoContainer').show();
            } else {
                $('#motivoContainer').hide();
            }

        } else {
            $('#timelineAprobacion').hide();
        }
} catch(error) {
}
}

/*=============================================
FUNCIONES AUXILIARES PARA FORMATEO - MEJORADAS
=============================================*/
function formatearFecha(fecha, conDia = false) {

    if(!fecha) return 'Fecha no disponible';

    try {
        var date = new Date(fecha);

        // Verificar que la fecha sea válida
        if(isNaN(date.getTime())) {
            return fecha; // Devolver la fecha original si no se puede parsear
        }

        var opciones = {

            year: 'numeric',

            month: '2-digit',

            day: '2-digit'

        };

        if(conDia) {
            opciones.weekday = 'long';
        }

        return date.toLocaleDateString('es-ES', opciones);

    } catch(error) {
return fecha;
    }
}

function formatearHora(fecha) {

    if(!fecha) return '--:--';

    try {
        var date = new Date(fecha);

        if(isNaN(date.getTime())) {
            return '--:--';
        }

        return date.toLocaleTimeString('es-ES', {

            hour: '2-digit',

            minute: '2-digit'

        });

    } catch(error) {
return '--:--';
    }
}

/*=============================================
CONFIGURAR BOTONES DE EXPORTACIÓN - FUNCIONAL
=============================================*/
function configurarBotonesExportacion(solicitud) {
    $('#btnExportarPDF').attr('idSolicitud', solicitud ? solicitud.id : '');
}

/*=============================================
EXPORTAR SOLICITUD A PDF - Igual que despachos (AJAX + descarga)
=============================================*/
$(document).on('click', '.btnExportarPDFSolicitud', function() {
    var idSolicitud = $(this).attr('idSolicitud');
    if (!idSolicitud) {
        swal({ title: 'Error', text: 'No se pudo identificar la solicitud', type: 'error' });
        return;
    }
    swal({
        title: 'Generando PDF...',
        text: 'Por favor espere mientras se genera el documento',
        type: 'info',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    var datos = new FormData();
    datos.append('accion', 'exportar_pdf');
    datos.append('idSolicitud', idSolicitud);
    $.ajax({
        url: 'ajax/exportar-solicitud-stock.ajax.php',
        method: 'POST',
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(respuesta) {
            swal.close();
            if (respuesta.success) {
                var link = document.createElement('a');
                link.href = respuesta.url;
                link.download = respuesta.nombreArchivo;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                swal({
                    title: '¡PDF Generado!',
                    text: 'El documento se ha descargado correctamente',
                    type: 'success',
                    confirmButtonText: 'Cerrar'
                });
            } else {
                swal({
                    title: 'Error',
                    text: respuesta.error || 'No se pudo generar el PDF',
                    type: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        },
        error: function() {
            swal.close();
            swal({
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor',
                type: 'error',
                confirmButtonText: 'Cerrar'
            });
        }
    });
});

/*=============================================
EXPORTAR SOLICITUD A EXCEL - FUNCIONAL CON SHEETJS
=============================================*/
function exportarSolicitudExcel(solicitud) {
try {
        // ✅ VERIFICAR QUE SHEETJS ESTÉ DISPONIBLE
        if(typeof XLSX === 'undefined') {
            swal({
                title: 'Error',
                text: 'La librería de Excel no está disponible. Contacte al administrador.',
                type: 'error',
                confirmButtonText: 'Cerrar'
            });
            return;
        }

        // ✅ PROCESAR PRODUCTOS
        var productos = [];
        try {
            productos = JSON.parse(solicitud.productos_solicitados);
        } catch(e) {
productos = [];
        }

        // ✅ CREAR DATOS PARA EXCEL
        var datosExcel = [];

        // ENCABEZADO PRINCIPAL
        datosExcel.push(['SOLICITUD DE STOCK', '', '', '', '', '']);
        datosExcel.push([solicitud.numero_solicitud, '', '', '', '', '']);
        datosExcel.push(['']); // Línea vacía

        // INFORMACIÓN GENERAL
        datosExcel.push(['INFORMACIÓN GENERAL', '', '', '', '', '']);
        datosExcel.push(['Sucursal:', solicitud.nombre_sucursal_solicitante, '', 'Estado:', solicitud.estado.toUpperCase(), '']);
        datosExcel.push(['Usuario:', solicitud.nombre_usuario_solicitante, '', 'Total Productos:', solicitud.total_productos, '']);
        datosExcel.push(['Fecha:', formatearFecha(solicitud.fecha_solicitud), '', 'Total Cantidad:', solicitud.total_cantidad, '']);
        datosExcel.push(['Tipo:', solicitud.tipo_solicitud.toUpperCase(), '', 'Aprobado por:', solicitud.nombre_usuario_aprobacion || 'Sin aprobar', '']);
        datosExcel.push(['']); // Línea vacía

        // INFORMACIÓN DE REMISIÓN (si aplica)
        if(solicitud.tipo_solicitud === 'remision' && solicitud.codigo_remision) {
            datosExcel.push(['INFORMACIÓN DE REMISIÓN', '', '', '', '', '']);
            datosExcel.push(['Código Remisión:', solicitud.codigo_remision, '', '', '', '']);
            datosExcel.push(['Cliente:', solicitud.nombre_cliente_remision || 'No especificado', '', '', '', '']);
            datosExcel.push(['']); // Línea vacía
        }

        // DETALLE ADICIONAL (si existe)
        if(solicitud.detalle_adicional && solicitud.detalle_adicional.trim() !== '') {
            datosExcel.push(['DETALLE ADICIONAL', '', '', '', '', '']);
            datosExcel.push([solicitud.detalle_adicional, '', '', '', '', '']);
            datosExcel.push(['']); // Línea vacía
        }

        // PRODUCTOS SOLICITADOS
        datosExcel.push(['PRODUCTOS SOLICITADOS', '', '', '', '', '']);
        datosExcel.push(['#', 'CÓDIGO', 'DESCRIPCIÓN', 'CANTIDAD', 'OBSERVACIONES', '']);

        // Agregar productos
        if(productos && productos.length > 0) {
            productos.forEach(function(producto, index) {
                datosExcel.push([
                    index + 1,
                    producto.codigo || 'N/A',
                    producto.descripcion || 'Sin descripción',
                    producto.cantidad || 0,
                    producto.observacion || 'Sin observaciones',
                    ''
                ]);
            });
        } else {
            datosExcel.push(['No hay productos registrados', '', '', '', '', '']);
        }

        // TOTAL
        datosExcel.push(['']); // Línea vacía
        datosExcel.push(['TOTAL:', '', '', solicitud.total_cantidad, solicitud.total_productos + ' productos', '']);

        // MOTIVO DE CANCELACIÓN (si aplica)
        if(solicitud.estado === 'cancelado' && solicitud.motivo_cancelacion) {
            datosExcel.push(['']); // Línea vacía
            datosExcel.push(['MOTIVO DE CANCELACIÓN', '', '', '', '', '']);
            datosExcel.push([solicitud.motivo_cancelacion, '', '', '', '', '']);
        }

        // INFORMACIÓN DE GENERACIÓN
        datosExcel.push(['']); // Línea vacía
        datosExcel.push(['INFORMACIÓN DEL REPORTE', '', '', '', '', '']);
        datosExcel.push(['Generado por:', $('#usuarioSolicitante').text() || 'Usuario actual', '', 'Fecha:', new Date().toLocaleString('es-ES'), '']);

        // ✅ CREAR LIBRO DE EXCEL
        var ws = XLSX.utils.aoa_to_sheet(datosExcel);
        var wb = XLSX.utils.book_new();

        // ✅ CONFIGURAR ANCHOS DE COLUMNA
        ws['!cols'] = [
            {wch: 20}, // Columna A
            {wch: 30}, // Columna B
            {wch: 40}, // Columna C
            {wch: 15}, // Columna D
            {wch: 30}, // Columna E
            {wch: 10}  // Columna F
        ];

        // ✅ AGREGAR HOJA AL LIBRO
        XLSX.utils.book_append_sheet(wb, ws, "Solicitud " + solicitud.numero_solicitud);

        // ✅ GENERAR Y DESCARGAR ARCHIVO
        var nombreArchivo = 'Solicitud_' + solicitud.numero_solicitud + '_' +

                           new Date().toISOString().slice(0,10) + '.xlsx';

        XLSX.writeFile(wb, nombreArchivo);
// ✅ MOSTRAR CONFIRMACIÓN
        swal({
            title: '¡Excel Generado!',
            text: 'El archivo ' + nombreArchivo + ' se ha descargado correctamente',
            type: 'success',
            timer: 3000,
            showConfirmButton: false
        });

    } catch(error) {
swal({
            title: 'Error',
            text: 'Error al generar el Excel: ' + error.message,
            type: 'error',
            confirmButtonText: 'Cerrar'
        });
    }
}

/*=============================================
CARGAR STOCK SUCURSALES EN EL MODAL
=============================================*/
function cargarStockSucursalesEnModal(productosJson) {

try {
        var productos = [];

        // Parsear JSON si es string
        if(typeof productosJson === 'string') {
            productos = JSON.parse(productosJson);
        } else if(Array.isArray(productosJson)) {
            productos = productosJson;
        } else {
productos = [];
        }
if(!productos || productos.length === 0) {
            $('#tbodyStockSucursales').html('<tr><td colspan="5" class="text-center text-muted">No hay productos para consultar</td></tr>');
            return;
        }

        // Mostrar loading
        $('#tbodyStockSucursales').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Consultando stock disponible...</td></tr>');

        // Consultar stock en todas las sucursales
        $.ajax({
            url: 'ajax/stock-disponible-sucursales.ajax.php',
            type: 'POST',
            data: {
                accion: 'consultar_stock_sucursales',
                productos: JSON.stringify(productos)
            },
            dataType: 'json',
            success: function(response) {
if(response.success) {
                    mostrarStockSucursalesEnTabla(response.data);
                } else {
                    $('#tbodyStockSucursales').html('<tr><td colspan="5" class="text-center text-danger">Error: ' + (response.message || 'No se pudo consultar el stock') + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
$('#tbodyStockSucursales').html('<tr><td colspan="5" class="text-center text-danger">Error de conexión al consultar stock</td></tr>');
            }
        });

    } catch(error) {
$('#tbodyStockSucursales').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar stock de sucursales</td></tr>');
    }
}

/*=============================================
MOSTRAR STOCK SUCURSALES EN TABLA
=============================================*/
function mostrarStockSucursalesEnTabla(stockData) {
if(!stockData || !Array.isArray(stockData) || stockData.length === 0) {
        $('#tbodyStockSucursales').html('<tr><td colspan="5" class="text-center text-muted">No hay datos de stock disponibles</td></tr>');
        return;
    }

    // Obtener todas las sucursales únicas
    var sucursalesUnicas = [];
    var sucursalesMap = {};

    stockData.forEach(function(producto) {
        if (producto.sucursales && Array.isArray(producto.sucursales)) {
            producto.sucursales.forEach(function(sucursal) {
                if (!sucursalesMap[sucursal.id]) {
                    sucursalesMap[sucursal.id] = sucursal;
                    sucursalesUnicas.push(sucursal);
                }
            });
        }
    });

    // Ordenar sucursales por nombre
    sucursalesUnicas.sort(function(a, b) {
        return a.nombre.localeCompare(b.nombre);
    });
// Actualizar header de la tabla con columnas de sucursales
    var headerHtml = '<th style="width: 10px;">#</th>' +
                     '<th>Código</th>' +
                     '<th>Descripción</th>' +
                     '<th>Cantidad Solicitada</th>';

    sucursalesUnicas.forEach(function(sucursal) {
        headerHtml += '<th class="text-center" style="min-width: 80px;">' + sucursal.nombre + '</th>';
    });

    $('#sucursalesHeader').parent().html(headerHtml);

    // Generar filas de productos
    var tbodyHtml = '';

    stockData.forEach(function(producto, index) {
        var filaHtml = '<tr>';
        filaHtml += '<td class="text-center"><strong>' + (index + 1) + '</strong></td>';
        filaHtml += '<td><code>' + (producto.codigo || 'N/A') + '</code></td>';
        filaHtml += '<td>' + (producto.descripcion || 'N/A') + '</td>';
        filaHtml += '<td class="text-center"><span class="label label-primary">' + (producto.cantidad_solicitada || 0) + '</span></td>';

        // Agregar stock de cada sucursal
        sucursalesUnicas.forEach(function(sucursal) {
            var stockSucursal = 0;
            var puedeSatisfacer = false;

            // Buscar stock de esta sucursal para este producto
            if (producto.sucursales && Array.isArray(producto.sucursales)) {
                var sucursalData = producto.sucursales.find(function(s) {
                    return s.id === sucursal.id;
                });

                if (sucursalData) {
                    stockSucursal = sucursalData.stock_disponible || 0;
                    puedeSatisfacer = sucursalData.puede_satisfacer || false;
                }
            }

            // Determinar clase CSS según disponibilidad
            var stockClass = 'text-muted';
            var stockIcon = '';

            if (stockSucursal > 0) {
                if (puedeSatisfacer) {
                    stockClass = 'text-success';
                    stockIcon = '<i class="fa fa-check-circle"></i> ';
                } else {
                    stockClass = 'text-warning';
                    stockIcon = '<i class="fa fa-exclamation-triangle"></i> ';
                }
            } else {
                stockClass = 'text-danger';
                stockIcon = '<i class="fa fa-times-circle"></i> ';
            }

            filaHtml += '<td class="text-center ' + stockClass + '">' +

                       stockIcon + '<strong>' + stockSucursal + '</strong></td>';
        });

        filaHtml += '</tr>';
        tbodyHtml += filaHtml;
    });

    $('#tbodyStockSucursales').html(tbodyHtml);
}

/*=============================================
MOSTRAR MODAL DE SELECCIÓN DE PRODUCTOS
=============================================*/
function mostrarModalSeleccionProductos(solicitud, productos, url) {
try {

    // Crear HTML de la modal
    var modalHtml = `
        <div class="modal fade" id="modalSeleccionProductos" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="padding-bottom: 0;">
                        <h4 class="modal-title">
                            <i class="fa fa-shopping-cart"></i> Seleccionar Productos para Despacho
                        </h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding-top: 0;">
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Solicitud:</strong><br>
                                    <span class="badge badge-primary">${solicitud.numero_solicitud}</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total productos:</strong><br>
                                    <span class="badge badge-info">${productos.length} productos</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total unidades:</strong><br>
                                    <span class="badge badge-secondary">${solicitud.total_cantidad}</span>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th width="100">Stock Actual</th>
                                        <th width="100">Cantidad Solicitada</th>
                                    </tr>
                                </thead>
                                <tbody id="listaProductosSeleccion">
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning" id="alertaProductos" style="display: none;">
                            <i class="fa fa-exclamation-triangle"></i>

                            <span id="mensajeAlerta"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-success" id="btnCrearDespachoSeleccion">
                            <i class="fa fa-truck"></i> Crear Despacho
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remover modal existente si existe
    $('#modalSeleccionProductos').remove();

    // Agregar modal al DOM
    $('body').append(modalHtml);

    // Llenar tabla de productos inicialmente con stock 0 (se actualizará con AJAX)
    var tbodyHtml = '';
    productos.forEach(function(producto, index) {
        tbodyHtml += `
            <tr data-codigo="${producto.codigo}">
                <td><strong>${producto.codigo}</strong></td>
                <td>${producto.descripcion}</td>
                <td class="text-center stock-actual" data-cantidad-solicitada="${producto.cantidad}">
                    <i class="fa fa-spinner fa-spin"></i> <strong>Cargando...</strong>
                </td>
                <td class="text-center">
                    <span class="badge badge-primary">${producto.cantidad}</span>
                </td>
            </tr>
        `;
    });

    $('#listaProductosSeleccion').html(tbodyHtml);

    // Obtener stock actual de la sucursal actual mediante AJAX
    var codigos = productos.map(function(p) { return p.codigo; });
    
    $.ajax({
        url: 'ajax/productos-despacho.ajax.php',
        type: 'POST',
        data: {
            obtenerStockActual: 'ok',
            codigos: codigos
        },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.productos) {
                // Actualizar stock en la tabla
                response.productos.forEach(function(productoStock) {
                    var $row = $('#listaProductosSeleccion tr[data-codigo="' + productoStock.codigo + '"]');
                    var $stockCell = $row.find('.stock-actual');
                    var cantidadSolicitada = parseInt($stockCell.data('cantidad-solicitada')) || 0;
                    var stockActual = parseInt(productoStock.stock) || 0;
                    
                    var stockClass = 'text-danger';
                    var stockIcon = '<i class="fa fa-times-circle"></i> ';
                    
                    if(stockActual >= cantidadSolicitada) {
                        stockClass = 'text-success';
                        stockIcon = '<i class="fa fa-check-circle"></i> ';
                    } else if(stockActual > 0) {
                        stockClass = 'text-warning';
                        stockIcon = '<i class="fa fa-exclamation-triangle"></i> ';
                    }
                    
                    $stockCell.removeClass('text-danger text-warning text-success').addClass(stockClass);
                    $stockCell.html(stockIcon + '<strong>' + stockActual + '</strong>');
                });
            } else {
                // Si falla, mostrar 0
                $('#listaProductosSeleccion tr').each(function() {
                    var $stockCell = $(this).find('.stock-actual');
                    $stockCell.removeClass('text-danger text-warning text-success').addClass('text-danger');
                    $stockCell.html('<i class="fa fa-times-circle"></i> <strong>0</strong>');
                });
            }
        },
        error: function() {
            // Si hay error, mostrar 0
            $('#listaProductosSeleccion tr').each(function() {
                var $stockCell = $(this).find('.stock-actual');
                $stockCell.removeClass('text-danger text-warning text-success').addClass('text-danger');
                $stockCell.html('<i class="fa fa-times-circle"></i> <strong>0</strong>');
            });
        }
    });

    // Configurar eventos
    configurarEventosModalSeleccion(url);

    // Mostrar modal
$('#modalSeleccionProductos').modal('show');
} catch(error) {
}
}

/*=============================================
CONFIGURAR EVENTOS DE LA MODAL DE SELECCIÓN
=============================================*/
function configurarEventosModalSeleccion(url) {

    // Botón crear despacho
    $('#btnCrearDespachoSeleccion').on('click', function() {
// Obtener productos desde la tabla de la modal
        var productosSeleccionados = [];
        $('#listaProductosSeleccion tr').each(function(index) {
            if (index > 0) { // Saltar header
                var $row = $(this);
                var codigo = $row.find('td:first').text().trim();
                var cantidad = $row.find('.badge').text().trim();

                if (codigo && cantidad) {
                    productosSeleccionados.push({
                        index: index - 1, // Ajustar índice
                        cantidad: parseInt(cantidad)
                    });
                }
            }
        });
// Guardar productos seleccionados en localStorage
        localStorage.setItem('productosSeleccionados', JSON.stringify(productosSeleccionados));
// Cerrar modal y redirigir
$('#modalSeleccionProductos').modal('hide');
        window.location.href = url;
    });
}
