<?php
/*=============================================
LIMPIAR DATATABLE COMPLETO
=============================================*/
?>
<!DOCTYPE html>
<html>
<head>
    <title>Limpiar DataTable</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <h2>🧹 Limpiar DataTable Completamente</h2>
    
    <div id="resultado"></div>
    
    <script>
    $(document).ready(function() {
        console.log("🧹 Iniciando limpieza completa de DataTable...");
        
        // 1. Verificar si existe
        if ($.fn.DataTable.isDataTable("#tabla-registro-descargas")) {
            console.log("🔄 DataTable existe, destruyendo...");
            try {
                $("#tabla-registro-descargas").DataTable().destroy();
                console.log("✅ DataTable destruido");
            } catch (e) {
                console.log("⚠️ Error al destruir:", e.message);
            }
        }
        
        // 2. Limpiar completamente el DOM
        $("#tabla-registro-descargas").removeClass("dataTable");
        $("#tabla-registro-descargas").find(".dataTables_wrapper").remove();
        $("#tabla-registro-descargas").unwrap();
        $("#tabla-registro-descargas").empty();
        
        // 3. Recrear estructura básica
        $("#tabla-registro-descargas").html(`
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
        `);
        
        // 4. Inicializar DataTable nuevo
        console.log("🔄 Inicializando DataTable nuevo...");
        
        $("#tabla-registro-descargas").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "ajax/datatable-registro-descargas-simple.ajax.php",
                "type": "POST"
            },
            "columns": [
                { "data": "id" },
                { "data": "fecha_hora" },
                { "data": "codigo_producto" },
                { "data": "descripcion_producto" },
                { "data": "cantidad_descargada" },
                { "data": "usuario_nombre" },
                { "data": "transportador_nombre" },
                { "data": "sucursal_nombre" },
                { "data": "numero_despacho" },
                { "data": "observaciones" }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "pageLength": 25,
            "order": [[0, "desc"]],
            "responsive": true,
            "autoWidth": false,
            "error": function(xhr, error, thrown) {
                console.error("❌ Error en DataTable:", error, thrown);
                console.error("❌ Respuesta del servidor:", xhr.responseText);
                $("#resultado").html("<p style='color: red;'>❌ Error: " + error + "</p>");
            },
            "initComplete": function() {
                console.log("✅ DataTable inicializado correctamente");
                $("#resultado").html("<p style='color: green;'>✅ DataTable inicializado correctamente</p>");
            }
        });
    });
    </script>
</body>
</html>
