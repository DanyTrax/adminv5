$(document).ready(function() {

    /*=============================================
    CARGAR LA TABLA DINÁMICA DE SUCURSALES
    =============================================*/
    var tablaSucursales = $('.tablaSucursales').DataTable({
        "ajax": {
            "url": "ajax/sucursales.ajax.php",
            "method": "POST",
            "data": {"accion": "datatable"},
            "dataSrc": function(json) {
                if (json.success) {
                    return json.data;
                } else {
                    console.error('Error al cargar sucursales:', json.message);
                    return [];
                }
            }
        },
        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
        },
        "columnDefs": [{
            "targets": [0, 7], // Logo y Acciones
            "orderable": false
        }],
        "order": [[2, "asc"]], // Ordenar por nombre por defecto
        "pageLength": 10,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                titleAttr: 'Exportar a Excel'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                titleAttr: 'Exportar a PDF'
            }
        ]
    });

    /*=============================================
    CARGAR ESTADÍSTICAS AL INICIAR
    =============================================*/
    cargarEstadisticas();

    /*=============================================
    FUNCIÓN PARA CARGAR ESTADÍSTICAS
    =============================================*/
    function cargarEstadisticas() {
        $.ajax({
            url: "ajax/sucursales.ajax.php",
            method: "POST",
            data: {"accion": "obtener_estadisticas"},
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    $("#totalSucursales").text(respuesta.estadisticas.total);
                    $("#sucursalesActivas").text(respuesta.estadisticas.activas);
                    $("#sucursalesInactivas").text(respuesta.estadisticas.inactivas);
                    $("#ultimaSincronizacion").text(respuesta.estadisticas.ultima_sincronizacion);
                }
            },
            error: function() {
                console.error('Error al cargar estadísticas');
            }
        });
    }

    /*=============================================
    PREVISUALIZAR LOGO
    =============================================*/
    $(".nuevoLogo").change(function() {
        var imagen = this.files[0];
        
        if (imagen) {
            if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png" && imagen["type"] != "image/gif") {
                $(".nuevoLogo").val("");
                swal({
                    title: "Error al subir la imagen",
                    text: "¡La imagen debe estar en formato JPG, PNG o GIF!",
                    type: "error",
                    confirmButtonText: "¡Cerrar!"
                });
            } else if (imagen["size"] > 2000000) { // 2MB
                $(".nuevoLogo").val("");
                swal({
                    title: "Error al subir la imagen",
                    text: "¡La imagen no debe pesar más de 2MB!",
                    type: "error",
                    confirmButtonText: "¡Cerrar!"
                });
            } else {
                var datosImagen = new FileReader();
                datosImagen.readAsDataURL(imagen);
                $(datosImagen).on("load", function(event) {
                    var rutaImagen = event.target.result;
                    $(".previsualizar").attr("src", rutaImagen);
                });
            }
        }
    });

    $(".nuevaImagen").change(function() {
        var imagen = this.files[0];
        
        if (imagen) {
            if (imagen["type"] != "image/jpeg" && imagen["type"] != "image/png" && imagen["type"] != "image/gif") {
                $(".nuevaImagen").val("");
                swal({
                    title: "Error al subir la imagen",
                    text: "¡La imagen debe estar en formato JPG, PNG o GIF!",
                    type: "error",
                    confirmButtonText: "¡Cerrar!"
                });
            } else if (imagen["size"] > 2000000) { // 2MB
                $(".nuevaImagen").val("");
                swal({
                    title: "Error al subir la imagen",
                    text: "¡La imagen no debe pesar más de 2MB!",
                    type: "error",
                    confirmButtonText: "¡Cerrar!"
                });
            } else {
                var datosImagen = new FileReader();
                datosImagen.readAsDataURL(imagen);
                $(datosImagen).on("load", function(event) {
                    var rutaImagen = event.target.result;
                    $(".previsualizar").attr("src", rutaImagen);
                });
            }
        }
    });

    /*=============================================
    GENERAR CÓDIGO AUTOMÁTICO
    =============================================*/
    $("#btnGenerarCodigo").click(function() {
        $.ajax({
            url: "ajax/sucursales.ajax.php",
            method: "POST",
            data: {"accion": "generar_codigo"},
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.success) {
                    $("#nuevoCodigo").val(respuesta.codigo);
                } else {
                    swal({
                        type: "error",
                        title: "Error",
                        text: respuesta.message
                    });
                }
            },
            error: function() {
                swal({
                    type: "error",
                    title: "Error",
                    text: "No se pudo generar el código automáticamente"
                });
            }
        });
    });

