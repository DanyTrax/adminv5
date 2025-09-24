/*=============================================
VARIABLES GLOBALES
=============================================*/
var productosSeleccionados = [];
var solicitudActualId = null;

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
            "language": configuracionIdioma
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

    // Botón buscar remisión
    $('#btnBuscarRemision').click(function() {
        var busqueda = $('#buscarRemision').val().trim();
        if(busqueda.length >= 2) {
            buscarRemisiones(busqueda);
        } else {
            mostrarAlerta('warning', 'Ingrese al menos 2 caracteres para buscar');
        }
    });

    // Validar formulario antes de enviar

$('.formularioSolicitudStock').submit(function(e) {
    e.preventDefault(); 
    
    if(productosSeleccionados.length === 0) {
        mostrarAlerta('warning', 'Debe seleccionar al menos un producto');
        return false;
    }
    
    
    swal({
        title: '¿Crear esta solicitud?',
        text: "Se enviará la solicitud con " + productosSeleccionados.length + " productos",
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, crear solicitud',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.value) {
            
            $('#productosJsonInput').val(JSON.stringify(productosSeleccionados));
            
            
            var formData = new FormData($('.formularioSolicitudStock')[0]);
            
            $.ajax({
                url: 'index.php?ruta=solicitudes-stock',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    
                    window.location.reload();
                },
                error: function() {
                    mostrarAlerta('error', 'Error al crear la solicitud');
                }
            });
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

    // Limpiar modal cantidad al cerrarlo
    $('#modalCantidadProducto').on('hidden.bs.modal', function() {
        limpiarModalCantidad();
    });
});

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
AGREGAR PRODUCTO DESDE CATÁLOGO (SIN VALIDAR STOCK)
=============================================*/
$(document).on('click', '.btnAgregarProducto', function() {
    
    var idProducto = $(this).attr('idProducto');
    var codigoProducto = $(this).attr('codigoProducto');
    var descripcionProducto = $(this).attr('descripcionProducto');

    // ✅ NO VERIFICAR SI YA ESTÁ SELECCIONADO - PERMITIR AGREGAR CUALQUIERA
    // ✅ NO IMPORTA EL STOCK ACTUAL

    // Abrir modal para cantidad
    $('#nombreProductoModal').text(descripcionProducto);
    $('#stockDisponible').text('Sin límite'); // ✅ NO MOSTRAR STOCK
    $('#cantidadProductoModal').val(1).removeAttr('max'); // ✅ QUITAR LÍMITE MÁXIMO
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
AGREGAR PRODUCTO A LA LISTA (SIN VALIDAR STOCK)
=============================================*/
function agregarProductoALista() {
    
    var producto = $('#modalCantidadProducto').data('producto');
    var cantidad = $('#cantidadProductoModal').val();
    var observacion = $('#observacionProductoModal').val().trim();

    // ✅ VALIDACIONES ESTRICTAS
    
    // Validar que cantidad no esté vacía
    if(!cantidad || cantidad === '') {
        mostrarAlerta('error', 'Debe ingresar una cantidad');
        $('#cantidadProductoModal').focus();
        return;
    }
    
    // Convertir a número
    cantidad = parseInt(cantidad);
    
    // Validar que sea un número válido
    if(isNaN(cantidad)) {
        mostrarAlerta('error', 'La cantidad debe ser un número válido');
        $('#cantidadProductoModal').focus().select();
        return;
    }
    
    // Validar que sea mayor a 0
    if(cantidad <= 0) {
        mostrarAlerta('error', 'La cantidad debe ser mayor a 0');
        $('#cantidadProductoModal').val(1).focus().select();
        return;
    }
    
    // Validar que no exceda el máximo
    if(cantidad > 9999) {
        mostrarAlerta('error', 'La cantidad máxima es 9,999');
        $('#cantidadProductoModal').val(9999).focus().select();
        return;
    }
    
    // Validar longitud de observación
    if(observacion.length > 200) {
        mostrarAlerta('error', 'La observación no puede exceder 200 caracteres');
        $('#observacionProductoModal').focus();
        return;
    }

    // ✅ VERIFICAR SI YA ESTÁ EN LA LISTA
    var yaSeleccionado = productosSeleccionados.find(p => p.codigo === producto.codigo);
    if(yaSeleccionado) {
        mostrarAlerta('warning', 'Este producto ya está en la lista. Si desea cambiar la cantidad, elimínelo primero.');
        return;
    }

    // ✅ AGREGAR PRODUCTO (SIN VALIDAR STOCK)
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

// ✅ VALIDACIÓN EN TIEMPO REAL PARA EL INPUT DE CANTIDAD
$(document).on('input', '#cantidadProductoModal', function() {
    var valor = $(this).val();
    var numero = parseInt(valor);
    
    // Remover caracteres no numéricos
    $(this).val(valor.replace(/[^0-9]/g, ''));
    
    // Validar rango
    if(numero > 9999) {
        $(this).val(9999);
        mostrarAlerta('warning', 'Cantidad máxima: 9,999');
    }
});

// ✅ EVITAR NÚMEROS NEGATIVOS Y DECIMALES
$(document).on('keydown', '#cantidadProductoModal', function(e) {
    // Permitir: backspace, delete, tab, escape, enter
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
        // Permitir: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
        (e.keyCode === 65 && e.ctrlKey === true) ||
        (e.keyCode === 67 && e.ctrlKey === true) ||
        (e.keyCode === 86 && e.ctrlKey === true) ||
        (e.keyCode === 88 && e.ctrlKey === true) ||
        // Permitir: home, end, left, right
        (e.keyCode >= 35 && e.keyCode <= 39)) {
        return;
    }
    // Asegurar que sea un dígito
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
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
BUSCAR REMISIONES (VENTAS EN BASE LOCAL)
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
                console.log('Error: ' + response.message);
                $('#resultadosRemision').hide();
                mostrarAlerta('warning', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error AJAX al buscar remisiones:', error);
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
            var fecha = new Date(venta.fecha).toLocaleDateString();
            
            html += '<a href="#" class="list-group-item seleccionar-remision" ' +
                   'data-codigo="' + venta.codigo + '" ' +
                   'data-cliente="' + cliente + '">' +
                   '<strong>Remisión: ' + venta.codigo + '</strong><br>' +
                   '<small>Cliente: ' + cliente + ' | Total: $' + 
                   parseFloat(venta.total).toLocaleString() + ' | Fecha: ' + fecha + '</small>' +
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
    
    // ✅ SOLO ACTUALIZAR CAMPOS INFORMATIVOS - NO CARGAR PRODUCTOS
    $('#buscarRemision').val('Remisión: ' + codigo + ' - ' + cliente);
    $('#codigoRemisionSeleccionada').val(codigo);
    $('#nombreClienteRemision').val(cliente);
    
    // Ocultar resultados
    $('#resultadosRemision').hide();
    
    // ✅ NO CARGAR PRODUCTOS - SOLO MOSTRAR CONFIRMACIÓN
    mostrarAlerta('info', 'Remisión seleccionada: ' + codigo + '. Ahora agregue manualmente los productos que necesita solicitar.');
});

// ✅ ELIMINAR LA FUNCIÓN cargarProductosDeRemision O COMENTARLA
/*
function cargarProductosDeRemision(codigo) {
    // ✅ FUNCIÓN DESHABILITADA - Ya no carga productos automáticamente
    console.log('Función deshabilitada - agregar productos manualmente');
}
*/

/*=============================================
CARGAR PRODUCTOS DE REMISIÓN
=============================================*/
function cargarProductosDeRemision(codigo) {
    
    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: {
            accion: 'productos_venta',
            codigo_venta: codigo
        },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.productos.length > 0) {
                // Limpiar lista actual
                productosSeleccionados = [];
                
                // Agregar productos de la remisión
                response.productos.forEach(function(producto) {
                    productosSeleccionados.push({
                        id: producto.id || 0,
                        codigo: producto.codigo,
                        descripcion: producto.descripcion,
                        cantidad: parseInt(producto.cantidad),
                        observacion: 'Desde remisión ' + codigo
                    });
                });
                
                // Actualizar interfaz
                actualizarListaProductosSeleccionados();
                actualizarContadorProductos();
                habilitarBotonCrear();
                
                mostrarAlerta('success', 'Se cargaron ' + response.productos.length + ' productos de la remisión');
            } else {
                mostrarAlerta('warning', response.message || 'No se encontraron productos en la remisión');
            }
        },
        error: function() {
            console.log('Error al cargar productos de remisión');
            mostrarAlerta('error', 'Error al cargar productos de la remisión');
        }
    });
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
    $('#stockDisponible').text('');
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
    
    // Agregar nueva alerta
    $('body').prepend(alerta);
    
    // Auto-remover después de 5 segundos
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

/*=============================================
VER DETALLES DE SOLICITUD
=============================================*/
$(document).on('click', '.btnVerSolicitud', function() {
    
    var idSolicitud = $(this).attr('idSolicitud');
    solicitudActualId = idSolicitud;
    
    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: { idSolicitud: idSolicitud },
        dataType: 'json',
        success: function(solicitud) {
            mostrarDetallesSolicitud(solicitud);
        },
        error: function() {
            mostrarAlerta('error', 'Error al cargar los detalles de la solicitud');
        }
    });
});

