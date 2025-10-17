/*=============================================
VARIABLES GLOBALES DE SUCURSALES
=============================================*/
var tablaSucursales;
var procesoSincronizacion = false;
var intervaloProgreso;

/*=============================================
CARGAR DATATABLE DE SUCURSALES
=============================================*/
$(document).ready(function() {
    
    // Cargar estado inicial de la sucursal
    cargarEstadoSucursalActual();
    
    // Inicializar DataTable
    tablaSucursales = $('.tablaSucursales').DataTable({
        "ajax": {
            "url": "ajax/sucursales.ajax.php",
            "type": "POST",
            "data": { "accion": "datatable" }
        },
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
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "columns": [
            { "data": "codigo", "width": "80px" },
            { "data": "nombre" },
            { "data": "direccion", "width": "200px" },
            { "data": "telefono", "width": "120px" },
            { "data": "estado", "width": "80px" },
            { "data": "ultima_sincronizacion", "width": "150px" },
            { "data": "acciones", "width": "120px", "orderable": false }
        ],
        "order": [[0, "asc"]],
        "responsive": true,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "pageLength": 25
    });
    
    // Auto-refrescar cada 2 minutos
    setInterval(function() {
        if (!procesoSincronizacion) {
            tablaSucursales.ajax.reload(null, false);
            cargarEstadoSucursalActual();
        }
    }, 120000);
});

/*=============================================
CARGAR ESTADO DE SUCURSAL ACTUAL
=============================================*/
function cargarEstadoSucursalActual() {
    
    $.ajax({
        url: "ajax/sucursales.ajax.php",
        method: "POST",
        data: { "accion": "verificar_estado" },
        dataType: "json",
        success: function(respuesta) {
            
            if (respuesta.success) {
                
                var estado = respuesta.estado;
                
                if (estado.configurada) {
                    
                    // Mostrar datos de la sucursal actual
                    $("#codigoActual").text(estado.datos.codigo_sucursal).removeClass("label-warning").addClass("label-primary");
                    $("#nombreActual").text(estado.datos.nombre);
                    
                    if (estado.registrada) {
                        $("#estadoActual").text("Registrada").removeClass("label-warning").addClass("label-success");
                        $("#btnRegistrarEsta").hide();
                    } else {
                        $("#estadoActual").text("No registrada").removeClass("label-success").addClass("label-warning");
                        $("#btnRegistrarEsta").show();
                    }
                    
                } else {
                    
                    // No configurada
                    $("#codigoActual").text("No configurado").removeClass("label-primary").addClass("label-warning");
                    $("#nombreActual").text("No configurado");
                    $("#estadoActual").text("Sin configurar").removeClass("label-success").addClass("label-danger");
                    $("#btnRegistrarEsta").hide();
                }
            }
        },
        error: function() {
            console.error("Error al verificar estado de sucursal");
        }
    });
}

/*=============================================
EDITAR CONFIGURACIÓN LOCAL
=============================================*/
$(document).on("click", "#btnEditarSucursalLocal", function() {
    
    // Cargar configuración actual
    $.ajax({
        url: "ajax/sucursales.ajax.php",
        method: "POST",
        data: { "accion": "obtener_config_local" },
        dataType: "json",
        success: function(respuesta) {
            
            if (respuesta.success && respuesta.data) {
                
                var datos = respuesta.data;
                
                // Llenar formulario con datos existentes
                $("#codigoLocal").val(datos.codigo_sucursal);
                $("#nombreLocal").val(datos.nombre);
                $("#direccionLocal").val(datos.direccion);
                $("#telefonoLocal").val(datos.telefono);
                $("#emailLocal").val(datos.email);
                $("#urlBaseLocal").val(datos.url_base);
                $("#urlApiLocal").val(datos.url_api);
                $("#esPrincipal").prop('checked', datos.es_principal == 1);
                
            } else {
                
                // Limpiar formulario para nueva configuración
                $("#formConfigurarLocal")[0].reset();
                
                // Detectar URL automáticamente
                detectarURLAutomatica();
                
                // Generar código automático
                generarCodigoAutomatico();
            }
            
            $("#modalConfigurarLocal").modal("show");
        },
        error: function() {
            swal({
                type: "error",
                title: "Error",
                text: "No se pudo cargar la configuración actual"
            });
        }
    });
});

