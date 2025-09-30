/*=============================================
VARIABLES GLOBALES
=============================================*/
var tablaStockTransito;
var perfilUsuario = window.perfilUsuario || 'Invitado';
var idUsuario = window.idUsuario || 0;

/*=============================================
INICIALIZACIÓN
=============================================*/
$(document).ready(function() {
    
    console.log("🚛 Stock en Tránsito inicializado");
    console.log("👤 Perfil:", perfilUsuario);
    console.log("🔢 ID Usuario:", idUsuario);
    
    cargarTablaStockTransito();
    configurarEventos();
});

/*=============================================
CARGAR DATATABLE
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
                "targets": [0, 3, 7, 8], // AÑADIR columna 8 para botones
                "orderable": false
            },
            {
                "targets": [3],
                "className": "text-center"
            },
            {
                "targets": [8], // Columna de botones
                "className": "text-center"
            }
        ]
    });
}

/*=============================================
CONFIGURAR EVENTOS
=============================================*/
function configurarEventos() {
    
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

console.log("✅ Stock en Tránsito - JavaScript cargado correctamente");