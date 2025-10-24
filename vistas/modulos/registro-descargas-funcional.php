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
            "ajax": {
                "url": "ajax/datatable-registro-descargas-funcional.ajax.php",
                "data": function(d) {
                    // Pasar parámetros de fecha si existen
                    <?php if(isset($_GET["fechaInicial"])): ?>
                    d.fechaInicial = "<?php echo $_GET['fechaInicial']; ?>";
                    d.fechaFinal = "<?php echo $_GET['fechaFinal']; ?>";
                    <?php endif; ?>
                }
            },
            "deferRender": true,
            "retrieve": true,
            "processing": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
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
    
    // Activar filtro de fechas
    activarFiltroFechas();
});

function activarFiltroFechas() {
    // Si el botón existe en la página actual
    if ($('#daterange-btn-registro-descargas').length) {
        
        // Se lee el rango guardado para mantener el estado del botón
        if (localStorage.getItem('capturarRangoRegistroDescargas') != null) {
            $('#daterange-btn-registro-descargas span').html(localStorage.getItem('capturarRangoRegistroDescargas'));
        } else {
            $('#daterange-btn-registro-descargas span').html('<i class="fa fa-calendar"></i> Rango de fecha');
        }

        // Se inicializa el calendario en el botón
        $('#daterange-btn-registro-descargas').daterangepicker({
            ranges: {
                'Hoy'           : [moment(), moment()],
                'Ayer'          : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 días'  : [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes'      : [moment().startOf('month'), moment().endOf('month')],
                'Mes anterior'    : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment(),
            endDate: moment()
        },
        function(start, end) {
            var capturarRango = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY');
            $('#daterange-btn-registro-descargas span').html(capturarRango);
            
            var fechaInicial = start.format('YYYY-MM-DD');
            var fechaFinal = end.format('YYYY-MM-DD');

            localStorage.setItem('capturarRangoRegistroDescargas', capturarRango);
            window.location = "index.php?ruta=registro-descargas-funcional&fechaInicial=" + fechaInicial + "&fechaFinal=" + fechaFinal;
        });

        // Se maneja el botón de cancelar
        $('#daterange-btn-registro-descargas').on('cancel.daterangepicker', function() {
            localStorage.removeItem('capturarRangoRegistroDescargas');
            window.location = "registro-descargas-funcional";
        });
    }
}
</script>
