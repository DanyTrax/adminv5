/*=============================================
JAVASCRIPT MEDIOS DE PAGO CENTRAL
=============================================*/

$(document).ready(function() {
    // Cargar datos iniciales
    cargarMediosPagoCentral();
    cargarSucursalesAsignacion();
    
    // Event listeners
    $("#btnNuevoMedioPago").click(function() {
        abrirModalNuevoMedioPago();
    });
    
    $("#btnAsignarSucursales").click(function() {
        abrirModalAsignarSucursales();
    });
    
    $("#btnCopiarMasivo").click(function() {
        abrirModalCopiaMasiva();
    });
    
    $("#btnSincronizarTodos").click(function() {
        sincronizarTodosMedios();
    });
    
    $("#btnGuardarMedioPago").click(function() {
        guardarMedioPago();
    });
    
    $("#btnConfirmarAsignacion").click(function() {
        confirmarAsignacionSucursales();
    });
    
    $("#btnConfirmarCopiaMasiva").click(function() {
        confirmarCopiaMasiva();
    });
    
    $("#selectSucursalAsignacion").change(function() {
        cargarMediosAsignadosSucursal($(this).val());
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

// Cargar sucursales para asignación
function cargarSucursalesAsignacion() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_asignacion" },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = '<option value="">Seleccionar sucursal...</option>';
                respuesta.data.forEach(function(sucursal) {
                    html += `<option value="${sucursal.id}">${sucursal.nombre}</option>`;
                });
                $("#selectSucursalAsignacion").html(html);
            }
        }
    });
}

// Cargar medios asignados por sucursal
function cargarMediosAsignadosSucursal(sucursalId) {
    if (!sucursalId) {
        $("#mediosAsignadosSucursal").html('<p class="text-muted">Selecciona una sucursal para ver sus medios asignados</p>');
        return;
    }
    
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { 
            accion: "obtener_medios_asignados_sucursal",
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
                                    <button class="btn btn-xs btn-danger" onclick="desasignarMedioSucursal(${medio.id}, ${sucursalId})" title="Desasignar">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html = '<p class="text-muted">No hay medios asignados a esta sucursal</p>';
                }
                
                $("#mediosAsignadosSucursal").html(html);
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
    
    // Actualizar en modal de asignación
    var htmlAsignacion = "";
    if (mediosSeleccionados.length > 0) {
        mediosSeleccionados.forEach(function(medio) {
            htmlAsignacion += `<span class="label label-primary" style="margin: 2px;">${medio.codigo}</span>`;
        });
    } else {
        htmlAsignacion = '<p class="text-muted">Selecciona medios de pago de la tabla</p>';
    }
    $("#mediosSeleccionadosAsignacion").html(htmlAsignacion);
    
    // Actualizar en modal de copia masiva
    var htmlCopia = "";
    if (mediosSeleccionados.length > 0) {
        mediosSeleccionados.forEach(function(medio) {
            htmlCopia += `<span class="label label-warning" style="margin: 2px;">${medio.codigo}</span>`;
        });
    } else {
        htmlCopia = '<p class="text-muted">Selecciona medios de pago de la tabla</p>';
    }
    $("#mediosSeleccionadosCopia").html(htmlCopia);
}

// Abrir modal nuevo medio de pago
function abrirModalNuevoMedioPago() {
    $("#formNuevoMedioPago")[0].reset();
    $("#modalNuevoMedioPago").modal("show");
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
    
    cargarSucursalesDisponiblesAsignacion();
    $("#modalAsignarSucursales").modal("show");
}

// Abrir modal copia masiva
function abrirModalCopiaMasiva() {
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
    
    cargarSucursalesDestinoCopia();
    $("#modalCopiaMasiva").modal("show");
}

// Cargar sucursales disponibles para asignación
function cargarSucursalesDisponiblesAsignacion() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_disponibles_asignacion" },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = "";
                respuesta.data.forEach(function(sucursal) {
                    html += `
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="checkbox-sucursal-asignacion" value="${sucursal.id}">
                                ${sucursal.nombre}
                            </label>
                        </div>
                    `;
                });
                $("#sucursalesDisponiblesAsignacion").html(html);
            }
        }
    });
}

// Cargar sucursales destino para copia masiva
function cargarSucursalesDestinoCopia() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_destino_copia" },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = "";
                respuesta.data.forEach(function(sucursal) {
                    html += `
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="checkbox-sucursal-copia" value="${sucursal.id}">
                                ${sucursal.nombre}
                            </label>
                        </div>
                    `;
                });
                $("#sucursalesDestinoCopia").html(html);
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

// Confirmar asignación a sucursales
function confirmarAsignacionSucursales() {
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push($(this).val());
    });
    
    var sucursalesSeleccionadas = [];
    $(".checkbox-sucursal-asignacion:checked").each(function() {
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
                    text: `Medios asignados a ${respuesta.asignaciones} sucursales`,
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

// Confirmar copia masiva
function confirmarCopiaMasiva() {
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push($(this).val());
    });
    
    var sucursalesSeleccionadas = [];
    $(".checkbox-sucursal-copia:checked").each(function() {
        sucursalesSeleccionadas.push($(this).val());
    });
    
    if (sucursalesSeleccionadas.length === 0) {
        Swal.fire({
            type: "warning",
            title: "Selección Requerida",
            text: "Debes seleccionar al menos una sucursal destino",
            showConfirmButton: true
        });
        return;
    }
    
    var datos = {
        accion: "copiar_medios_masivo",
        medios_pago: mediosSeleccionados,
        sucursales: sucursalesSeleccionadas
    };
    
    Swal.fire({
        title: "¿Confirmar Copia Masiva?",
        text: `Se copiarán ${mediosSeleccionados.length} medios a ${sucursalesSeleccionadas.length} sucursales en sus BD locales`,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, Copiar",
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
                            text: `Medios copiados a BD local de ${respuesta.copias} sucursales`,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        $("#modalCopiaMasiva").modal("hide");
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

// Sincronizar todos los medios
function sincronizarTodosMedios() {
    Swal.fire({
        title: "¿Confirmar Sincronización Completa?",
        text: "Esto copiará TODOS los medios de pago centrales a las BD locales de TODAS las sucursales",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, Sincronizar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "ajax/medios-pago-central.ajax.php",
                method: "POST",
                data: { accion: "sincronizar_todos_medios" },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        Swal.fire({
                            type: "success",
                            title: "¡Éxito!",
                            text: `Sincronización completada: ${respuesta.sincronizados} medios copiados a BD locales`,
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

// Desasignar medio de sucursal
function desasignarMedioSucursal(medioId, sucursalId) {
    Swal.fire({
        title: "¿Confirmar Desasignación?",
        text: "El medio de pago se desasignará de esta sucursal",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, Desasignar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "ajax/medios-pago-central.ajax.php",
                method: "POST",
                data: { 
                    accion: "desasignar_medio_sucursal",
                    medio_id: medioId,
                    sucursal_id: sucursalId
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        Swal.fire({
                            type: "success",
                            title: "¡Éxito!",
                            text: "Medio de pago desasignado correctamente",
                            showConfirmButton: false,
                            timer: 2000
                        });
                        cargarMediosAsignadosSucursal(sucursalId);
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
