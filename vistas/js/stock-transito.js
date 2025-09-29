/*=============================================
VARIABLES GLOBALES
=============================================*/
var tablaStockTransito;
var solicitudesPendientes = [];
var intervalActualizacion;

/*=============================================
INICIALIZACIÓN DEL MÓDULO
=============================================*/
$(document).ready(function() {
    
    console.log("🚛 Módulo Stock en Tránsito inicializado");
    
    // Cargar DataTable
    cargarTablaStockTransito();
    
    // Cargar resumen del dashboard
    cargarResumenDashboard();
    
    // Cargar solicitudes pendientes si es transportador
    if(perfilUsuario === "Transportador") {
        cargarSolicitudesPendientesTransportador();
    }
    
    // Configurar eventos
    configurarEventos();
    
    // Auto-actualizar cada 30 segundos
    iniciarActualizacionAutomatica();
    
    // Activar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
});

/*=============================================
CARGAR DATATABLE DE STOCK EN TRÁNSITO
=============================================*/
function cargarTablaStockTransito() {
    
    tablaStockTransito = $('#tablaStockTransito').DataTable({
        "ajax": {
            "url": "ajax/datatable-stock-transito.ajax.php",
            "type": "POST",
            "data": {
                "tabla": "stock-transito"
            }
        },
        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "order": [[4, "asc"], [1, "asc"]], // Ordenar por transportador y código
        "columnDefs": [
            {
                "targets": [0, 3, 7, 8], // Columnas no ordenables
                "orderable": false
            },
            {
                "targets": [3], // Columna cantidad - centrada
                "className": "text-center"
            },
            {
                "targets": [8], // Columna acciones - centrada
                "className": "text-center"
            }
        ],
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron productos en tránsito",
            "sEmptyTable": "No hay productos en tránsito en este momento",
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
        "drawCallback": function(settings) {
            // Reactivar tooltips después de cada redraw
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
}

/*=============================================
CARGAR RESUMEN DEL DASHBOARD
=============================================*/
function cargarResumenDashboard() {
    
    $.ajax({
        url: "ajax/datatable-stock-transito.ajax.php",
        method: "POST",
        data: {
            "resumen": "dashboard"
        },
        dataType: "json",
        success: function(resumen) {
            
            // Actualizar contadores en las cajas
            $("#totalProductosTransito").text(resumen.total_productos || 0);
            $("#totalUnidadesTransito").text(formatearNumero(resumen.total_unidades || 0));
            $("#totalTransportadores").text(resumen.total_transportadores || 0);
            $("#solicitudesPendientes").text(resumen.solicitudes_pendientes || 0);
            
            // Cambiar colores según cantidad
            actualizarColoresResumen(resumen);
            
        },
        error: function(xhr, status, error) {
            console.error("Error cargando resumen:", error);
        }
    });
}

/*=============================================
ACTUALIZAR COLORES DEL RESUMEN
=============================================*/
function actualizarColoresResumen(resumen) {
    
    // Cambiar color de solicitudes pendientes
    var $solicitudesBox = $("#solicitudesPendientes").closest(".small-box");
    var solicitudes = resumen.solicitudes_pendientes || 0;
    
    if(solicitudes == 0) {
        $solicitudesBox.removeClass("bg-red bg-yellow").addClass("bg-green");
    } else if(solicitudes <= 3) {
        $solicitudesBox.removeClass("bg-red bg-green").addClass("bg-yellow");
    } else {
        $solicitudesBox.removeClass("bg-yellow bg-green").addClass("bg-red");
    }
    
    // Animar números si hay cambios significativos
    if(resumen.total_unidades > 1000) {
        $("#totalUnidadesTransito").addClass("text-bold");
    }
}

/*=============================================
CONFIGURAR EVENTOS
=============================================*/
function configurarEventos() {
    
    // Filtro por transportador
    $("#filtroTransportador").on("change", function() {
        filtrarPorTransportador();
    });
    
    // Botón actualizar
    $(".btn[onclick='actualizarStockTransito()']").on("click", function() {
        actualizarStockTransito();
    });
    
    // Eventos de botones en tabla (delegados)
    configurarEventosTabla();
    
    // Validación en tiempo real de cantidades
    configurarValidacionCantidades();
    
    // Eventos de modales
    configurarEventosModales();
}

/*=============================================
CONFIGURAR EVENTOS DE TABLA
=============================================*/
function configurarEventosTabla() {
    
    // Solicitar descarga
    $(document).on("click", ".btnSolicitarDescarga", function() {
        mostrarModalSolicitarDescarga($(this));
    });
    
    // Ver historial de producto
    $(document).on("click", ".btnVerHistorialProducto", function() {
        var codigoProducto = $(this).attr("codigoProducto");
        var transportadorId = $(this).attr("transportadorId");
        mostrarHistorialProducto(codigoProducto, transportadorId);
    });
    
    // Ver solicitudes pendientes (transportadores)
    $(document).on("click", ".btnVerSolicitudesPendientes", function() {
        var transportadorId = $(this).attr("transportadorId");
        mostrarSolicitudesPendientes(transportadorId);
    });
    
    // Forzar descarga (administradores)
    $(document).on("click", ".btnForzarDescarga", function() {
        mostrarModalForzarDescarga($(this));
    });
}

/*=============================================
CONFIGURAR VALIDACIÓN DE CANTIDADES
=============================================*/
function configurarValidacionCantidades() {
    
    // Validación en tiempo real al escribir cantidad
    $("#cantidadDescargar").on("input", function() {
        
        var cantidad = parseInt($(this).val()) || 0;
        var idStockTransito = $("#idStockTransitoDescarga").val();
        
        if(cantidad > 0 && idStockTransito) {
            validarCantidadEnTiempoReal(idStockTransito, cantidad);
        } else {
            limpiarValidacionCantidad();
        }
    });
    
    // Validar al perder el foco
    $("#cantidadDescargar").on("blur", function() {
        var cantidad = parseInt($(this).val()) || 0;
        var maximo = parseInt($("#maximoPermitidoDescarga").text()) || 0;
        
        if(cantidad > maximo) {
            $(this).val(maximo);
            validarCantidadEnTiempoReal($("#idStockTransitoDescarga").val(), maximo);
        }
    });
}

/*=============================================
VALIDAR CANTIDAD EN TIEMPO REAL
=============================================*/
function validarCantidadEnTiempoReal(idStockTransito, cantidad) {
    
    var datos = new FormData();
    datos.append("idStockTransito", idStockTransito);
    datos.append("cantidadSolicitar", cantidad);
    
    $.ajax({
        url: "ajax/stock-transito.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            
            var $input = $("#cantidadDescargar");
            var $alerta = $("#alertaValidacionDescarga");
            var $mensaje = $("#mensajeValidacionDescarga");
            var $boton = $("#btnSolicitarDescarga");
            
            if(respuesta.valido) {
                // ✅ CANTIDAD VÁLIDA
                $input.removeClass("validacion-cantidad-error").addClass("validacion-cantidad-ok");
                $alerta.hide();
                $boton.prop("disabled", false);
                
                // Mostrar información adicional si hay stock solicitado
                if(respuesta.stock_solicitado > 0) {
                    mostrarInfoStockSolicitado(respuesta);
                }
                
            } else {
                // ❌ CANTIDAD INVÁLIDA
                $input.removeClass("validacion-cantidad-ok").addClass("validacion-cantidad-error");
                $mensaje.text(respuesta.mensaje);
                $alerta.show();
                $boton.prop("disabled", true);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error validando cantidad:", error);
            limpiarValidacionCantidad();
        }
    });
}

/*=============================================
MOSTRAR INFO DE STOCK SOLICITADO
=============================================*/
function mostrarInfoStockSolicitado(respuesta) {
    
    var $info = $("#maximoPermitidoDescarga").parent();
    
    if(respuesta.stock_solicitado > 0) {
        $info.html(`
            <i class="fa fa-info-circle"></i> 
            Disponible: <strong>${respuesta.stock_disponible}</strong> unidades
            <br>
            <small class="text-warning">
                (${respuesta.stock_solicitado} ya solicitadas de ${respuesta.stock_total} totales)
            </small>
        `);
    } else {
        $info.html(`
            <i class="fa fa-info-circle"></i> 
            Máximo: <span id="maximoPermitidoDescarga">${respuesta.stock_disponible}</span> unidades
        `);
    }
}

/*=============================================
LIMPIAR VALIDACIÓN DE CANTIDAD
=============================================*/
function limpiarValidacionCantidad() {
    
    $("#cantidadDescargar").removeClass("validacion-cantidad-error validacion-cantidad-ok");
    $("#alertaValidacionDescarga").hide();
    $("#btnSolicitarDescarga").prop("disabled", false);
}

/*=============================================
MOSTRAR MODAL SOLICITAR DESCARGA
=============================================*/
function mostrarModalSolicitarDescarga($boton) {
    
    // Extraer datos del botón
    var idStockTransito = $boton.attr("idStockTransito");
    var codigoProducto = $boton.attr("codigoProducto");
    var descripcionProducto = $boton.attr("descripcionProducto");
    var cantidadDisponible = parseInt($boton.attr("cantidadDisponible"));
    var transportadorId = $boton.attr("transportadorId");
    var nombreTransportador = $boton.attr("nombreTransportador");
    var sucursalOrigen = $boton.attr("sucursalOrigen");
    
    // Llenar modal con información
    $("#codigoProductoDescarga").text(codigoProducto);
    $("#descripcionProductoDescarga").text(descripcionProducto);
    $("#cantidadDisponibleDescarga").text(cantidadDisponible);
    $("#transportadorProductoDescarga").text(nombreTransportador);
    $("#origenProductoDescarga").text(sucursalOrigen);
    $("#maximoPermitidoDescarga").text(cantidadDisponible);
    
    // Configurar campos ocultos
    $("#idStockTransitoDescarga").val(idStockTransito);
    $("#codigoProductoDescargaHidden").val(codigoProducto);
    $("#transportadorIdDescarga").val(transportadorId);
    
    // Configurar cantidad máxima
    $("#cantidadDescargar").attr("max", cantidadDisponible).val(1);
    
    // Limpiar validaciones previas
    limpiarValidacionCantidad();
    
    // Mostrar modal
    $("#modalSolicitarDescarga").modal("show");
}

/*=============================================
MOSTRAR HISTORIAL DE PRODUCTO
=============================================*/
function mostrarHistorialProducto(codigoProducto, transportadorId) {
    
    var datos = new FormData();
    datos.append("codigoProducto", codigoProducto);
    datos.append("transportadorId", transportadorId);
    datos.append("obtenerHistorial", true);
    
    $.ajax({
        url: "ajax/stock-transito.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            
            if(respuesta.success) {
                generarTimelineHistorial(respuesta.historial);
                $("#codigoProductoHistorial").text(codigoProducto);
                $("#modalHistorialProducto").modal("show");
            } else {
                swal({
                    title: "Error",
                    text: respuesta.error || "No se pudo cargar el historial",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error cargando historial:", error);
            swal({
                title: "Error de conexión",
                text: "No se pudo conectar para obtener el historial",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
}

/*=============================================
GENERAR TIMELINE DE HISTORIAL
=============================================*/
function generarTimelineHistorial(historial) {
    
    if(!historial || historial.length == 0) {
        $("#timelineProductoHistorial").html(`
            <div class="text-center text-muted">
                <i class="fa fa-info-circle"></i>
                No hay historial disponible para este producto
            </div>
        `);
        return;
    }
    
    var html = '';
    
    historial.forEach(function(movimiento, index) {
        
        var fecha = formatearFecha(movimiento.fecha_movimiento);
        var hora = formatearHora(movimiento.fecha_movimiento);
        var tipoConfig = obtenerConfiguracionTipoMovimiento(movimiento.tipo_movimiento);
        
        html += `
            <div class="time-label">
                <span class="${tipoConfig.colorClass}">
                    <i class="${tipoConfig.icono}"></i> ${fecha}
                </span>
            </div>
            <div>
                <i class="${tipoConfig.icono} ${tipoConfig.bgClass}"></i>
                <div class="timeline-item">
                    <span class="time">
                        <i class="fa fa-clock-o"></i> ${hora}
                    </span>
                    <h3 class="timeline-header">
                        ${tipoConfig.titulo} - <strong>${movimiento.cantidad} unidades</strong>
                    </h3>
                    <div class="timeline-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Transportador:</strong> ${movimiento.nombre_transportador}<br>
                                ${movimiento.sucursal_origen ? '<strong>Origen:</strong> ' + movimiento.sucursal_origen + '<br>' : ''}
                                ${movimiento.sucursal_destino ? '<strong>Destino:</strong> ' + movimiento.sucursal_destino : ''}
                            </div>
                            <div class="col-md-6">
                                ${movimiento.nombre_usuario_origen ? '<strong>Usuario:</strong> ' + movimiento.nombre_usuario_origen + '<br>' : ''}
                                ${movimiento.numero_despacho ? '<strong>Despacho:</strong> ' + movimiento.numero_despacho : ''}
                            </div>
                        </div>
                        ${movimiento.observaciones ? '<div class="row" style="margin-top: 10px;"><div class="col-md-12"><strong>Observaciones:</strong> ' + movimiento.observaciones + '</div></div>' : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    // Agregar final del timeline
    html += '<div><i class="fa fa-clock-o bg-gray"></i></div>';
    
    $("#timelineProductoHistorial").html(html);
}

/*=============================================
OBTENER CONFIGURACIÓN DE TIPO DE MOVIMIENTO
=============================================*/
function obtenerConfiguracionTipoMovimiento(tipo) {
    
    var configuraciones = {
        'carga': {
            titulo: 'Producto Cargado',
            icono: 'fa-truck',
            colorClass: 'bg-blue',
            bgClass: 'bg-blue'
        },
        'descarga': {
            titulo: 'Producto Descargado',
            icono: 'fa-download',
            colorClass: 'bg-green',
            bgClass: 'bg-green'
        },
        'solicitud_descarga': {
            titulo: 'Solicitud de Descarga',
            icono: 'fa-bell',
            colorClass: 'bg-yellow',
            bgClass: 'bg-yellow'
        },
        'rechazo_descarga': {
            titulo: 'Solicitud Rechazada',
            icono: 'fa-ban',
            colorClass: 'bg-red',
            bgClass: 'bg-red'
        },
        'descarga_forzada': {
            titulo: 'Descarga Forzada (Admin)',
            icono: 'fa-exclamation-triangle',
            colorClass: 'bg-red',
            bgClass: 'bg-red'
        },
        'transferencia': {
            titulo: 'Transferencia',
            icono: 'fa-exchange',
            colorClass: 'bg-purple',
            bgClass: 'bg-purple'
        }
    };
    
    return configuraciones[tipo] || {
        titulo: 'Movimiento',
        icono: 'fa-question',
        colorClass: 'bg-gray',
        bgClass: 'bg-gray'
    };
}

/*=============================================
CARGAR SOLICITUDES PENDIENTES PARA TRANSPORTADOR
=============================================*/
function cargarSolicitudesPendientesTransportador() {
    
    if(perfilUsuario !== "Transportador") return;
    
    var datos = new FormData();
    datos.append("transportadorId", idUsuario);
    datos.append("obtenerSolicitudes", true);
    
    $.ajax({
        url: "ajax/stock-transito.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            
            if(respuesta.success) {
                solicitudesPendientes = respuesta.solicitudes;
                mostrarSolicitudesPendientesEnUI(respuesta.solicitudes);
                $("#contadorSolicitudesPendientes").text(respuesta.total);
            } else {
                console.error("Error cargando solicitudes:", respuesta.error);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en AJAX de solicitudes:", error);
        }
    });
}

/*=============================================
MOSTRAR SOLICITUDES PENDIENTES EN UI
=============================================*/
function mostrarSolicitudesPendientesEnUI(solicitudes) {
    
    var $contenedor = $("#contenedorSolicitudesPendientes");
    
    if(!solicitudes || solicitudes.length == 0) {
        $contenedor.html(`
            <p class="text-center text-muted">
                <i class="fa fa-check-circle text-success"></i> 
                No hay solicitudes pendientes
            </p>
        `);
        return;
    }
    
    var html = '';
    
    solicitudes.forEach(function(solicitud, index) {
        
        var fechaSolicitud = formatearFechaCompleta(solicitud.fecha_solicitud);
        
        html += `
            <div class="solicitud-pendiente">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="text-primary">
                            <i class="fa fa-cube"></i> 
                            ${solicitud.codigo_producto} - ${solicitud.descripcion_producto}
                        </h4>
                        <p>
                            <strong>Cantidad solicitada:</strong> 
                            <span class="text-bold text-blue">${solicitud.cantidad_solicitada} unidades</span>
                        </p>
                        <p>
                            <strong>Destino:</strong> ${solicitud.sucursal_destino}<br>
                            <strong>Solicitado por:</strong> ${solicitud.nombre_usuario_solicitante}<br>
                            <strong>Fecha:</strong> ${fechaSolicitud}
                        </p>
                        ${solicitud.observaciones ? '<p><strong>Observaciones:</strong> ' + solicitud.observaciones + '</p>' : ''}
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="btn-group-vertical">
                            <button type="button" class="btn btn-success btn-sm" 
                                    onclick="confirmarDescarga(${solicitud.id})">
                                <i class="fa fa-check"></i> Confirmar
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" 
                                    onclick="rechazarDescarga(${solicitud.id})">
                                <i class="fa fa-ban"></i> Rechazar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    $contenedor.html(html);
}

/*=============================================
CONFIRMAR DESCARGA
=============================================*/
function confirmarDescarga(idSolicitud) {
    
    // Buscar solicitud en el array local
    var solicitud = solicitudesPendientes.find(function(s) {
        return s.id == idSolicitud;
    });
    
    if(!solicitud) {
        swal({
            title: "Error",
            text: "Solicitud no encontrada",
            type: "error",
            confirmButtonText: "Cerrar"
        });
        return;
    }
    
    // Llenar modal con datos de la solicitud
    $("#productoConfirmarDescarga").text(solicitud.codigo_producto + " - " + solicitud.descripcion_producto);
    $("#usuarioSolicitanteConfirmar").text(solicitud.nombre_usuario_solicitante);
    $("#cantidadSolicitadaConfirmar").text(solicitud.cantidad_solicitada);
    $("#destinoConfirmarDescarga").text(solicitud.sucursal_destino);
    $("#observacionesSolicitanteConfirmar").text(solicitud.observaciones || "Sin observaciones");
    
    // Configurar campo oculto
    $("#idSolicitudDescargaConfirmar").val(idSolicitud);
    
    // Mostrar modal
    $("#modalConfirmarDescarga").modal("show");
}

/*=============================================
RECHAZAR DESCARGA
=============================================*/
function rechazarDescarga(idSolicitud) {
    
    $("#idSolicitudRechazarHidden").val(idSolicitud);
    $("#modalRechazarDescarga").modal("show");
}

/*=============================================
FILTRAR POR TRANSPORTADOR
=============================================*/
function filtrarPorTransportador() {
    
    var transportadorId = $("#filtroTransportador").val();
    
    if(transportadorId === "") {
        // Mostrar todos
        tablaStockTransito.columns(4).search("").draw();
    } else {
        // Filtrar por nombre del transportador
        var nombreTransportador = $("#filtroTransportador option:selected").text();
        tablaStockTransito.columns(4).search(nombreTransportador).draw();
    }
}

/*=============================================
ACTUALIZAR STOCK EN TRÁNSITO
=============================================*/
function actualizarStockTransito() {
    
    // Mostrar indicador de carga
    var $boton = $(".btn[onclick='actualizarStockTransito()']");
    var textoOriginal = $boton.html();
    
    $boton.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Actualizando...');
    
    // Recargar tabla
    tablaStockTransito.ajax.reload(function() {
        
        // Recargar resumen
        cargarResumenDashboard();
        
        // Recargar solicitudes si es transportador
        if(perfilUsuario === "Transportador") {
            cargarSolicitudesPendientesTransportador();
        }
        
        // Restaurar botón
        $boton.prop("disabled", false).html(textoOriginal);
        
        // Mostrar mensaje
        swal({
            title: "✅ Actualizado",
            text: "Stock en tránsito actualizado correctamente",
            type: "success",
            timer: 2000,
            showConfirmButton: false
        });
        
    }, false);
}

/*=============================================
INICIAR ACTUALIZACIÓN AUTOMÁTICA
=============================================*/
function iniciarActualizacionAutomatica() {
    
    // Actualizar cada 30 segundos
    intervalActualizacion = setInterval(function() {
        
        // Solo actualizar si la pestaña está activa
        if(!document.hidden) {
            tablaStockTransito.ajax.reload(null, false);
            cargarResumenDashboard();
            
            if(perfilUsuario === "Transportador") {
                cargarSolicitudesPendientesTransportador();
            }
        }
        
    }, 30000);
}

/*=============================================
CONFIGURAR EVENTOS DE MODALES
=============================================*/
function configurarEventosModales() {
    
    // Limpiar validaciones al cerrar modal de solicitud
    $("#modalSolicitarDescarga").on("hidden.bs.modal", function() {
        limpiarValidacionCantidad();
        $("#cantidadDescargar").val(1);
    });
    
    // Auto-focus en campos importantes
    $("#modalSolicitarDescarga").on("shown.bs.modal", function() {
        $("#cantidadDescargar").focus().select();
    });
    
    $("#modalRechazarDescarga").on("shown.bs.modal", function() {
        $("textarea[name='motivoRechazo']").focus();
    });
}

/*=============================================
EXPORTAR STOCK TRÁNSITO A PDF
=============================================*/
function exportarStockTransitoPDF() {
    
    var filtros = {
        transportador: $("#filtroTransportador").val(),
        fecha_desde: '',
        fecha_hasta: ''
    };
    
    // Construir URL con filtros
    var url = "extensiones/tcpdf/pdf/reporte-stock-transito.php?";
    var parametros = [];
    
    Object.keys(filtros).forEach(function(key) {
        if(filtros[key] !== '') {
            parametros.push(key + "=" + encodeURIComponent(filtros[key]));
        }
    });
    
    url += parametros.join("&");
    
    // Abrir PDF en nueva ventana
    window.open(url, '_blank');
}

/*=============================================
EXPORTAR STOCK TRÁNSITO A EXCEL
=============================================*/
function exportarStockTransitoExcel() {
    
    var filtros = {
        transportador: $("#filtroTransportador").val()
    };
    
    $.ajax({
        url: "ajax/exportar-stock-transito.ajax.php",
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
                    {wch: 12}, // Código
                    {wch: 30}, // Descripción
                    {wch: 10}, // Cantidad
                    {wch: 20}, // Transportador
                    {wch: 20}, // Origen
                    {wch: 12}, // Fecha
                    {wch: 25}  // Observaciones
                ];
                
                XLSX.utils.book_append_sheet(wb, ws, "Stock en Tránsito");
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
}

/*=============================================
FUNCIONES DE UTILIDAD
=============================================*/
function formatearNumero(numero) {
    return new Intl.NumberFormat('es-CO').format(numero);
}

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

function formatearFechaCompleta(fecha) {
    if(!fecha) return 'Sin fecha';
    
    var date = new Date(fecha);
    var dia = String(date.getDate()).padStart(2, '0');
    var mes = String(date.getMonth() + 1).padStart(2, '0');
    var año = date.getFullYear();
    var horas = String(date.getHours()).padStart(2, '0');
    var minutos = String(date.getMinutes()).padStart(2, '0');
    
    return `${dia}/${mes}/${año} ${horas}:${minutos}`;
}

/*=============================================
VARIABLES GLOBALES - REFERENCIA A WINDOW
=============================================*/
var perfilUsuario = window.perfilUsuario || 'Invitado';
var idUsuario = window.idUsuario || 0;

/*=============================================
CLEANUP AL SALIR DE LA PÁGINA
=============================================*/
$(window).on('beforeunload', function() {
    if(intervalActualizacion) {
        clearInterval(intervalActualizacion);
    }
});

/*=============================================
ATAJOS DE TECLADO
=============================================*/
$(document).on("keydown", function(e) {
    
    // F5 para actualizar
    if(e.which === 116) {
        actualizarStockTransito();
        e.preventDefault();
    }
    
    // ESC para cerrar modales
    if(e.which === 27) {
        $(".modal").modal("hide");
    }
    
    // Ctrl + E para exportar Excel
    if(e.ctrlKey && e.which === 69) {
        exportarStockTransitoExcel();
        e.preventDefault();
    }
});
/*=============================================
LOG DE INICIALIZACIÓN
=============================================*/
console.log("✅ Stock en Tránsito - JavaScript cargado completamente");
console.log("👤 Perfil de usuario:", perfilUsuario);
console.log("🔢 ID de usuario:", idUsuario);

// FUNCIÓN DE DEBUG - Agregar al inicio del archivo
function debugDataTable() {
    console.log("🔍 DEBUGGING DATATABLE STOCK-TRANSITO");
    
    // Verificar si el datatable existe
    if($.fn.DataTable.isDataTable('#tablaStockTransito')) {
        console.log("✅ DataTable ya inicializado");
        var table = $('#tablaStockTransito').DataTable();
        console.log("📊 Info del datatable:", table.page.info());
    } else {
        console.log("❌ DataTable NO inicializado");
    }
    
    // Hacer petición AJAX manual
    $.ajax({
        url: "ajax/datatable-stock-transito.ajax.php",
        method: "POST",
        data: {
            draw: 1,
            start: 0,
            length: 10
        },
        dataType: "json",
        success: function(respuesta) {
            console.log("✅ Respuesta AJAX exitosa:");
            console.log("- draw:", respuesta.draw);
            console.log("- recordsTotal:", respuesta.recordsTotal);
            console.log("- recordsFiltered:", respuesta.recordsFiltered);
            console.log("- Registros en data:", respuesta.data ? respuesta.data.length : 0);
            
            if(respuesta.data && respuesta.data.length > 0) {
                console.log("📋 Primer registro:", respuesta.data[0]);
            } else {
                console.log("❌ No hay datos en la respuesta");
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error AJAX:");
            console.error("- Status:", status);
            console.error("- Error:", error);
            console.error("- Respuesta:", xhr.responseText);
        }
    });
}

// Llamar debug al cargar la página
$(document).ready(function() {
    setTimeout(function() {
        debugDataTable();
    }, 2000);
});