/*=============================================
MOSTRAR DETALLES EN MODAL (CONTINUACIÓN)
=============================================*/
function mostrarDetallesSolicitud(solicitud) {
    
    // Información básica
    $('#modalNumeroSolicitud').text(solicitud.numero_solicitud);
    $('#modalSucursal').text(solicitud.nombre_sucursal_solicitante);
    $('#modalUsuario').text(solicitud.nombre_usuario_solicitante);
    
    // Tipo de solicitud
    var tipoTexto = solicitud.tipo_solicitud === 'stock' ? 'Por Stock' : 'Por Remisión';
    $('#modalTipo').html('<i class="fa fa-' + (solicitud.tipo_solicitud === 'stock' ? 'cubes' : 'file-text-o') + '"></i> ' + tipoTexto);
    
    // Estado
    var estadoClass = '';
    switch(solicitud.estado) {
        case 'pendiente': estadoClass = 'label-warning'; break;
        case 'aprobado': estadoClass = 'label-success'; break;
        case 'cancelado': estadoClass = 'label-danger'; break;
    }
    $('#modalEstado').html('<span class="label ' + estadoClass + '">' + solicitud.estado.charAt(0).toUpperCase() + solicitud.estado.slice(1) + '</span>');
    
    // Fechas
    var fechaSolicitud = new Date(solicitud.fecha_solicitud).toLocaleString();
    $('#modalFechaSolicitud').text(fechaSolicitud);
    
    var aprobadoPor = solicitud.nombre_usuario_aprobacion || 'N/A';
    var fechaAprobacion = solicitud.fecha_aprobacion ? new Date(solicitud.fecha_aprobacion).toLocaleString() : 'N/A';
    $('#modalAprobadoPor').text(aprobadoPor);
    $('#modalFechaAprobacion').text(fechaAprobacion);
    
    // Información de remisión
    if(solicitud.tipo_solicitud === 'remision' && solicitud.codigo_remision) {
        $('#modalCodigoRemision').text(solicitud.codigo_remision);
        $('#modalClienteRemision').text(solicitud.nombre_cliente_remision || 'N/A');
        $('#infoRemision').show();
    } else {
        $('#infoRemision').hide();
    }
    
    // Detalle adicional
    if(solicitud.detalle_adicional && solicitud.detalle_adicional.trim() !== '') {
        $('#modalDetalleTexto').text(solicitud.detalle_adicional);
        $('#detalleAdicional').show();
    } else {
        $('#detalleAdicional').hide();
    }
    
    // Productos solicitados
    mostrarProductosEnModal(solicitud.productos_solicitados);
    
    // Mostrar/ocultar botones según estado
    if(solicitud.estado === 'pendiente') {
        $('#botonesAccionModal').show();
        $('#btnAprobarModal').data('solicitud-id', solicitud.id);
        $('#btnCancelarModal').data('solicitud-id', solicitud.id);
    } else {
        $('#botonesAccionModal').hide();
    }
}

