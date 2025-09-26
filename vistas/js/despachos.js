/*=============================================
DESPACHOS - FUNCIONALIDADES COMPLETAS
=============================================*/

$(document).ready(function() {
    
    console.log("🚛 Sistema de despachos inicializado");
    
    /*=============================================
    CARGAR DATATABLE DE DESPACHOS
    =============================================*/
    $(".tablaDespachos").DataTable({
        "ajax": "ajax/datatable-despachos.ajax.php",
        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
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
        }
    });

    /*=============================================
    VER DETALLES DEL DESPACHO
    =============================================*/
    $(document).on("click", ".btnVerDespacho", function() {
        
        var idDespacho = $(this).attr("idDespacho");
        console.log("👁️ Ver detalles del despacho ID:", idDespacho);
        
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
                
                console.log("📦 Datos del despacho:", respuesta);
                
                if(respuesta.error) {
                    swal({
                        title: "Error",
                        text: respuesta.error,
                        type: "error",
                        confirmButtonText: "Cerrar"
                    });
                    return;
                }
                
                // Cargar información básica
                cargarInformacionDespacho(respuesta);
                
                // Cargar productos
                cargarProductosDespacho(respuesta.productos_despacho);
                
                // Cargar historial
                cargarHistorialDespacho(respuesta);
                
                // Mostrar modal
                $("#modalVerDespacho").modal("show");
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener detalles del despacho:", error);
                swal({
                    title: "Error de conexión",
                    text: "No se pudieron cargar los detalles del despacho",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        });
    });

    /*=============================================
    EDITAR DESPACHO
    =============================================*/
    $(document).on("click", ".btnEditarDespacho", function() {
        
        var idDespacho = $(this).attr("idDespacho");
        console.log("✏️ Editar despacho ID:", idDespacho);
        
        // Redirigir a la página de edición con el ID
        window.location.href = "editar-despacho/" + idDespacho;
    });

    /*=============================================
    ACEPTAR DESPACHO
    =============================================*/
    $(document).on("click", ".btnAceptarDespacho", function() {
        
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
                                text: "El despacho ha sido aceptado y los productos se han movido al stock en tránsito.",
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
    CANCELAR DESPACHO
    =============================================*/
    $(document).on("click", ".btnCancelarDespacho", function() {
        
        var idDespacho = $(this).attr("idDespacho");
        var boton = $(this);
        
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
                
                boton.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i>');
                
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
                        
                        boton.prop("disabled", false).html('<i class="fa fa-times"></i>');
                        
                        if(respuesta.success) {
                            swal({
                                title: "¡Despacho cancelado!",
                                text: "El despacho ha sido cancelado correctamente.",
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
                        boton.prop("disabled", false).html('<i class="fa fa-times"></i>');
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
    ELIMINAR DESPACHO
    =============================================*/
    $(document).on("click", ".btnEliminarDespacho", function() {
        
        var idDespacho = $(this).attr("idDespacho");
        var boton = $(this);
        
        swal({
            title: "¿Eliminar despacho?",
            text: "¡Esta acción no se puede deshacer!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3c8dbc",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            
            if(result.value) {
                
                boton.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i>');
                
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
                        
                        boton.prop("disabled", false).html('<i class="fa fa-trash"></i>');
                        
                        if(respuesta.success) {
                            swal({
                                title: "¡Despacho eliminado!",
                                text: "El despacho ha sido eliminado correctamente.",
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
                        boton.prop("disabled", false).html('<i class="fa fa-trash"></i>');
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

    /*=============================================
    FILTROS RÁPIDOS POR ESTADO
    =============================================*/
    $(document).on("click", ".btnFiltroEstado", function() {
        
        var estado = $(this).attr("data-estado");
        
        // Actualizar botones activos
        $(".btnFiltroEstado").removeClass("active");
        $(this).addClass("active");
        
        // Aplicar filtro
        if(estado == "todos") {
            $(".tablaDespachos").DataTable().search("").draw();
        } else {
            $(".tablaDespachos").DataTable().search(estado.toUpperCase()).draw();
        }
    });

});

/*=============================================
FUNCIONES AUXILIARES PARA LA MODAL
=============================================*/

function cargarInformacionDespacho(despacho) {
    
    // Información básica
    $("#numeroDespachoModal").text(despacho.numero_despacho);
    $("#sucursalOrigenDespacho").text(despacho.sucursal_origen || despacho.nombre_sucursal_origen);
    $("#usuarioCreadorDespacho").text(despacho.nombre_usuario_creador);
    $("#fechaCreacionDespacho").text(formatearFecha(despacho.fecha_creacion));
    $("#transportadorDespacho").text(despacho.nombre_transportador || "Sin asignar");
    $("#totalProductosDespacho").text(despacho.total_productos);
    $("#estadoDespacho").text(despacho.estado.toUpperCase());
    
    // Configurar icono y color del estado
    var estadoIcon = $("#estadoIconDespacho");
    estadoIcon.removeClass("bg-red bg-yellow bg-blue bg-green bg-gray");
    
    switch(despacho.estado.toLowerCase()) {
        case 'pendiente':
            estadoIcon.addClass("bg-yellow");
            estadoIcon.find("i").removeClass().addClass("fa fa-clock-o");
            break;
        case 'aceptado':
            estadoIcon.addClass("bg-blue");
            estadoIcon.find("i").removeClass().addClass("fa fa-check");
            break;
        case 'en_transito':
            estadoIcon.addClass("bg-blue");
            estadoIcon.find("i").removeClass().addClass("fa fa-truck");
            break;
        case 'finalizado':
        case 'entregado':
            estadoIcon.addClass("bg-green");
            estadoIcon.find("i").removeClass().addClass("fa fa-check-circle");
            break;
        case 'cancelado':
            estadoIcon.addClass("bg-red");
            estadoIcon.find("i").removeClass().addClass("fa fa-times-circle");
            break;
        default:
            estadoIcon.addClass("bg-gray");
            estadoIcon.find("i").removeClass().addClass("fa fa-question");
    }
    
    // Detalle adicional
    if(despacho.detalle_adicional) {
        $("#detalleAdicional").text(despacho.detalle_adicional);
        $("#detalleAdicionalDespachoContainer").show();
    } else {
        $("#detalleAdicionalDespachoContainer").hide();
    }
}

function cargarProductosDespacho(productosJson) {
    
    try {
        var productos = JSON.parse(productosJson);
        var html = "";
        var totalCantidad = 0;
        
        for(var i = 0; i < productos.length; i++) {
            var producto = productos[i];
            totalCantidad += parseInt(producto.cantidad);
            
            html += `<tr>
                <td class="text-center">${i + 1}</td>
                <td><strong>${producto.codigo}</strong></td>
                <td>${producto.descripcion}</td>
                <td class="text-center"><span class="label label-primary">${producto.cantidad}</span></td>
                <td>${producto.observacion || 'Sin observaciones'}</td>
            </tr>`;
        }
        
        $("#productosDespachoBody").html(html);
        $("#totalCantidadDespacho").text(totalCantidad);
        
    } catch(e) {
        console.error("Error al parsear productos del despacho:", e);
        $("#productosDespachoBody").html('<tr><td colspan="5" class="text-center text-danger">Error al cargar productos</td></tr>');
    }
}

function cargarHistorialDespacho(despacho) {
    
    // Información de creación
    $("#fechaCreacionHistorial").text(formatearFecha(despacho.fecha_creacion));
    $("#horaCreacionHistorial").text(formatearHora(despacho.fecha_creacion));
    $("#usuarioCreacionHistorial").text(despacho.nombre_usuario_creador);
    $("#numeroCreacionHistorial").text(despacho.numero_despacho);
    
    // Mostrar/ocultar eventos según estado
    if(despacho.estado != 'pendiente') {
        $("#timelineAceptacionDespacho").show();
        
        if(despacho.estado == 'cancelado') {
            $("#labelAceptacionDespacho").removeClass("bg-green").addClass("bg-red").html('<i class="fa fa-times"></i> Cancelación');
            $("#iconAceptacionDespacho").removeClass("fa-check bg-green").addClass("fa-times bg-red");
            $("#accionAceptacionDespacho").text("Cancelado");
        } else {
            $("#labelAceptacionDespacho").removeClass("bg-red").addClass("bg-green").html('<i class="fa fa-check"></i> Aceptación');
            $("#iconAceptacionDespacho").removeClass("fa-times bg-red").addClass("fa-check bg-green");
            $("#accionAceptacionDespacho").text("Aceptado");
        }
        
        $("#horaAceptacionDespacho").text(formatearHora(despacho.fecha_aceptacion || despacho.fecha_actualizacion));
        $("#usuarioAceptacionDespacho").text(despacho.nombre_transportador || "Sistema");
    } else {
        $("#timelineAceptacionDespacho").hide();
    }
}

function formatearFecha(fecha) {
    if(!fecha) return "-";
    var d = new Date(fecha);
    return d.toLocaleDateString("es-ES");
}

function formatearHora(fecha) {
    if(!fecha) return "-";
    var d = new Date(fecha);
    return d.toLocaleTimeString("es-ES", { hour: '2-digit', minute: '2-digit' });
}

/*=============================================
FUNCIONES DE EXPORTACIÓN
=============================================*/

function exportarDespachosPDF() {
    console.log("📄 Exportando despachos a PDF...");
    window.open("ajax/exportar-despachos.ajax.php?formato=pdf", "_blank");
}

function exportarDespachosExcel() {
    console.log("📊 Exportando despachos a Excel...");
    window.open("ajax/exportar-despachos.ajax.php?formato=excel", "_blank");
}