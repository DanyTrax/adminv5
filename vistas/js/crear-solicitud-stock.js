/*=============================================
VARIABLES GLOBALES
=============================================*/
var productosSeleccionados = [];

/*=============================================
DOCUMENT READY
=============================================*/
$(document).ready(function() {

    // Inicializar DataTable de productos
    if($('.tablaProductosCatalogo').length > 0) {
        $('.tablaProductosCatalogo').DataTable({
            "ajax": "ajax/datatable-productos-catalogo.ajax.php",
            "deferRender": true,
            "retrieve": true,
            "processing": true,
            "language": {
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
            },
            "columnDefs": [
                { "orderable": false, "targets": [0, 4] },
                { "width": "60px", "targets": 0 },
                { "width": "80px", "targets": 3 },
                { "width": "80px", "targets": 4 }
            ]
        });
    }

    // ✅ VALIDAR TIPO DE SOLICITUD EN TIEMPO REAL
    $('input[name="tipo_solicitud"]').change(function() {
        $('#errorTipoSolicitud').hide();

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

    $('#btnBuscarRemision').click(function() {
        var busqueda = $('#buscarRemision').val().trim();
        if(busqueda.length >= 2) {
            buscarRemisiones(busqueda);
        } else {
            mostrarAlerta('warning', 'Ingrese al menos 2 caracteres para buscar');
        }
    });

    // ✅ VALIDAR Y CREAR SOLICITUD
    $('#btnCrearSolicitudFinal').click(function(e) {
        e.preventDefault();

        // ✅ VALIDAR TIPO DE SOLICITUD OBLIGATORIO
        if(!$('input[name="tipo_solicitud"]:checked').length) {
            $('#errorTipoSolicitud').show();
            mostrarModalValidacion(
                'Tipo de Solicitud Requerido',

                'Debe seleccionar un tipo de solicitud antes de continuar. Por favor, elija entre "Por Stock" o "Por Remisión".',

                'warning'
            );
            $('html, body').animate({
                scrollTop: $('input[name="tipo_solicitud"]').first().offset().top - 100
            }, 500);
            return false;
        }

        // ✅ VALIDAR QUE HAYA PRODUCTOS
        if(productosSeleccionados.length === 0) {
            mostrarModalValidacion(
                'Productos Requeridos',

                'Debe agregar al menos un producto a la solicitud antes de continuar.',

                'warning'
            );
            return false;
        }

        // ✅ CONFIRMAR ANTES DE CREAR
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
                crearSolicitud();
            }
        });
    });

    // Confirmar agregar producto
    $('#confirmarAgregarProducto').click(function() {
        agregarProductoALista();
    });

    // Limpiar modal cantidad al cerrarlo
    $('#modalCantidadProducto').on('hidden.bs.modal', function() {
        limpiarModalCantidad();
    });

    // Event listener para cambiar cantidades en la lista
    $(document).on('change', '.cantidad-producto', function() {
        var index = $(this).data('index');
        var nuevaCantidad = parseInt($(this).val());

        if(isNaN(nuevaCantidad) || nuevaCantidad < 1) {
            $(this).val(productosSeleccionados[index].cantidad);
            return;
        }

        if(nuevaCantidad > 9999) {
            $(this).val(9999);
            nuevaCantidad = 9999;
        }

        productosSeleccionados[index].cantidad = nuevaCantidad;
        actualizarContadorProductos();
    });

    // Inicializar estado de botones
    actualizarEstadoBotonesAgregar();

});

