/*=============================================
JAVASCRIPT MEDIOS DE PAGO CENTRAL
=============================================*/

$(document).ready(function() {
    // Cargar datos iniciales
    cargarMediosPagoCentral();
<<<<<<< HEAD
    cargarSucursalesEstado();
=======
    cargarSucursalesAsignacion();
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
    
    // Event listeners
    $("#btnNuevoMedioPago").click(function() {
        abrirModalNuevoMedioPago();
    });
    
<<<<<<< HEAD
    $("#btnActivarSucursales").click(function() {
        abrirModalActivarSucursales();
    });
    
    $("#btnDesactivarSucursales").click(function() {
        abrirModalDesactivarSucursales();
    });
    
    $("#btnVerEstado").click(function() {
        mostrarEstadoCompleto();
=======
    $("#btnAsignarSucursales").click(function() {
        abrirModalAsignarSucursales();
    });
    
    $("#btnCopiarMasivo").click(function() {
        abrirModalCopiaMasiva();
    });
    
    $("#btnSincronizarTodos").click(function() {
        sincronizarTodosMedios();
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
    });
    
    $("#btnGuardarMedioPago").click(function() {
        guardarMedioPago();
    });
    
<<<<<<< HEAD
    $("#btnConfirmarActivacion").click(function() {
        confirmarActivacionSucursales();
    });
    
    $("#btnConfirmarDesactivacion").click(function() {
        confirmarDesactivacionSucursales();
    });
    
    $("#selectSucursalEstado").change(function() {
        cargarEstadoMediosSucursal($(this).val());
=======
    $("#btnConfirmarAsignacion").click(function() {
        confirmarAsignacionSucursales();
    });
    
    $("#btnConfirmarCopiaMasiva").click(function() {
        confirmarCopiaMasiva();
    });
    
    $("#selectSucursalAsignacion").change(function() {
        cargarMediosAsignadosSucursal($(this).val());
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
                                <button class="btn btn-xs btn-info" onclick="verEstadoMedio(${medio.id})" title="Ver Estado">
                                    <i class="fa fa-eye"></i>
                                </button>
=======
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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

<<<<<<< HEAD
// Cargar sucursales para estado
function cargarSucursalesEstado() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_estado" },
=======
// Cargar sucursales para asignación
function cargarSucursalesAsignacion() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_asignacion" },
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = '<option value="">Seleccionar sucursal...</option>';
                respuesta.data.forEach(function(sucursal) {
                    html += `<option value="${sucursal.id}">${sucursal.nombre}</option>`;
                });
<<<<<<< HEAD
                $("#selectSucursalEstado").html(html);
=======
                $("#selectSucursalAsignacion").html(html);
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            }
        }
    });
}

<<<<<<< HEAD
// Cargar estado de medios por sucursal
function cargarEstadoMediosSucursal(sucursalId) {
    if (!sucursalId) {
        $("#estadoMediosSucursal").html('<p class="text-muted">Selecciona una sucursal para ver el estado de los medios</p>');
=======
// Cargar medios asignados por sucursal
function cargarMediosAsignadosSucursal(sucursalId) {
    if (!sucursalId) {
        $("#mediosAsignadosSucursal").html('<p class="text-muted">Selecciona una sucursal para ver sus medios asignados</p>');
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        return;
    }
    
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { 
<<<<<<< HEAD
            accion: "obtener_estado_medios_sucursal",
=======
            accion: "obtener_medios_asignados_sucursal",
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
                                    <button class="btn btn-xs btn-warning" onclick="toggleEstadoMedioSucursal(${medio.id}, ${sucursalId}, ${medio.activo ? 0 : 1})" title="${medio.activo ? 'Desactivar' : 'Activar'}">
                                        <i class="fa fa-${medio.activo ? 'times' : 'check'}"></i>
=======
                                    <button class="btn btn-xs btn-danger" onclick="desasignarMedioSucursal(${medio.id}, ${sucursalId})" title="Desasignar">
                                        <i class="fa fa-times"></i>
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html = '<p class="text-muted">No hay medios asignados a esta sucursal</p>';
                }
                
<<<<<<< HEAD
                $("#estadoMediosSucursal").html(html);
=======
                $("#mediosAsignadosSucursal").html(html);
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
    
<<<<<<< HEAD
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
=======
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
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
}

// Abrir modal nuevo medio de pago
function abrirModalNuevoMedioPago() {
    $("#formNuevoMedioPago")[0].reset();
    $("#modalNuevoMedioPago").modal("show");
}

<<<<<<< HEAD
// Abrir modal activar sucursales
function abrirModalActivarSucursales() {
=======
// Abrir modal asignar sucursales
function abrirModalAsignarSucursales() {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
    
<<<<<<< HEAD
    cargarSucursalesDestinoActivar();
    $("#modalActivarSucursales").modal("show");
}

// Abrir modal desactivar sucursales
function abrirModalDesactivarSucursales() {
=======
    cargarSucursalesDisponiblesAsignacion();
    $("#modalAsignarSucursales").modal("show");
}

// Abrir modal copia masiva
function abrirModalCopiaMasiva() {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
    
<<<<<<< HEAD
    cargarSucursalesDestinoDesactivar();
    $("#modalDesactivarSucursales").modal("show");
}

// Cargar sucursales destino para activación
function cargarSucursalesDestinoActivar() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_destino_activar" },
=======
    cargarSucursalesDestinoCopia();
    $("#modalCopiaMasiva").modal("show");
}

// Cargar sucursales disponibles para asignación
function cargarSucursalesDisponiblesAsignacion() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_disponibles_asignacion" },
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = "";
                respuesta.data.forEach(function(sucursal) {
                    html += `
                        <div class="checkbox">
                            <label>
<<<<<<< HEAD
                                <input type="checkbox" class="checkbox-sucursal-activar" value="${sucursal.id}">
=======
                                <input type="checkbox" class="checkbox-sucursal-asignacion" value="${sucursal.id}">
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                                ${sucursal.nombre}
                            </label>
                        </div>
                    `;
                });
<<<<<<< HEAD
                $("#sucursalesDestinoActivar").html(html);
=======
                $("#sucursalesDisponiblesAsignacion").html(html);
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            }
        }
    });
}

