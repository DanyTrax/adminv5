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
                    <!--=====================================
                    BOTÓN BORRAR TODO (SOLO PARA USUARIO "admin")
                    ======================================-->
                    <?php if(isset($_SESSION["usuario"]) && $_SESSION["usuario"] == "admin"): ?>
                    <button type="button" class="btn btn-danger btn-sm btnBorrarTodosRegistrosDescargas" style="margin-right: 15px;" title="Borrar todos los registros de descargas">
                        <i class="fa fa-trash"></i> Borrar Todo
                    </button>
                    <?php endif; ?>
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
                            <?php if(isset($_SESSION["perfil"]) && $_SESSION["perfil"] == "Administrador"): ?>
                            <th>Acción</th>
                            <?php endif; ?>
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
                { "width": "60px", "targets": 0 },
                { "width": "120px", "targets": 1 },
                { "width": "100px", "targets": 2 },
                { "width": "80px", "targets": 4 }
                <?php if(isset($_SESSION["perfil"]) && $_SESSION["perfil"] == "Administrador"): ?>
                ,{ "orderable": false, "width": "70px", "targets": 10, "className": "text-center" }
                <?php endif; ?>
            ],
            "initComplete": function() {
            }
        });
    }
    
    // El filtro de fechas se activa automáticamente desde filtros-fechas.js

    /*=============================================
    BORRAR UN REGISTRO DE DESCARGA (SOLO ADMIN)
    =============================================*/
    $(document).on("click", ".btnEliminarRegistroDescarga", function() {
        var id = $(this).data("id");
        var $btn = $(this);
        swal({
            title: "¿Eliminar este registro?",
            text: "Esta acción no se puede deshacer.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.value) {
                var datos = new FormData();
                datos.append("eliminarRegistroDescarga", id);
                $.ajax({
                    url: "ajax/registro-descargas.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function(respuesta) {
                        if (respuesta.success) {
                            var table = $('.tablaRegistroDescargas').DataTable();
                            table.row($btn.closest("tr")).remove().draw();
                            swal("Eliminado", respuesta.message, "success");
                        } else {
                            swal("Error", respuesta.message || "No se pudo eliminar", "error");
                        }
                    },
                    error: function() {
                        swal("Error", "Error de conexión", "error");
                    }
                });
            }
        });
    });

    /*=============================================
    BORRAR TODOS LOS REGISTROS DE DESCARGAS
    =============================================*/
    $(document).on("click", ".btnBorrarTodosRegistrosDescargas", function() {
        swal({
            title: "¿Está seguro?",
            text: "¡Esta acción eliminará TODOS los registros de descargas! Esta acción no se puede deshacer.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, borrar todo",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.value) {
                var datos = new FormData();
                datos.append("borrarTodosRegistrosDescargas", "ok");
                
                $.ajax({
                    url: "ajax/registro-descargas.ajax.php",
                    method: "POST",
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(respuesta) {
                        // La respuesta viene del controlador con el swal incluido
                        eval(respuesta);
                    }
                });
            }
        });
    });
});
</script>
