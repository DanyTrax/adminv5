$(document).ready(function() {
    // Cargar datos iniciales
    cargarMediosPagoCentral();
    cargarSucursalesEstado();
    
    // Event listeners para checkboxes
    $("#selectAllMedios").on("change", function() {
        $(".checkbox-medio").prop("checked", $(this).prop("checked"));
        actualizarMediosSeleccionados();
    });

    $(document).on("change", ".checkbox-medio", function() {
        actualizarMediosSeleccionados();
    });

    // Event listeners para botones principales
    $("#btnGuardarMedioPagoCentral").click(function(e) {
        e.preventDefault();
        crearMedioPagoCentral();
    });

    $(document).on("click", ".btnEditarMedioPagoCentral", function() {
        var idMedio = $(this).attr("idMedioPago");
        editarMedioPagoCentral(idMedio);
    });

    $("#btnActualizarMedioPagoCentral").click(function(e) {
        e.preventDefault();
        actualizarMedioPagoCentral();
    });

    $(document).on("click", ".btnEliminarMedioPagoCentral", function() {
        var idMedio = $(this).attr("idMedioPago");
        eliminarMedioPagoCentral(idMedio);
    });

    // Event listeners para botones de acción
    $("#btnSincronizarTodos").click(function() {
        sincronizarConSucursalesActivas();
    });

    $("#btnAsignarSucursales").click(function() {
        abrirModalAsignarSucursales();
    });

    $("#btnGestionarAsignaciones").click(function() {
        abrirModalGestionarAsignaciones();
    });

    $("#btnConfirmarAsignacion").click(function() {
        confirmarAsignacionSucursales();
    });

    $("#btnVerEstadoCompleto").click(function() {
        mostrarEstadoCompleto();
    });

    $("#btnDesactivarTodos").click(function() {
        desactivarEnTodasLasSucursales();
    });

    $("#btnEliminarAsignaciones").click(function() {
        eliminarTodasLasAsignaciones();
    });

    $("#selectSucursalEstado").change(function() {
        cargarEstadoMediosSucursal($(this).val());
    });
});

