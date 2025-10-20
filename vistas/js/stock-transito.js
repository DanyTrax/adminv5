/*=============================================
VARIABLES GLOBALES
=============================================*/
var tablaStockTransito;
if (typeof window.perfilUsuario === 'undefined') {
    window.perfilUsuario = window.perfilUsuario || 'Invitado';
}
if (typeof window.idUsuario === 'undefined') {
    window.idUsuario = window.idUsuario || 0;
}
// Usar window.perfilUsuario directamente para evitar conflictos con const

/*=============================================
INICIALIZACIÓN
=============================================*/
$(document).ready(function() {
    
    console.log("🚛 Stock en Tránsito inicializado");
    console.log("👤 Perfil:", window.perfilUsuario);
    console.log("🔢 ID Usuario:", window.idUsuario);
    
    cargarTablaStockTransito();
    configurarEventosStock();
});

/*=============================================
CARGAR DATATABLE
=============================================*/
function cargarTablaStockTransito() {
    
    console.log("🔄 Cargando DataTable Stock Tránsito...");
    
    tablaStockTransito = $('#tablaStockTransito').DataTable({
        "ajax": {
            "url": "ajax/datatable-stock-transito.ajax.php",
            "type": "POST",
            "error": function(xhr, error, code) {
                console.error("❌ Error AJAX:", xhr.status, error);
                console.error("Respuesta:", xhr.responseText);
            }
        },
        "deferRender": true,
        "retrieve": true,
        "processing": true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron productos en tránsito",
            "sEmptyTable": "No hay productos en stock de tránsito",
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
            }
        },
        "columnDefs": [
            {
                "targets": [0, 3, 7, 8], // #, Cantidad, Solicitudes, Acciones
                "orderable": false
            },
            {
                "targets": [3, 7, 8], // Cantidad, Solicitudes, Acciones - centradas
                "className": "text-center"
            }
        ],
        "drawCallback": function() {
            console.log("✅ DataTable Stock Tránsito cargado exitosamente");
            var info = this.api().page.info();
            console.log("📊 Registros totales:", info.recordsTotal);
            
            // Configurar eventos después de cargar la tabla
            configurarEventosStock();
        }
    });
}

/*=============================================
CONFIGURAR EVENTOS
=============================================*/
function configurarEventosStock() {
    
    // Ver historial
    $(document).on("click", ".btnVerHistorial", function() {
        var codigo = $(this).attr("data-codigo");
        verHistorialProducto(codigo);
    });
    
    // Solicitar descarga
    $(document).on("click", ".btnSolicitarDescarga", function() {
        var id = $(this).attr("data-id");
        var codigo = $(this).attr("data-codigo");
        var descripcion = $(this).attr("data-descripcion");
        var cantidad = $(this).attr("data-cantidad");
        var transportador = $(this).attr("data-transportador");
        var origen = $(this).attr("data-origen");
        
        mostrarModalSolicitarDescarga(id, codigo, descripcion, cantidad, transportador, origen);
    });
    
    // Enviar solicitud
    $("#formSolicitarDescarga").on("submit", function(e) {
        e.preventDefault();
        procesarSolicitudDescarga();
    });
}

/*=============================================
VER HISTORIAL DE PRODUCTO
=============================================*/
function verHistorialProducto(codigo) {
    
    $("#historialCodigo").text(codigo);
    $("#contenidoHistorial").html(`
        <div class="text-center">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <p>Cargando historial...</p>
        </div>
    `);
    
    $("#modalHistorial").modal("show");
    
    // Simular carga del historial
    setTimeout(function() {
        $("#contenidoHistorial").html(`
            <div class="timeline">
                <div class="time-label">
                    <span class="bg-blue">26 Sep 2024</span>
                </div>
                <div>
                    <i class="fa fa-truck bg-blue"></i>
                    <div class="timeline-item">
                        <span class="time"><i class="fa fa-clock-o"></i> 14:30</span>
                        <h3 class="timeline-header">Producto cargado en tránsito</h3>
                        <div class="timeline-body">
                            Producto ${codigo} cargado desde almacén central.
                            <br><strong>Cantidad:</strong> 10 unidades
                            <br><strong>Transportador:</strong> Administrador
                        </div>
                    </div>
                </div>
                <div>
                    <i class="fa fa-clock-o bg-gray"></i>
                </div>
            </div>
        `);
    }, 1000);
}