/*=============================================
MOSTRAR PRODUCTOS EN MODAL
=============================================*/
function mostrarProductosEnModal(productosJson) {
    
    try {
        var productos = JSON.parse(productosJson);
        var html = '';
        
        if(productos.length === 0) {
            html = '<tr><td colspan="4" class="text-center text-muted">No hay productos en esta solicitud</td></tr>';
        } else {
            productos.forEach(function(producto) {
                html += '<tr>' +
                       '<td>' + (producto.codigo || 'N/A') + '</td>' +
                       '<td>' + producto.descripcion + '</td>' +
                       '<td class="text-center"><span class="badge bg-blue">' + producto.cantidad + '</span></td>' +
                       '<td>' + (producto.observacion || '-') + '</td>' +
                       '</tr>';
            });
        }
        
        $('#modalProductosLista').html(html);
        
    } catch(e) {
        console.error('Error al parsear productos:', e);
        $('#modalProductosLista').html('<tr><td colspan="4" class="text-center text-danger">Error al cargar productos</td></tr>');
    }
}

/*=============================================
APROBAR SOLICITUD DESDE MODAL
=============================================*/
$(document).on('click', '#btnAprobarModal', function() {
    var solicitudId = $(this).data('solicitud-id');
    if(solicitudId) {
        confirmarAprobacion(solicitudId);
    }
});

