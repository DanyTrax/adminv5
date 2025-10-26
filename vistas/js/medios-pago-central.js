/*=============================================
JAVASCRIPT MEDIOS DE PAGO CENTRAL
=============================================*/

$(document).ready(function() {
    // Cargar datos iniciales
    cargarMediosPagoCentral();
    cargarSucursalesEstado();
    
    // Event listeners
    $("#btnNuevoMedioPago").click(function() {
        abrirModalNuevoMedioPago();
    });
    
    $("#btnActivarSucursales").click(function() {
        abrirModalActivarSucursales();
    });
    
    $("#btnDesactivarSucursales").click(function() {
        abrirModalDesactivarSucursales();
    });
    
    $("#btnVerEstado").click(function() {
        mostrarEstadoCompleto();
    });
    
    $("#btnGuardarMedioPago").click(function() {
        guardarMedioPago();
    });
    
    $("#btnConfirmarActivacion").click(function() {
        confirmarActivacionSucursales();
    });
    
    $("#btnConfirmarDesactivacion").click(function() {
        confirmarDesactivacionSucursales();
    });
    
    $("#selectSucursalEstado").change(function() {
        cargarEstadoMediosSucursal($(this).val());
    });
    
    $("#selectAllMedios").change(function() {
        var isChecked = $(this).is(":checked");
        $(".checkbox-medio").prop("checked", isChecked);
        actualizarMediosSeleccionados();
    });
    
    $(document).on("change", ".checkbox-medio", function() {
        actualizarMediosSeleccionados();
    });
});

// Cargar medios de pago centrales
function cargarMediosPagoCentral() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_medios_pago_central" },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = "";
                respuesta.data.forEach(function(medio) {
                    var estadoBadge = medio.activo ? 
                        '<span class="label label-success">Activo</span>' : 
                        '<span class="label label-danger">Inactivo</span>';
                    
                    var tipoBadge = getTipoBadge(medio.tipo);
                    
                    html += `
                        <tr>
                            <td>
                                <input type="checkbox" class="checkbox-medio" value="${medio.id}" data-codigo="${medio.codigo}" data-nombre="${medio.nombre}">
                            </td>
                            <td>${medio.codigo}</td>
                            <td>${medio.nombre}</td>
                            <td>${tipoBadge}</td>
                            <td>${estadoBadge}</td>
                            <td>
                                <button class="btn btn-xs btn-primary" onclick="editarMedioPago(${medio.id})" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-xs btn-info" onclick="verEstadoMedio(${medio.id})" title="Ver Estado">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button class="btn btn-xs btn-danger" onclick="eliminarMedioPago(${medio.id})" title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                $("#tablaMediosPagoCentral tbody").html(html);
                $("#tablaMediosPagoCentral").DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                    },
                    "pageLength": 10,
                    "order": [[1, "asc"]]
                });
            }
        },
        error: function() {
            console.error("Error cargando medios de pago centrales");
        }
    });
}

// Cargar sucursales para estado
function cargarSucursalesEstado() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_estado" },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = '<option value="">Seleccionar sucursal...</option>';
                respuesta.data.forEach(function(sucursal) {
                    html += `<option value="${sucursal.id}">${sucursal.nombre}</option>`;
                });
                $("#selectSucursalEstado").html(html);
            }
        }
    });
}

// Cargar estado de medios por sucursal
function cargarEstadoMediosSucursal(sucursalId) {
    if (!sucursalId) {
        $("#estadoMediosSucursal").html('<p class="text-muted">Selecciona una sucursal para ver el estado de los medios</p>');
        return;
    }
    
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { 
            accion: "obtener_estado_medios_sucursal",
            sucursal_id: sucursalId
        },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = "";
                if (respuesta.data.length > 0) {
                    respuesta.data.forEach(function(medio) {
                        var estadoBadge = medio.activo ? 
                            '<span class="label label-success">Activo</span>' : 
                            '<span class="label label-danger">Inactivo</span>';
                        
                        html += `
                            <div class="media">
                                <div class="media-body">
                                    <h5 class="media-heading">${medio.nombre}</h5>
                                    <p class="text-muted">${medio.codigo} - ${medio.tipo}</p>
                                    <p>${estadoBadge}</p>
                                </div>
                                <div class="media-right">
                                    <button class="btn btn-xs btn-warning" onclick="toggleEstadoMedioSucursal(${medio.id}, ${sucursalId}, ${medio.activo ? 0 : 1})" title="${medio.activo ? 'Desactivar' : 'Activar'}">
                                        <i class="fa fa-${medio.activo ? 'times' : 'check'}"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html = '<p class="text-muted">No hay medios asignados a esta sucursal</p>';
                }
                
                $("#estadoMediosSucursal").html(html);
            }
        }
    });
}