/*=============================================
MOSTRAR MODAL SOLICITAR DESCARGA
=============================================*/
function mostrarModalSolicitarDescarga(id, codigo, descripcion, cantidad, transportador, origen) {
    
    // Llenar información del producto
    $("#infoCodigo").text(codigo);
    $("#infoDescripcion").text(descripcion);
    $("#infoTransportador").text(transportador);
    $("#infoOrigen").text(origen);
    
    // Configurar campos
    $("#cantidadDisponible").val(cantidad);
    $("#cantidadSolicitada").attr("max", cantidad).val(1);
    
    // Campos ocultos
    $("#idStockTransito").val(id);
    $("#codigoProducto").val(codigo);
    
    // Mostrar modal
    $("#modalSolicitarDescarga").modal("show");
}

/*=============================================
PROCESAR SOLICITUD DE DESCARGA
=============================================*/
function procesarSolicitudDescarga() {
    
    var datos = new FormData(document.getElementById("formSolicitarDescarga"));
    datos.append("solicitarDescarga", true);
    
    $.ajax({
        url: "ajax/stock-transito.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        beforeSend: function() {
            $("#formSolicitarDescarga button[type='submit']").prop("disabled", true)
                .html('<i class="fa fa-spinner fa-spin"></i> Enviando...');
        },
        success: function(respuesta) {
            
            $("#modalSolicitarDescarga").modal("hide");
            
            if(respuesta.success) {
                swal({
                    title: "¡Solicitud enviada!",
                    text: respuesta.message || "La solicitud ha sido enviada correctamente",
                    type: "success",
                    confirmButtonText: "Aceptar"
                }).then(function() {
                    actualizarTabla();
                });
            } else {
                swal({
                    title: "Error",
                    text: respuesta.error || "No se pudo procesar la solicitud",
                    type: "error",
                    confirmButtonText: "Cerrar"
                });
            }
        },
        error: function(xhr, status, error) {
            $("#modalSolicitarDescarga").modal("hide");
            
            swal({
                title: "Error de conexión",
                text: "No se pudo conectar con el servidor",
                type: "error",
                confirmButtonText: "Cerrar"
            });
        },
        complete: function() {
            $("#formSolicitarDescarga button[type='submit']").prop("disabled", false)
                .html('<i class="fa fa-paper-plane"></i> Enviar Solicitud');
        }
    });
}

/*=============================================
ACTUALIZAR TABLA
=============================================*/
function actualizarTabla() {
    
    if(tablaStockTransito) {
        tablaStockTransito.ajax.reload(null, false);
        
        swal({
            title: "¡Actualizado!",
            text: "La tabla ha sido actualizada",
            type: "success",
            timer: 1500,
            showConfirmButton: false
        });
    }
}

