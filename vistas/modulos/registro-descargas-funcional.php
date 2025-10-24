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
        <!-- Info Boxes -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 id="total-descargas">0</h3>
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
                        <h3 id="total-cantidad">0</h3>
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
                        <h3 id="productos-unicos">0</h3>
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
                        <h3 id="usuarios-unicos">0</h3>
                        <p>Usuarios Únicos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Registros -->
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Registro de Descargas</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" onclick="cargarEstadisticas()">
                        <i class="fa fa-refresh"></i> Actualizar
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
                cargarEstadisticas();
            }
        });
    }
});

function cargarEstadisticas() {
    // Obtener datos del DataTable
    var table = $('.tablaRegistroDescargas').DataTable();
    var data = table.data().toArray();
    
    // Calcular estadísticas
    var totalDescargas = data.length;
    var totalCantidad = 0;
    var productosUnicos = new Set();
    var usuariosUnicos = new Set();
    
    data.forEach(function(row) {
        totalCantidad += parseInt(row[4]) || 0; // Cantidad (columna 4)
        productosUnicos.add(row[2]); // Código producto (columna 2)
        usuariosUnicos.add(row[5]); // Usuario (columna 5)
    });
    
    // Actualizar info boxes
    $('#total-descargas').text(totalDescargas);
    $('#total-cantidad').text(totalCantidad);
    $('#productos-unicos').text(productosUnicos.size);
    $('#usuarios-unicos').text(usuariosUnicos.size);
    
    console.log("✅ Estadísticas actualizadas:", {
        totalDescargas: totalDescargas,
        totalCantidad: totalCantidad,
        productosUnicos: productosUnicos.size,
        usuariosUnicos: usuariosUnicos.size
    });
}
</script>
