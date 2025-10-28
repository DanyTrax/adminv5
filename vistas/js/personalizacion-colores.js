/*=============================================
PERSONALIZACIÓN DE COLORES - JAVASCRIPT
=============================================*/

$(document).ready(function() {
    
    // Inicializar vista previa
    inicializarVistaPrevia();
    
    // Event listeners para cambios de color
    $('input[type="color"]').on('change', function() {
        actualizarVistaPrevia();
        actualizarTextoColor($(this));
    });
    
    // Event listeners para formulario
    $('#formNuevaConfiguracion').on('submit', function(e) {
        e.preventDefault();
        guardarConfiguracion();
    });
    
    // Event listeners para botones de acción
    $(document).on('click', '.btn-activar-config', function() {
        var id = $(this).data('id');
        activarConfiguracion(id);
    });
    
    $(document).on('click', '.btn-eliminar-config', function() {
        var id = $(this).data('id');
        eliminarConfiguracion(id);
    });
    
    // Event listener para aplicar estilos dinámicamente
    $(document).on('click', '.btn-aplicar-estilos', function() {
        aplicarEstilosDinamicos();
    });
});

/*=============================================
INICIALIZAR VISTA PREVIA
=============================================*/
function inicializarVistaPrevia() {
    actualizarVistaPrevia();
    
    // Actualizar todos los textos de color
    $('input[type="color"]').each(function() {
        actualizarTextoColor($(this));
    });
}

/*=============================================
ACTUALIZAR VISTA PREVIA
=============================================*/
function actualizarVistaPrevia() {
    var navbarColor = $('#navbar_color').val() || '#3c8dbc';
    var navbarTextColor = $('#navbar_text_color').val() || '#ffffff';
    var sidebarColor = $('#sidebar_color').val() || '#222d32';
    var sidebarTextColor = $('#sidebar_text_color').val() || '#b8c7ce';
    var gradientStart = $('#login_gradient_start').val() || '#3c8dbc';
    var gradientEnd = $('#login_gradient_end').val() || '#2c3e50';
    var loginTextColor = $('#login_text_color').val() || '#ffffff';
    
    // Actualizar navbar
    $('.preview-navbar').css({
        'background-color': navbarColor,
        'color': navbarTextColor
    });
    
    // Actualizar sidebar
    $('.preview-sidebar').css({
        'background-color': sidebarColor,
        'color': sidebarTextColor
    });
    
    // Actualizar login
    $('.preview-login').css({
        'background': 'linear-gradient(135deg, ' + gradientStart + ' 0%, ' + gradientEnd + ' 100%)',
        'color': loginTextColor
    });
    
    // Actualizar gradiente en el historial
    $('.gradient-preview').css({
        'background': 'linear-gradient(90deg, ' + gradientStart + ', ' + gradientEnd + ')'
    });
}

/*=============================================
ACTUALIZAR TEXTO DE COLOR
=============================================*/
function actualizarTextoColor(colorInput) {
    var textInput = colorInput.closest('.input-group').find('.color-text');
    textInput.val(colorInput.val());
}