/*=============================================
CANCELAR SOLICITUD DESDE MODAL
=============================================*/
$(document).on('click', '#btnCancelarModal', function() {
    var solicitudId = $(this).data('solicitud-id');
    if(solicitudId) {
        confirmarCancelacion(solicitudId);
    }
});

/*=============================================
APROBAR SOLICITUD DESDE TABLA
=============================================*/
$(document).on('click', '.btnAprobarSolicitud', function() {
    var solicitudId = $(this).attr('idSolicitud');
    confirmarAprobacion(solicitudId);
});

/*=============================================
CANCELAR SOLICITUD DESDE TABLA
=============================================*/
$(document).on('click', '.btnCancelarSolicitud', function() {
    var solicitudId = $(this).attr('idSolicitud');
    confirmarCancelacion(solicitudId);
});

/*=============================================
CONFIRMAR APROBACIÓN
=============================================*/
function confirmarAprobacion(solicitudId) {
    
    swal({
        title: '¿Aprobar esta solicitud?',
        text: "La solicitud será marcada como aprobada y se notificará al solicitante",
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar',
        input: 'textarea',
        inputPlaceholder: 'Observaciones de aprobación (opcional)...',
        inputAttributes: {
            'aria-label': 'Observaciones de aprobación'
        }
    }).then((result) => {
        if (result.value !== undefined) {
            procesarAprobacion(solicitudId, result.value || '');
        }
    });
}

/*=============================================
CONFIRMAR CANCELACIÓN
=============================================*/
function confirmarCancelacion(solicitudId) {
    
    swal({
        title: '¿Cancelar esta solicitud?',
        text: "La solicitud será marcada como cancelada",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No cancelar',
        input: 'textarea',
        inputPlaceholder: 'Motivo de cancelación (opcional)...',
        inputAttributes: {
            'aria-label': 'Motivo de cancelación'
        }
    }).then((result) => {
        if (result.value !== undefined) {
            procesarCancelacion(solicitudId, result.value || '');
        }
    });
}

