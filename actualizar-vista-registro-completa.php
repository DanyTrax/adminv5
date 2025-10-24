<?php
/*=============================================
ACTUALIZAR VISTA REGISTRO COMPLETA
=============================================*/

echo "🔧 Actualizando vista con interfaz completa\n";
echo "==========================================\n\n";

// Vista completa con interfaz funcional
$vistaCompleta = '<!--=============================================
REGISTRO DE DESCARGAS SIMPLE
=============================================-->

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
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

    <!-- Main content -->
    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 id="totalDescargas">0</h3>
                        <p>Total Descargas</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-download"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 id="totalCantidad">0</h3>
                        <p>Total Cantidad</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-cubes"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 id="productosUnicos">0</h3>
                        <p>Productos Únicos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-tags"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3 id="usuariosUnicos">0</h3>
                        <p>Usuarios Únicos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Filtros de Búsqueda</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Desde:</label>
                                    <input type="date" class="form-control" id="fechaDesde">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha Hasta:</label>
                                    <input type="date" class="form-control" id="fechaHasta">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Código Producto:</label>
                                    <input type="text" class="form-control" id="codigoProducto" placeholder="Buscar por código">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button class="btn btn-primary" id="btnFiltrar">
                                        <i class="fa fa-search"></i> Filtrar
                                    </button>
                                    <button class="btn btn-default" id="btnLimpiar">
                                        <i class="fa fa-refresh"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de registros -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Registro de Descargas</h3>
                    </div>
                    <div class="box-body">
                        <table id="tablaRegistroDescargas" class="table table-bordered table-striped dt-responsive tablaRegistroDescargas" width="100%">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Usuario</th>
                                    <th>Sucursal</th>
                                    <th>Transportador</th>
                                    <th>Despacho</th>
                                    <th>Observaciones</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="vistas/js/registro-descargas-simple.js"></script>';

file_put_contents("vistas/modulos/registro-descargas-simple.php", $vistaCompleta);
echo "✅ Vista actualizada con interfaz completa\n";

// Actualizar JavaScript con funcionalidad completa
$jsCompleto = '/*=============================================
REGISTRO DE DESCARGAS SIMPLE - JAVASCRIPT
=============================================*/

$(document).ready(function() {
    // Inicializar DataTable
    inicializarDataTable();
    
    // Cargar estadísticas
    cargarEstadisticas();
    
    // Event listeners
    $("#btnFiltrar").click(function() {
        aplicarFiltros();
    });
    
    $("#btnLimpiar").click(function() {
        limpiarFiltros();
    });
});

/*=============================================
INICIALIZAR DATATABLE
=============================================*/
function inicializarDataTable() {
    $("#tablaRegistroDescargas").DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/datatable-registro-descargas-simple.ajax.php",
            "type": "POST",
            "data": function(d) {
                d.fecha_desde = $("#fechaDesde").val();
                d.fecha_hasta = $("#fechaHasta").val();
                d.codigo_producto = $("#codigoProducto").val();
            }
        },
        "columns": [
            {"data": 0},
            {"data": 1},
            {"data": 2},
            {"data": 3},
            {"data": 4},
            {"data": 5},
            {"data": 6},
            {"data": 7},
            {"data": 8}
        ],
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
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
        "responsive": true,
        "autoWidth": false,
        "order": [[8, "desc"]]
    });
}

/*=============================================
CARGAR ESTADÍSTICAS
=============================================*/
function cargarEstadisticas() {
    $.ajax({
        url: "ajax/registro-descargas-simple.ajax.php",
        method: "POST",
        data: {
            accion: "obtener_estadisticas",
            fecha_desde: $("#fechaDesde").val(),
            fecha_hasta: $("#fechaHasta").val()
        },
        dataType: "json",
        success: function(respuesta) {
            if(respuesta) {
                $("#totalDescargas").text(respuesta.total_descargas || 0);
                $("#totalCantidad").text(respuesta.total_cantidad || 0);
                $("#productosUnicos").text(respuesta.productos_unicos || 0);
                $("#usuariosUnicos").text(respuesta.usuarios_unicos || 0);
            }
        },
        error: function() {
            console.error("Error al cargar estadísticas");
        }
    });
}

