
$(document).ready(function() {
// Variables globales
    var tablaCategorias;
    var soloActivas = false;

    // Inicializar interfaz
    inicializarInterfaz();

    // Event listeners
    $('.btnCrearCategoriaCentral').click(function() {
        mostrarModalCrear();
    });

    $('.btnSincronizarCategorias').click(function() {
mostrarModalSincronizacion();
    });

    // Verificar que el botón existe en el DOM

if ($('.btnSincronizarCategorias').length === 0) {
} else {
}

    $('.btnFiltrarActivas').click(function() {
        soloActivas = true;
        $(this).addClass('btn-primary').removeClass('btn-default');
        $('.btnFiltrarTodas').addClass('btn-default').removeClass('btn-primary');
        cargarCategorias();
    });

    $('.btnFiltrarTodas').click(function() {
        soloActivas = false;
        $(this).addClass('btn-primary').removeClass('btn-default');
        $('.btnFiltrarActivas').addClass('btn-default').removeClass('btn-primary');
        cargarCategorias();
    });

    // Formulario crear categoría
    $('#formCrearCategoriaCentral').on('submit', function(e) {
        e.preventDefault();
        crearCategoria();
    });

    // Formulario editar categoría
    $('#formEditarCategoriaCentral').on('submit', function(e) {
        e.preventDefault();
        editarCategoria();
    });

    // Confirmar eliminación
    $('#btnConfirmarEliminacionCategoria').click(function() {
        eliminarCategoria();
    });

    // Confirmar sincronización
    $('#btnConfirmarSincronizacion').click(function() {
        ejecutarSincronizacion();
    });

    // Delegación de eventos para botones dinámicos
    $(document).on('click', '.btnEditarCategoriaCentral', function() {
        var idCategoria = $(this).attr('idCategoria');
        var categoria = $(this).attr('categoria');
        mostrarModalEditar(idCategoria, categoria);
    });

    $(document).on('click', '.btnEliminarCategoriaCentral', function() {
        var idCategoria = $(this).attr('idCategoria');
        var nombreCategoria = $(this).attr('nombreCategoria');
        mostrarModalEliminar(idCategoria, nombreCategoria);
    });

    /*=============================================
    INICIALIZAR INTERFAZ
    =============================================*/
    function inicializarInterfaz() {
        cargarCategorias();
        $('.btnFiltrarTodas').addClass('btn-primary').removeClass('btn-default');
    }

    /*=============================================
    CARGAR CATEGORÍAS
    =============================================*/
    function cargarCategorias() {
        $.ajax({
            url: "ajax/categorias-original.ajax.php",
            method: "POST",
            data: {
                accion: "obtener",
                soloActivas: soloActivas
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    mostrarCategorias(respuesta.data);
                } else {
}
            },
            error: function(xhr, status, error) {
}
        });
    }

    /*=============================================
    MOSTRAR CATEGORÍAS EN TABLA
    =============================================*/
    function mostrarCategorias(categorias) {
        var tbody = $('.tablaCategoriasCentral tbody');
        tbody.empty();

        if (categorias.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center">No hay categorías disponibles</td></tr>');
            return;
        }

        categorias.forEach(function(categoria, index) {
            var fechaCreacion = new Date(categoria.fecha).toLocaleDateString('es-ES');

            var acciones = '<div class="btn-group">' +
                '<button class="btn btn-warning btn-xs btnEditarCategoriaCentral" ' +
                'idCategoria="' + categoria.id + '" ' +
                'categoria="' + categoria.categoria + '" ' +
                'title="Editar categoría">' +
                '<i class="fa fa-pencil"></i>' +
                '</button>' +
                '<button class="btn btn-danger btn-xs btnEliminarCategoriaCentral" ' +
                'idCategoria="' + categoria.id + '" ' +
                'nombreCategoria="' + categoria.categoria + '" ' +
                'title="Eliminar categoría">' +
                '<i class="fa fa-trash"></i>' +
                '</button>' +
                '</div>';

            var fila = '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td><strong>' + categoria.categoria + '</strong></td>' +
                '<td>' + fechaCreacion + '</td>' +
                '<td>' + acciones + '</td>' +
                '</tr>';

            tbody.append(fila);
        });
    }

    /*=============================================
    MOSTRAR MODAL CREAR
    =============================================*/
    function mostrarModalCrear() {
        $('#formCrearCategoriaCentral')[0].reset();
        $('#error-categoria').text('');
        $('#modalCrearCategoriaCentral').modal('show');
    }

    /*=============================================
    MOSTRAR MODAL EDITAR
    =============================================*/
    function mostrarModalEditar(idCategoria, categoria) {
        $('#idCategoriaEditar').val(idCategoria);
        $('#categoriaEditar').val(categoria);
        $('#error-categoria-editar').text('');
        $('#modalEditarCategoriaCentral').modal('show');
    }

    /*=============================================
    MOSTRAR MODAL ELIMINAR
    =============================================*/
    function mostrarModalEliminar(idCategoria, nombreCategoria) {
        $('#nombreCategoriaEliminar').text(nombreCategoria);
        $('#btnConfirmarEliminacionCategoria').attr('idCategoria', idCategoria);
        $('#modalConfirmarEliminacionCategoria').modal('show');
    }

    /*=============================================
    CREAR CATEGORÍA
    =============================================*/
    function crearCategoria() {
        var formData = new FormData($('#formCrearCategoriaCentral')[0]);
        formData.append('accion', 'crear');

        $.ajax({
            url: "ajax/categorias-original.ajax.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    $('#modalCrearCategoriaCentral').modal('hide');
                    mostrarSweetAlert('success', 'Éxito', respuesta.message);
                    cargarCategorias();
                } else {
                    $('#error-categoria').text(respuesta.message);
                }
            },
            error: function(xhr, status, error) {
                mostrarSweetAlert('error', 'Error', 'Error al crear la categoría');
            }
        });
    }

    /*=============================================
    EDITAR CATEGORÍA
    =============================================*/
    function editarCategoria() {
        var formData = new FormData($('#formEditarCategoriaCentral')[0]);
        formData.append('accion', 'editar');

        $.ajax({
            url: "ajax/categorias-original.ajax.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    $('#modalEditarCategoriaCentral').modal('hide');
                    mostrarSweetAlert('success', 'Éxito', respuesta.message);
                    cargarCategorias();
                } else {
                    $('#error-categoria-editar').text(respuesta.message);
                }
            },
            error: function(xhr, status, error) {
                mostrarSweetAlert('error', 'Error', 'Error al editar la categoría');
            }
        });
    }

    /*=============================================
    ELIMINAR CATEGORÍA
    =============================================*/
    function eliminarCategoria() {
        var idCategoria = $('#btnConfirmarEliminacionCategoria').attr('idCategoria');

        $.ajax({
            url: "ajax/categorias-original.ajax.php",
            method: "POST",
            data: {
                accion: "eliminar",
                id: idCategoria
            },
            dataType: "json",
            success: function(respuesta) {
                $('#modalConfirmarEliminacionCategoria').modal('hide');
                if (respuesta.success) {
                    mostrarSweetAlert('success', 'Éxito', respuesta.message);
                } else {
                    mostrarSweetAlert('error', 'Error', respuesta.message);
                }
                cargarCategorias();
            },
            error: function(xhr, status, error) {
                $('#modalConfirmarEliminacionCategoria').modal('hide');
                mostrarSweetAlert('error', 'Error', 'Error al eliminar la categoría');
            }
        });
    }

    /*=============================================
    MOSTRAR MODAL SINCRONIZACIÓN
    =============================================*/
    function mostrarModalSincronizacion() {
// Resetear modal
        $('#info-sincronizacion').hide();
        $('#resultado-sincronizacion').hide();
        $('#btnConfirmarSincronizacion').show().html('<i class="fa fa-refresh"></i> Sí, Sincronizar');

        // Mostrar modal
        $('#modalSincronizarCategorias').modal('show');
    }

    /*=============================================
    EJECUTAR SINCRONIZACIÓN
    =============================================*/
    function ejecutarSincronizacion() {
// Mostrar loading en modal
        $('#info-sincronizacion').show();
        $('#btnConfirmarSincronizacion').hide();
$.ajax({
                    url: "ajax/categorias-original.ajax.php",
                    method: "POST",
                    data: {
                        accion: "sincronizar"
                    },
            dataType: "json",
            timeout: 30000, // 30 segundos timeout
            success: function(respuesta) {
if (respuesta && respuesta.success) {
                    // Mostrar resultado exitoso
                    $('#info-sincronizacion').hide();
                    $('#resultado-sincronizacion').show();
                    $('#detalles-sincronizacion').html(
                        '<p>' + respuesta.message + '</p>' +
                        '<p><strong>Sucursales sincronizadas:</strong> ' + respuesta.sucursales_sincronizadas + '/' + respuesta.total_sucursales + '</p>'
                    );

                    // Cambiar botón a "Cerrar"
                    $('#btnConfirmarSincronizacion').show().html('<i class="fa fa-check"></i> Cerrar').removeClass('btn-success').addClass('btn-primary');

                    // Recargar categorías
                    cargarCategorias();

                    // Auto-cerrar después de 3 segundos
                    setTimeout(function() {
                        $('#modalSincronizarCategorias').modal('hide');
                    }, 3000);

                } else {
mostrarErrorSincronizacion(respuesta ? respuesta.message : "Respuesta inválida");
                }
            },
            error: function(xhr, status, error) {
let mensajeError = "Error al sincronizar categorías";
                if (xhr.status === 0) {
                    mensajeError = "Error de conexión. Verifica tu conexión a internet.";
                } else if (xhr.status === 404) {
                    mensajeError = "Archivo no encontrado. Verifica la ruta del AJAX.";
                } else if (xhr.status === 500) {
                    mensajeError = "Error del servidor. Revisa los logs.";
                }

                mostrarErrorSincronizacion(mensajeError);
            }
        });
    }

    /*=============================================
    MOSTRAR ERROR EN SINCRONIZACIÓN
    =============================================*/
    function mostrarErrorSincronizacion(mensaje) {
        $('#info-sincronizacion').hide();
        $('#resultado-sincronizacion').show();
        $('#resultado-sincronizacion .alert').removeClass('alert-success').addClass('alert-danger');
        $('#resultado-sincronizacion .fa').removeClass('fa-check-circle').addClass('fa-exclamation-circle');
        $('#resultado-sincronizacion strong').text('Error en Sincronización');
        $('#detalles-sincronizacion').html('<p>' + mensaje + '</p>');

        // Cambiar botón a "Cerrar"
        $('#btnConfirmarSincronizacion').show().html('<i class="fa fa-times"></i> Cerrar').removeClass('btn-success').addClass('btn-danger');
    }

    /*=============================================
    MOSTRAR SWEET ALERT
    =============================================*/
    function mostrarSweetAlert(tipo, titulo, mensaje) {
        swal({
            title: titulo,
            text: mensaje,
            type: tipo,
            confirmButtonText: "Aceptar"
        });
    }
});