<<<<<<< HEAD
// Cargar sucursales destino para desactivación
function cargarSucursalesDestinoDesactivar() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_destino_desactivar" },
=======
// Cargar sucursales destino para copia masiva
function cargarSucursalesDestinoCopia() {
    $.ajax({
        url: "ajax/medios-pago-central.ajax.php",
        method: "POST",
        data: { accion: "obtener_sucursales_destino_copia" },
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.success) {
                var html = "";
                respuesta.data.forEach(function(sucursal) {
                    html += `
                        <div class="checkbox">
                            <label>
<<<<<<< HEAD
                                <input type="checkbox" class="checkbox-sucursal-desactivar" value="${sucursal.id}">
=======
                                <input type="checkbox" class="checkbox-sucursal-copia" value="${sucursal.id}">
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                                ${sucursal.nombre}
                            </label>
                        </div>
                    `;
                });
<<<<<<< HEAD
                $("#sucursalesDestinoDesactivar").html(html);
=======
                $("#sucursalesDestinoCopia").html(html);
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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

<<<<<<< HEAD
// Confirmar activación en sucursales
function confirmarActivacionSucursales() {
=======
// Confirmar asignación a sucursales
function confirmarAsignacionSucursales() {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push($(this).val());
    });
    
    var sucursalesSeleccionadas = [];
<<<<<<< HEAD
    $(".checkbox-sucursal-activar:checked").each(function() {
=======
    $(".checkbox-sucursal-asignacion:checked").each(function() {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
        accion: "activar_medios_sucursales",
=======
        accion: "asignar_medios_sucursales",
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
                    text: `Medios activados en ${respuesta.activaciones} sucursales`,
                    showConfirmButton: false,
                    timer: 2000
                });
                $("#modalActivarSucursales").modal("hide");
=======
                    text: `Medios asignados a ${respuesta.asignaciones} sucursales`,
                    showConfirmButton: false,
                    timer: 2000
                });
                $("#modalAsignarSucursales").modal("hide");
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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

<<<<<<< HEAD
// Confirmar desactivación en sucursales
function confirmarDesactivacionSucursales() {
=======
// Confirmar copia masiva
function confirmarCopiaMasiva() {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
    var mediosSeleccionados = [];
    $(".checkbox-medio:checked").each(function() {
        mediosSeleccionados.push($(this).val());
    });
    
    var sucursalesSeleccionadas = [];
<<<<<<< HEAD
    $(".checkbox-sucursal-desactivar:checked").each(function() {
=======
    $(".checkbox-sucursal-copia:checked").each(function() {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        sucursalesSeleccionadas.push($(this).val());
    });
    
    if (sucursalesSeleccionadas.length === 0) {
        Swal.fire({
            type: "warning",
            title: "Selección Requerida",
<<<<<<< HEAD
            text: "Debes seleccionar al menos una sucursal",
=======
            text: "Debes seleccionar al menos una sucursal destino",
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            showConfirmButton: true
        });
        return;
    }
    
    var datos = {
<<<<<<< HEAD
        accion: "desactivar_medios_sucursales",
=======
        accion: "copiar_medios_masivo",
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        medios_pago: mediosSeleccionados,
        sucursales: sucursalesSeleccionadas
    };
    
<<<<<<< HEAD
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
=======
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
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        }
    });
}

<<<<<<< HEAD
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
=======
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
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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

<<<<<<< HEAD
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

=======
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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

<<<<<<< HEAD
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
=======
// Desasignar medio de sucursal
function desasignarMedioSucursal(medioId, sucursalId) {
    Swal.fire({
        title: "¿Confirmar Desasignación?",
        text: "El medio de pago se desasignará de esta sucursal",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, Desasignar",
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "ajax/medios-pago-central.ajax.php",
                method: "POST",
                data: { 
<<<<<<< HEAD
                    accion: "toggle_estado_medio_sucursal",
                    medio_id: medioId,
                    sucursal_id: sucursalId,
                    nuevo_estado: nuevoEstado
=======
                    accion: "desasignar_medio_sucursal",
                    medio_id: medioId,
                    sucursal_id: sucursalId
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.success) {
                        Swal.fire({
                            type: "success",
                            title: "¡Éxito!",
<<<<<<< HEAD
                            text: `Medio ${texto}do correctamente`,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        cargarEstadoMediosSucursal(sucursalId);
=======
                            text: "Medio de pago desasignado correctamente",
                            showConfirmButton: false,
                            timer: 2000
                        });
                        cargarMediosAsignadosSucursal(sucursalId);
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