/*=============================================
GENERAR CÓDIGO AUTOMÁTICO
=============================================*/
$(document).on("click", "#btnGenerarCodigo", function() {
    generarCodigoAutomatico();
});

function generarCodigoAutomatico() {
    
    $.ajax({
        url: "ajax/sucursales.ajax.php",
        method: "POST",
        data: { "accion": "generar_codigo" },
        dataType: "json",
        success: function(respuesta) {
            
            if (respuesta.success) {
                $("#codigoLocal").val(respuesta.codigo);
                
                // Mostrar notificación
                $("#codigoLocal").parent().addClass("has-success");
                setTimeout(function() {
                    $("#codigoLocal").parent().removeClass("has-success");
                }, 2000);
            }
        },
        error: function() {
            console.error("Error al generar código automático");
        }
    });
}

/*=============================================
DETECTAR URL AUTOMÁTICA
=============================================*/
$(document).on("click", "#btnDetectarURL", function() {
    detectarURLAutomatica();
});

function detectarURLAutomatica() {
    
    $.ajax({
        url: "ajax/sucursales.ajax.php",
        method: "POST",
        data: { "accion": "detectar_url" },
        dataType: "json",
        success: function(respuesta) {
            
            if (respuesta.success) {
                $("#urlBaseLocal").val(respuesta.url_base);
                $("#urlApiLocal").val(respuesta.url_api);
                
                // Mostrar notificación visual
                $("#urlBaseLocal, #urlApiLocal").parent().addClass("has-success");
                setTimeout(function() {
                    $("#urlBaseLocal, #urlApiLocal").parent().removeClass("has-success");
                }, 2000);
            }
        },
        error: function() {
            console.error("Error al detectar URL automática");
        }
    });
}

/*=============================================
AUTO-GENERAR URL API
=============================================*/
$(document).on("click", "#btnAutoAPI", function() {
    
    var urlBase = $("#urlBaseLocal").val().trim();
    
    if (urlBase) {
        var urlApi = urlBase;
        if (!urlApi.endsWith('/')) {
            urlApi += '/';
        }
        urlApi += 'api-transferencias/';
        
        $("#urlApiLocal").val(urlApi);
        
        // Mostrar notificación
        $("#urlApiLocal").parent().addClass("has-success");
        setTimeout(function() {
            $("#urlApiLocal").parent().removeClass("has-success");
        }, 2000);
    } else {
        swal({
            type: "warning",
            title: "Advertencia",
            text: "Primero ingrese la URL Base"
        });
    }
});

/*=============================================
VALIDAR CÓDIGO EN TIEMPO REAL
=============================================*/
$(document).on("blur", "#codigoLocal", function() {
    
    var codigo = $(this).val().trim();
    
    if (codigo && codigo.length >= 3) {
        
        $.ajax({
            url: "ajax/sucursales.ajax.php",
            method: "POST",
            data: { 
                "accion": "validar_codigo",
                "codigo": codigo
            },
            dataType: "json",
            success: function(respuesta) {
                
                if (respuesta.disponible) {
                    $("#codigoLocal").parent().removeClass("has-error").addClass("has-success");
                } else {
                    $("#codigoLocal").parent().removeClass("has-success").addClass("has-error");
                    swal({
                        type: "warning",
                        title: "Código no disponible",
                        text: respuesta.message
                    });
                }
            }
        });
    }
});

/*=============================================
REGISTRAR ESTA SUCURSAL
=============================================*/
$(document).on("click", "#btnRegistrarEsta", function() {
    
    swal({
        title: "¿Registrar esta sucursal?",
        text: "Se agregará esta sucursal al directorio central",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, registrar"
    }).then(function(result) {
        
        if (result.value) {
            
            // Mostrar loading
            $("#btnRegistrarEsta").html('<i class="fa fa-spinner fa-spin"></i> Registrando...');
            $("#btnRegistrarEsta").prop('disabled', true);
            
            $.ajax({
                url: "ajax/sucursales.ajax.php",
                method: "POST",
                data: { "accion": "registrar_esta" },
                dataType: "json",
                success: function(respuesta) {
                    
                    if (respuesta.success) {
                        
                        swal({
                            type: "success",
                            title: "¡Registrada!",
                            text: respuesta.message,
                            confirmButtonText: "Cerrar"
                        }).then(function() {
                            cargarEstadoSucursalActual();
                            tablaSucursales.ajax.reload();
                        });
                        
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
                        text: "Error de comunicación con el servidor"
                    });
                },
                complete: function() {
                    $("#btnRegistrarEsta").html('<i class="fa fa-plus-circle"></i> Registrar Esta Sucursal');
                    $("#btnRegistrarEsta").prop('disabled', false);
                }
            });
        }
    });
});

