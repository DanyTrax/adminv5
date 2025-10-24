<?php
// Script simple para probar si hay errores de JavaScript
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Script Categorías</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>🧪 Test Script Categorías</h1>
    
    <button class="btnSincronizarCategorias" style="background: #5cb85c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        🔄 Sincronizar Categorías
    </button>
    
    <div id="resultado" style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
        <h3>Logs de Prueba:</h3>
        <div id="logs"></div>
    </div>

    <script>
    console.log("📋 Script de prueba iniciando...");
    
    $(document).ready(function() {
        console.log("📋 Document ready ejecutado");
        agregarLog("📋 Document ready ejecutado");
        
        // Event listener del botón
        $('.btnSincronizarCategorias').click(function() {
            console.log("🔄 Botón clickeado");
            agregarLog("🔄 Botón clickeado");
            sincronizarCategorias();
        });
        
        function sincronizarCategorias() {
            console.log("🔄 FUNCIÓN sincronizarCategorias() EJECUTÁNDOSE");
            agregarLog("🔄 FUNCIÓN sincronizarCategorias() EJECUTÁNDOSE");
            
            swal({
                title: "¿Sincronizar Categorías?",
                text: "Esto actualizará las categorías en todas las sucursales activas. ¿Continuar?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#5cb85c",
                confirmButtonText: "Sí, Sincronizar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                console.log("🔍 Resultado del SweetAlert:", result);
                agregarLog("🔍 Resultado del SweetAlert: " + JSON.stringify(result));
                
                if (result.isConfirmed) {
                    console.log("✅ Usuario confirmó sincronización");
                    agregarLog("✅ Usuario confirmó sincronización");
                    
                    // Simular petición AJAX
                    $.ajax({
                        url: "ajax/categorias-central.ajax.php",
                        method: "POST",
                        data: {
                            accion: "sincronizar"
                        },
                        dataType: "json",
                        success: function(respuesta) {
                            console.log("📥 Respuesta recibida:", respuesta);
                            agregarLog("📥 Respuesta recibida: " + JSON.stringify(respuesta));
                            
                            if (respuesta && respuesta.success) {
                                swal("Éxito", "Sincronización completada", "success");
                                agregarLog("✅ Sincronización exitosa");
                            } else {
                                swal("Error", respuesta ? respuesta.message : "Error desconocido", "error");
                                agregarLog("❌ Error: " + (respuesta ? respuesta.message : "Error desconocido"));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("❌ Error AJAX:", {status, error, responseText: xhr.responseText});
                            agregarLog("❌ Error AJAX: " + status + " - " + error);
                            swal("Error", "Error al sincronizar categorías", "error");
                        }
                    });
                } else {
                    console.log("❌ Usuario canceló sincronización");
                    agregarLog("❌ Usuario canceló sincronización");
                }
            }).catch((error) => {
                console.error("❌ Error en SweetAlert:", error);
                agregarLog("❌ Error en SweetAlert: " + error);
            });
        }
        
        function agregarLog(mensaje) {
            var timestamp = new Date().toLocaleTimeString();
            $('#logs').append('<div>[' + timestamp + '] ' + mensaje + '</div>');
        }
        
        console.log("📋 Script de prueba completamente cargado");
        agregarLog("📋 Script de prueba completamente cargado");
    });
    </script>
</body>
</html>