/*=============================================
AUTOCOMPLETAR API URL DESDE URL BASE
=============================================*/
$(document).on('click', '#btnAutocompletarApi', function() {
    var urlBase = $("input[name='nuevaUrlBase']").val().trim();
    
    if (urlBase === '') {
        swal({
            type: "warning",
            title: "Advertencia",
            text: "Primero debe ingresar la URL Base"
        });
        return;
    }

    // Asegurar que termine con /
    if (!urlBase.endsWith('/')) {
        urlBase += '/';
    }

    var apiUrl = urlBase + 'api-transferencias/';
    $("input[name='nuevaApiUrl']").val(apiUrl);
    
    swal({
        type: "success",
        title: "¡Completado!",
        text: "API URL generada automáticamente",
        timer: 1500,
        showConfirmButton: false
    });
});

/*=============================================
PROBAR CONEXIÓN AL CREAR SUCURSAL
=============================================*/
$(document).on('click', '#btnProbarConexionNueva', function() {
    var apiUrl = $("input[name='nuevaApiUrl']").val().trim();
    
    if (apiUrl === '') {
        swal({
            type: "warning",
            title: "Advertencia",
            text: "Debe ingresar la API URL primero"
        });
        return;
    }

    probarConexion(apiUrl, 'Nueva Sucursal');
});

    /*=============================================
    FUNCIÓN PARA PROBAR CONEXIÓN
    =============================================*/
    function probarConexion(apiUrl, nombreSucursal) {
        // Mostrar loading
        swal({
            title: 'Probando conexión...',
            text: 'Verificando conectividad con ' + nombreSucursal,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            onOpen: function() {
                swal.showLoading();
            }
        });

        $.ajax({
            url: "ajax/sucursales.ajax.php",
            method: "POST",
            data: {
                "accion": "probar_conexion",
                "apiUrl": apiUrl
            },
            dataType: "json",
            timeout: 15000, // 15 segundos timeout
            success: function(respuesta) {
                if (respuesta.success) {
                    swal({
                        type: "success",
                        title: "¡Conexión exitosa!",
                        text: respuesta.message + (respuesta.tiempo_respuesta ? ' (Tiempo: ' + respuesta.tiempo_respuesta + ')' : ''),
                        confirmButtonText: "Cerrar"
                    });
                } else {
                    swal({
                        type: "error",
                        title: "Conexión fallida",
                        text: respuesta.message,
                        confirmButtonText: "Cerrar"
                    });
                }
            },
            error: function(xhr, status, error) {
                var mensaje = "Error de conexión";
                if (status === 'timeout') {
                    mensaje = "Tiempo de espera agotado (15s)";
                }
                swal({
                    type: "error",
                    title: "Error",
                    text: mensaje + ": " + error,
                    confirmButtonText: "Cerrar"
                });
            }
        });
    }

    /*=============================================
    EDITAR SUCURSAL
    =============================================*/
    $(".tablaSucursales").on("click", ".btnEditarSucursal", function() {
        var idSucursal = $(this).attr("idSucursal");
        
        var datos = new FormData();
        datos.append("idSucursal", idSucursal);

        $.ajax({
            url: "ajax/sucursales.ajax.php",
            method: "POST",
            data: datos,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(respuesta) {
                if (respuesta) {
                    // Llenar campos del modal de edición
                    $("#editarId").val(respuesta.id);
                    $("#editarCodigo").val(respuesta.codigo_sucursal);
                    $("#editarNombre").val(respuesta.nombre);
                    $("#editarDireccion").val(respuesta.direccion || '');
                    $("#editarTelefono").val(respuesta.telefono || '');
                    $("#editarEmail").val(respuesta.email || '');
                    $("#editarUrlBase").val(respuesta.url_base);
                    $("#editarApiUrl").val(respuesta.api_url);
                    $("#editarObservaciones").val(respuesta.observaciones || '');
                    
                    // Checkboxes
                    $("#editarActiva").prop('checked', respuesta.activa);
                    $("#editarPrincipal").prop('checked', respuesta.es_principal);
                    
                    // Logo
                    if (respuesta.logo && respuesta.logo !== '') {
                        $("#logoActual").val(respuesta.logo);
                        $("#modalEditarSucursal .previsualizar").attr("src", "vistas/img/sucursales/" + respuesta.logo);
                    } else {
                        $("#logoActual").val('');
                        $("#modalEditarSucursal .previsualizar").attr("src", "vistas/img/productos/default/anonymous.png");
                    }
                } else {
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No se pudieron cargar los datos de la sucursal"
                    });
                }
            },
            error: function() {
                swal({
                    type: "error",
                    title: "Error",
                    text: "Error de comunicación con el servidor"
                });
            }
        });
    });

    /*=============================================
    PROBAR CONEXIÓN AL EDITAR SUCURSAL
    =============================================*/
    $(".btnProbarConexionEditar").click(function() {
        var apiUrl = $("#editarApiUrl").val().trim();
        var nombreSucursal = $("#editarNombre").val().trim();
        
        if (apiUrl === '') {
            swal({
                type: "warning",
                title: "Advertencia",
                text: "Debe ingresar la API URL primero"
            });
            return;
        }

        probarConexion(apiUrl, nombreSucursal);
    });

    /*=============================================
    CAMBIAR ESTADO DE SUCURSAL
    =============================================*/
    $(".tablaSucursales").on("click", ".btnCambiarEstado", function() {
        var idSucursal = $(this).attr("idSucursal");
        var estadoSucursal = $(this).attr("estadoSucursal");
        
        var textoEstado = estadoSucursal == 1 ? "activar" : "desactivar";
        
        swal({
            title: '¿Está seguro de ' + textoEstado + ' la sucursal?',
            text: "¡Puede cancelar la acción si no está seguro!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: '¡Sí, ' + textoEstado + '!'
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "ajax/sucursales.ajax.php",
                    method: "POST",
                    data: {
                        "accion": "cambiar_estado",
                        "idSucursal": idSucursal,
                        "estadoSucursal": estadoSucursal
                    },
                    dataType: "json",
                    success: function(respuesta) {
                        if (respuesta.success) {
                            swal({
                                type: "success",
                                title: "¡Estado cambiado!",
                                text: respuesta.message
                            }).then(function() {
                                tablaSucursales.ajax.reload(null, false);
                                cargarEstadisticas();
                            });
                        } else {
                            swal({
                                type: "error",
                                title: "Error",
                                text: respuesta.message
                            });
                        }
                    }
                });
            }
        });
    });

    /*=============================================
    ELIMINAR SUCURSAL
    =============================================*/
    $(".tablaSucursales").on("click", ".btnEliminarSucursal", function() {
        var idSucursal = $(this).attr("idSucursal");
        var nombreSucursal = $(this).attr("nombreSucursal");
        var logoSucursal = $(this).attr("logoSucursal");

        swal({
            title: '¿Está seguro de eliminar la sucursal "' + nombreSucursal + '"?',
            text: "¡Esta acción no se puede deshacer!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: '¡Sí, eliminar!'
        }).then(function(result) {
            if (result.value) {
                window.location = "index.php?ruta=sucursales&idSucursal=" + idSucursal;
            }
        });
    });

