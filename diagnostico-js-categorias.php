<?php
// Script de diagnóstico para verificar errores de JavaScript
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico JS Categorías</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>🔍 Diagnóstico JavaScript Categorías</h1>
    
    <div id="resultado" style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
        <h3>Logs de Diagnóstico:</h3>
        <div id="logs"></div>
    </div>

    <script>
    // Capturar errores de JavaScript
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        var string = msg.toLowerCase();
        var substring = "script error";
        if (string.indexOf(substring) > -1){
            agregarLog("❌ Error de Script: " + msg);
        } else {
            agregarLog("❌ Error JavaScript: " + msg + " en " + url + ":" + lineNo);
        }
        return false;
    };
    
    // Capturar errores de Promise
    window.addEventListener('unhandledrejection', function(event) {
        agregarLog("❌ Error de Promise: " + event.reason);
    });
    
    function agregarLog(mensaje) {
        var timestamp = new Date().toLocaleTimeString();
        $('#logs').append('<div>[' + timestamp + '] ' + mensaje + '</div>');
    }
    
    console.log("📋 Diagnóstico iniciando...");
    agregarLog("📋 Diagnóstico iniciando...");
    
    $(document).ready(function() {
        console.log("📋 Document ready ejecutado");
        agregarLog("📋 Document ready ejecutado");
        
        // Probar SweetAlert
        try {
            console.log("📋 Probando SweetAlert...");
            agregarLog("📋 Probando SweetAlert...");
            
            swal({
                title: "Test",
                text: "Probando SweetAlert",
                type: "info"
            }).then(() => {
                agregarLog("✅ SweetAlert funciona correctamente");
            });
            
        } catch (error) {
            console.error("❌ Error en SweetAlert:", error);
            agregarLog("❌ Error en SweetAlert: " + error.message);
        }
        
        // Probar AJAX
        try {
            console.log("📋 Probando AJAX...");
            agregarLog("📋 Probando AJAX...");
            
            $.ajax({
                url: "ajax/categorias-central.ajax.php",
                method: "POST",
                data: {
                    accion: "obtener"
                },
                dataType: "json",
                success: function(respuesta) {
                    console.log("📥 Respuesta AJAX:", respuesta);
                    agregarLog("📥 Respuesta AJAX: " + JSON.stringify(respuesta));
                },
                error: function(xhr, status, error) {
                    console.error("❌ Error AJAX:", {status, error});
                    agregarLog("❌ Error AJAX: " + status + " - " + error);
                }
            });
            
        } catch (error) {
            console.error("❌ Error en AJAX:", error);
            agregarLog("❌ Error en AJAX: " + error.message);
        }
        
        console.log("📋 Diagnóstico completado");
        agregarLog("📋 Diagnóstico completado");
    });
    </script>
</body>
</html>