// Cargar medios de pago central
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
                    var estadoBadge = medio.activo == 1 ? 
                        '<span class="label label-success">Activo</span>' : 
                        '<span class="label label-danger">Inactivo</span>';
                    
                    var tipoBadge = getTipoBadge(medio.tipo);
                    
                    html += `
                        <tr>
                            <td><input type="checkbox" class="checkbox-medio" value="${medio.id}" data-codigo="${medio.codigo}"></td>
                            <td>${medio.codigo}</td>
                            <td>${medio.nombre}</td>
                            <td>${tipoBadge}</td>
                            <td>${estadoBadge}</td>
                            <td>
                                <button class="btn btn-xs btn-info" onclick="verEstadoMedio(${medio.id})" title="Ver Estado">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-warning btn-xs btnEditarMedioPagoCentral" idMedioPago="${medio.id}" title="Editar">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger btn-xs btnEliminarMedioPagoCentral" idMedioPago="${medio.id}" title="Eliminar">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                $(".tablaMediosPagoCentral tbody").html(html);
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
        'otro': '<span class="label label-default">Otro</span>'
    };
    return badges[tipo] || '<span class="label label-default">Otro</span>';
}

// Crear medio de pago central
function crearMedioPagoCentral() {
    var datos = {
        accion: "crear_medio_pago",
        codigo: $("#nuevoCodigoMedio").val(),
        nombre: $("#nuevoNombreMedio").val(),
        descripcion: $("#nuevaDescripcionMedio").val(),
        tipo: $("#nuevoTipoMedio").val()
    };

    if (!datos.codigo || !datos.nombre) {
        Swal.fire({
            type: "warning",
            title: "Campos Requeridos",
            text: "Debes completar código y nombre",
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
                    text: respuesta.message,
                    showConfirmButton: false,
                    timer: 2000
                });
                $("#modalAgregarMedioPagoCentral").modal("hide");
                cargarMediosPagoCentral();
                limpiarFormularioAgregar();
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

// Editar medio de pago central
function editarMedioPagoCentral(idMedio) {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_medio_pago_central", id: idMedio },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var medio = respuesta.data;
                $("#editarIdMedio").val(medio.id);
                $("#editarCodigoMedio").val(medio.codigo);
                $("#editarNombreMedio").val(medio.nombre);
                $("#editarDescripcionMedio").val(medio.descripcion);
                $("#editarTipoMedio").val(medio.tipo);
                $("#editarEstadoMedio").val(medio.activo);
                $("#modalEditarMedioPagoCentral").modal("show");
            }
        }
    });
}

// Actualizar medio de pago central
function actualizarMedioPagoCentral() {
    var datos = {
        accion: "editar_medio_pago",
        id: $("#editarIdMedio").val(),
        codigo: $("#editarCodigoMedio").val(),
        nombre: $("#editarNombreMedio").val(),
        descripcion: $("#editarDescripcionMedio").val(),
        tipo: $("#editarTipoMedio").val(),
        activo: $("#editarEstadoMedio").val()
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
                    text: respuesta.message,
                    showConfirmButton: false,
                    timer: 2000
                });
                $("#modalEditarMedioPagoCentral").modal("hide");
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

// Eliminar medio de pago central
function eliminarMedioPagoCentral(idMedio) {
    Swal.fire({
        title: "¿Confirmar Eliminación?",
        text: "Esta acción eliminará el medio de pago y todas sus asignaciones",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, Eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "ajax/medios-pago-central.ajax.php",
                method: "POST",
                data: { accion: "eliminar_medio_pago", id: idMedio },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        Swal.fire({
                            type: "success",
                            title: "¡Éxito!",
                            text: respuesta.message,
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

// Actualizar medios seleccionados en modales
function actualizarMediosSeleccionados() {
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push({
            id: $(this).val(),
            codigo: $(this).data("codigo")
        });
    });

    var htmlAsignar = "";
    var htmlGestionar = "";
    if (mediosSeleccionados.length > 0) {
        mediosSeleccionados.forEach(function(medio) {
            htmlAsignar += `<span class="label label-primary" style="margin: 2px;">${medio.codigo}</span>`;
            htmlGestionar += `<span class="label label-warning" style="margin: 2px;">${medio.codigo}</span>`;
        });
    } else {
        htmlAsignar = '<p class="text-muted">Selecciona medios de pago de la tabla</p>';
        htmlGestionar = '<p class="text-muted">Selecciona medios de pago de la tabla</p>';
    }
    $("#mediosSeleccionadosAsignar").html(htmlAsignar);
    $("#mediosSeleccionadosGestionar").html(htmlGestionar);
}

// Sincronizar con sucursales activas
function sincronizarConSucursalesActivas() {
    Swal.fire({
        title: "¿Sincronizar con Sucursales Activas?",
        text: "Esto sincronizará todos los medios de pago centrales con las sucursales activas",
        type: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, Sincronizar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "ajax/medios-pago-central.ajax.php",
                method: "POST",
                data: { accion: "sincronizar_sucursales_activas" },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        Swal.fire({
                            type: "success",
                            title: "¡Sincronización Exitosa!",
                            text: `Se sincronizaron ${respuesta.medios_sincronizados} medios en ${respuesta.sucursales_sincronizadas} sucursales`,
                            showConfirmButton: false,
                            timer: 3000
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

// Abrir modal asignar sucursales
function abrirModalAsignarSucursales() {
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
    
    cargarSucursalesDestinoAsignar();
    $("#modalAsignarSucursales").modal("show");
}

// Abrir modal gestionar asignaciones
function abrirModalGestionarAsignaciones() {
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
    
    $("#modalGestionarAsignaciones").modal("show");
}

// Cargar sucursales destino para asignación
function cargarSucursalesDestinoAsignar() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_destino_asignar" },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = "";
                respuesta.data.forEach(function(sucursal) {
                    html += `
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="checkbox-sucursal-asignar" value="${sucursal.id}">
                                ${sucursal.nombre}
                            </label>
                        </div>
                    `;
                });
                $("#sucursalesDestinoAsignar").html(html);
            }
        }
    });
}

// Confirmar asignación en sucursales
function confirmarAsignacionSucursales() {
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push($(this).val());
    });
    
    var sucursalesSeleccionadas = [];
    $(".checkbox-sucursal-asignar:checked").each(function() {
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
        accion: "asignar_medios_sucursales",
        medios_pago: mediosSeleccionados,
        sucursales: sucursalesSeleccionadas
    };
    
    Swal.fire({
        title: "¿Confirmar Asignación?",
        text: `Se asignarán ${mediosSeleccionados.length} medios en ${sucursalesSeleccionadas.length} sucursales`,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, Asignar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
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
                            text: `Medios asignados en ${respuesta.asignaciones} sucursales`,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        $("#modalAsignarSucursales").modal("hide");
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

// Mostrar estado completo
function mostrarEstadoCompleto() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_estado_completo" },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = `
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Medio</th>
                                <th>Sucursal</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                respuesta.data.forEach(function(item) {
                    html += `
                        <tr>
                            <td>${item.medio_nombre}</td>
                            <td>${item.sucursal_nombre}</td>
                            <td>${item.activo == 1 ? '<span class="label label-success">Activo</span>' : '<span class="label label-danger">Inactivo</span>'}</td>
                        </tr>
                    `;
                });
                html += `
                        </tbody>
                    </table>
                `;
                Swal.fire({
                    title: "Estado Completo de Medios por Sucursal",
                    html: html,
                    width: '800px',
                    showConfirmButton: true
                });
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