/*=============================================
DESCARGA DIRECTA
=============================================*/
// COMENTADO: Event listener duplicado - se maneja en stock-transito-usuarios.php
/*
$(document).on("click", ".btnDescargaDirecta", function(e) {
    e.preventDefault();
    
    console.log("🔍 Evento click detectado en .btnDescargaDirecta");
    console.log("🔍 Elemento clickeado:", $(this));
    
    var idStockTransito = $(this).attr("idStockTransito");
    var codigoProducto = $(this).attr("codigoProducto");
    var descripcionProducto = $(this).attr("descripcionProducto");
    var cantidadDisponible = $(this).attr("cantidadDisponible");
    var transportadorId = $(this).attr("transportadorId");
    var nombreTransportador = $(this).attr("nombreTransportador");
    var sucursalOrigen = $(this).attr("sucursalOrigen");
    var idDespacho = $(this).attr("idDespacho");
    var numeroDespacho = $(this).attr("numeroDespacho");
    
    console.log("📥 Abriendo modal de descarga directa");
    console.log("ID Stock:", idStockTransito);
    console.log("Producto:", codigoProducto, "-", descripcionProducto);
    console.log("Cantidad disponible:", cantidadDisponible);
    console.log("Modal existe:", $("#modalDescargaDirecta").length > 0);
    
    // Llenar información del producto
    $("#descargaCodigo").text(codigoProducto);
    $("#descargaDescripcion").text(descripcionProducto);
    $("#descargaTransportador").text(nombreTransportador);
    $("#descargaOrigen").text(sucursalOrigen);
    $("#descargaDespacho").text(numeroDespacho);
    $("#descargaCantidadDisponible").val(cantidadDisponible);
    
    // Configurar máximo en el input
    $("#cantidadDescargar").attr("max", cantidadDisponible);
    $("#cantidadDescargar").val("");
    $("#observacionesDescarga").val("");
    
    // Mostrar modal
    console.log("🔍 Intentando mostrar modal...");
    $("#modalDescargaDirecta").modal("show");
    console.log("🔍 Modal mostrado");
});
*/

/*=============================================
ENVIAR DESCARGA DIRECTA
=============================================*/
$(document).on("submit", "#formDescargaDirecta", function(e) {
    e.preventDefault();
    
    var idStockTransito = $(".btnDescargaDirecta").attr("idStockTransito");
    var cantidadDescargar = $("#cantidadDescargar").val();
    var observaciones = $("#observacionesDescarga").val();
    
    if(!cantidadDescargar || cantidadDescargar <= 0) {
        swal("Error", "Debe ingresar una cantidad válida", "error");
        return;
    }
    
    var cantidadDisponible = parseInt($("#descargaCantidadDisponible").val());
    if(parseInt(cantidadDescargar) > cantidadDisponible) {
        swal("Error", "La cantidad a descargar no puede ser mayor a la disponible", "error");
        return;
    }
    
    console.log("📤 Enviando descarga directa");
    console.log("ID Stock:", idStockTransito);
    console.log("Cantidad:", cantidadDescargar);
    
    var datos = new FormData();
    datos.append("descargarStockDirecto", true);
    datos.append("codigoProducto", idStockTransito); // Usar como código de producto
    datos.append("cantidadDescargar", cantidadDescargar);
    datos.append("observaciones", observaciones);
    
    $.ajax({
        url: "ajax/stock-transito.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            
            console.log("📨 Respuesta descarga:", respuesta);
            
            if(respuesta.success) {
                swal({
                    title: "¡Descarga exitosa!",
                    text: respuesta.message,
                    type: "success",
                    confirmButtonText: "Cerrar"
                }).then(function() {
                    $("#modalDescargaDirecta").modal("hide");
                    tablaStockTransito.ajax.reload();
                });
            } else {
                swal("Error", respuesta.error || "No se pudo procesar la descarga", "error");
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error AJAX descarga:", error);
            swal("Error", "Error al procesar la descarga", "error");
        }
    });
});

/*=============================================
EVENTO DE PRUEBA PARA BOTÓN DESCARGA
=============================================*/
// COMENTADO: Event listener duplicado - se maneja en stock-transito-usuarios.php
/*
$(document).on("click", ".btnDescargaDirecta", function(e) {
    e.preventDefault();
    console.log("🎯 Botón de descarga directa clickeado!");
    
    // Obtener datos de los data attributes
    var idStockTransito = $(this).data("id-stock");
    var codigoProducto = $(this).data("codigo");
    var descripcionProducto = $(this).data("descripcion");
    var cantidadDisponible = $(this).data("cantidad");
    var nombreTransportador = $(this).data("transportador");
    var sucursalOrigen = $(this).data("origen");
    var numeroDespacho = $(this).data("despacho");
    
    console.log("📥 Datos obtenidos:", {
        idStockTransito,
        codigoProducto,
        descripcionProducto,
        cantidadDisponible,
        nombreTransportador,
        sucursalOrigen,
        numeroDespacho
    });
    
    // Llenar información del producto
    $("#descargaCodigo").text(codigoProducto);
    $("#descargaDescripcion").text(descripcionProducto);
    $("#descargaTransportador").text(nombreTransportador);
    $("#descargaOrigen").text(sucursalOrigen);
    $("#descargaDespacho").text(numeroDespacho);
    $("#descargaCantidadDisponible").val(cantidadDisponible);
    
    // Configurar máximo en el input
    $("#cantidadDescargar").attr("max", cantidadDisponible);
    $("#cantidadDescargar").val("");
    $("#observacionesDescarga").val("");
    
    // Mostrar modal
    console.log("🔍 Intentando mostrar modal...");
    $("#modalDescargaDirecta").modal("show");
    console.log("🔍 Modal mostrado");
});
*/