/*=============================================
GUARDAR CONFIGURACIÓN
=============================================*/
function guardarConfiguracion() {
    
    // Recopilar datos del formulario
    var datos = {
        nombre_configuracion: $('#nombre_configuracion').val(),
        navbar_color: $('#navbar_color').val(),
        navbar_text_color: $('#navbar_text_color').val(),
        navbar_hover_color: $('#navbar_hover_color').val(),
        sidebar_color: $('#sidebar_color').val(),
        sidebar_text_color: $('#sidebar_text_color').val(),
        sidebar_hover_color: $('#sidebar_hover_color').val(),
        logo_mini_color: $('#logo_mini_color').val(),
        logo_lg_color: $('#logo_lg_color').val(),
        logo_background_color: $('#logo_background_color').val(),
        icon_color: $('#icon_color').val(),
        sidebar_toggle_hover_color: $('#sidebar_toggle_hover_color').val(),
        dropdown_hover_color: $('#dropdown_hover_color').val(),
        button_primary_color: $('#button_primary_color').val(),
        button_primary_hover_color: $('#button_primary_hover_color').val(),
        link_hover_color: $('#link_hover_color').val(),
        active_menu_color: $('#active_menu_color').val(),
        active_menu_text_color: $('#active_menu_text_color').val(),
        login_gradient_start: $('#login_gradient_start').val(),
        login_gradient_end: $('#login_gradient_end').val(),
        login_logo_color: $('#login_logo_color').val(),
        login_text_color: $('#login_text_color').val()
    };
    
    // Validar datos
    if (!validarDatosConfiguracion(datos)) {
        return;
    }
    
    // Mostrar loading
    var btnSubmit = $('#formNuevaConfiguracion button[type="submit"]');
    var textoOriginal = btnSubmit.html();
    btnSubmit.html('<i class="fa fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);
    
    // Enviar datos
    $.ajax({
        url: "ajax/personalizacion-colores.ajax.php",
        method: "POST",
        data: {
            accion: "actualizarConfiguracion",
            datos: JSON.stringify(datos)
        },
        dataType: "json",
        success: function(respuesta) {
            
            btnSubmit.html(textoOriginal).prop('disabled', false);
            
            if (respuesta.success) {
                swal({
                    type: "success",
                    title: "¡Configuración guardada!",
                    text: respuesta.message,
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result) {
                    if (result.value) {
                        $('#modalNuevaConfiguracion').modal('hide');
                        location.reload();
                    }
                });
            } else {
                swal({
                    type: "error",
                    title: "Error",
                    text: respuesta.error,
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function() {
            btnSubmit.html(textoOriginal).prop('disabled', false);
            swal({
                type: "error",
                title: "Error",
                text: "Error de conexión",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
VALIDAR DATOS DE CONFIGURACIÓN
=============================================*/
function validarDatosConfiguracion(datos) {
    
    // Validar campos requeridos
    var camposRequeridos = [
        'nombre_configuracion',
        'navbar_color',
        'navbar_text_color',
        'navbar_hover_color',
        'sidebar_color',
        'sidebar_text_color',
        'sidebar_hover_color',
        'logo_mini_color',
        'logo_lg_color',
        'logo_background_color',
        'icon_color',
        'sidebar_toggle_hover_color',
        'dropdown_hover_color',
        'button_primary_color',
        'button_primary_hover_color',
        'link_hover_color',
        'active_menu_color',
        'active_menu_text_color',
        'login_gradient_start',
        'login_gradient_end',
        'login_logo_color',
        'login_text_color'
    ];
    
    for (var i = 0; i < camposRequeridos.length; i++) {
        var campo = camposRequeridos[i];
        if (!datos[campo] || datos[campo].trim() === '') {
            swal({
                type: "error",
                title: "Campo requerido",
                text: "El campo '" + campo + "' es obligatorio",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
            return false;
        }
    }
    
    // Validar formato de colores hexadecimales
    var patronColor = /^#[0-9A-Fa-f]{6}$/;
    for (var i = 0; i < camposRequeridos.length; i++) {
        var campo = camposRequeridos[i];
        if (campo !== 'nombre_configuracion' && !patronColor.test(datos[campo])) {
            swal({
                type: "error",
                title: "Color inválido",
                text: "El campo '" + campo + "' debe tener un formato de color hexadecimal válido (#RRGGBB)",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
            return false;
        }
    }
    
    return true;
}

/*=============================================
ACTIVAR CONFIGURACIÓN
=============================================*/
function activarConfiguracion(id) {
    
    swal({
        title: "¿Activar configuración?",
        text: "¿Estás seguro de que quieres activar esta configuración?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        confirmButtonText: "Sí, activar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {
        if (result.value) {
            
            $.ajax({
                url: "ajax/personalizacion-colores.ajax.php",
                method: "POST",
                data: {
                    accion: "activarConfiguracion",
                    id: id
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        swal({
                            type: "success",
                            title: "¡Configuración activada!",
                            text: respuesta.message,
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                location.reload();
                            }
                        });
                    } else {
                        swal({
                            type: "error",
                            title: "Error",
                            text: respuesta.error,
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    }
                },
                error: function() {
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error de conexión",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                }
            });
        }
    });
}

/*=============================================
ELIMINAR CONFIGURACIÓN
=============================================*/
function eliminarConfiguracion(id) {
    
    swal({
        title: "¿Eliminar configuración?",
        text: "¿Estás seguro de que quieres eliminar esta configuración? Esta acción no se puede deshacer.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {
        if (result.value) {
            
            $.ajax({
                url: "ajax/personalizacion-colores.ajax.php",
                method: "POST",
                data: {
                    accion: "eliminarConfiguracion",
                    id: id
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        swal({
                            type: "success",
                            title: "¡Configuración eliminada!",
                            text: respuesta.message,
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                location.reload();
                            }
                        });
                    } else {
                        swal({
                            type: "error",
                            title: "Error",
                            text: respuesta.error,
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    }
                },
                error: function() {
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error de conexión",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                }
            });
        }
    });
}

/*=============================================
APLICAR ESTILOS DINÁMICOS
=============================================*/
function aplicarEstilosDinamicos() {
    
    $.ajax({
        url: "ajax/personalizacion-colores.ajax.php",
        method: "POST",
        data: {
            accion: "aplicarEstilos"
        },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                // Aplicar estilos al head del documento
                $('head').append(respuesta.estilos);
                
                swal({
                    type: "success",
                    title: "¡Estilos aplicados!",
                    text: "Los estilos se han aplicado correctamente",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            } else {
                swal({
                    type: "error",
                    title: "Error",
                    text: respuesta.error,
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function() {
            swal({
                type: "error",
                title: "Error",
                text: "Error de conexión",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
CARGAR CONFIGURACIÓN EXISTENTE
=============================================*/
function cargarConfiguracionExistente(configuracion) {
    
    $('#nombre_configuracion').val(configuracion.nombre_configuracion);
    $('#navbar_color').val(configuracion.navbar_color);
    $('#navbar_text_color').val(configuracion.navbar_text_color);
    $('#sidebar_color').val(configuracion.sidebar_color);
    $('#sidebar_text_color').val(configuracion.sidebar_text_color);
    $('#logo_mini_color').val(configuracion.logo_mini_color);
    $('#logo_lg_color').val(configuracion.logo_lg_color);
    $('#icon_color').val(configuracion.icon_color);
    $('#login_gradient_start').val(configuracion.login_gradient_start);
    $('#login_gradient_end').val(configuracion.login_gradient_end);
    $('#login_logo_color').val(configuracion.login_logo_color);
    $('#login_text_color').val(configuracion.login_text_color);
    
    // Actualizar vista previa
    actualizarVistaPrevia();
}
