/*=============================================
VARIABLES GLOBALES
=============================================*/
var tablaHistoricoTransito;
var graficosInicializados = false;
var intervalActualizacion;
var chartTiposMovimiento, chartTransportadores, chartLineaTiempo;

// Variables de sesión
if (typeof window.window.perfilUsuario === 'undefined') {
    window.window.perfilUsuario = window.window.perfilUsuario || 'Invitado';
}
if (typeof window.window.idUsuario === 'undefined') {
    window.window.idUsuario = window.window.idUsuario || 0;
}
// Usar window.window.perfilUsuario directamente para evitar conflictos con const

/*=============================================
INICIALIZACIÓN DEL MÓDULO
=============================================*/
$(document).ready(function() {
    
    console.log("📊 Módulo Histórico de Movimientos inicializado");
    
    // Cargar DataTable
    cargarTablaHistoricoTransito();
    
    // Cargar estadísticas
    cargarEstadisticasHistorico();
    
    // Configurar eventos
    configurarEventos();
    
    // Inicializar gráficos si es administrador
    if(window.perfilUsuario === "Administrador") {
        setTimeout(function() {
            inicializarGraficos();
        }, 1000);
    }
    
    // Auto-actualizar cada 60 segundos
    iniciarActualizacionAutomatica();
    
});

/*=============================================
CARGAR DATATABLE DE HISTÓRICO
=============================================*/
function cargarTablaHistoricoTransito() {
    
    tablaHistoricoTransito = $('.tablaHistoricoTransito').DataTable({
        "ajax": {
            "url": "ajax/datatable-historico-transito.ajax.php",
            "type": "POST",
            "data": function(d) {
                // Agregar filtros actuales
                d.tabla = "historico-transito";
                d.fecha_desde = $("#fechaDesdeHistorico").val();
                d.fecha_hasta = $("#fechaHastaHistorico").val();
                d.tipo_movimiento = $("#filtroTipoMovimiento").val();
                d.transportador = $("#filtroTransportadorHistorico").val();
                d.codigo_producto = $("#filtroCodigoProducto").val();
                d.sucursal = $("#filtroSucursal").val();
                d.usuario = $("#filtroUsuario").val();
            }
        },
        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "serverSide": false,
        "order": [[1, "desc"]], // Ordenar por fecha descendente
        "pageLength": 25,
        "columnDefs": [
            {
                "targets": [0, 5, 9], // Columnas no ordenables
                "orderable": false
            },
            {
                "targets": [1, 5, 8], // Columnas centradas
                "className": "text-center"
            },
            {
                "targets": [2], // Columna tipo movimiento
                "className": "text-center"
            }
        ],
        "language": {
            "sProcessing": "Procesando movimientos...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron movimientos con los filtros aplicados",
            "sEmptyTable": "No hay movimientos registrados en el período seleccionado",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "sLoadingRecords": "Cargando histórico...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            }
        },
        "drawCallback": function(settings) {
            // Reactivar tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Actualizar contador de registros
            var info = this.api().page.info();
            $("#contadorRegistros").text(info.recordsTotal + " registros");
        }
    });
}

/*=============================================
CARGAR ESTADÍSTICAS DEL HISTÓRICO
=============================================*/
function cargarEstadisticasHistorico() {
    
    var filtros = {
        estadisticas: "historico",
        fecha_desde: $("#fechaDesdeHistorico").val(),
        fecha_hasta: $("#fechaHastaHistorico").val(),
        transportador: $("#filtroTransportadorHistorico").val(),
        tipo_movimiento: $("#filtroTipoMovimiento").val()
    };
    
    $.ajax({
        url: "ajax/datatable-historico-transito.ajax.php",
        method: "POST",
        data: filtros,
        dataType: "json",
        success: function(respuesta) {
            
            if(respuesta.estadisticas_generales) {
                actualizarContadoresEstadisticas(respuesta.estadisticas_generales);
            }
            
            if(window.perfilUsuario === "Administrador") {
                actualizarDatosGraficos(respuesta);
            }
            
        },
        error: function(xhr, status, error) {
            console.error("Error cargando estadísticas:", error);
        }
    });
}