/*=============================================
CREAR SOLICITUD - VERSION CORREGIDA
=============================================*/
function crearSolicitud() {

    // ✅ VALIDAR ANTES DE ENVIAR
    if(!$('input[name="tipo_solicitud"]:checked').length) {
        $('#errorTipoSolicitud').show();
        mostrarModalValidacion(
            'Tipo de Solicitud Requerido',

            'Debe seleccionar un tipo de solicitud antes de continuar. Por favor, elija entre "Por Stock" o "Por Remisión".',

            'warning'
        );
        return false;
    }

    if(productosSeleccionados.length === 0) {
        mostrarModalValidacion(
            'Productos Requeridos',

            'Debe agregar al menos un producto a la solicitud antes de continuar.',

            'warning'
        );
        return false;
    }

    // ✅ DEBUG: Ver datos antes de enviar
// ✅ ACTUALIZAR CAMPO HIDDEN CON JSON DE PRODUCTOS
    $('#productosJsonInput').val(JSON.stringify(productosSeleccionados));
// ✅ MOSTRAR LOADING
    $('#btnCrearSolicitudFinal').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creando solicitud...');

    // ✅ CREAR FormData MANUALMENTE PARA ASEGURAR QUE TODOS LOS DATOS SE ENVÍEN
    var formData = new FormData();

    // ✅ AGREGAR TODOS LOS CAMPOS MANUALMENTE
    formData.append('productos_solicitados', JSON.stringify(productosSeleccionados));
    formData.append('tipo_solicitud', $('input[name="tipo_solicitud"]:checked').val());

    // ✅ CAMPOS OPCIONALES
    var detalleAdicional = $('#detalleAdicional').val().trim();
    if(detalleAdicional) {
        formData.append('detalle_adicional', detalleAdicional);
    }

    // ✅ CAMPOS DE REMISIÓN (si aplica)
    var codigoRemision = $('#codigoRemisionSeleccionada').val();
    var nombreClienteRemision = $('#nombreClienteRemision').val();

    if(codigoRemision) {
        formData.append('codigo_remision', codigoRemision);
    }

    if(nombreClienteRemision) {
        formData.append('nombre_cliente_remision', nombreClienteRemision);
    }

    // ✅ DEBUG: Ver todos los datos que se envían
for (var pair of formData.entries()) {
}

    // ✅ ENVIAR VIA AJAX
    $.ajax({
        url: window.location.href, // Enviar a la misma página
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {

// ✅ RESETEAR BOTÓN PRIMERO
            $('#btnCrearSolicitudFinal').prop('disabled', false).html('<i class="fa fa-save"></i> Crear Solicitud');

            // ✅ BUSCAR SWEETALERT EN LA RESPUESTA
            if(response.indexOf('swal') > -1) {

                if(response.indexOf('success') > -1) {
swal({
                        title: '¡Solicitud creada!',
                        text: 'La solicitud se ha creado correctamente',
                        type: 'success',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function(result) {
                        window.location.href = 'solicitudes-stock';
                    });

                } else if(response.indexOf('error') > -1) {
swal({
                        title: 'Error',
                        text: 'Error al crear la solicitud. Revise los datos.',
                        type: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                }

            } else {

// ✅ MOSTRAR PARTE DE LA RESPUESTA PARA DEBUG
                if(response.trim() === '') {
                    mostrarAlerta('error', 'Respuesta vacía del servidor. Verifique los logs.');
                } else {
                    // ✅ ASUMIR ÉXITO SI NO HAY ERROR OBVIO
                    swal({
                        title: '¡Solicitud enviada!',
                        text: 'La solicitud se ha procesado. Verificando...',
                        type: 'info',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location.href = 'solicitudes-stock';
                    });
                }
            }
        },
        error: function(xhr, status, error) {
$('#btnCrearSolicitudFinal').prop('disabled', false).html('<i class="fa fa-save"></i> Crear Solicitud');

            swal({
                title: 'Error de conexión',
                text: 'Error de conexión: ' + error,
                type: 'error',
                confirmButtonText: 'Cerrar'
            });
        }
    });
}

/*=============================================
AGREGAR PRODUCTO DESDE CATÁLOGO
=============================================*/
$(document).on('click', '.btnAgregarProducto', function() {

    var idProducto = $(this).attr('idProducto');
    var codigoProducto = $(this).attr('codigoProducto');
    var descripcionProducto = $(this).attr('descripcionProducto');

    // Abrir modal para cantidad
    $('#nombreProductoModal').text(descripcionProducto);
    $('#cantidadProductoModal').val(1);
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

    // Validaciones
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

    // Verificar si ya está en la lista
    var yaSeleccionado = productosSeleccionados.find(p => p.codigo === producto.codigo);
    if(yaSeleccionado) {
        // Si ya está, actualizar la cantidad
        yaSeleccionado.cantidad = cantidad;
        yaSeleccionado.observacion = observacion;
    } else {
        // Si no está, agregar nuevo producto
        var nuevoProducto = {
            id: producto.id,
            codigo: producto.codigo,
            descripcion: producto.descripcion,
            cantidad: cantidad,
            observacion: observacion
        };
        productosSeleccionados.push(nuevoProducto);
    }

    // Actualizar interfaz
    actualizarListaProductosSeleccionados();
    actualizarContadorProductos();
    habilitarBotonCrear();
    actualizarEstadoBotonesAgregar();

    // Cerrar modal
    $('#modalCantidadProducto').modal('hide');
}

/*=============================================
BUSCAR REMISIONES
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
SELECCIONAR REMISIÓN
=============================================*/
$(document).on('click', '.seleccionar-remision', function(e) {
    e.preventDefault();

    var codigo = $(this).data('codigo');
    var cliente = $(this).data('cliente');

    // Solo actualizar campos informativos
    $('#buscarRemision').val('Remisión: ' + codigo + ' - ' + cliente);
    $('#codigoRemisionSeleccionada').val(codigo);
    $('#nombreClienteRemision').val(cliente);

    // Ocultar resultados
    $('#resultadosRemision').hide();

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
                   '<input type="number" class="form-control input-sm cantidad-producto" ' +
                   'value="' + producto.cantidad + '" min="1" max="9999" ' +
                   'data-index="' + index + '" style="width: 80px; display: inline-block;">' +
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
    actualizarEstadoBotonesAgregar();
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
        $('#btnCrearSolicitudFinal').prop('disabled', false);
    } else {
        $('#btnCrearSolicitudFinal').prop('disabled', true);
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

    // Agregar nueva alerta
    $('.content-wrapper').prepend(alerta);

    // Auto-remover después de 5 segundos
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

/*=============================================
MOSTRAR MODAL DE VALIDACIÓN
=============================================*/
function mostrarModalValidacion(titulo, mensaje, tipo = 'warning') {
    var icono = 'fa-warning';
    var claseBoton = 'btn-warning';

    switch(tipo) {
        case 'error':
            icono = 'fa-times-circle';
            claseBoton = 'btn-danger';
            break;
        case 'info':
            icono = 'fa-info-circle';
            claseBoton = 'btn-info';
            break;
        case 'success':
            icono = 'fa-check-circle';
            claseBoton = 'btn-success';
            break;
    }

    var modalHtml = `
        <div class="modal fade" id="modalValidacion" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header ${tipo === 'error' ? 'bg-danger' : tipo === 'success' ? 'bg-success' : 'bg-warning'}">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">
                            <i class="fa ${icono}"></i> ${titulo}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p>${mensaje}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn ${claseBoton}" data-dismiss="modal">
                            <i class="fa fa-check"></i> Entendido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remover modal existente si hay uno
    $('#modalValidacion').remove();

    // Agregar modal al body
    $('body').append(modalHtml);

    // Mostrar modal
    $('#modalValidacion').modal('show');
}

/*=============================================
VALIDACIONES DE INPUT
=============================================*/
$(document).on('input', '#cantidadProductoModal', function() {
    var valor = $(this).val().replace(/[^0-9]/g, '');
    $(this).val(valor);

    if(parseInt(valor) > 9999) {
        $(this).val(9999);
    }
});

/*=============================================
ACTUALIZAR ESTADO DE BOTONES AGREGAR
=============================================*/
function actualizarEstadoBotonesAgregar() {
    $('.btnAgregarProducto').each(function() {
        var codigoProducto = $(this).attr('codigoProducto');
        var yaSeleccionado = productosSeleccionados.find(p => p.codigo === codigoProducto);

        if(yaSeleccionado) {
            $(this).prop('disabled', true)
                   .removeClass('btn-success')
                   .addClass('btn-default')
                   .html('<i class="fa fa-check"></i> Agregado')
                   .attr('title', 'Producto ya agregado - Use la lista para cambiar cantidad');
        } else {
            $(this).prop('disabled', false)
                   .removeClass('btn-default')
                   .addClass('btn-success')
                   .html('<i class="fa fa-plus"></i> Agregar')
                   .attr('title', 'Agregar producto a la solicitud');
        }
    });
}

$(document).on('keydown', '#cantidadProductoModal', function(e) {
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
        (e.keyCode === 65 && e.ctrlKey === true) ||
        (e.keyCode === 67 && e.ctrlKey === true) ||
        (e.keyCode === 86 && e.ctrlKey === true) ||
        (e.keyCode === 88 && e.ctrlKey === true) ||
        (e.keyCode >= 35 && e.keyCode <= 39)) {
        return;
    }
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
});