/*=============================================
FUNCIÓN DIRECTA PARA ABRIR MODAL
=============================================*/
function abrirModalDescarga(boton) {
    console.log("🚀 Función abrirModalDescarga llamada");
    
    var idStockTransito = boton.attr("idStockTransito");
    var codigoProducto = boton.attr("codigoProducto");
    var descripcionProducto = boton.attr("descripcionProducto");
    var cantidadDisponible = boton.attr("cantidadDisponible");
    var transportadorId = boton.attr("transportadorId");
    var nombreTransportador = boton.attr("nombreTransportador");
    var sucursalOrigen = boton.attr("sucursalOrigen");
    var idDespacho = boton.attr("idDespacho");
    var numeroDespacho = boton.attr("numeroDespacho");
    
    console.log("📥 Datos del producto:", {
        idStockTransito,
        codigoProducto,
        descripcionProducto,
        cantidadDisponible
    });
    
    // Llenar información del producto
    $("#descargaCodigo").text(codigoProducto);
    $("#descargaDescripcion").text(descripcionProducto);
    $("#descargaTransportador").text(nombreTransportador);
    $("#descargaOrigen").text(sucursalOrigen);
    $("#descargaDespacho").text(numeroDespacho);
    $("#descargaCantidadDisponible").val(cantidadDisponible);
    
    // Configurar máximo en el input
    $("#cantidadDescargar").attr("max", cantidadDisponible);
    $("#cantidadDescargar").val("");
    $("#observacionesDescarga").val("");
    
    // Mostrar modal
    console.log("🔍 Intentando mostrar modal...");
    $("#modalDescargaDirecta").modal("show");
    console.log("🔍 Modal mostrado");
}

/*=============================================
FUNCIÓN GLOBAL PARA ABRIR MODAL (ONCLICK)
=============================================*/
window.abrirModalDescargaDirecta = function(idStockTransito, codigoProducto, descripcionProducto, cantidadDisponible, nombreTransportador, sucursalOrigen, numeroDespacho) {
    console.log("🚀 Función global abrirModalDescargaDirecta llamada");
    console.log("📥 Parámetros:", {
        idStockTransito,
        codigoProducto,
        descripcionProducto,
        cantidadDisponible,
        nombreTransportador,
        sucursalOrigen,
        numeroDespacho
    });
    
    // Llenar información del producto
    $("#descargaCodigo").text(codigoProducto);
    $("#descargaDescripcion").text(descripcionProducto);
    $("#descargaTransportador").text(nombreTransportador);
    $("#descargaOrigen").text(sucursalOrigen);
    $("#descargaDespacho").text(numeroDespacho);
    $("#descargaCantidadDisponible").val(cantidadDisponible);
    
    // Configurar máximo en el input
    $("#cantidadDescargar").attr("max", cantidadDisponible);
    $("#cantidadDescargar").val("");
    $("#observacionesDescarga").val("");
    
    // Mostrar modal
    console.log("🔍 Intentando mostrar modal...");
    $("#modalDescargaDirecta").modal("show");
    console.log("🔍 Modal mostrado");
}

// Verificar que la función esté disponible globalmente
console.log("🔍 Función abrirModalDescargaDirecta disponible:", typeof window.abrirModalDescargaDirecta);

console.log("✅ Stock en Tránsito - JavaScript cargado correctamente");