// Actualizar medios seleccionados
function actualizarMediosSeleccionados() {
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push({
            id: $(this).val(),
            codigo: $(this).data("codigo"),
            nombre: $(this).data("nombre")
        });
    });
    
    // Actualizar en modal de activación
    var htmlActivar = "";
    if (mediosSeleccionados.length > 0) {
        mediosSeleccionados.forEach(function(medio) {
            htmlActivar += `<span class="label label-success" style="margin: 2px;">${medio.codigo}</span>`;
        });
    } else {
        htmlActivar = '<p class="text-muted">Selecciona medios de pago de la tabla</p>';
    }
    $("#mediosSeleccionadosActivar").html(htmlActivar);
    
    // Actualizar en modal de desactivación
    var htmlDesactivar = "";
    if (mediosSeleccionados.length > 0) {
        mediosSeleccionados.forEach(function(medio) {
            htmlDesactivar += `<span class="label label-warning" style="margin: 2px;">${medio.codigo}</span>`;
        });
    } else {
        htmlDesactivar = '<p class="text-muted">Selecciona medios de pago de la tabla</p>';
    }
    $("#mediosSeleccionadosDesactivar").html(htmlDesactivar);
}

// Abrir modal nuevo medio de pago
function abrirModalNuevoMedioPago() {
    $("#formNuevoMedioPago")[0].reset();
    $("#modalNuevoMedioPago").modal("show");
}

// Abrir modal activar sucursales
function abrirModalActivarSucursales() {
    var mediosSeleccionados = $(".checkbox-medio:checked").length;
    if (mediosSeleccionados === 0) {
        Swal.fire({
            type: "warning",
            title: "Selección Requerida",
            text: "Debes seleccionar al menos un medio de pago",
            showConfirmButton: true
        });
        return;
    }
    
    cargarSucursalesDestinoActivar();
    $("#modalActivarSucursales").modal("show");
}

// Abrir modal desactivar sucursales
function abrirModalDesactivarSucursales() {
    var mediosSeleccionados = $(".checkbox-medio:checked").length;
    if (mediosSeleccionados === 0) {
        Swal.fire({
            type: "warning",
            title: "Selección Requerida",
            text: "Debes seleccionar al menos un medio de pago",
            showConfirmButton: true
        });
        return;
    }
    
    cargarSucursalesDestinoDesactivar();
    $("#modalDesactivarSucursales").modal("show");
}

// Cargar sucursales destino para activación
function cargarSucursalesDestinoActivar() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_destino_activar" },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = "";
                respuesta.data.forEach(function(sucursal) {
                    html += `
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="checkbox-sucursal-activar" value="${sucursal.id}">
                                ${sucursal.nombre}
                            </label>
                        </div>
                    `;
                });
                $("#sucursalesDestinoActivar").html(html);
            }
        }
    });
}

