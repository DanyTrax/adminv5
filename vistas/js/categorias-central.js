console.log("📋 Script categorias-central.js INICIANDO CARGA");
$(document).ready(function() {
    console.log("📋 Script categorias-central.js cargado correctamente");
    
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
        console.log("🔄 Botón de sincronización clickeado");
        sincronizarCategorias();
    });
    
    // Verificar que el botón existe en el DOM
    console.log("🔍 Verificando botón de sincronización...");
    console.log("🔍 Botones encontrados:", $('.btnSincronizarCategorias').length);
    if ($('.btnSincronizarCategorias').length === 0) {
        console.error("❌ No se encontró el botón .btnSincronizarCategorias");
    } else {
        console.log("✅ Botón .btnSincronizarCategorias encontrado");
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
    
    // Delegación de eventos para botones dinámicos
    $(document).on('click', '.btnEditarCategoriaCentral', function() {
        var idCategoria = $(this).attr('idCategoria');
        mostrarModalEditar(idCategoria);
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
            url: "ajax/categorias-central.ajax.php",
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
                    console.error("Error al cargar categorías:", respuesta.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", error);
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
            tbody.append('<tr><td colspan="7" class="text-center">No hay categorías disponibles</td></tr>');
            return;
        }
        
        categorias.forEach(function(categoria, index) {
            var estado = categoria.activo ? 
                '<span class="label label-success">Activa</span>' : 
                '<span class="label label-danger">Inactiva</span>';
                
            var sincronizado = categoria.sincronizado ? 
                '<span class="label label-success">Sí</span>' : 
                '<span class="label label-warning">No</span>';
            
            var fechaCreacion = new Date(categoria.fecha_creacion).toLocaleDateString('es-ES');
            
            var acciones = '<div class="btn-group">' +
                '<button class="btn btn-warning btn-xs btnEditarCategoriaCentral" ' +
                'idCategoria="' + categoria.id + '" title="Editar categoría">' +
                '<i class="fa fa-pencil"></i>' +
                '</button>' +
                '<button class="btn btn-danger btn-xs btnEliminarCategoriaCentral" ' +
                'idCategoria="' + categoria.id + '" ' +
                'nombreCategoria="' + categoria.categoria + '" ' +
                'title="Desactivar categoría">' +
                '<i class="fa fa-times"></i>' +
                '</button>' +
                '</div>';
            
            var fila = '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td><strong>' + categoria.categoria + '</strong></td>' +
                '<td>' + (categoria.descripcion || 'Sin descripción') + '</td>' +
                '<td>' + estado + '</td>' +
                '<td>' + sincronizado + '</td>' +
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
    function mostrarModalEditar(idCategoria) {
        $.ajax({
            url: "ajax/categorias-central.ajax.php",
            method: "POST",
            data: {
                idCategoria: idCategoria
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    var categoria = respuesta.data;
                    $('#idCategoriaEditar').val(categoria.id);
                    $('#categoriaEditar').val(categoria.categoria);
                    $('#descripcionEditar').val(categoria.descripcion || '');
                    $('#activoEditar').prop('checked', categoria.activo);
                    $('#error-categoria-editar').text('');
                    $('#modalEditarCategoriaCentral').modal('show');
                } else {
                    mostrarSweetAlert('error', 'Error', respuesta.message);
                }
            },
            error: function(xhr, status, error) {
                mostrarSweetAlert('error', 'Error', 'Error al cargar la categoría');
            }
        });
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
            url: "ajax/categorias-central.ajax.php",
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
            url: "ajax/categorias-central.ajax.php",
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
            url: "ajax/categorias-central.ajax.php",
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
    SINCRONIZAR CATEGORÍAS
    =============================================*/
    function sincronizarCategorias() {
        console.log("🔄 FUNCIÓN sincronizarCategorias() EJECUTÁNDOSE");
        console.log("🔄 Iniciando sincronización de categorías...");
        
        swal({
            title: "¿Sincronizar Categorías?",
            text: "Esto actualizará las categorías en todas las sucursales activas. ¿Continuar?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#5cb85c",
            confirmButtonText: "Sí, Sincronizar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            console.log("🔍 Resultado del SweetAlert:", result);
            if (result.value) {
                console.log("✅ Usuario confirmó sincronización");
                
                // Mostrar loading
                swal({
                    title: "Sincronizando...",
                    text: "Por favor espera mientras se sincronizan las categorías",
                    type: "info",
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onOpen: function() {
                        swal.showLoading();
                    }
                });
                
                console.log("📡 Enviando petición AJAX...");
                $.ajax({
                    url: "ajax/categorias-central.ajax.php",
                    method: "POST",
                    data: {
                        accion: "sincronizar"
                    },
                    dataType: "json",
                    timeout: 30000, // 30 segundos timeout
                    success: function(respuesta) {
                        console.log("📥 Respuesta recibida:", respuesta);
                        
                        if (respuesta && respuesta.success) {
                            swal({
                                title: "Sincronización Completada",
                                text: respuesta.message + "\nSucursales sincronizadas: " + respuesta.sucursales_sincronizadas + "/" + respuesta.total_sucursales,
                                type: "success",
                                confirmButtonText: "Aceptar"
                            });
                            cargarCategorias();
                        } else {
                            console.error("❌ Error en respuesta:", respuesta);
                            swal("Error", respuesta ? respuesta.message : "Respuesta inválida", "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("❌ Error AJAX:", {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status
                        });
                        
                        let mensajeError = "Error al sincronizar categorías";
                        if (xhr.status === 0) {
                            mensajeError = "Error de conexión. Verifica tu conexión a internet.";
                        } else if (xhr.status === 404) {
                            mensajeError = "Archivo no encontrado. Verifica la ruta del AJAX.";
                        } else if (xhr.status === 500) {
                            mensajeError = "Error del servidor. Revisa los logs.";
                        }
                        
                        swal("Error", mensajeError, "error");
                    }
                });
            } else {
                console.log("❌ Usuario canceló sincronización");
            }
        }).catch((error) => {
            console.error("❌ Error en SweetAlert:", error);
        });
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
    
    console.log("📋 Script categorias-central.js COMPLETAMENTE CARGADO");
});