$(document).on("click", ".btnProbarConexion", function() {
    
    var apiUrl = $(this).attr("apiUrl");
    var nombreSucursal = $(this).attr("nombreSucursal");
    var boton = $(this);
    
    // Mostrar estado de carga
    boton.html('<i class="fa fa-spinner fa-spin"></i>');
    boton.prop('disabled', true);
    
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
                
                // Parsear respuesta anidada si existe
                var detalleRespuesta = "";
                if (respuesta.respuesta) {
                    try {
                        var datosAPI = JSON.parse(respuesta.respuesta);
                        if (datosAPI.version) {
                            detalleRespuesta = "<br><small>Versión API: " + datosAPI.version + "</small>";
                        }
                    } catch(e) {
                        // Si no se puede parsear, continuar normalmente
                    }
                }
                
                swal({
                    type: "success",
                    title: "Conexión exitosa",
                    html: "Conectado con <strong>" + nombreSucursal + "</strong><br>" +
                          "Tiempo de respuesta: " + (respuesta.tiempo_respuesta || 'N/A') +
                          detalleRespuesta
                });
                
            } else {
                
                swal({
                    type: "error",
                    title: "Conexión fallida",
                    html: "No se pudo conectar con <strong>" + nombreSucursal + "</strong><br>" +
                          "Error: " + respuesta.message
                });
            }
        },
        error: function(xhr, status, error) {
            
            var mensaje = "Error de comunicación";
            
            if (status === "timeout") {
                mensaje = "Timeout de conexión (más de 15 segundos)";
            } else if (xhr.status === 500) {
                mensaje = "Error interno del servidor destino";
            } else if (xhr.status === 404) {
                mensaje = "API no encontrada en la sucursal";
            }
            
            swal({
                type: "error",
                title: "Error de conexión",
                html: "<strong>" + nombreSucursal + "</strong><br>" +
                      "Estado: " + mensaje + "<br>" +
                      "Código: " + (xhr.status || 'N/A')
            });
        },
        complete: function() {
            boton.html('<i class="fa fa-wifi"></i>');
            boton.prop('disabled', false);
        }
    });
});