// Cargar sucursales destino para desactivación
function cargarSucursalesDestinoDesactivar() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_destino_desactivar" },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = "";
                respuesta.data.forEach(function(sucursal) {
                    html += `
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="checkbox-sucursal-desactivar" value="${sucursal.id}">
                                ${sucursal.nombre}
                            </label>
                        </div>
                    `;
                });
                $("#sucursalesDestinoDesactivar").html(html);
            }
        }
    });
}

// Guardar medio de pago
function guardarMedioPago() {
    var datos = {
        accion: "crear_medio_pago",
        codigo: $("#codigoMedioPago").val(),
        nombre: $("#nombreMedioPago").val(),
        descripcion: $("#descripcionMedioPago").val(),
        tipo: $("#tipoMedioPago").val()
    };
    
    if (!datos.codigo || !datos.nombre || !datos.tipo) {
        Swal.fire({
            type: "error",
            title: "Error",
            text: "Todos los campos son obligatorios",
            showConfirmButton: true
        });
        return;
    }
    
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: datos,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                Swal.fire({
                    type: "success",
                    title: "¡Éxito!",
                    text: "Medio de pago creado correctamente",
                    showConfirmButton: false,
                    timer: 2000
                });
                $("#modalNuevoMedioPago").modal("hide");
                cargarMediosPagoCentral();
            } else {
                Swal.fire({
                    type: "error",
                    title: "Error",
                    text: respuesta.error,
                    showConfirmButton: true
                });
            }
        }
    });
}

// Confirmar activación en sucursales
function confirmarActivacionSucursales() {
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push($(this).val());
    });
    
    var sucursalesSeleccionadas = [];
    $(".checkbox-sucursal-activar:checked").each(function() {
        sucursalesSeleccionadas.push($(this).val());
    });
    
    if (sucursalesSeleccionadas.length === 0) {
        Swal.fire({
            type: "warning",
            title: "Selección Requerida",
            text: "Debes seleccionar al menos una sucursal",
            showConfirmButton: true
        });
        return;
    }
    
    var datos = {
        accion: "activar_medios_sucursales",
        medios_pago: mediosSeleccionados,
        sucursales: sucursalesSeleccionadas
    };
    
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: datos,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                Swal.fire({
                    type: "success",
                    title: "¡Éxito!",
                    text: `Medios activados en ${respuesta.activaciones} sucursales`,
                    showConfirmButton: false,
                    timer: 2000
                });
                $("#modalActivarSucursales").modal("hide");
                cargarMediosPagoCentral();
            } else {
                Swal.fire({
                    type: "error",
                    title: "Error",
                    text: respuesta.error,
                    showConfirmButton: true
                });
            }
        }
    });
}

// Confirmar desactivación en sucursales
function confirmarDesactivacionSucursales() {
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push($(this).val());
    });
    
    var sucursalesSeleccionadas = [];
    $(".checkbox-sucursal-desactivar:checked").each(function() {
        sucursalesSeleccionadas.push($(this).val());
    });
    
    if (sucursalesSeleccionadas.length === 0) {
        Swal.fire({
            type: "warning",
            title: "Selección Requerida",
            text: "Debes seleccionar al menos una sucursal",
            showConfirmButton: true
        });
        return;
    }
    
    var datos = {
        accion: "desactivar_medios_sucursales",
        medios_pago: mediosSeleccionados,
        sucursales: sucursalesSeleccionadas
    };
    
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: datos,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                Swal.fire({
                    type: "success",
                    title: "¡Éxito!",
                    text: `Medios desactivados en ${respuesta.desactivaciones} sucursales`,
                    showConfirmButton: false,
                    timer: 2000
                });
                $("#modalDesactivarSucursales").modal("hide");
                cargarMediosPagoCentral();
            } else {
                Swal.fire({
                    type: "error",
                    title: "Error",
                    text: respuesta.error,
                    showConfirmButton: true
                });
            }
        }
    });
}

