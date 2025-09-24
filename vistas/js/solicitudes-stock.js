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
            console.log('Error al crear solicitud:', error);
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

$(document).on('keydown', '#cantidadProductoModal', function(e) {
    // Permitir: backspace, delete, tab, escape, enter
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
        (e.keyCode === 65 && e.ctrlKey === true) || // Ctrl+A
        (e.keyCode === 67 && e.ctrlKey === true) || // Ctrl+C
        (e.keyCode === 86 && e.ctrlKey === true) || // Ctrl+V
        (e.keyCode === 88 && e.ctrlKey === true) || // Ctrl+X
        (e.keyCode >= 35 && e.keyCode <= 39)) { // home, end, left, right
        return;
    }
    // Solo números
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
});

/*=============================================
VER DETALLES DE SOLICITUD
=============================================*/
$(document).on('click', '.btnVerSolicitud', function() {
    
    var idSolicitud = $(this).attr('idSolicitud');
    console.log("✅ Ver solicitud ID:", idSolicitud);
    
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
    console.log("✅ Aprobar solicitud ID:", idSolicitud);
    
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
    console.log("✅ Cancelar solicitud ID:", idSolicitud);
    
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
    console.log("✅ Eliminar solicitud ID:", idSolicitud);
    
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
    
    console.log("=== DATOS RECIBIDOS EN MODAL ===");
    console.log(solicitud);
    
    // ✅ VERIFICAR QUE EXISTAN LOS ELEMENTOS DEL MODAL
    if($('#modalVerSolicitud').length === 0) {
        console.error("El modal #modalVerSolicitud no existe en el DOM");
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
        
        // ✅ CONFIGURAR BOTONES DE EXPORTACIÓN
        configurarBotonesExportacion(solicitud);
        
        // ✅ MOSTRAR MODAL
        $('#modalVerSolicitud').modal('show');
        
        console.log("✅ Modal cargado correctamente");
        
    } catch(error) {
        console.error("Error cargando datos en modal:", error);
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
    
    console.log("=== CARGANDO PRODUCTOS ===");
    console.log("JSON recibido:", productosJson);
    
    try {
        var productos = [];
        
        // ✅ PARSEAR JSON si es string
        if(typeof productosJson === 'string') {
            productos = JSON.parse(productosJson);
        } else if(Array.isArray(productosJson)) {
            productos = productosJson;
        } else {
            console.error("Formato de productos no reconocido:", typeof productosJson);
            productos = [];
        }
        
        console.log("Productos parseados:", productos);
        
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
        
        console.log("✅ Productos cargados en tabla");
        
    } catch(e) {
        console.error('Error cargando productos:', e);
        $('#productosModalBody').html('<tr><td colspan="5" class="text-center text-danger">Error cargando productos: ' + e.message + '</td></tr>');
    }
}

/*=============================================
CARGAR HISTORIAL EN EL MODAL - VERSIÓN CORREGIDA
=============================================*/
function cargarHistorialEnModal(solicitud) {
    
    console.log("=== CARGANDO HISTORIAL ===");
    
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
        
        console.log("✅ Historial cargado");
        
    } catch(error) {
        console.error("Error cargando historial:", error);
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
        console.error('Error formateando fecha:', error);
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
        console.error('Error formateando hora:', error);
        return '--:--';
    }
}

/*=============================================
CONFIGURAR BOTONES DE EXPORTACIÓN - SIMPLIFICADO PARA PRUEBAS
=============================================*/
function configurarBotonesExportacion(solicitud) {
    
    // ✅ BOTÓN PDF - Por ahora solo mostrar alert
    $('#btnExportarPDF').off('click').on('click', function(e) {
        e.preventDefault();
        swal({
            title: 'Exportar PDF',
            text: 'Funcionalidad de PDF en desarrollo para solicitud: ' + solicitud.numero_solicitud,
            type: 'info',
            confirmButtonText: 'Entendido'
        });
    });
    
    // ✅ BOTÓN EXCEL - Por ahora solo mostrar alert
    $('#btnExportarExcel').off('click').on('click', function(e) {
        e.preventDefault();
        swal({
            title: 'Exportar Excel',
            text: 'Funcionalidad de Excel en desarrollo para solicitud: ' + solicitud.numero_solicitud,
            type: 'info',
            confirmButtonText: 'Entendido'
        });
    });
    
    console.log("✅ Botones de exportación configurados");
}

/*=============================================
EXPORTAR SOLICITUD A PDF
=============================================*/
function exportarSolicitudPDF(solicitud) {
    
    var ventana = window.open(
        'extensiones/tcpdf/pdf/solicitud-stock.php?id=' + solicitud.id,
        '_blank',
        'width=800,height=600'
    );
    
    if(!ventana) {
        swal({
            title: 'Popup bloqueado',
            text: 'Permita ventanas emergentes para descargar el PDF',
            type: 'warning',
            confirmButtonText: 'Entendido'
        });
    }
}

/*=============================================
EXPORTAR SOLICITUD A EXCEL
=============================================*/
function exportarSolicitudExcel(solicitud) {
    
    // ✅ CREAR DATOS PARA EXCEL
    var productos = JSON.parse(solicitud.productos_solicitados);
    
    var datosExcel = [];
    
    // Encabezados
    datosExcel.push([
        'SOLICITUD DE STOCK - ' + solicitud.numero_solicitud,
        '', '', '', ''
    ]);
    datosExcel.push(['']); // Línea vacía
    
    // Información general
    datosExcel.push(['Sucursal:', solicitud.nombre_sucursal_solicitante, '', '', '']);
    datosExcel.push(['Solicitante:', solicitud.nombre_usuario_solicitante, '', '', '']);
    datosExcel.push(['Fecha:', formatearFecha(solicitud.fecha_solicitud), '', '', '']);
    datosExcel.push(['Tipo:', solicitud.tipo_solicitud.toUpperCase(), '', '', '']);
    datosExcel.push(['Estado:', solicitud.estado.toUpperCase(), '', '', '']);
    datosExcel.push(['']); // Línea vacía
    
    // Encabezados de productos
    datosExcel.push(['#', 'CÓDIGO', 'DESCRIPCIÓN', 'CANTIDAD', 'OBSERVACIONES']);
    
    // Productos
    productos.forEach(function(producto, index) {
        datosExcel.push([
            index + 1,
            producto.codigo || '',
            producto.descripcion || '',
            producto.cantidad || 0,
            producto.observacion || ''
        ]);
    });
    
    // Total
    datosExcel.push(['']); // Línea vacía
    datosExcel.push([
        'TOTAL PRODUCTOS:',
        solicitud.total_productos,
        'TOTAL CANTIDAD:',
        solicitud.total_cantidad,
        ''
    ]);
    
    // ✅ GENERAR Y DESCARGAR EXCEL
    var ws = XLSX.utils.aoa_to_sheet(datosExcel);
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Solicitud " + solicitud.numero_solicitud);
    
    // Descargar archivo
    XLSX.writeFile(wb, 'Solicitud_' + solicitud.numero_solicitud + '.xlsx');
}