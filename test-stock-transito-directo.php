<!DOCTYPE html>
<html>
<head>
    <title>Test Stock Tránsito Directo</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="vistas/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="vistas/bower_components/font-awesome/css/font-awesome.min.css">
</head>
<body>
<?php session_start(); ?>

<div class="container-fluid" style="margin-top: 20px;">
    
    <h2>🧪 Test Stock Tránsito - Directo</h2>
    
    <div class="box">
        <div class="box-header">
            <h3>Tabla de Stock en Tránsito</h3>
        </div>
        <div class="box-body">
            <table id="tablaStockTransito" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Transportador</th>
                        <th>Origen</th>
                        <th>Fecha</th>
                        <th>Solicitudes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    
</div>

<!-- JavaScript -->
<script src="vistas/bower_components/jquery/dist/jquery.min.js"></script>
<script src="vistas/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="vistas/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="vistas/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="vistas/plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
// Variables globales
window.perfilUsuario = '<?php echo $_SESSION["perfil"] ?? "Administrador"; ?>';
window.idUsuario = <?php echo $_SESSION["id"] ?? 1; ?>;

console.log("🔧 Variables definidas:");
console.log("- perfilUsuario:", window.perfilUsuario);
console.log("- idUsuario:", window.idUsuario);

// Inicializar DataTable directamente
$(document).ready(function() {
    
    console.log("🚛 Inicializando DataTable de Stock Tránsito...");
    
    var tabla = $('#tablaStockTransito').DataTable({
        "ajax": {
            "url": "ajax/datatable-stock-transito.ajax.php",
            "type": "POST",
            "data": {
                "tabla": "stock-transito"
            },
            "error": function(xhr, error, code) {
                console.error("❌ Error AJAX:", xhr.status, error);
                console.error("Respuesta:", xhr.responseText);
            }
        },
        "processing": true,
        "language": {
            "sProcessing": "Procesando...",
            "sZeroRecords": "No se encontraron productos en tránsito",
            "sEmptyTable": "No hay productos en stock",
            "sLoadingRecords": "Cargando..."
        },
        "columnDefs": [
            {
                "targets": [0, 3, 7, 8],
                "orderable": false
            }
        ],
        "drawCallback": function() {
            console.log("✅ DataTable cargado exitosamente");
            var info = this.api().page.info();
            console.log("📊 Registros:", info.recordsTotal);
        }
    });
    
});
</script>

</body>
</html>