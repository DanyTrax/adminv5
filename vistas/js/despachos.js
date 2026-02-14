/*=============================================
FUNCIONES GLOBALES PARA ONCLICK (definir primero para evitar ReferenceError)
=============================================*/
function abrirModalCambiarEstado(idDespacho, numeroDespacho, estadoActual) {
    if (typeof $ === 'undefined') return;
    $("#modalVerDespacho").modal("hide");
    $("#idDespachoCambiarEstado").val(idDespacho);
    $("#numeroDespachoCambiarEstado").text(numeroDespacho || '');
    $("#estadoActualCambiar").text((estadoActual || "").toUpperCase());
    $("#nuevoEstadoDespacho").val("");
    $("#observacionesCambiarEstado").val("");
    $("#modalCambiarEstadoDespacho").modal("show");
}
window.abrirModalCambiarEstado = abrirModalCambiarEstado;

/*=============================================
CARGAR DATATABLE DE DESPACHOS (solo si la tabla existe)
=============================================*/
if ($('.tablaDespachos').length) {
    var params = new URLSearchParams(window.location.search);
    var fechaInicial = params.get('fechaInicial') || '';
    var fechaFinal = params.get('fechaFinal') || '';
    var ajaxUrl = "ajax/datatable-despachos.ajax.php";
    if (fechaInicial && fechaFinal) {
        ajaxUrl += "?fechaInicial=" + encodeURIComponent(fechaInicial) + "&fechaFinal=" + encodeURIComponent(fechaFinal);
    }
$('.tablaDespachos').DataTable({
    "ajax": ajaxUrl,
    "deferRender": true,
    "retrieve": true,
    "processing": true,
    "language": {
        "sProcessing":     "Procesando...",
        "sLengthMenu":     "Mostrar _MENU_ registros",
        "sZeroRecords":    "No se encontraron resultados",
        "sEmptyTable":     "Ningún dato disponible en esta tabla",
        "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
        "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0",
        "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
        "sInfoPostFix":    "",
        "sSearch":         "Buscar:",
        "sUrl":            "",
        "sInfoThousands":  ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst":    "Primero",
            "sLast":     "Último",
            "sNext":     "Siguiente",
            "sPrevious": "Anterior"
        },
        "oAria": {
            "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    }
});
}

/*=============================================
VER DETALLES DE DESPACHO (función global para onclick)
=============================================*/
function verDespacho(idDespacho) {
    var datos = new FormData();
    datos.append("idDespacho", idDespacho);

    $.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
if(respuesta.success && respuesta.data) {
                mostrarDetallesDespacho(respuesta.data);
            } else {
                swal({
                    title: "Error",
                    text: respuesta.error || "No se pudieron cargar los detalles del despacho",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
swal({
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
}
window.verDespacho = verDespacho;

$(document).on("click", ".btnVerDespacho", function(){
    verDespacho($(this).attr("idDespacho"));
});

/*=============================================
MOSTRAR DETALLES DEL DESPACHO EN MODAL
=============================================*/
function mostrarDetallesDespacho(despacho) {
// INFORMACIÓN BÁSICA
    $("#numeroDespachoModal").text(despacho.numero_despacho);
    $("#sucursalOrigenDespacho").text(despacho.sucursal_origen || despacho.nombre_sucursal_origen || 'Sin especificar');
    $("#usuarioCreadorDespacho").text(despacho.nombre_usuario_creador);
    $("#fechaCreacionDespacho").text(formatearFecha(despacho.fecha_creacion));
    $("#estadoDespacho").text(despacho.estado.toUpperCase());
    $("#totalProductosDespacho").text(despacho.total_productos + ' productos');
    $("#transportadorDespacho").text(despacho.nombre_transportador || 'Sin asignar');

    // CONFIGURAR ICONO DE ESTADO
    configurarIconoEstado(despacho.estado);

    // DETALLE ADICIONAL
    if(despacho.detalle_adicional && despacho.detalle_adicional.trim() !== '') {
        $("#detalleAdicionalDespacho").text(despacho.detalle_adicional);
        $("#detalleAdicionalDespachoContainer").show();
    } else {
        $("#detalleAdicionalDespachoContainer").hide();
    }

    // CARGAR PRODUCTOS
    cargarProductosDespacho(despacho.productos_despacho);

    // CARGAR TIMELINE
    cargarTimelineDespacho(despacho);

    // CONFIGURAR BOTONES DE ACCIÓN
    configurarBotonesModalDespacho(despacho);

    // CONFIGURAR BOTONES DE EXPORTACIÓN
    configurarBotonesExportacion(despacho.id);

    // MOSTRAR MODAL
    $("#modalVerDespacho").modal("show");
}

/*=============================================
CARGAR PRODUCTOS EN LA TABLA
=============================================*/
function cargarProductosDespacho(productosJson) {

    var productos = [];
    try {
        productos = JSON.parse(productosJson);
    } catch(e) {
productos = [];
    }

    var html = '';
    var totalCantidad = 0;

    if(productos.length > 0) {
        productos.forEach(function(producto, index) {
            totalCantidad += parseInt(producto.cantidad);

            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td><code>${producto.codigo}</code></td>
                    <td>${producto.descripcion}</td>
                    <td class="text-center"><strong>${producto.cantidad}</strong></td>
                    <td><small>${producto.observacion || 'Sin observaciones'}</small></td>
                </tr>
            `;
        });
    } else {
        html = `
            <tr>
                <td colspan="5" class="text-center text-muted">
                    <i class="fa fa-info-circle"></i> No hay productos registrados
                </td>
            </tr>
        `;
    }

    $("#productosDespachoBody").html(html);
    $("#totalCantidadDespacho").text(totalCantidad);
}

/*=============================================
CARGAR TIMELINE DEL DESPACHO - VERSIÓN MEJORADA
=============================================*/
function cargarTimelineDespacho(despacho) {

    var html = '';
// 1. CREACIÓN
    html += `
        <div class="time-label">
            <span class="bg-blue">
                <i class="fa fa-plus-circle"></i> ${formatearFecha(despacho.fecha_creacion)}
            </span>
        </div>
        <div>
            <i class="fa fa-file-o bg-blue"></i>
            <div class="timeline-item">
                <span class="time">
                    <i class="fa fa-clock-o"></i> ${formatearHora(despacho.fecha_creacion)}
                </span>
                <h3 class="timeline-header">
                    Despacho creado por <strong>${despacho.nombre_usuario_creador}</strong>
                </h3>
                <div class="timeline-body">
                    Despacho <strong>${despacho.numero_despacho}</strong> creado desde <strong>${despacho.sucursal_origen}</strong>.
                    <br>
                    <small class="text-muted">
                        <i class="fa fa-cubes"></i> ${despacho.total_productos} productos •

                        <i class="fa fa-calculator"></i> ${despacho.total_cantidad} unidades
                    </small>
                </div>
            </div>
        </div>
    `;

    // 2. ACEPTACIÓN / EN TRÁNSITO
    if(despacho.estado === 'en_transito' || despacho.estado === 'entregado') {

        // Usar fecha_actualizacion si no existe fecha_aceptacion específica
        var fechaAceptacion = despacho.fecha_aceptacion || despacho.fecha_actualizacion;
        var usuarioAceptacion = despacho.nombre_transportador || despacho.usuario_aceptacion || 'Sistema';

        if(fechaAceptacion && fechaAceptacion !== despacho.fecha_creacion) {
            html += `
                <div class="time-label">
                    <span class="bg-green">
                        <i class="fa fa-check"></i> ${formatearFecha(fechaAceptacion)}
                    </span>
                </div>
                <div>
                    <i class="fa fa-truck bg-green"></i>
                    <div class="timeline-item">
                        <span class="time">
                            <i class="fa fa-clock-o"></i> ${formatearHora(fechaAceptacion)}
                        </span>
                        <h3 class="timeline-header">
                            Despacho aceptado por <strong>${usuarioAceptacion}</strong>
                        </h3>
                        <div class="timeline-body">
                            Los productos han sido cargados y están en tránsito.
                            ${despacho.nombre_transportador ? '<br><small class="text-muted"><i class="fa fa-user"></i> Transportador: ' + despacho.nombre_transportador + '</small>' : ''}
                        </div>
                    </div>
                </div>
            `;
        }
    }

    // 3. ENTREGA (si aplica)
    if(despacho.estado === 'entregado') {
        var fechaEntrega = despacho.fecha_entrega || despacho.fecha_actualizacion;

        html += `
            <div class="time-label">
                <span class="bg-gray">
                    <i class="fa fa-flag-checkered"></i> ${formatearFecha(fechaEntrega)}
                </span>
            </div>
            <div>
                <i class="fa fa-flag-checkered bg-gray"></i>
                <div class="timeline-item">
                    <span class="time">
                        <i class="fa fa-clock-o"></i> ${formatearHora(fechaEntrega)}
                    </span>
                    <h3 class="timeline-header text-success">
                        <strong>Despacho entregado exitosamente</strong>
                    </h3>
                    <div class="timeline-body">
                        El despacho ha sido completado y entregado en su destino.
                    </div>
                </div>
            </div>
        `;
    }

    // 4. CANCELACIÓN
    if(despacho.estado === 'cancelado') {
        var fechaCancelacion = despacho.fecha_cancelacion || despacho.fecha_actualizacion;
        var usuarioCancelacion = despacho.usuario_cancelacion || despacho.nombre_usuario_creador || 'Sistema';

        html += `
            <div class="time-label">
                <span class="bg-red">
                    <i class="fa fa-ban"></i> ${formatearFecha(fechaCancelacion)}
                </span>
            </div>
            <div>
                <i class="fa fa-ban bg-red"></i>
                <div class="timeline-item">
                    <span class="time">
                        <i class="fa fa-clock-o"></i> ${formatearHora(fechaCancelacion)}
                    </span>
                    <h3 class="timeline-header text-red">
                        Despacho cancelado por <strong>${usuarioCancelacion}</strong>
                    </h3>
                    <div class="timeline-body">
                        <div class="alert alert-danger" style="margin: 10px 0;">
                            <strong><i class="fa fa-exclamation-triangle"></i> Motivo:</strong>
                            <br>
                            ${despacho.motivo_cancelacion || 'Sin motivo especificado'}
                        </div>
                        <small class="text-muted">
                            <i class="fa fa-clock-o"></i> Cancelado el ${formatearFecha(fechaCancelacion)} a las ${formatearHora(fechaCancelacion)}
                        </small>
                    </div>
                </div>
            </div>
        `;
    }

    // 5. ESTADO ACTUAL (si no está finalizado)
    if(despacho.estado === 'pendiente') {
        html += `
            <div class="time-label">
                <span class="bg-yellow">
                    <i class="fa fa-hourglass-half"></i> Estado Actual
                </span>
            </div>
            <div>
                <i class="fa fa-hourglass-half bg-yellow"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header text-yellow">
                        <strong>Pendiente de aceptación</strong>
                    </h3>
                    <div class="timeline-body">
                        El despacho está esperando ser aceptado por un transportador.
                    </div>
                </div>
            </div>
        `;
    } else if(despacho.estado === 'en_transito') {
        html += `
            <div class="time-label">
                <span class="bg-blue">
                    <i class="fa fa-truck"></i> Estado Actual
                </span>
            </div>
            <div>
                <i class="fa fa-truck bg-blue"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header text-blue">
                        <strong>En tránsito</strong>
                    </h3>
                    <div class="timeline-body">
                        Los productos están siendo transportados a su destino.
                        ${despacho.nombre_transportador ? '<br><small class="text-muted"><i class="fa fa-user"></i> Transportador: ' + despacho.nombre_transportador + '</small>' : ''}
                    </div>
                </div>
            </div>
        `;
    }

    // 6. FIN TIMELINE
    html += `
        <div>
            <i class="fa fa-clock-o bg-gray"></i>
        </div>
    `;

    $("#timelineDespacho").html(html);
}

/*=============================================
CONFIGURAR ICONO DE ESTADO
=============================================*/
function configurarIconoEstado(estado) {

    var $icono = $("#estadoIconDespacho");
    var configuraciones = {
        'pendiente': { icon: 'fa-clock-o', color: 'bg-yellow' },
        'en_transito': { icon: 'fa-truck', color: 'bg-blue' },
        'entregado': { icon: 'fa-check', color: 'bg-green' },
        'cancelado': { icon: 'fa-ban', color: 'bg-red' }
    };

    var config = configuraciones[estado] || { icon: 'fa-question', color: 'bg-gray' };

    $icono.removeClass().addClass('info-box-icon ' + config.color);
    $icono.find('i').removeClass().addClass('fa ' + config.icon);
}

/*=============================================
CONFIGURAR BOTONES DEL MODAL SEGÚN ESTADO Y PERFIL
=============================================*/
function configurarBotonesModalDespacho(despacho) {

    var html = '';
    var perfilUsuario = window.perfilUsuario || "Usuario";
// BOTÓN ACEPTAR (solo para pendientes y solo Transportador/Administrador)
    if(despacho.estado === 'pendiente' && (perfilUsuario === 'Transportador' || perfilUsuario === 'Administrador')) {
html += `
            <button type="button" class="btn btn-success" onclick="aceptarDespachoModal(${despacho.id})">
                <i class="fa fa-check"></i> Aceptar Despacho
            </button>
        `;
    } else {
}

    // BOTÓN CANCELAR (solo para pendientes y solo Administrador/Transportador)
    if(despacho.estado === 'pendiente' && (perfilUsuario === 'Administrador' || perfilUsuario === 'Transportador')) {
html += `
            <button type="button" class="btn btn-warning" onclick="cancelarDespachoModal(${despacho.id}, '${despacho.estado}')">
                <i class="fa fa-ban"></i> Cancelar
            </button>
        `;
    } else {
}

    // BOTÓN EDITAR (solo pendientes y solo Administrador/Especial/Contador)
    if(despacho.estado === 'pendiente' && (perfilUsuario === 'Administrador' || perfilUsuario === 'Especial' || perfilUsuario === 'Contador')) {
html += `
            <button type="button" class="btn btn-info" onclick="editarDespacho(${despacho.id})">
                <i class="fa fa-edit"></i> Editar
            </button>
        `;
    } else {
}

    // BOTÓN CAMBIAR ESTADO (solo Administrador - en cualquier estado)
    if(perfilUsuario === 'Administrador') {
        html += `
            <button type="button" class="btn btn-primary" onclick="abrirModalCambiarEstado(${despacho.id}, '${despacho.numero_despacho}', '${despacho.estado}')">
                <i class="fa fa-exchange"></i> Cambiar Estado
            </button>
        `;
    }

    // BOTÓN ELIMINAR (pendientes solo para Administrador/Especial/Contador, cualquier estado solo para administradores)
    var puedeEliminar = (despacho.estado === 'pendiente' && (perfilUsuario === 'Administrador' || perfilUsuario === 'Especial' || perfilUsuario === 'Contador')) || perfilUsuario === 'Administrador';
if(puedeEliminar) {
var textoEliminar = despacho.estado === 'pendiente' ? 'Eliminar' : 'Eliminar (Admin)';
        var claseBoton = despacho.estado === 'pendiente' ? 'btn-danger' : 'btn-warning';

        html += `
            <button type="button" class="btn ${claseBoton}" onclick="eliminarDespachoModal(${despacho.id}, '${despacho.numero_despacho}', '${despacho.estado}')">
                <i class="fa fa-trash"></i> ${textoEliminar}
            </button>
        `;
    } else {
}

    $("#botonesAccionDespacho").html(html);
}

/*=============================================
ACEPTAR DESPACHO DESDE TABLA
=============================================*/
$(document).on("click", ".btnAceptarDespacho", function(e){
    e.preventDefault();
    var idDespacho = $(this).attr("idDespacho");
    aceptarDespachoDirecto(idDespacho);
});

function aceptarDespachoDirecto(idDespacho) {
swal({
        title: "¿Aceptar despacho?",
        text: "Al aceptar este despacho, los productos se descontarán del stock local y se agregarán al stock en tránsito.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3c8dbc",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, aceptar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {

        if(result.value) {
            ejecutarAceptarDespacho(idDespacho);
        }
    });
}

/*=============================================
ACEPTAR DESPACHO DESDE MODAL
=============================================*/
function aceptarDespachoModal(idDespacho) {
$("#modalVerDespacho").modal("hide");

    swal({
        title: "¿Aceptar despacho?",
        text: "Al aceptar este despacho, los productos se descontarán del stock local y se agregarán al stock en tránsito.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3c8dbc",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, aceptar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {

        if(result.value) {
            ejecutarAceptarDespacho(idDespacho);
        }
    });
}

/*=============================================
EJECUTAR ACEPTACIÓN DE DESPACHO
=============================================*/
function ejecutarAceptarDespacho(idDespacho) {

    var datos = new FormData();
    datos.append("aceptarDespacho", idDespacho);
$.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
if(respuesta.success) {
                swal({
                    title: "¡Despacho aceptado!",
                    text: respuesta.message,
                    type: "success",
                    confirmButtonText: "Cerrar"
                }).then(function() {
                    $('.tablaDespachos').DataTable().ajax.reload();
                });
            } else {
                swal({
                    title: "Error",
                    text: respuesta.error || "No se pudo aceptar el despacho",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
swal({
                title: "Error de conexión",
                text: "No se pudo aceptar el despacho",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
CANCELAR DESPACHO DESDE TABLA
=============================================*/
$(document).on("click", ".btnCancelarDespacho", function(e){
    e.preventDefault();
    var idDespacho = $(this).attr("idDespacho");
    var estadoDespacho = $(this).attr("estadoDespacho");
    cancelarDespachoDirecto(idDespacho, estadoDespacho);
});

function cancelarDespachoDirecto(idDespacho, estadoDespacho) {
swal({
        title: "¿Cancelar despacho?",
        text: "Ingrese el motivo de la cancelación:",
        type: "warning",
        input: "textarea",
        inputPlaceholder: "Motivo de la cancelación...",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3c8dbc",
        confirmButtonText: "Sí, cancelar",
        cancelButtonText: "No cancelar",
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un motivo de cancelación'
            }
        }
    }).then(function(result) {

        if(result.value) {
            ejecutarCancelarDespacho(idDespacho, result.value);
        }
    });
}

/*=============================================
CANCELAR DESPACHO DESDE MODAL
=============================================*/
function cancelarDespachoModal(idDespacho, estadoDespacho) {
$("#modalVerDespacho").modal("hide");

    swal({
        title: "¿Cancelar despacho?",
        text: "Ingrese el motivo de la cancelación:",
        type: "warning",
        input: "textarea",
        inputPlaceholder: "Motivo de la cancelación...",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3c8dbc",
        confirmButtonText: "Sí, cancelar",
        cancelButtonText: "No cancelar",
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un motivo de cancelación'
            }
        }
    }).then(function(result) {

        if(result.value) {
            ejecutarCancelarDespacho(idDespacho, result.value);
        }
    });
}

/*=============================================
EJECUTAR CANCELACIÓN DE DESPACHO
=============================================*/
function ejecutarCancelarDespacho(idDespacho, motivo) {

    var datos = new FormData();
    datos.append("cancelarDespacho", idDespacho);
    datos.append("motivoCancelacion", motivo);
$.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
if(respuesta.success) {
swal({
                    title: "¡Despacho cancelado!",
                    text: respuesta.message,
                    type: "success",
                    confirmButtonText: "Cerrar"
                }).then(function() {

// Método 1: Recarga simple de DataTable
                    if($.fn.DataTable.isDataTable('.tablaDespachos')) {
$('.tablaDespachos').DataTable().ajax.reload(null, false);
                    }

                    // Método 2: Recarga de página después de un breve delay
                    setTimeout(function() {
window.location.reload();
                    }, 1500);
                });
            } else {
swal({
                    title: "Error",
                    text: respuesta.error || "No se pudo cancelar el despacho",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
swal({
                title: "Error de conexión",
                text: "No se pudo cancelar el despacho",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
CAMBIAR ESTADO DESDE TABLA (SOLO ADMIN)
=============================================*/
$(document).on("click", ".btnCambiarEstadoDespacho", function(e){
    e.preventDefault();
    var idDespacho = $(this).attr("idDespacho");
    var numeroDespacho = $(this).attr("numeroDespacho");
    var estadoDespacho = $(this).attr("estadoDespacho");
    abrirModalCambiarEstado(idDespacho, numeroDespacho, estadoDespacho);
});

/*=============================================
CONFIRMAR Y EJECUTAR CAMBIO DE ESTADO
=============================================*/
function confirmarCambiarEstadoDespacho() {
    var idDespacho = $("#idDespachoCambiarEstado").val();
    var nuevoEstado = $("#nuevoEstadoDespacho").val();
    var observaciones = $("#observacionesCambiarEstado").val();

    if(!nuevoEstado) {
        swal({
            title: "Campo requerido",
            text: "Debe seleccionar el nuevo estado",
            type: "warning",
            confirmButtonText: "Cerrar"
        });
        return;
    }

    var datos = new FormData();
    datos.append("cambiarEstado", "1");
    datos.append("idDespacho", idDespacho);
    datos.append("nuevoEstado", nuevoEstado);
    datos.append("observaciones", observaciones);

    $.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            if(respuesta.success) {
                $("#modalCambiarEstadoDespacho").modal("hide");
                swal({
                    title: "¡Estado actualizado!",
                    text: respuesta.message,
                    type: "success",
                    confirmButtonText: "Cerrar"
                }).then(function() {
                    $('.tablaDespachos').DataTable().ajax.reload();
                    if(typeof $("#modalVerDespacho").modal === 'function' && $("#modalVerDespacho").hasClass('in')) {
                        $("#modalVerDespacho").modal("hide");
                    }
                });
            } else {
                swal({
                    title: "Error",
                    text: respuesta.error || "No se pudo cambiar el estado",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
            swal({
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
ELIMINAR DESPACHO DESDE TABLA
=============================================*/
$(document).on("click", ".btnEliminarDespacho", function(e){
    e.preventDefault();
    var idDespacho = $(this).attr("idDespacho");
    var numeroDespacho = $(this).attr("numeroDespacho");
    var estadoDespacho = $(this).attr("estadoDespacho");
    eliminarDespachoDirecto(idDespacho, numeroDespacho, estadoDespacho);
});

function eliminarDespachoDirecto(idDespacho, numeroDespacho, estadoDespacho) {
var titulo = "¿Eliminar despacho " + numeroDespacho + "?";
    var texto = "¡Esta acción no se puede deshacer!";
    var tipo = "warning";

    // Si es un despacho que no está pendiente, mostrar advertencia especial
    if(estadoDespacho && estadoDespacho !== 'pendiente') {
        texto = "⚠️ ADVERTENCIA: Este despacho está en estado '" + estadoDespacho + "'. ¡Esta acción no se puede deshacer!";
        tipo = "error";
    }

    swal({
        title: titulo,
        text: texto,
        type: tipo,
        showCancelButton: true,
        confirmButtonColor: estadoDespacho !== 'pendiente' ? "#f39c12" : "#d33",
        cancelButtonColor: "#3c8dbc",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {

        if(result.value) {
            ejecutarEliminarDespacho(idDespacho);
        }
    });
}
window.eliminarDespachoDirecto = eliminarDespachoDirecto;

/*=============================================
ELIMINAR DESPACHO DESDE MODAL
=============================================*/
function eliminarDespachoModal(idDespacho, numeroDespacho, estadoDespacho) {
$("#modalVerDespacho").modal("hide");

    var titulo = "¿Eliminar despacho " + numeroDespacho + "?";
    var texto = "¡Esta acción no se puede deshacer!";
    var tipo = "warning";

    // Si es un despacho que no está pendiente, mostrar advertencia especial
    if(estadoDespacho && estadoDespacho !== 'pendiente') {
        texto = "⚠️ ADVERTENCIA: Este despacho está en estado '" + estadoDespacho + "'. ¡Esta acción no se puede deshacer!";
        tipo = "error";
    }

    swal({
        title: titulo,
        text: texto,
        type: tipo,
        showCancelButton: true,
        confirmButtonColor: estadoDespacho !== 'pendiente' ? "#f39c12" : "#d33",
        cancelButtonColor: "#3c8dbc",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {

        if(result.value) {
            ejecutarEliminarDespacho(idDespacho);
        }
    });
}

/*=============================================
EJECUTAR ELIMINACIÓN DE DESPACHO
=============================================*/
function ejecutarEliminarDespacho(idDespacho) {

    var datos = new FormData();
    datos.append("eliminarDespacho", idDespacho);
$.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
if(respuesta.success) {
                swal({
                    title: "¡Despacho eliminado!",
                    text: respuesta.message,
                    type: "success",
                    confirmButtonText: "Cerrar"
                }).then(function() {
                    $('.tablaDespachos').DataTable().ajax.reload();
                });
            } else {
                swal({
                    title: "Error",
                    text: respuesta.error || "No se pudo eliminar el despacho",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
swal({
                title: "Error de conexión",
                text: "No se pudo eliminar el despacho",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
EDITAR DESPACHO
=============================================*/
function editarDespacho(idDespacho) {
window.location = "crear-despacho?editar=" + idDespacho;
}

/*=============================================
FILTROS RÁPIDOS POR ESTADO
=============================================*/
$(document).on("click", ".btnFiltroEstado", function(){

    var estado = $(this).attr("data-estado");

    // Actualizar botones activos
    $(".btnFiltroEstado").removeClass("active");
    $(this).addClass("active");

    // Aplicar filtro a DataTable
    if(estado === "" || estado === "todos") {
        $('.tablaDespachos').DataTable().columns(4).search("").draw();
    } else {
        // Mapear estados a texto mostrado en tabla
        var estadoTexto = {
            'pendiente': 'PENDIENTE',
            'en_transito': 'EN TRÁNSITO',
            'entregado': 'ENTREGADO',
            'cancelado': 'CANCELADO'
        };

        var textoBuscar = estadoTexto[estado] || estado;
        $('.tablaDespachos').DataTable().columns(4).search(textoBuscar).draw();
    }
});

/*=============================================
FUNCIONES AUXILIARES
=============================================*/
function formatearFecha(fecha) {
    if(!fecha) return 'Sin fecha';

    var date = new Date(fecha);
    var dia = String(date.getDate()).padStart(2, '0');
    var mes = String(date.getMonth() + 1).padStart(2, '0');
    var año = date.getFullYear();

    return dia + '/' + mes + '/' + año;
}

function formatearHora(fecha) {
    if(!fecha) return 'Sin hora';

    var date = new Date(fecha);
    var horas = String(date.getHours()).padStart(2, '0');
    var minutos = String(date.getMinutes()).padStart(2, '0');

    return horas + ':' + minutos;
}

/*=============================================
INICIALIZACIÓN
=============================================*/
$(document).ready(function() {

    // Activar tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Recargar tabla cada 60 segundos
    setInterval(function() {
        $('.tablaDespachos').DataTable().ajax.reload(null, false);
    }, 60000);
});

/*=============================================
CONFIGURAR BOTONES DE EXPORTACIÓN
=============================================*/
function configurarBotonesExportacion(idDespacho) {
// Asignar ID del despacho a los botones
    $(".btnExportarPDF").attr("idDespacho", idDespacho);
    $(".btnExportarExcel").attr("idDespacho", idDespacho);
}

/*=============================================
EXPORTAR DESPACHO A PDF
=============================================*/
$(document).on("click", ".btnExportarPDF", function(){

    var idDespacho = $(this).attr("idDespacho");
if(!idDespacho) {
        swal({
            title: "Error",
            text: "No se pudo identificar el despacho",
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }

    // Mostrar loading
    swal({
        title: "Generando PDF...",
        text: "Por favor espere mientras se genera el documento",
        type: "info",
        showConfirmButton: false,
        allowOutsideClick: false
    });

    var datos = new FormData();
    datos.append("accion", "exportar_pdf");
    datos.append("idDespacho", idDespacho);

    $.ajax({
        url: "ajax/exportar-despacho-detalle.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
if(respuesta.success) {
                // Cerrar loading
                swal.close();

                // Crear enlace de descarga
                var link = document.createElement('a');
                link.href = respuesta.url;
                link.download = respuesta.nombreArchivo;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                swal({
                    title: "¡PDF Generado!",
                    text: "El documento se ha descargado correctamente",
                    type: "success",
                    confirmButtonText: "Cerrar"
                });

            } else {
                swal({
                    title: "Error",
                    text: respuesta.error || "No se pudo generar el PDF",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
swal({
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
});

/*=============================================
EXPORTAR DESPACHO A EXCEL
=============================================*/
$(document).on("click", ".btnExportarExcel", function(){

    var idDespacho = $(this).attr("idDespacho");
if(!idDespacho) {
        swal({
            title: "Error",
            text: "No se pudo identificar el despacho",
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }

    // Mostrar loading
    swal({
        title: "Generando Excel...",
        text: "Por favor espere mientras se genera el documento",
        type: "info",
        showConfirmButton: false,
        allowOutsideClick: false
    });

    var datos = new FormData();
    datos.append("accion", "exportar_excel");
    datos.append("idDespacho", idDespacho);

    $.ajax({
        url: "ajax/exportar-despacho-detalle.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
if(respuesta.success) {
                // Cerrar loading
                swal.close();

                // Crear enlace de descarga
                var link = document.createElement('a');
                link.href = respuesta.url;
                link.download = respuesta.nombreArchivo;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                swal({
                    title: "¡Excel Generado!",
                    text: "El documento se ha descargado correctamente",
                    type: "success",
                    confirmButtonText: "Cerrar"
                });

            } else {
                swal({
                    title: "Error",
                    text: respuesta.error || "No se pudo generar el Excel",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
swal({
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
});

/*=============================================
VER DETALLES DE DESPACHO DE TRANSPORTADOR
=============================================*/
$(document).on("click", ".btnVerDetalleDespacho", function(){

    var idDespacho = $(this).attr("data-id");
var datos = new FormData();
    datos.append("idDespacho", idDespacho);

    $.ajax({
        url: "ajax/despachos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
if(respuesta.success && respuesta.data) {
                mostrarDetallesDespacho(respuesta.data);
            } else {
                swal({
                    title: "Error",
                    text: respuesta.error || "No se pudieron cargar los detalles del despacho",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
swal({
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
});