/*=============================================
PROCESAR APROBACIÓN
=============================================*/
function procesarAprobacion(solicitudId, observaciones) {
    
    var datos = new FormData();
    datos.append('idSolicitudAprobar', solicitudId);
    datos.append('observaciones_aprobacion', observaciones);
    
    $.ajax({
        url: 'index.php?ruta=solicitudes-stock',
        method: 'POST',
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            $('#modalVerSolicitud').modal('hide');
            window.location.reload();
        },
        error: function() {
            mostrarAlerta('error', 'Error al procesar la aprobación');
        }
    });
}

/*=============================================
PROCESAR CANCELACIÓN
=============================================*/
function procesarCancelacion(solicitudId, motivo) {
    
    var datos = new FormData();
    datos.append('idSolicitudCancelar', solicitudId);
    datos.append('motivo_cancelacion', motivo);
    
    $.ajax({
        url: 'index.php?ruta=solicitudes-stock',
        method: 'POST',
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            $('#modalVerSolicitud').modal('hide');
            window.location.reload();
        },
        error: function() {
            mostrarAlerta('error', 'Error al procesar la cancelación');
        }
    });
}

/*=============================================
ELIMINAR SOLICITUD
=============================================*/
$(document).on('click', '.btnEliminarSolicitud', function() {
    
    var solicitudId = $(this).attr('idSolicitud');
    
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
            window.location = 'index.php?ruta=solicitudes-stock&idSolicitud=' + solicitudId;
        }
    });
});

/*=============================================
IMPRIMIR SOLICITUD
=============================================*/
function imprimirSolicitud() {
    
    if(!solicitudActualId) {
        mostrarAlerta('warning', 'No hay solicitud seleccionada para imprimir');
        return;
    }
    
    // Crear ventana de impresión
    var ventanaImpresion = window.open('', '_blank', 'width=800,height=600');
    
    // Obtener contenido del modal
    var contenido = $('#modalVerSolicitud .modal-body').clone();
    
    // Remover botones de acción
    contenido.find('#botonesAccionModal').remove();
    
    // Estructura HTML para impresión
    var htmlImpresion = `
    <!DOCTYPE html>
    <html>
    <head>
        <title>Solicitud de Stock</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #3c8dbc; border-bottom: 2px solid #3c8dbc; padding-bottom: 10px; }
            .row { display: flex; margin-bottom: 15px; }
            .col-md-6 { flex: 1; padding-right: 15px; }
            strong { color: #333; }
            .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .table th { background-color: #f8f9fa; font-weight: bold; }
            .label { padding: 2px 6px; border-radius: 3px; color: white; font-size: 11px; }
            .label-warning { background-color: #f39c12; }
            .label-success { background-color: #00a65a; }
            .label-danger { background-color: #dd4b39; }
            .well { background-color: #f5f5f5; border: 1px solid #e3e3e3; border-radius: 4px; padding: 15px; }
            .badge { background-color: #3c8dbc; color: white; padding: 2px 6px; border-radius: 3px; font-size: 12px; }
            .text-center { text-align: center; }
            hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }
            .header-info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            @media print { 
                body { margin: 0; } 
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <h1><i class="fa fa-cubes"></i> Solicitud de Stock</h1>
        <div class="header-info">
            ${contenido.html()}
        </div>
        <div class="no-print" style="text-align: center; margin-top: 30px;">
            <button onclick="window.print()" style="background-color: #3c8dbc; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
                Imprimir
            </button>
            <button onclick="window.close()" style="background-color: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                Cerrar
            </button>
        </div>
    </body>
    </html>
    `;
    
    ventanaImpresion.document.write(htmlImpresion);
    ventanaImpresion.document.close();
}

/*=============================================
EXPORTAR A EXCEL
=============================================*/
function exportarExcel() {
    
    if(!solicitudActualId) {
        mostrarAlerta('warning', 'No hay solicitud seleccionada para exportar');
        return;
    }
    
    // Obtener datos de la solicitud
    $.ajax({
        url: 'ajax/solicitudes-stock.ajax.php',
        type: 'POST',
        data: { idSolicitud: solicitudActualId },
        dataType: 'json',
        success: function(solicitud) {
            generarArchivoExcel(solicitud);
        },
        error: function() {
            mostrarAlerta('error', 'Error al obtener datos para exportar');
        }
    });
}