/*=============================================
EDITAR SUCURSAL
=============================================*/
$(document).on("click", ".btnEditarSucursal", function() {
    
    var idSucursal = $(this).attr("idSucursal");
    
    $.ajax({
        url: "ajax/sucursales.ajax.php",
        method: "POST",
        data: { "idSucursal": idSucursal },
        dataType: "json",
        success: function(respuesta) {
            
            if (respuesta.success) {
                
                var datos = respuesta.data;
                
                // Llenar formulario de edición
                $("#editarId").val(datos.id);
                $("#editarCodigo").val(datos.codigo_sucursal);
                $("#editarNombre").val(datos.nombre);
                $("#editarDireccion").val(datos.direccion);
                $("#editarTelefono").val(datos.telefono);
                $("#editarEmail").val(datos.email);
                $("#editarUrlBase").val(datos.url_base);
                $("#editarUrlApi").val(datos.url_api);
                $("#editarActivo").prop('checked', datos.activo == 1);
                
                $("#modalEditarSucursal").modal("show");
                
            } else {
                
                swal({
                    type: "error",
                    title: "Error",
                    text: respuesta.message || "No se pudo cargar la sucursal"
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
CAMBIAR ESTADO DE SUCURSAL
=============================================*/
$(document).on("click", ".btnCambiarEstado", function() {
    
    var idSucursal = $(this).attr("idSucursal");
    var estadoActual = $(this).attr("estadoActual");
    var nombreSucursal = $(this).attr("nombreSucursal");
    var nuevoEstado = estadoActual == "1" ? "0" : "1";
    var textoAccion = nuevoEstado == "1" ? "activar" : "desactivar";
    
    swal({
        title: "¿" + textoAccion.charAt(0).toUpperCase() + textoAccion.slice(1) + " sucursal?",
        text: "¿Está seguro de " + textoAccion + " " + nombreSucursal + "?",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, " + textoAccion
    }).then(function(result) {
        
        if (result.value) {
            
            $.ajax({
                url: "ajax/sucursales.ajax.php",
                method: "POST",
                data: { 
                    "accion": "cambiar_estado",
                    "idSucursal": idSucursal,
                    "nuevoEstado": nuevoEstado
                },
                dataType: "json",
                success: function(respuesta) {
                    
                    if (respuesta.success) {
                        
                        swal({
                            type: "success",
                            title: "Estado actualizado",
                            text: respuesta.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        tablaSucursales.ajax.reload();
                        
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
                        text: "Error de comunicación con el servidor"
                    });
                }
            });
        }
    });
});

/*=============================================
ELIMINAR SUCURSAL
=============================================*/
$(document).on("click", ".btnEliminarSucursal", function() {
    
    var idSucursal = $(this).attr("idSucursal");
    var nombreSucursal = $(this).attr("nombreSucursal");
    
    swal({
        title: "¿Eliminar sucursal?",
        text: "¿Está seguro de eliminar " + nombreSucursal + "? Esta acción no se puede deshacer.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, eliminar"
    }).then(function(result) {
        
        if (result.value) {
            
            $.ajax({
                url: "ajax/sucursales.ajax.php",
                method: "POST",
                data: { 
                    "accion": "eliminar_sucursal",
                    "idSucursal": idSucursal
                },
                dataType: "json",
                success: function(respuesta) {
                    
                    if (respuesta.success) {
                        
                        swal({
                            type: "success",
                            title: "Eliminada",
                            text: respuesta.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        tablaSucursales.ajax.reload();
                        
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
                        text: "Error de comunicación con el servidor"
                    });
                }
            });
        }
    });
});

/*=============================================
SINCRONIZAR CATÁLOGO MAESTRO
=============================================*/
$(document).on("click", "#btnSincronizarCatalogo", function() {
    
    if (procesoSincronizacion) {
        swal({
            type: "info",
            title: "Sincronización en proceso",
            text: "Ya hay una sincronización en curso, por favor espere"
        });
        return;
    }
    
    swal({
        title: "¿Sincronizar catálogo maestro?",
        text: "Se distribuirá el catálogo maestro a todas las sucursales activas. Este proceso puede tomar varios minutos.",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, sincronizar"
    }).then(function(result) {
        
        if (result.value) {
            iniciarSincronizacionCatalogo();
        }
    });
});

function iniciarSincronizacionCatalogo() {
    
    procesoSincronizacion = true;
    
    // Cambiar estado del botón
    $("#btnSincronizarCatalogo")
        .html('<i class="fa fa-spinner fa-spin"></i> Sincronizando...')
        .prop('disabled', true)
        .removeClass('btn-success')
        .addClass('btn-warning');
    
    // Mostrar modal de progreso
    mostrarModalProgreso();
    
    $.ajax({
        url: "ajax/sucursales.ajax.php",
        method: "POST",
        data: { "accion": "sincronizar_catalogo" },
        dataType: "json",
        timeout: 3600000, // 1 hora timeout para tu servidor de 8GB
        success: function(respuesta) {
            
            ocultarModalProgreso();
            
            if (respuesta.success) {
                
                swal({
                    type: "success",
                    title: "¡Sincronización completada!",
                    html: respuesta.mensaje_detallado,
                    confirmButtonText: "Cerrar"
                }).then(function() {
                    tablaSucursales.ajax.reload();
                });
                
            } else {
                
                swal({
                    type: "warning",
                    title: "Sincronización completada con errores",
                    html: respuesta.mensaje_detallado || respuesta.message,
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
            
            ocultarModalProgreso();
            
            var mensaje = "Error de comunicación con el servidor";
            
            if (status === "timeout") {
                mensaje = "La sincronización tardó demasiado. Verifique manualmente el estado de las sucursales.";
            }
            
            swal({
                type: "error",
                title: "Error en sincronización",
                text: mensaje
            });
        },
        complete: function() {
            
            procesoSincronizacion = false;
            
            // Restaurar botón
            $("#btnSincronizarCatalogo")
                .html('<i class="fa fa-refresh"></i> Sincronizar Catálogo Maestro')
                .prop('disabled', false)
                .removeClass('btn-warning')
                .addClass('btn-success');
        }
    });
}

/*=============================================
MODAL DE PROGRESO DE SINCRONIZACIÓN
=============================================*/
function mostrarModalProgreso() {
    
    var modalHTML = `
        <div class="modal fade" id="modalProgresoSync" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h4 class="modal-title">
                            <i class="fa fa-refresh fa-spin"></i> Sincronizando Catálogo Maestro
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 100%">
                                    Distribuyendo productos a todas las sucursales...
                                </div>
                            </div>
                            <br>
                            <p class="text-muted">
                                <i class="fa fa-clock-o"></i> Este proceso puede tomar varios minutos dependiendo del número de productos y sucursales.<br>
                                <strong>Por favor no cierre esta ventana.</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $("body").append(modalHTML);
    $("#modalProgresoSync").modal("show");
}

function ocultarModalProgreso() {
    $("#modalProgresoSync").modal("hide");
    setTimeout(function() {
        $("#modalProgresoSync").remove();
    }, 1000);
}

/*=============================================
VALIDACIONES DE FORMULARIOS
=============================================*/

// Validación en tiempo real de URL Base
$(document).on("blur", "#urlBaseLocal", function() {
    
    var url = $(this).val().trim();
    
    if (url) {
        
        // Validar formato de URL
        var regex = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
        
        if (regex.test(url)) {
            $(this).parent().removeClass("has-error").addClass("has-success");
            
            // Auto-completar protocolo si no existe
            if (!url.startsWith('http://') && !url.startsWith('https://')) {
                $(this).val('http://' + url);
            }
            
        } else {
            $(this).parent().removeClass("has-success").addClass("has-error");
        }
    }
});

// Validación de email
$(document).on("blur", "#emailLocal", function() {
    
    var email = $(this).val().trim();
    
    if (email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (regex.test(email)) {
            $(this).parent().removeClass("has-error").addClass("has-success");
        } else {
            $(this).parent().removeClass("has-success").addClass("has-error");
        }
    }
});

// Formatear teléfono
$(document).on("input", "#telefonoLocal", function() {
    
    var telefono = $(this).val().replace(/\D/g, '');
    
    if (telefono.length >= 7) {
        // Formato colombiano: (000) 000-0000
        if (telefono.length === 10) {
            telefono = '(' + telefono.substr(0,3) + ') ' + telefono.substr(3,3) + '-' + telefono.substr(6,4);
        }
        $(this).val(telefono);
    }
});

/*=============================================
ENVÍO DE FORMULARIOS
=============================================*/

// Formulario de configuración local
$("#formConfigurarLocal").on("submit", function(e) {
    
    e.preventDefault();
    
    var formData = $(this).serialize();
    
    swal({
        title: "¿Guardar configuración?",
        text: "Se actualizarán los datos de esta sucursal",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, guardar"
    }).then(function(result) {
        
        if (result.value) {
            
            // El formulario se enviará normalmente al controlador
            $("#formConfigurarLocal")[0].submit();
        }
    });
});

// Formulario de edición de sucursal
$("#formEditarSucursal").on("submit", function(e) {
    
    e.preventDefault();
    
    var formData = $(this).serialize();
    
    swal({
        title: "¿Actualizar sucursal?",
        text: "Se guardarán los cambios realizados",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, actualizar"
    }).then(function(result) {
        
        if (result.value) {
            
            // El formulario se enviará normalmente al controlador
            $("#formEditarSucursal")[0].submit();
        }
    });
});

/*=============================================
UTILIDADES ADICIONALES
=============================================*/

// Función para formatear fechas
function formatearFecha(fecha) {
    if (!fecha || fecha === '0000-00-00 00:00:00') return 'Nunca';
    
    var date = new Date(fecha);
    return date.toLocaleDateString('es-CO') + ' ' + date.toLocaleTimeString('es-CO');
}

// Función para validar conexión de red
function validarConexionRed() {
    
    return navigator.onLine;
}

// Limpiar formularios al cerrar modales
$("#modalConfigurarLocal, #modalEditarSucursal").on("hidden.bs.modal", function() {
    
    $(this).find("form")[0].reset();
    $(this).find(".has-error, .has-success").removeClass("has-error has-success");
});

//console.log("Módulo de Sucursales cargado correctamente - AdminV5");