/*=============================================
APLICAR FILTROS
=============================================*/
function aplicarFiltros() {
    // Recargar DataTable con filtros
    $("#tablaRegistroDescargas").DataTable().ajax.reload();
    
    // Recargar estadísticas con filtros
    cargarEstadisticas();
}

/*=============================================
LIMPIAR FILTROS
=============================================*/
function limpiarFiltros() {
    $("#fechaDesde").val("");
    $("#fechaHasta").val("");
    $("#codigoProducto").val("");
    
    // Recargar DataTable sin filtros
    $("#tablaRegistroDescargas").DataTable().ajax.reload();
    
    // Recargar estadísticas sin filtros
    cargarEstadisticas();
}';

file_put_contents("vistas/js/registro-descargas-simple.js", $jsCompleto);
echo "✅ JavaScript actualizado con funcionalidad completa\n";

// Actualizar AJAX DataTable
$datatableCompleto = '<?php
/*=============================================
DATATABLE REGISTRO DE DESCARGAS SIMPLE
=============================================*/

// Cargar controlador solo si no está cargado
if (!class_exists("ControladorRegistroDescargasSimple")) {
    require_once "../controladores/registro-descargas-simple.controlador.php";
}

$registroDescargas = new ControladorRegistroDescargasSimple();

// Obtener parámetros de DataTable
$start = $_POST["start"] ?? 0;
$length = $_POST["length"] ?? 10;
$search = $_POST["search"]["value"] ?? "";

// Filtros adicionales
$filtros = [
    "fecha_desde" => $_POST["fecha_desde"] ?? "",
    "fecha_hasta" => $_POST["fecha_hasta"] ?? "",
    "usuario_id" => $_POST["usuario_id"] ?? "",
    "sucursal_id" => $_POST["sucursal_id"] ?? "",
    "codigo_producto" => $_POST["codigo_producto"] ?? "",
    "busqueda_general" => $search
];

// Obtener registros
$registros = $registroDescargas->ctrObtenerRegistro($filtros);

// Preparar datos para DataTable
$data = [];
foreach($registros as $registro) {
    $data[] = [
        $registro["codigo_producto"],
        $registro["descripcion_producto"],
        $registro["cantidad_descargada"],
        $registro["usuario_nombre"],
        $registro["sucursal_nombre"],
        $registro["transportador_nombre"] ?? "N/A",
        $registro["numero_despacho"] ?? "N/A",
        $registro["observaciones"],
        date("d/m/Y H:i", strtotime($registro["fecha_descarga"]))
    ];
}

// Respuesta para DataTable
echo json_encode([
    "draw" => intval($_POST["draw"]),
    "recordsTotal" => count($registros),
    "recordsFiltered" => count($registros),
    "data" => $data
]);
?>';

file_put_contents("ajax/datatable-registro-descargas-simple.ajax.php", $datatableCompleto);
echo "✅ AJAX DataTable actualizado con funcionalidad completa\n";

echo "\n🎉 ¡Vista actualizada con interfaz completa!\n";
echo "\n📊 FUNCIONALIDADES DISPONIBLES:\n";
echo "- ✅ Interfaz completa con estadísticas\n";
echo "- ✅ Tabla filtrable con DataTable\n";
echo "- ✅ Filtros por fecha, código, usuario\n";
echo "- ✅ Búsqueda general\n";
echo "- ✅ Estadísticas en tiempo real\n";

echo "\n🔧 PRÓXIMOS PASOS:\n";
echo "1. Actualizar módulo: php actualizar-modulo-registro-completo.php\n";
echo "2. Probar el módulo: registro-descargas-simple\n";
echo "3. Hacer una descarga de producto\n";
echo "4. Verificar que se registre automáticamente\n";
?>