/*=============================================
GENERAR ARCHIVO EXCEL
=============================================*/
function generarArchivoExcel(solicitud) {
    
    try {
        var productos = JSON.parse(solicitud.productos_solicitados);
        
        // Crear contenido CSV
        var csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "SOLICITUD DE STOCK\n\n";
        csvContent += "Número de Solicitud," + solicitud.numero_solicitud + "\n";
        csvContent += "Sucursal Solicitante," + solicitud.nombre_sucursal_solicitante + "\n";
        csvContent += "Usuario," + solicitud.nombre_usuario_solicitante + "\n";
        csvContent += "Tipo," + (solicitud.tipo_solicitud === 'stock' ? 'Por Stock' : 'Por Remisión') + "\n";
        csvContent += "Estado," + solicitud.estado + "\n";
        csvContent += "Fecha Solicitud," + new Date(solicitud.fecha_solicitud).toLocaleString() + "\n";
        
        if(solicitud.codigo_remision) {
            csvContent += "Remisión," + solicitud.codigo_remision + "\n";
            csvContent += "Cliente," + (solicitud.nombre_cliente_remision || 'N/A') + "\n";
        }
        
        if(solicitud.detalle_adicional) {
            csvContent += "Detalle Adicional," + solicitud.detalle_adicional + "\n";
        }
        
        csvContent += "\nPRODUCTOS SOLICITADOS\n";
        csvContent += "Código,Descripción,Cantidad,Observación\n";
        
        productos.forEach(function(producto) {
            csvContent += '"' + (producto.codigo || 'N/A') + '","' + 
                         producto.descripcion + '","' + 
                         producto.cantidad + '","' + 
                         (producto.observacion || '') + '"\n';
        });
        
        // Crear y descargar archivo
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "solicitud_" + solicitud.numero_solicitud + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        mostrarAlerta('success', 'Archivo exportado correctamente');
        
    } catch(e) {
        console.error('Error al generar Excel:', e);
        mostrarAlerta('error', 'Error al generar el archivo');
    }
}

/*=============================================
RECARGAR TABLA DE SOLICITUDES
=============================================*/
function recargarTablaSolicitudes() {
    if($('.tablaSolicitudesStock').length > 0) {
        $('.tablaSolicitudesStock').DataTable().ajax.reload();
    }
}

/*=============================================
VALIDACIONES DE FORMULARIO
=============================================*/
$(document).on('keypress', '#cantidadProductoModal', function(e) {
    // Solo permitir números
    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
        return false;
    }
});

$(document).on('input', '#cantidadProductoModal', function() {
    var cantidad = parseInt($(this).val());
    var stock = parseInt($(this).attr('max'));
    
    if(cantidad > stock) {
        $(this).val(stock);
        mostrarAlerta('warning', 'La cantidad no puede ser mayor al stock disponible');
    }
    
    if(cantidad <= 0) {
        $(this).val(1);
    }
});

/*=============================================
EVENTOS DE TECLADO
=============================================*/
$(document).on('keypress', '#buscarRemision', function(e) {
    if(e.which === 13) { // Enter
        e.preventDefault();
        $('#btnBuscarRemision').click();
    }
});

$(document).on('keypress', '#cantidadProductoModal', function(e) {
    if(e.which === 13) { // Enter
        e.preventDefault();
        $('#confirmarAgregarProducto').click();
    }
});

/*=============================================
CLICK FUERA PARA CERRAR RESULTADOS
=============================================*/
$(document).on('click', function(e) {
    if(!$(e.target).closest('#buscarRemision, #resultadosRemision').length) {
        $('#resultadosRemision').hide();
    }
});