<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Registro de Descargas
            <small>Stock en Tránsito</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Registro de Descargas</li>
        </ol>
    </section>

    <section class="content">
        <!-- Tabla de Registros -->
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Registro de Descargas</h3>
                <div class="box-tools pull-right">
                    <?php
                        $urlDescarga = "vistas/modulos/descargar-registro-descargas-funcional.php";
                        if (isset($_GET["fechaInicial"])) {
                            $urlDescarga .= "?fechaInicial=" . $_GET["fechaInicial"] . "&fechaFinal=" . $_GET["fechaFinal"];
                        }
                    ?>
                    <a href="<?= $urlDescarga ?>" style="margin-left:10px;">
                        <button class="btn btn-success btn-sm" style="margin-right: 15px;">
                            <i class="fa fa-file-excel-o"></i> Exportar Excel
                        </button>
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="location.reload()">
                        <i class="fa fa-refresh"></i> Actualizar
                    </button>
                    <button type="button" class="btn btn-default pull-right" id="daterange-btn-registro-descargas">
                        <span>
                            <i class="fa fa-calendar"></i> 
                            <?php
                                if (isset($_GET["fechaInicial"])) {
                                    echo $_GET["fechaInicial"] . " - " . $_GET["fechaFinal"];
                                } else {
                                    echo 'Rango de fecha';
                                }
                            ?>
                        </span>
                        <i class="fa fa-caret-down"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped dt-responsive tablaRegistroDescargas" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha y Hora</th>
                            <th>Código Producto</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Usuario</th>
                            <th>Transportador</th>
                            <th>Sucursal</th>
                            <th>Despacho</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargarán via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    console.log("✅ Módulo Registro de Descargas Funcional cargado");
    
    // Inicializar DataTable
    if($('.tablaRegistroDescargas').length > 0) {
        $('.tablaRegistroDescargas').DataTable({
            "ajax": "ajax/datatable-registro-descargas-funcional.ajax.php",
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
            },
            "order": [[0, "desc"]], // Ordenar por ID descendente
            "columnDefs": [
                { "orderable": false, "targets": [] }, // Todas las columnas ordenables
                { "width": "60px", "targets": 0 }, // ID
                { "width": "120px", "targets": 1 }, // Fecha
                { "width": "100px", "targets": 2 }, // Código
                { "width": "80px", "targets": 4 }  // Cantidad
            ],
            "initComplete": function() {
                console.log("✅ DataTable inicializado correctamente");
            }
        });
    }
    
    // El filtro de fechas se activa automáticamente desde filtros-fechas.js
});
</script>