/*=============================================
ACTUALIZAR CONTADORES DE ESTADÍSTICAS
=============================================*/
function actualizarContadoresEstadisticas(stats) {
    
    $("#totalMovimientos").text(formatearNumero(stats.total_movimientos || 0));
    $("#descargasExitosas").text(formatearNumero(stats.descargas_exitosas || 0));
    $("#productosCargados").text(formatearNumero(stats.productos_cargados || 0));
    $("#solicitudesRechazadas").text(formatearNumero(stats.solicitudes_rechazadas || 0));
    
    // Animar contadores si hay cambios significativos
    animarContadores();
}

/*=============================================
ANIMAR CONTADORES
=============================================*/
function animarContadores() {
    
    $(".info-box-number").each(function() {
        var $this = $(this);
        var valor = parseInt($this.text().replace(/,/g, '')) || 0;
        
        if(valor > 0) {
            $this.addClass("text-bold");
            
            // Efecto de pulso para valores altos
            if(valor > 100) {
                $this.closest(".info-box").addClass("pulse-animation");
                setTimeout(function() {
                    $this.closest(".info-box").removeClass("pulse-animation");
                }, 2000);
            }
        }
    });
}

/*=============================================
CONFIGURAR EVENTOS
=============================================*/
function configurarEventos() {
    
    // Eventos de botones en tabla (delegados)
    configurarEventosTabla();
    
    // Eventos de filtros
    configurarEventosFiltros();
    
    // Eventos de botones de acción
    configurarBotonesAccion();
}

