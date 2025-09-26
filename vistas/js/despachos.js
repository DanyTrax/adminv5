/*=============================================
CARGAR DATATABLE DE DESPACHOS
=============================================*/
$('.tablaDespachos').DataTable({
    "ajax": "ajax/datatable-despachos.ajax.php",
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

/*=============================================
VER DETALLES DE DESPACHO
=============================================*/
$(document).on("click", ".btnVerDespacho", function(){
    
    var idDespacho = $(this).attr("idDespacho");
    
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
            
            if(respuesta) {
                mostrarDetallesDespacho(respuesta);
            } else {
                swal({
                    title: "Error",
                    text: "No se pudieron cargar los detalles del despacho",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error AJAX:", error);
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
MOSTRAR DETALLES DEL DESPACHO EN MODAL
=============================================*/
function mostrarDetallesDespacho(despacho) {
    
    console.log("Despacho recibido:", despacho);
    
    // INFORMACIÓN BÁSICA
    $("#numeroDespachoModal").text(despacho.numero_despacho);
    $("#sucursalOrigenDespacho").text(despacho.nombre_sucursal_origen);
    $("#usuarioCreadorDespacho").text(despacho.nombre_usuario_creador);
    $("#fechaCreacionDespacho").text(formatearFecha(despacho.fecha_creacion));
    $("#estadoDespacho").text(despacho.estado.toUpperCase());
    $("#totalProductosDespacho").text(despacho.total_productos + ' productos');
    $("#transportadorDespacho").text(despacho.nombre_transportador_asignado || 'Sin asignar');
    
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
        console.error("Error parsing productos:", e);
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
CARGAR TIMELINE DEL DESPACHO
=============================================*/
function cargarTimelineDespacho(despacho) {
    
    var html = '';
    
    // CREACIÓN
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
                    Despacho <strong>${despacho.numero_despacho}</strong> creado correctamente.
                </div>
            </div>
        </div>
    `;
    
    // ACEPTACIÓN
    if(despacho.fecha_aceptacion) {
        html += `
            <div class="time-label">
                <span class="bg-green">
                    <i class="fa fa-check"></i> ${formatearFecha(despacho.fecha_aceptacion)}
                </span>
            </div>
            <div>
                <i class="fa fa-check bg-green"></i>
                <div class="timeline-item">
                    <span class="time">
                        <i class="fa fa-clock-o"></i> ${formatearHora(despacho.fecha_aceptacion)}
                    </span>
                    <h3 class="timeline-header">
                        Aceptado por <strong>${despacho.nombre_transportador_asignado}</strong>
                    </h3>
                    <div class="timeline-body">
                        Los productos han sido cargados y están en tránsito.
                    </div>
                </div>
            </div>
        `;
    }
    
    // CANCELACIÓN
    if(despacho.estado === 'cancelado' && despacho.motivo_cancelacion) {
        html += `
            <div class="time-label">
                <span class="bg-red">
                    <i class="fa fa-ban"></i> Cancelado
                </span>
            </div>
            <div>
                <i class="fa fa-ban bg-red"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header text-red">
                        Despacho cancelado
                    </h3>
                                        <div class="timeline-body">
                        <strong>Motivo:</strong> ${despacho.motivo_cancelacion}
                    </div>
                </div>
            </div>
        `;
    }
    
    // FIN TIMELINE
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
        'aceptado': { icon: 'fa-check', color: 'bg-green' },
        'en_transito': { icon: 'fa-truck', color: 'bg-blue' },
        'finalizado': { icon: 'fa-flag-checkered', color: 'bg-gray' },
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
    var perfilUsuario = '<?php echo $_SESSION["perfil"]; ?>';
    var idUsuario = '<?php echo $_SESSION["id"]; ?>';
    
    // BOTÓN ACEPTAR (Transportadores y Administradores) - Solo pendientes
    if(despacho.estado === 'pendiente' && (perfilUsuario === 'Transportador' || perfilUsuario === 'Administrador')) {
        html += `
            <button type="button" class="btn btn-success" onclick="aceptarDespacho(${despacho.id})">
                <i class="fa fa-check"></i> Aceptar Despacho
            </button>
        `;
    }
    
    // BOTÓN CANCELAR (Administrador o Transportador asignado) - No finalizados ni cancelados
    if(despacho.estado !== 'finalizado' && despacho.estado !== 'cancelado') {
        var puedeCancel = (perfilUsuario === 'Administrador') || 
                         (perfilUsuario === 'Transportador' && despacho.id_transportador_asignado == idUsuario);
        
        if(puedeCancel) {
            html += `
                <button type="button" class="btn btn-warning" onclick="cancelarDespacho(${despacho.id}, '${despacho.estado}')">
                    <i class="fa fa-ban"></i> Cancelar
                </button>
            `;
        }
    }
    
    // BOTÓN EDITAR (Solo Administrador) - Solo pendientes
    if(despacho.estado === 'pendiente' && perfilUsuario === 'Administrador') {
        html += `
            <button type="button" class="btn btn-info" onclick="editarDespacho(${despacho.id})">
                <i class="fa fa-edit"></i> Editar
            </button>
        `;
    }
    
    // BOTÓN ELIMINAR (Solo Administrador) - Solo pendientes
    if(despacho.estado === 'pendiente' && perfilUsuario === 'Administrador') {
        html += `
            <button type="button" class="btn btn-danger" onclick="eliminarDespacho(${despacho.id}, '${despacho.numero_despacho}')">
                <i class="fa fa-trash"></i> Eliminar
            </button>
        `;
    }
    
    $("#botonesAccionDespacho").html(html);
}

/*=============================================
ACEPTAR DESPACHO
=============================================*/
function aceptarDespacho(idDespacho) {
    
    $("#idDespachoAceptar").val(idDespacho);
    $("#modalVerDespacho").modal("hide");
    $("#modalAceptarDespacho").modal("show");
}

$(document).on("click", ".btnAceptarDespacho", function(){
    var idDespacho = $(this).attr("idDespacho");
    aceptarDespacho(idDespacho);
});

/*=============================================
CANCELAR DESPACHO
=============================================*/
function cancelarDespacho(idDespacho, estadoActual) {
    
    $("#idDespachoCancelar").val(idDespacho);
    
    // Mostrar alerta de devolución de stock si ya fue aceptado
    if(estadoActual === 'aceptado' || estadoActual === 'en_transito') {
        $("#alertaDevolucionStock").show();
    } else {
        $("#alertaDevolucionStock").hide();
    }
    
    $("#modalVerDespacho").modal("hide");
    $("#modalCancelarDespacho").modal("show");
}

$(document).on("click", ".btnCancelarDespacho", function(){
    var idDespacho = $(this).attr("idDespacho");
    var estadoDespacho = $(this).attr("estadoDespacho");
    cancelarDespacho(idDespacho, estadoDespacho);
});

/*=============================================
EDITAR DESPACHO
=============================================*/
function editarDespacho(idDespacho) {
    window.location = "crear-despacho?editar=" + idDespacho;
}

/*=============================================
ELIMINAR DESPACHO
=============================================*/
function eliminarDespacho(idDespacho, numeroDespacho) {
    
    $("#idDespachoEliminar").val(idDespacho);
    $("#modalVerDespacho").modal("hide");
    $("#modalEliminarDespacho").modal("show");
}

$(document).on("click", ".btnEliminarDespacho", function(){
    var idDespacho = $(this).attr("idDespacho");
    var numeroDespacho = $(this).attr("numeroDespacho");
    eliminarDespacho(idDespacho, numeroDespacho);
});

/*=============================================
CONFIRMAR ELIMINACIÓN DE DESPACHO
=============================================*/
function confirmarEliminacionDespacho() {
    
    var idDespacho = $("#idDespachoEliminar").val();
    var motivo = $("#motivoEliminacion").val().trim();
    
    if(motivo === '') {
        swal({
            title: "Error",
            text: "Debe especificar el motivo de eliminación",
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    swal({
        title: "¿Está seguro?",
        text: "Esta acción eliminará permanentemente el despacho",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {
        if (result.value) {
            
            window.location = "index.php?ruta=despachos&idDespacho=" + idDespacho + "&motivo=" + encodeURIComponent(motivo);
        }
    });
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
    if(estado === "todos") {
        $('.tablaDespachos').DataTable().columns(4).search("").draw();
    } else {
        $('.tablaDespachos').DataTable().columns(4).search(estado.toUpperCase()).draw();
    }
});

/*=============================================
EXPORTAR DESPACHOS A PDF
=============================================*/
function exportarDespachosPDF() {
    $("#modalFiltrosExportar").modal("show");
}

function ejecutarExportacionPDF() {
    
    var filtros = {
        fechaDesde: $("#fechaDesdeExport").val(),
        fechaHasta: $("#fechaHastaExport").val(),
        estado: $("#estadoExport").val(),
        transportador: $("#transportadorExport").val()
    };
    
    // Construir URL con filtros
    var url = "extensiones/tcpdf/pdf/reporte-despachos.php?";
    var parametros = [];
    
    Object.keys(filtros).forEach(function(key) {
        if(filtros[key] !== '') {
            parametros.push(key + "=" + encodeURIComponent(filtros[key]));
        }
    });
    
    url += parametros.join("&");
    
    // Abrir PDF en nueva ventana
    window.open(url, '_blank');
    
    $("#modalFiltrosExportar").modal("hide");
}

/*=============================================
EXPORTAR DESPACHOS A EXCEL
=============================================*/
function exportarDespachosExcel() {
    $("#modalFiltrosExportar").modal("show");
}

function ejecutarExportacionExcel() {
    
    var filtros = {
        fechaDesde: $("#fechaDesdeExport").val(),
        fechaHasta: $("#fechaHastaExport").val(),
        estado: $("#estadoExport").val(),
        transportador: $("#transportadorExport").val()
    };
    
    $.ajax({
        url: "ajax/exportar-despachos.ajax.php",
        method: "POST",
        data: filtros,
        dataType: "json",
        success: function(response) {
            
            if(response.success) {
                
                // Crear archivo Excel usando SheetJS
                var wb = XLSX.utils.book_new();
                var ws = XLSX.utils.aoa_to_sheet(response.data);
                
                // Configurar anchos de columna
                ws['!cols'] = [
                    {wch: 15}, // N° Despacho
                    {wch: 20}, // Sucursal
                    {wch: 20}, // Usuario
                    {wch: 12}, // Estado
                    {wch: 10}, // Productos
                    {wch: 12}, // Cantidad
                    {wch: 20}, // Transportador
                    {wch: 15}, // Fecha Creación
                    {wch: 15}, // Fecha Aceptación
                    {wch: 30}  // Observaciones
                ];
                
                XLSX.utils.book_append_sheet(wb, ws, "Despachos");
                XLSX.writeFile(wb, response.filename);
                
                swal({
                    title: "¡Exportación exitosa!",
                    text: "El archivo Excel se ha descargado correctamente",
                    type: "success",
                    timer: 2000,
                    showConfirmButton: false
                });
                
            } else {
                swal({
                    title: "Error",
                    text: "No se pudo generar el archivo Excel",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function() {
            swal({
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor para exportar",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
    
    $("#modalFiltrosExportar").modal("hide");
}

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
    
    // Recargar tabla cada 60 segundos para ver nuevos despachos
    setInterval(function() {
        $('.tablaDespachos').DataTable().ajax.reload(null, false);
    }, 60000);
    
    console.log("✅ Sistema de despachos inicializado correctamente");
});

/*=============================================
MANEJAR ENVÍO DE FORMULARIOS
=============================================*/
$("#formAceptarDespacho").on("submit", function(e) {
    
    var botonSubmit = $(this).find('button[type="submit"]');
    botonSubmit.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
    
    // El formulario se envía normalmente, pero deshabilitamos el botón para evitar doble envío
});

$("#formCancelarDespacho").on("submit", function(e) {
    
    var botonSubmit = $(this).find('button[type="submit"]');
    botonSubmit.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cancelando...');
});
/*=============================================
ACEPTAR DESPACHO - AJAX
=============================================*/
$(document).on("click", ".btnAceptarDespacho", function(e) {
    e.preventDefault(); // Evitar recarga de página
    
    var idDespacho = $(this).attr("idDespacho");
    var boton = $(this);
    
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
            
            boton.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i>');
            
            var datos = new FormData();
            datos.append("aceptarDespacho", true);
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
                    
                    boton.prop("disabled", false).html('<i class="fa fa-check"></i>');
                    
                    if(respuesta.success) {
                        swal({
                            title: "¡Despacho aceptado!",
                            text: respuesta.message,
                            type: "success",
                            confirmButtonText: "Cerrar"
                        }).then(function() {
                            // Recargar tabla
                            $(".tablaDespachos").DataTable().ajax.reload();
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
                    boton.prop("disabled", false).html('<i class="fa fa-check"></i>');
                    console.error("Error AJAX:", error);
                    swal({
                        title: "Error de conexión",
                        text: "No se pudo aceptar el despacho",
                        type: "error",
                        confirmButtonText: "Cerrar"
                    });
                }
            });
        }
    });
});

/*=============================================
CANCELAR DESPACHO - AJAX CORREGIDO
=============================================*/
$(document).on("click", ".btnCancelarDespacho", function(e) {
    e.preventDefault(); // Evitar recarga de página
    
    var idDespacho = $(this).attr("idDespacho");
    var estadoDespacho = $(this).attr("estadoDespacho");
    
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
            
            var datos = new FormData();
            datos.append("cancelarDespacho", true);
            datos.append("idDespacho", idDespacho);
            datos.append("motivoCancelacion", result.value);
            
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
                            // Recargar tabla
                            $(".tablaDespachos").DataTable().ajax.reload();
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
                    console.error("Error AJAX:", error);
                    swal({
                        title: "Error de conexión",
                        text: "No se pudo cancelar el despacho",
                        type: "error",
                        confirmButtonText: "Cerrar"
                    });
                }
            });
        }
    });
});

/*=============================================
ELIMINAR DESPACHO - AJAX CORREGIDO
=============================================*/
$(document).on("click", ".btnEliminarDespacho", function(e) {
    e.preventDefault(); // Evitar recarga de página
    
    var idDespacho = $(this).attr("idDespacho");
    var numeroDespacho = $(this).attr("numeroDespacho");
    
    swal({
        title: "¿Eliminar despacho " + numeroDespacho + "?",
        text: "¡Esta acción no se puede deshacer!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3c8dbc",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {
        
        if(result.value) {
            
            var datos = new FormData();
            datos.append("eliminarDespacho", true);
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
                    
                    if(respuesta.success) {
                        swal({
                            title: "¡Despacho eliminado!",
                            text: respuesta.message,
                            type: "success",
                            confirmButtonText: "Cerrar"
                        }).then(function() {
                            // Recargar tabla
                            $(".tablaDespachos").DataTable().ajax.reload();
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
                    console.error("Error AJAX:", error);
                    swal({
                        title: "Error de conexión",
                        text: "No se pudo eliminar el despacho",
                        type: "error",
                        confirmButtonText: "Cerrar"
                    });
                }
            });
        }
    });
});