<?php
// Script simple para probar la búsqueda de solicitudes
session_start();

// Simular una sesión válida
if(!isset($_SESSION["iniciarSesion"])) {
    $_SESSION["iniciarSesion"] = "ok";
    $_SESSION["perfil"] = "Administrador";
    $_SESSION["nombre"] = "Test User";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Búsqueda Solicitudes</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <h1>🔍 Debug Búsqueda de Solicitudes</h1>
        
        <div class="form-group">
            <label>Número de solicitud:</label>
            <div class="input-group">
                <input type="text" 
                       class="form-control" 
                       id="numeroSolicitudBuscar"
                       placeholder="Escribe al menos 3 caracteres">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-info" onclick="buscarSolicitudes()">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                </span>
            </div>
        </div>
        
        <div id="resultados" class="mt-3"></div>
        
        <div class="mt-3">
            <h3>Debug Info:</h3>
            <div id="debugInfo"></div>
        </div>
    </div>
    
    <script>
        console.log("🔍 Debug script iniciado");
        
        function buscarSolicitudes() {
            var termino = $("#numeroSolicitudBuscar").val();
            console.log("🔍 Buscando con término:", termino);
            
            if(termino.length < 3) {
                alert("Escribe al menos 3 caracteres");
                return;
            }
            
            $("#resultados").html('<div class="alert alert-info">Buscando...</div>');
            
            $.ajax({
                url: "ajax/productos-despacho.ajax.php",
                method: "POST",
                data: {
                    buscarSolicitudes: true,
                    termino: termino
                },
                dataType: "json",
                success: function(respuesta) {
                    console.log("✅ Respuesta:", respuesta);
                    $("#resultados").html('<pre>' + JSON.stringify(respuesta, null, 2) + '</pre>');
                },
                error: function(xhr, status, error) {
                    console.error("❌ Error:", error);
                    $("#resultados").html('<div class="alert alert-danger">Error: ' + error + '</div>');
                }
            });
        }
        
        $(document).ready(function() {
            console.log("📄 Document ready");
            console.log("🔍 Elemento existe:", $("#numeroSolicitudBuscar").length > 0);
            
            // Event listener simple
            $("#numeroSolicitudBuscar").on("keyup", function(e) {
                console.log("⌨️ Keyup detectado:", $(this).val());
                
                if(e.keyCode === 13) { // Enter
                    console.log("⏎ Enter presionado");
                    buscarSolicitudes();
                }
            });
            
            $("#numeroSolicitudBuscar").on("input", function(e) {
                console.log("⌨️ Input detectado:", $(this).val());
            });
            
            // Debug info
            $("#debugInfo").html(`
                <p><strong>jQuery cargado:</strong> ${typeof $ !== 'undefined' ? 'Sí' : 'No'}</p>
                <p><strong>Elemento encontrado:</strong> ${$("#numeroSolicitudBuscar").length > 0 ? 'Sí' : 'No'}</p>
                <p><strong>URL actual:</strong> ${window.location.href}</p>
            `);
        });
    </script>
</body>
</html>