/*=============================================
CONFIGURAR EVENTOS DE TABLA
=============================================*/
function configurarEventosTabla() {
    
    // Ver detalles completos del movimiento
    $(document).on("click", ".btnVerDetallesMovimiento", function() {
        var movimientoData = $(this).attr("data-movimiento");
        try {
            var movimiento = JSON.parse(movimientoData);
            mostrarDetallesMovimiento(movimiento);
        } catch(e) {
            console.error("Error parseando datos del movimiento:", e);
            swal({
                title: "Error",
                text: "Error cargando detalles del movimiento",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        }
    });
    
    // Ver trazabilidad del producto
    $(document).on("click", ".btnVerTrazabilidadProducto", function() {
        var codigoProducto = $(this).attr("codigoProducto");
        var transportadorId = $(this).attr("transportadorId");
        mostrarTrazabilidadProducto(codigoProducto, transportadorId);
    });
    
    // Generar reporte individual
    $(document).on("click", ".btnReporteIndividual", function() {
        var idMovimiento = $(this).attr("idMovimiento");
        generarReporteIndividual(idMovimiento);
    });
}

/*=============================================
CONFIGURAR EVENTOS DE FILTROS
=============================================*/
function configurarEventosFiltros() {
    
    // Aplicar filtros automáticamente en ciertos cambios
    $("#fechaDesdeHistorico, #fechaHastaHistorico").on("change", function() {
        validarRangoFechas();
    });
    
    // Validar que fecha desde no sea mayor que fecha hasta
    function validarRangoFechas() {
        var fechaDesde = new Date($("#fechaDesdeHistorico").val());
        var fechaHasta = new Date($("#fechaHastaHistorico").val());
        
        if(fechaDesde > fechaHasta) {
            $("#fechaDesdeHistorico").val($("#fechaHastaHistorico").val());
            swal({
                title: "Rango de fechas corregido",
                text: "La fecha de inicio no puede ser mayor que la fecha final",
                type: "warning",
                timer: 3000,
                showConfirmButton: false
            });
        }
    }
}

/*=============================================
CONFIGURAR BOTONES DE ACCIÓN
=============================================*/
function configurarBotonesAccion() {
    
    // Botón aplicar filtros
    window.aplicarFiltrosHistorico = function() {
        
        // Mostrar indicador de carga
        var $boton = $("button[onclick='aplicarFiltrosHistorico()']");
        var textoOriginal = $boton.html();
        
        $boton.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Aplicando...');
        
        // Recargar tabla con filtros
        tablaHistoricoTransito.ajax.reload(function() {
            
            // Recargar estadísticas
            cargarEstadisticasHistorico();
            
            // Restaurar botón
            $boton.prop("disabled", false).html(textoOriginal);
            
            // Actualizar gráficos si están inicializados
            if(graficosInicializados) {
                actualizarGraficos();
            }
            
        }, false);
    };
    
    // Botón limpiar filtros
    window.limpiarFiltrosHistorico = function() {
        
        $("#fechaDesdeHistorico").val(obtenerFecha(-30));
        $("#fechaHastaHistorico").val(obtenerFecha(0));
        $("#filtroTipoMovimiento").val("");
        $("#filtroTransportadorHistorico").val("");
        $("#filtroCodigoProducto").val("");
        $("#filtroSucursal").val("");
        $("#filtroUsuario").val("");
        
        aplicarFiltrosHistorico();
        
        swal({
            title: "Filtros limpiados",
            text: "Se restablecieron los filtros por defecto",
            type: "success",
            timer: 2000,
            showConfirmButton: false
        });
    };
    
    // Filtros rápidos
    window.filtrosRapidos = function(periodo) {
        
        var fechaDesde, fechaHasta = obtenerFecha(0);
        
        switch(periodo) {
            case 'hoy':
                fechaDesde = obtenerFecha(0);
                break;
            case 'semana':
                fechaDesde = obtenerFecha(-7);
                break;
            case 'mes':
                fechaDesde = obtenerFecha(-30);
                break;
        }
        
        $("#fechaDesdeHistorico").val(fechaDesde);
        $("#fechaHastaHistorico").val(fechaHasta);
        
        aplicarFiltrosHistorico();
    };
    
    // Botón actualizar
    window.actualizarHistorico = function() {
        aplicarFiltrosHistorico();
        
        swal({
            title: "✅ Actualizado",
            text: "Histórico de movimientos actualizado",
            type: "success",
            timer: 2000,
            showConfirmButton: false
        });
    };
}

/*=============================================
MOSTRAR DETALLES COMPLETOS DEL MOVIMIENTO
=============================================*/
function mostrarDetallesMovimiento(movimiento) {
    
    // TAB 1: INFORMACIÓN GENERAL
    $("#detalleIdMovimiento").text(movimiento.id);
    $("#detalleFechaMovimiento").text(formatearFechaCompleta(movimiento.fecha_movimiento));
    $("#detalleTipoMovimiento").html(obtenerTextoTipoMovimientoCompleto(movimiento.tipo_movimiento));
    $("#detalleCantidadMovimiento").text(formatearNumero(movimiento.cantidad) + " unidades");
    
    $("#detalleTransportadorMovimiento").text(movimiento.nombre_transportador || "-");
    $("#detalleUsuarioOrigen").text(movimiento.nombre_usuario_origen || "-");
    $("#detalleUsuarioDestino").text(movimiento.nombre_usuario_destino || "-");
    
    $("#detalleSucursalOrigen").text(movimiento.sucursal_origen || "-");
    $("#detalleSucursalDestino").text(movimiento.sucursal_destino || "-");
    
    // Observaciones
    if(movimiento.observaciones && movimiento.observaciones.trim() !== '') {
        $("#detalleObservaciones").text(movimiento.observaciones);
        $("#detalleObservacionesContainer").show();
    } else {
        $("#detalleObservacionesContainer").hide();
    }
    
    // TAB 2: DETALLE DEL PRODUCTO
    $("#detalleCodigoProducto").text(movimiento.codigo_producto);
    $("#detalleDescripcionProducto").text(movimiento.descripcion_producto);
    
    // Información de despacho/solicitud
    if(movimiento.numero_despacho) {
        $("#detalleNumeroDespacho").text(movimiento.numero_despacho);
        $("#detalleDespachoInfo").show();
    } else {
        $("#detalleDespachoInfo").hide();
    }
    
    if(movimiento.id_solicitud_descarga) {
        $("#detalleIdSolicitud").text("SOL-" + movimiento.id_solicitud_descarga);
        $("#detalleSolicitudInfo").show();
    } else {
        $("#detalleSolicitudInfo").hide();
    }
    
    // TAB 3: TRAZABILIDAD
    cargarTrazabilidadEnModal(movimiento.codigo_producto, movimiento.transportador_id);
    
    // Mostrar modal
    $("#modalDetallesMovimiento").modal("show");
}

/*=============================================
CARGAR TRAZABILIDAD EN MODAL
=============================================*/
function cargarTrazabilidadEnModal(codigoProducto, transportadorId) {
    
    var datos = new FormData();
    datos.append("codigoProducto", codigoProducto);
    datos.append("transportadorId", transportadorId);
    datos.append("obtenerTrazabilidad", true);
    
    $.ajax({
        url: "ajax/historico-transito.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            
            if(respuesta.success) {
                generarTimelineTrazabilidad(respuesta.trazabilidad);
            } else {
                $("#timelineTrazabilidad").html(`
                    <div class="text-center text-muted">
                        <i class="fa fa-info-circle"></i>
                        ${respuesta.error || "No se pudo cargar la trazabilidad"}
                    </div>
                `);
            }
        },
        error: function() {
            $("#timelineTrazabilidad").html(`
                <div class="text-center text-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                    Error de conexión al cargar trazabilidad
                </div>
            `);
        }
    });
}

/*=============================================
GENERAR TIMELINE DE TRAZABILIDAD
=============================================*/
function generarTimelineTrazabilidad(trazabilidad) {
    
    if(!trazabilidad || trazabilidad.length === 0) {
        $("#timelineTrazabilidad").html(`
            <div class="text-center text-muted">
                <i class="fa fa-info-circle"></i>
                No hay trazabilidad disponible para este producto
            </div>
        `);
        return;
    }
    
    var html = '<div class="timeline-condensed">';
    
    trazabilidad.forEach(function(movimiento, index) {
        
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
    
    html += '<div><i class="fa fa-clock-o bg-gray"></i></div></div>';
    
    $("#timelineTrazabilidad").html(html);
}

/*=============================================
MOSTRAR TRAZABILIDAD DE PRODUCTO
=============================================*/
function mostrarTrazabilidadProducto(codigoProducto, transportadorId) {
    
    // Reutilizar el modal de stock-transito si existe
    if(typeof mostrarHistorialProducto === 'function') {
        mostrarHistorialProducto(codigoProducto, transportadorId);
    } else {
        // Implementación alternativa
        swal({
            title: "Trazabilidad del Producto",
            text: "Código: " + codigoProducto,
            type: "info",
            confirmButtonText: "Cerrar"
        });
    }
}

/*=============================================
INICIALIZAR GRÁFICOS (SOLO ADMINISTRADORES)
=============================================*/
function inicializarGraficos() {
    
    if(window.perfilUsuario !== "Administrador") return;
    
    // Chart.js debe estar cargado
    if(typeof Chart === 'undefined') {
        console.warn("Chart.js no está disponible");
        return;
    }
    
    try {
        inicializarGraficoTiposMovimiento();
        inicializarGraficoTransportadores();
        inicializarGraficoLineaTiempo();
        
        graficosInicializados = true;
        
    } catch(error) {
        console.error("Error inicializando gráficos:", error);
    }
}

/*=============================================
INICIALIZAR GRÁFICO DE TIPOS DE MOVIMIENTO
=============================================*/
function inicializarGraficoTiposMovimiento() {
    
    var ctx = document.getElementById('graficoTiposMovimiento');
    if(!ctx) return;
    
    chartTiposMovimiento = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Cargues', 'Descargas', 'Solicitudes', 'Rechazos', 'Forzadas'],
            datasets: [{
                data: [0, 0, 0, 0, 0],
                backgroundColor: [
                    '#3c8dbc',
                    '#00a65a',
                    '#f39c12',
                    '#dd4b39',
                    '#605ca8'
                ],
                borderWidth: 2
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                position: 'bottom'
            }
        }
    });
}

/*=============================================
INICIALIZAR GRÁFICO DE TRANSPORTADORES
=============================================*/
function inicializarGraficoTransportadores() {
    
    var ctx = document.getElementById('graficoTransportadores');
    if(!ctx) return;
    
    chartTransportadores = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Movimientos',
                data: [],
                backgroundColor: '#f39c12',
                borderColor: '#e08e0b',
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
}

/*=============================================
INICIALIZAR GRÁFICO DE LÍNEA DE TIEMPO
=============================================*/
function inicializarGraficoLineaTiempo() {
    
    var ctx = document.getElementById('graficoLineaTiempo');
    if(!ctx) return;
    
    chartLineaTiempo = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Cargues',
                    data: [],
                    borderColor: '#3c8dbc',
                    backgroundColor: 'rgba(60, 141, 188, 0.1)',
                    fill: false
                },
                {
                    label: 'Descargas',
                    data: [],
                    borderColor: '#00a65a',
                    backgroundColor: 'rgba(0, 166, 90, 0.1)',
                    fill: false
                },
                {
                    label: 'Solicitudes',
                    data: [],
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.1)',
                    fill: false
                },
                {
                    label: 'Rechazos',
                    data: [],
                    borderColor: '#dd4b39',
                    backgroundColor: 'rgba(221, 75, 57, 0.1)',
                    fill: false
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                xAxes: [{
                    type: 'time',
                    time: {
                        displayFormats: {
                            day: 'DD/MM'
                        }
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            },
            legend: {
                position: 'bottom'
            }
        }
    });
}