// Mostrar estado completo
function mostrarEstadoCompleto() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_estado_completo" },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = "<h4>Estado Completo de Medios de Pago</h4>";
                respuesta.data.forEach(function(sucursal) {
                    html += `<h5>${sucursal.nombre}</h5>`;
                    html += `<ul>`;
                    sucursal.medios.forEach(function(medio) {
                        var estado = medio.activo ? "Activo" : "Inactivo";
                        var color = medio.activo ? "success" : "danger";
                        html += `<li><span class="label label-${color}">${estado}</span> ${medio.nombre}</li>`;
                    });
                    html += `</ul>`;
                });
                
                Swal.fire({
                    title: "Estado Completo",
                    html: html,
                    width: "80%",
                    showConfirmButton: true
                });
            }
        }
    });
}

// Obtener badge de tipo
function getTipoBadge(tipo) {
    var badges = {
        'efectivo': '<span class="label label-success">Efectivo</span>',
        'tarjeta': '<span class="label label-primary">Tarjeta</span>',
        'transferencia': '<span class="label label-info">Transferencia</span>',
        'cheque': '<span class="label label-warning">Cheque</span>',
        'otro': '<span class="label label-default">Otro</span>'
    };
    return badges[tipo] || '<span class="label label-default">Otro</span>';
}

// Editar medio de pago
function editarMedioPago(id) {
    // Implementar edición
    console.log("Editar medio de pago:", id);
}

// Ver estado de medio específico
function verEstadoMedio(id) {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { 
            accion: "obtener_estado_medio",
            medio_id: id
        },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = `<h4>Estado del Medio: ${respuesta.medio.nombre}</h4>`;
                html += `<ul>`;
                respuesta.sucursales.forEach(function(sucursal) {
                    var estado = sucursal.activo ? "Activo" : "Inactivo";
                    var color = sucursal.activo ? "success" : "danger";
                    html += `<li><span class="label label-${color}">${estado}</span> ${sucursal.nombre}</li>`;
                });
                html += `</ul>`;
                
                Swal.fire({
                    title: "Estado del Medio",
                    html: html,
                    showConfirmButton: true
                });
            }
        }
    });
}

// Eliminar medio de pago
function eliminarMedioPago(id) {
    Swal.fire({
        title: "¿Confirmar Eliminación?",
        text: "Esta acción no se puede deshacer",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, Eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "ajax/medios-pago-central.ajax.php",
                method: "POST",
                data: { 
                    accion: "eliminar_medio_pago",
                    id: id
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        Swal.fire({
                            type: "success",
                            title: "¡Éxito!",
                            text: "Medio de pago eliminado correctamente",
                            showConfirmButton: false,
                            timer: 2000
                        });
                        cargarMediosPagoCentral();
                    } else {
                        Swal.fire({
                            type: "error",
                            title: "Error",
                            text: respuesta.error,
                            showConfirmButton: true
                        });
                    }
                }
            });
        }
    });
}

// Toggle estado de medio en sucursal
function toggleEstadoMedioSucursal(medioId, sucursalId, nuevoEstado) {
    var accion = nuevoEstado ? "activar" : "desactivar";
    var texto = nuevoEstado ? "activar" : "desactivar";
    
    Swal.fire({
        title: `¿Confirmar ${texto}?`,
        text: `El medio se ${texto}á en esta sucursal`,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: `Sí, ${texto}`,
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "ajax/medios-pago-central.ajax.php",
                method: "POST",
                data: { 
                    accion: "toggle_estado_medio_sucursal",
                    medio_id: medioId,
                    sucursal_id: sucursalId,
                    nuevo_estado: nuevoEstado
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        Swal.fire({
                            type: "success",
                            title: "¡Éxito!",
                            text: `Medio ${texto}do correctamente`,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        cargarEstadoMediosSucursal(sucursalId);
                    } else {
                        Swal.fire({
                            type: "error",
                            title: "Error",
                            text: respuesta.error,
                            showConfirmButton: true
                        });
                    }
                }
            });
        }
    });
}
