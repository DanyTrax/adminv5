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

    $('#btnBuscarRemision').click(function() {
        var busqueda = $('#buscarRemision').val().trim();
        if(busqueda.length >= 2) {
            buscarRemisiones(busqueda);
        } else {
            mostrarAlerta('warning', 'Ingrese al menos 2 caracteres para buscar');
        }
    });

    // Envío del formulario
    $('.formularioCrearSolicitud').submit(function(e) {
        e.preventDefault();
        
        if(productosSeleccionados.length === 0) {
            mostrarAlerta('warning', 'Debe seleccionar al menos un producto');
            return false;
        }

        // Confirmar antes de crear
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

});

/*=============================================
CREAR SOLICITUD
=============================================*/
function crearSolicitud() {
    
    // Actualizar campo hidden con productos
    $('#productosJsonInput').val(JSON.stringify(productosSeleccionados));
    
    // Crear FormData
    var formData = new FormData($('.formularioCrearSolicitud')[0]);
    
    // Deshabilitar botón
    $('#btnCrearSolicitudFinal').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creando...');
    
    $.ajax({
        url: 'index.php?ruta=crear-solicitud-stock',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Redireccionar a la lista de solicitudes
            window.location = 'solicitudes-stock';
        },
        error: function(xhr, status, error) {
            console.log('Error al crear solicitud:', error);
            mostrarAlerta('error', 'Error al crear la solicitud. Intente nuevamente.');
            $('#btnCrearSolicitudFinal').prop('disabled', false).html('<i class="fa fa-save"></i> Crear Solicitud');
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
        mostrarAlerta('warning', 'Este producto ya está en la lista. Si desea cambiar la cantidad, elimínelo primero.');
        return;
    }

    // Agregar producto
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

    // Cerrar modales
    $('#modalCantidadProducto').modal('hide');
    $('#modalCatalogoProductos').modal('hide');

    mostrarAlerta('success', 'Producto agregado: ' + producto.codigo + ' (Cantidad: ' + cantidad + ')');
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
VALIDACIONES DE INPUT
=============================================*/
$(document).on('input', '#cantidadProductoModal', function() {
    var valor = $(this).val().replace(/[^0-9]/g, '');
    $(this).val(valor);
    
    if(parseInt(valor) > 9999) {
        $(this).val(9999);
    }
});

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