<div class="content-wrapper">
    
    <section class="content-header">
        
        <h1>
            Historial de Recepciones
            <small>Registro de productos recibidos del stock en tránsito</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Historial Recepciones</li>
        </ol>

    </section>

    <section class="content">

        <div class="box">
            
            <div class="box-header with-border">
                
                <h3 class="box-title">
                    <i class="fa fa-history"></i> 
                    Registro de recepciones agrupadas
                </h3>
                
            </div>

            <div class="box-body">
                
            <table id="tablaHistorialRecepciones" class="table table-bordered table-striped dt-responsive" width="100%">
                
                <thead>
                    <tr>
                        <th style="width:10px">#</th>
                        <th>Fecha Recepción</th>
                        <th>Usuario</th>
                        <th>Sucursal</th>
                        <th>Total Productos</th>
                        <th>Total Cantidad</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

            </table>

            </div>

        </div>

    </section>

</div>

<!--=====================================
MODAL VER DETALLE RECEPCIÓN
======================================-->
<div id="modalDetalleRecepcion" class="modal fade" role="dialog">
    
    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header" style="background:#3c8dbc; color:white">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-list"></i> Detalle de Recepción #<span id="detalleRecepcionId"></span>
                </h4>
            </div>

            <div class="modal-body">
                <div id="contenidoDetalleRecepcion">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando detalle...</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>

        </div>

    </div>

</div>

<script>
(function() {
    
    console.log("📋 Inicializando Historial de Recepciones...");
    
    var tablaHistorial = null;
    
    $(document).ready(function() {
        
        setTimeout(function() {
            
            console.log("⚡ Creando DataTable Historial...");
            
            tablaHistorial = $('#tablaHistorialRecepciones').DataTable({
                "ajax": {
                    "url": "ajax/datatable-historial-recepciones.ajax.php",
                    "type": "POST"
                },
                "processing": true,
                "order": [[1, 'desc']], // Ordenar por fecha descendente
                "language": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron recepciones",
                    "sEmptyTable": "No hay recepciones registradas",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sSearch": "Buscar:",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último", 
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    }
                },
                "columnDefs": [
                    { "targets": [0, 7], "orderable": false },
                    { "targets": [4, 5, 7], "className": "text-center" }
                ],
                "drawCallback": function() {
                    var info = this.api().page.info();
                    console.log("✅ Historial cargado:", info.recordsTotal, "recepciones");
                    
                    // Configurar evento ver detalle
                    $(document).off('click', '.btnVerDetalleRecepcion').on('click', '.btnVerDetalleRecepcion', function() {
                        var recepcionId = $(this).attr('recepcionId');
                        console.log("📋 Ver detalle recepción:", recepcionId);
                        
                        $("#detalleRecepcionId").text(recepcionId);
                        $("#modalDetalleRecepcion").modal("show");
                        cargarDetalleRecepcion(recepcionId);
                    });
                }
            });
            
        }, 1000);
        
    });
    
    // Función para cargar detalle de recepción
    function cargarDetalleRecepcion(recepcionId) {
        $.ajax({
            url: 'ajax/obtener-detalle-recepcion.ajax.php',
            type: 'POST',
            data: { recepcion_id: recepcionId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var html = '<table class="table table-bordered">';
                    html += '<thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Transportador</th><th>Origen</th></tr></thead>';
                    html += '<tbody>';
                    
                    response.detalles.forEach(function(detalle) {
                        html += '<tr>';
                        html += '<td>' + detalle.codigo_producto + '</td>';
                        html += '<td>' + detalle.descripcion_producto + '</td>';
                        html += '<td class="text-center">' + detalle.cantidad_recibida + '</td>';
                        html += '<td>' + detalle.transportador_nombre + '</td>';
                        html += '<td>' + detalle.sucursal_origen + '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    $("#contenidoDetalleRecepcion").html(html);
                } else {
                    $("#contenidoDetalleRecepcion").html('<p class="text-center text-danger">Error al cargar el detalle</p>');
                }
            },
            error: function() {
                $("#contenidoDetalleRecepcion").html('<p class="text-center text-danger">Error al cargar el detalle</p>');
            }
        });
    }
    
})();

console.log("✅ Módulo Historial Recepciones cargado");
</script>