/*=============================================
SINCRONIZAR CATÁLOGO CON TODAS LAS SUCURSALES
=============================================*/
$("#btnSincronizarTodas").click(function() {
    swal({
        title: '¿Sincronizar catálogo con todas las sucursales activas?',
        text: "Esto actualizará el catálogo maestro de productos en todas las sucursales conectadas",
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: '¡Sí, sincronizar catálogo!'
    }).then(function(result) {
        if (result.value) {
            ejecutarSincronizacionCatalogo();
        }
    });
});

/*=============================================
FUNCIÓN PARA EJECUTAR SINCRONIZACIÓN DE CATÁLOGO
=============================================*/
function ejecutarSincronizacionCatalogo() {
    // Mostrar loading
    swal({
        title: 'Sincronizando catálogo...',
        text: 'Actualizando catálogo maestro en todas las sucursales. Esto puede tomar varios minutos.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        onOpen: function() {
            swal.showLoading();
        }
    });

    $.ajax({
        url: "ajax/sucursales.ajax.php",
        method: "POST",
        data: {"accion": "sincronizar_catalogo_maestro"}, // ← CAMBIO AQUÍ
        dataType: "json",
        timeout: 300000, // 5 minutos timeout
        success: function(respuesta) {
            if (respuesta.success) {
                swal({
                    type: "success",
                    title: "¡Catálogo sincronizado!",
                    text: respuesta.message_detallado || respuesta.message,
                    confirmButtonText: "Cerrar"
                }).then(function() {
                    cargarEstadisticas();
                    tablaSucursales.ajax.reload(null, false);
                });
            } else {
                swal({
                    type: "error",
                    title: "Error en sincronización",
                    text: respuesta.message,
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
            var mensaje = "Error de comunicación";
            if (status === 'timeout') {
                mensaje = "La sincronización está tomando más tiempo del esperado. Puede continuar en segundo plano.";
            }
            swal({
                type: "warning",
                title: "Atención",
                text: mensaje,
                confirmButtonText: "Cerrar"
            });
        }
    });
}

    /*=============================================
    FUNCIÓN PARA EJECUTAR SINCRONIZACIÓN COMPLETA
    =============================================*/
    function ejecutarSincronizacionCompleta() {
        // Mostrar loading
        swal({
            title: 'Sincronizando...',
            text: 'Actualizando catálogo en todas las sucursales. Esto puede tomar varios minutos.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            onOpen: function() {
                swal.showLoading();
            }
        });

        $.ajax({
            url: "ajax/sucursales.ajax.php",
            method: "POST",
            data: {"accion": "sincronizar_todas"},
            dataType: "json",
            timeout: 300000, // 5 minutos timeout
            success: function(respuesta) {
                if (respuesta.success) {
                    swal({
                        type: "success",
                        title: "¡Sincronización completada!",
                        text: respuesta.message_detallado || respuesta.message,
                        confirmButtonText: "Cerrar"
                    }).then(function() {
                        cargarEstadisticas();
                        tablaSucursales.ajax.reload(null, false);
                    });
                } else {
                    swal({
                        type: "error",
                        title: "Error en sincronización",
                        text: respuesta.message,
                        confirmButtonText: "Cerrar"
                    });
                }
            },
            error: function(xhr, status, error) {
                var mensaje = "Error de comunicación";
                if (status === 'timeout') {
                    mensaje = "La sincronización está tomando más tiempo del esperado. Puede continuar en segundo plano.";
                }
                swal({
                    type: "warning",
                    title: "Atención",
                    text: mensaje,
                    confirmButtonText: "Cerrar"
                });
            }
        });
    }

    /*=============================================
    SINCRONIZACIÓN INDIVIDUAL DE SUCURSAL
    =============================================*/
    $(".tablaSucursales").on("click", ".btnSincronizarIndividual", function() {
        var idSucursal = $(this).attr("idSucursal");
        var nombreSucursal = $(this).attr("nombreSucursal");

        swal({
            title: '¿Sincronizar con "' + nombreSucursal + '"?',
            text: "Se actualizará el catálogo de productos en esta sucursal específica",
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: '¡Sí, sincronizar!'
        }).then(function(result) {
            if (result.value) {
                ejecutarSincronizacionIndividual([idSucursal], nombreSucursal);
            }
        });
    });

    /*=============================================
    FUNCIÓN PARA SINCRONIZACIÓN INDIVIDUAL
    =============================================*/
    function ejecutarSincronizacionIndividual(sucursales, nombreSucursal) {
        // Mostrar loading
        swal({
            title: 'Sincronizando...',
            text: 'Actualizando catálogo en ' + nombreSucursal,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            onOpen: function() {
                swal.showLoading();
            }
        });

        $.ajax({
            url: "ajax/sucursales.ajax.php",
            method: "POST",
            data: {
                "accion": "sincronizar_seleccionadas",
                "sucursales": JSON.stringify(sucursales)
            },
            dataType: "json",
            timeout: 120000, // 2 minutos timeout
            success: function(respuesta) {
                if (respuesta.success) {
                    var mensaje = "Sincronización completada con " + nombreSucursal;
                    if (respuesta.local && respuesta.remoto) {
                        mensaje += "\n\nLocal: " + respuesta.local.sincronizados + " nuevos, " + respuesta.local.actualizados + " actualizados";
                        mensaje += "\nRemoto: " + respuesta.remoto.sucursales_exitosas + "/" + respuesta.remoto.sucursales_procesadas + " exitosas";
                    }
                    
                    swal({
                        type: "success",
                        title: "¡Sincronización completada!",
                        text: mensaje,
                        confirmButtonText: "Cerrar"
                    }).then(function() {
                        cargarEstadisticas();
                        tablaSucursales.ajax.reload(null, false);
                    });
                } else {
                    swal({
                        type: "error",
                        title: "Error en sincronización",
                        text: respuesta.message,
                        confirmButtonText: "Cerrar"
                    });
                }
            },
            error: function(xhr, status, error) {
                var mensaje = "Error de comunicación con " + nombreSucursal;
                if (status === 'timeout') {
                    mensaje = "Tiempo de espera agotado para " + nombreSucursal;
                }
                swal({
                    type: "error",
                    title: "Error",
                    text: mensaje,
                    confirmButtonText: "Cerrar"
                });
            }
        });
    }

    /*=============================================
    PROBAR CONEXIÓN DESDE DATATABLE
    =============================================*/
    $(".tablaSucursales").on("click", ".btnProbarConexion", function() {
        var apiUrl = $(this).attr("apiUrl");
        var nombreSucursal = $(this).attr("nombreSucursal");
        
        probarConexion(apiUrl, nombreSucursal);
    });

    /*=============================================
    VALIDACIONES DE FORMULARIO
    =============================================*/
    
    // Validar código de sucursal en tiempo real
    $("#nuevoCodigo, #editarCodigo").on('input', function() {
        var codigo = $(this).val().toUpperCase();
        $(this).val(codigo);
        
        if (codigo.length >= 3) {
            // Validar formato
            if (!/^[A-Z0-9]{3,10}$/.test(codigo)) {
                $(this).addClass('is-invalid');
                $(this).parent().after('<div class="invalid-feedback">Código debe tener 3-10 caracteres alfanuméricos</div>');
            } else {
                $(this).removeClass('is-invalid');
                $(this).parent().siblings('.invalid-feedback').remove();
            }
        }
    });

    // Validar URLs
    $("input[name='nuevaUrlBase'], input[name='editarUrlBase'], input[name='nuevaApiUrl'], input[name='editarApiUrl']").on('input', function() {
        var url = $(this).val().trim();
        if (url !== '' && !isValidUrl(url)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    /*=============================================
    FUNCIÓN PARA VALIDAR URL
    =============================================*/
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    /*=============================================
    REFRESCAR TABLA CADA 30 SEGUNDOS
    =============================================*/
    setInterval(function() {
        if (tablaSucursales) {
            tablaSucursales.ajax.reload(null, false); // Refrescar sin resetear paginación
        }
    }, 30000);

    /*=============================================
    ACTUALIZAR ESTADÍSTICAS CADA MINUTO
    =============================================*/
    setInterval(function() {
        cargarEstadisticas();
    }, 60000);

});