// Desactivar en todas las sucursales
function desactivarEnTodasLasSucursales() {
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push($(this).val());
    });
    
    Swal.fire({
        title: "¿Desactivar en Todas las Sucursales?",
        text: `Se desactivarán ${mediosSeleccionados.length} medios en todas las sucursales`,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, Desactivar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "ajax/medios-pago-central.ajax.php",
                method: "POST",
                data: { 
                    accion: "desactivar_todas_sucursales",
                    medios_pago: mediosSeleccionados
                },
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

// Eliminar todas las asignaciones
function eliminarTodasLasAsignaciones() {
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push($(this).val());
    });
    
    Swal.fire({
        title: "¿Eliminar Todas las Asignaciones?",
        text: `Se eliminarán todas las asignaciones de ${mediosSeleccionados.length} medios`,
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
                    accion: "eliminar_todas_asignaciones",
                    medios_pago: mediosSeleccionados
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        Swal.fire({
                            type: "success",
                            title: "¡Éxito!",
                            text: `Se eliminaron ${respuesta.eliminaciones} asignaciones`,
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
                var html = '<ul class="list-group">';
                if (respuesta.data.length > 0) {
                    respuesta.data.forEach(function(medio) {
                        html += `
                            <li class="list-group-item">
                                <div class="media">
                                    <div class="media-body">
                                        <h4 class="media-heading">${medio.nombre}</h4>
                                        <p>${medio.activo == 1 ? '<span class="label label-success">Activo</span>' : '<span class="label label-danger">Inactivo</span>'}</p>
                                    </div>
                                    <div class="media-right">
                                        <button class="btn btn-xs btn-warning" onclick="toggleEstadoMedioSucursal(${medio.id}, ${sucursalId}, ${medio.activo ? 0 : 1})" title="${medio.activo ? 'Desactivar' : 'Activar'}">
                                            <i class="fa fa-${medio.activo ? 'times' : 'check'}"></i>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                } else {
                    html += '<li class="list-group-item text-muted">No hay medios asignados a esta sucursal.</li>';
                }
                html += '</ul>';
                $("#estadoMediosSucursal").html(html);
            } else {
                $("#estadoMediosSucursal").html(`<p class="text-danger">Error: ${respuesta.error}</p>`);
            }
        }
    });
}

// Ver estado de medio específico
function verEstadoMedio(id) {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_estado_medio_especifico", medio_id: id },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = `
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sucursal</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                if (respuesta.data.length > 0) {
                    respuesta.data.forEach(function(item) {
                        html += `
                            <tr>
                                <td>${item.sucursal_nombre}</td>
                                <td>${item.activo == 1 ? '<span class="label label-success">Activo</span>' : '<span class="label label-danger">Inactivo</span>'}</td>
                            </tr>
                        `;
                    });
                } else {
                    html += `<tr><td colspan="2" class="text-center">No asignado a ninguna sucursal.</td></tr>`;
                }
                html += `
                        </tbody>
                    </table>
                `;
                Swal.fire({
                    title: `Estado del Medio: ${respuesta.medio_nombre}`,
                    html: html,
                    width: '600px',
                    showConfirmButton: true
                });
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

// Toggle estado de medio en sucursal
function toggleEstadoMedioSucursal(medioId, sucursalId, nuevoEstado) {
    var texto = nuevoEstado == 1 ? "activar" : "desactivar";
    Swal.fire({
        title: `¿Confirmar ${texto.charAt(0).toUpperCase() + texto.slice(1)}?`,
        text: `Se va a ${texto} este medio de pago en la sucursal seleccionada.`,
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

// Limpiar formulario agregar
function limpiarFormularioAgregar() {
    $("#nuevoCodigoMedio").val("");
    $("#nuevoNombreMedio").val("");
    $("#nuevaDescripcionMedio").val("");
    $("#nuevoTipoMedio").val("efectivo");
}