/*=============================================
ACTUALIZAR DATOS DE GRÁFICOS
=============================================*/
function actualizarDatosGraficos(respuesta) {
    
    if(!graficosInicializados) return;
    
    try {
        // Actualizar gráfico de tipos de movimiento
        if(respuesta.tipos_movimiento && chartTiposMovimiento) {
            var datosTipos = [0, 0, 0, 0, 0]; // cargue, descarga, solicitud, rechazo, forzada
            
            respuesta.tipos_movimiento.forEach(function(tipo) {
                switch(tipo.tipo_movimiento) {
                    case 'cargue': datosTipos[0] = tipo.cantidad; break;
                    case 'descarga': datosTipos[1] = tipo.cantidad; break;
                    case 'solicitud_descarga': datosTipos[2] = tipo.cantidad; break;
                    case 'rechazo_descarga': datosTipos[3] = tipo.cantidad; break;
                    case 'descarga_forzada': datosTipos[4] = tipo.cantidad; break;
                }
            });
            
            chartTiposMovimiento.data.datasets[0].data = datosTipos;
            chartTiposMovimiento.update();
        }
        
        // Actualizar gráfico de transportadores
        if(respuesta.transportadores && chartTransportadores) {
            var nombres = respuesta.transportadores.map(function(t) { 
                return t.nombre_transportador.substring(0, 12); 
            });
            var movimientos = respuesta.transportadores.map(function(t) { 
                return t.cantidad_movimientos; 
            });
            
            chartTransportadores.data.labels = nombres;
            chartTransportadores.data.datasets[0].data = movimientos;
            chartTransportadores.update();
        }
        
        // Actualizar gráfico de línea de tiempo
        actualizarGraficoLineaTiempo();
        
    } catch(error) {
        console.error("Error actualizando gráficos:", error);
    }
}

/*=============================================
ACTUALIZAR GRÁFICO DE LÍNEA DE TIEMPO
=============================================*/
function actualizarGraficoLineaTiempo() {
    
    var filtros = {
        graficos: "historico",
        fecha_desde: $("#fechaDesdeHistorico").val(),
        fecha_hasta: $("#fechaHastaHistorico").val()
    };
    
    $.ajax({
        url: "ajax/datatable-historico-transito.ajax.php",
        method: "POST",
        data: filtros,
        dataType: "json",
        success: function(respuesta) {
            
            if(respuesta.fechas && respuesta.series && chartLineaTiempo) {
                
                // Formatear fechas para mostrar
                var fechasFormateadas = respuesta.fechas.map(function(fecha) {
                    return fecha.substring(8, 10) + '/' + fecha.substring(5, 7);
                });
                
                chartLineaTiempo.data.labels = fechasFormateadas;
                chartLineaTiempo.data.datasets[0].data = respuesta.series.cargue || [];
                chartLineaTiempo.data.datasets[1].data = respuesta.series.descarga || [];
                chartLineaTiempo.data.datasets[2].data = respuesta.series.solicitud_descarga || [];
                chartLineaTiempo.data.datasets[3].data = respuesta.series.rechazo_descarga || [];
                
                chartLineaTiempo.update();
            }
        },
        error: function(xhr, status, error) {
            console.error("Error actualizando gráfico línea de tiempo:", error);
        }
    });
}

/*=============================================
ACTUALIZAR GRÁFICOS
=============================================*/
function actualizarGraficos() {
    if(graficosInicializados) {
        cargarEstadisticasHistorico();
    }
}

/*=============================================
GENERAR REPORTE INDIVIDUAL
=============================================*/
function generarReporteIndividual(idMovimiento) {
    
    swal({
        title: "Generar Reporte Individual",
        text: "¿Desea generar un reporte detallado de este movimiento?",
        type: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, generar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {
        if(result.value) {
            
            // Abrir PDF del movimiento individual
            var url = "extensiones/tcpdf/pdf/reporte-movimiento-individual.php?id=" + idMovimiento;
            window.open(url, '_blank');
        }
    });
}

/*=============================================
EXPORTAR HISTÓRICO A PDF
=============================================*/
function exportarHistoricoPDF() {
    
    var filtros = {
        fecha_desde: $("#fechaDesdeHistorico").val(),
        fecha_hasta: $("#fechaHastaHistorico").val(),
        tipo_movimiento: $("#filtroTipoMovimiento").val(),
        transportador: $("#filtroTransportadorHistorico").val(),
        codigo_producto: $("#filtroCodigoProducto").val(),
        sucursal: $("#filtroSucursal").val(),
        usuario: $("#filtroUsuario").val()
    };
    
    // Construir URL con filtros
    var url = "extensiones/tcpdf/pdf/reporte-historico-movimientos.php?";
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
EXPORTAR HISTÓRICO A EXCEL
=============================================*/
function exportarHistoricoExcel() {
    
    var filtros = {
        fecha_desde: $("#fechaDesdeHistorico").val(),
        fecha_hasta: $("#fechaHastaHistorico").val(),
        tipo_movimiento: $("#filtroTipoMovimiento").val(),
        transportador: $("#filtroTransportadorHistorico").val(),
        codigo_producto: $("#filtroCodigoProducto").val(),
        sucursal: $("#filtroSucursal").val(),
        usuario: $("#filtroUsuario").val()
    };
    
    $.ajax({
        url: "ajax/exportar-historico-movimientos.ajax.php",
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
                    {wch: 15}, // Fecha/Hora
                    {wch: 12}, // Tipo
                    {wch: 12}, // Código
                    {wch: 30}, // Descripción
                    {wch: 8},  // Cantidad
                    {wch: 18}, // Transportador
                    {wch: 15}, // Origen
                    {wch: 15}, // Destino
                    {wch: 15}, // Usuario
                    {wch: 40}  // Observaciones
                ];
                
                XLSX.utils.book_append_sheet(wb, ws, "Histórico Movimientos");
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
                    text: response.message || "No se pudo generar el archivo Excel",
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
EXPORTAR RESUMEN EJECUTIVO
=============================================*/
function exportarResumenEjecutivo() {
    
    var filtros = {
        fecha_desde: $("#fechaDesdeHistorico").val(),
        fecha_hasta: $("#fechaHastaHistorico").val()
    };
    
    // Construir URL para resumen ejecutivo
    var url = "extensiones/tcpdf/pdf/resumen-ejecutivo-transito.php?";
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
INICIAR ACTUALIZACIÓN AUTOMÁTICA
=============================================*/
function iniciarActualizacionAutomatica() {
    
    // Actualizar cada 60 segundos (histórico se actualiza menos frecuentemente)
    intervalActualizacion = setInterval(function() {
        
        // Solo actualizar si la pestaña está activa y no hay modales abiertos
        if(!document.hidden && !$('.modal').hasClass('in')) {
            
            // Recargar tabla sin mostrar loading
            tablaHistoricoTransito.ajax.reload(null, false);
            
            // Actualizar estadísticas
            cargarEstadisticasHistorico();
        }
        
    }, 60000);
}

/*=============================================
FUNCIONES DE UTILIDAD
=============================================*/
function obtenerTextoTipoMovimientoCompleto(tipo) {
    
    var configuraciones = {
        'cargue': '<span class="label label-primary">CARGUE DE PRODUCTOS</span>',
        'descarga': '<span class="label label-success">DESCARGA CONFIRMADA</span>',
        'solicitud_descarga': '<span class="label label-warning">SOLICITUD DE DESCARGA</span>',
        'rechazo_descarga': '<span class="label label-danger">SOLICITUD RECHAZADA</span>',
        'descarga_forzada': '<span class="label label-purple">DESCARGA FORZADA</span>',
        'transferencia': '<span class="label label-info">TRANSFERENCIA</span>'
    };
    
    return configuraciones[tipo] || '<span class="label label-default">' + tipo.toUpperCase() + '</span>';
}

function obtenerConfiguracionTipoMovimiento(tipo) {
    
    var configuraciones = {
        'cargue': {
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
            colorClass: 'bg-purple',
            bgClass: 'bg-purple'
        },
        'transferencia': {
            titulo: 'Transferencia',
            icono: 'fa-exchange',
            colorClass: 'bg-aqua',
            bgClass: 'bg-aqua'
        }
    };
    
    return configuraciones[tipo] || {
        titulo: 'Movimiento',
        icono: 'fa-question',
        colorClass: 'bg-gray',
        bgClass: 'bg-gray'
    };
}

function obtenerFecha(dias) {
    var fecha = new Date();
    fecha.setDate(fecha.getDate() + dias);
    return fecha.toISOString().split('T')[0];
}

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
    var segundos = String(date.getSeconds()).padStart(2, '0');
    
    return `${dia}/${mes}/${año} ${horas}:${minutos}:${segundos}`;
}

/*=============================================
VARIABLES GLOBALES ADICIONALES
=============================================*/
// Las variables window.perfilUsuario e window.idUsuario ya están declaradas arriba

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
        actualizarHistorico();
        e.preventDefault();
    }
    
    // ESC para cerrar modales
    if(e.which === 27) {
        $(".modal").modal("hide");
    }
    
    // Ctrl + F para enfocar búsqueda
    if(e.ctrlKey && e.which === 70) {
        $(".dataTables_filter input").focus();
        e.preventDefault();
    }
    
    // Ctrl + E para exportar Excel
    if(e.ctrlKey && e.which === 69) {
        exportarHistoricoExcel();
        e.preventDefault();
    }
    
    // Ctrl + P para exportar PDF
    if(e.ctrlKey && e.which === 80) {
        exportarHistoricoPDF();
        e.preventDefault();
    }
});

/*=============================================
EFECTOS CSS ADICIONALES
=============================================*/
// Agregar clase de pulso para animaciones
$('<style>')
.text(`
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .label-purple {
        background-color: #605ca8;
    }
    
    .bg-purple {
        background-color: #605ca8 !important;
    }
`)
.appendTo('head');

/*=============================================
LOG DE INICIALIZACIÓN
=============================================*/
console.log("✅ Histórico de Movimientos - JavaScript cargado completamente");
console.log("👤 Perfil de usuario:", window.perfilUsuario);
console.log("🔢 ID de usuario:", window.idUsuario);
console.log("📊 Gráficos habilitados:", window.perfilUsuario === "Administrador");