<!DOCTYPE html>
<html>
<head>
    <title>Debug Sincronización Categorías</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        #logs { background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 10px; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <h1>🔍 Debug Sincronización Categorías</h1>
    
    <div class="debug-section info">
        <h3>1. Verificar SweetAlert</h3>
        <p>Verificando si SweetAlert está disponible y funciona...</p>
        <button onclick="verificarSweetAlert()">Verificar SweetAlert</button>
        <div id="sweetalert-status"></div>
    </div>
    
    <div class="debug-section info">
        <h3>2. Test de AJAX Backend</h3>
        <p>Probando la conexión con el backend...</p>
        <button onclick="testBackend()">Probar Backend</button>
        <div id="backend-status"></div>
    </div>
    
    <div class="debug-section info">
        <h3>3. Test de Sincronización Completa</h3>
        <p>Simulando el flujo completo de sincronización...</p>
        <button onclick="testSincronizacionCompleta()">Probar Sincronización</button>
        <div id="sincronizacion-status"></div>
    </div>
    
    <div class="debug-section">
        <h3>Logs de Debug</h3>
        <div id="logs"></div>
    </div>

    <script>
    function agregarLog(mensaje, tipo = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logDiv = document.createElement('div');
        logDiv.innerHTML = `[${timestamp}] ${mensaje}`;
        logDiv.style.color = tipo === 'error' ? 'red' : tipo === 'success' ? 'green' : 'black';
        document.getElementById('logs').appendChild(logDiv);
        
        // Scroll to bottom
        const logs = document.getElementById('logs');
        logs.scrollTop = logs.scrollHeight;
    }
    
    function verificarSweetAlert() {
        agregarLog("🔍 Verificando SweetAlert...");
        
        // Verificar si swal está disponible
        if (typeof swal === 'undefined') {
            agregarLog("❌ swal no está definido", 'error');
            document.getElementById('sweetalert-status').innerHTML = '<span style="color: red;">❌ swal no disponible</span>';
            return;
        }
        
        // Verificar si Swal está disponible
        if (typeof Swal === 'undefined') {
            agregarLog("❌ Swal no está definido", 'error');
            document.getElementById('sweetalert-status').innerHTML = '<span style="color: red;">❌ Swal no disponible</span>';
            return;
        }
        
        agregarLog("✅ swal y Swal están disponibles", 'success');
        document.getElementById('sweetalert-status').innerHTML = '<span style="color: green;">✅ SweetAlert disponible</span>';
        
        // Probar SweetAlert
        try {
            swal({
                title: "Test SweetAlert",
                text: "¿Funciona correctamente?",
                type: "question",
                showCancelButton: true,
                confirmButtonText: "Sí",
                cancelButtonText: "No"
            }).then((result) => {
                if (result.value) {
                    agregarLog("✅ SweetAlert funciona correctamente", 'success');
                } else {
                    agregarLog("❌ Usuario canceló", 'error');
                }
            }).catch((error) => {
                agregarLog("❌ Error en SweetAlert: " + error.message, 'error');
            });
        } catch (error) {
            agregarLog("❌ Error al ejecutar SweetAlert: " + error.message, 'error');
        }
    }
    
    function testBackend() {
        agregarLog("🔍 Probando backend...");
        
        $.ajax({
            url: "ajax/categorias-central.ajax.php",
            method: "POST",
            data: {
                accion: "obtener"
            },
            dataType: "json",
            timeout: 10000,
            success: function(respuesta) {
                agregarLog("✅ Backend responde correctamente", 'success');
                agregarLog("📥 Respuesta: " + JSON.stringify(respuesta), 'info');
                document.getElementById('backend-status').innerHTML = '<span style="color: green;">✅ Backend OK</span>';
            },
            error: function(xhr, status, error) {
                agregarLog("❌ Error backend: " + status + " - " + error, 'error');
                agregarLog("❌ Response: " + xhr.responseText, 'error');
                document.getElementById('backend-status').innerHTML = '<span style="color: red;">❌ Backend Error</span>';
            }
        });
    }
    
    function testSincronizacionCompleta() {
        agregarLog("🔍 Iniciando test de sincronización completa...");
        
        // Simular el flujo exacto del botón
        swal({
            title: "¿Sincronizar Categorías?",
            text: "Esto actualizará las categorías en todas las sucursales activas. ¿Continuar?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#5cb85c",
            confirmButtonText: "Sí, Sincronizar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            agregarLog("🔍 Resultado del SweetAlert: " + JSON.stringify(result), 'info');
            
            if (result.value) {
                agregarLog("✅ Usuario confirmó sincronización", 'success');
                
                // Mostrar loading
                swal({
                    title: "Sincronizando...",
                    text: "Por favor espera mientras se sincronizan las categorías",
                    type: "info",
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onOpen: function() {
                        swal.showLoading();
                    }
                });
                
                agregarLog("📡 Enviando petición AJAX de sincronización...");
                
                // Ejecutar AJAX de sincronización
                $.ajax({
                    url: "ajax/categorias-central.ajax.php",
                    method: "POST",
                    data: {
                        accion: "sincronizar"
                    },
                    dataType: "json",
                    timeout: 30000,
                    success: function(respuesta) {
                        agregarLog("📥 Respuesta de sincronización recibida", 'success');
                        agregarLog("📥 Datos: " + JSON.stringify(respuesta), 'info');
                        
                        if (respuesta && respuesta.success) {
                            swal({
                                title: "Sincronización Completada",
                                text: respuesta.message + "\nSucursales sincronizadas: " + respuesta.sucursales_sincronizadas + "/" + respuesta.total_sucursales,
                                type: "success",
                                confirmButtonText: "Aceptar"
                            });
                            agregarLog("✅ Sincronización exitosa", 'success');
                            document.getElementById('sincronizacion-status').innerHTML = '<span style="color: green;">✅ Sincronización OK</span>';
                        } else {
                            swal("Error", respuesta ? respuesta.message : "Respuesta inválida", "error");
                            agregarLog("❌ Error en respuesta: " + (respuesta ? respuesta.message : "Respuesta inválida"), 'error');
                            document.getElementById('sincronizacion-status').innerHTML = '<span style="color: red;">❌ Error en respuesta</span>';
                        }
                    },
                    error: function(xhr, status, error) {
                        agregarLog("❌ Error AJAX: " + status + " - " + error, 'error');
                        agregarLog("❌ Response: " + xhr.responseText, 'error');
                        
                        let mensajeError = "Error al sincronizar categorías";
                        if (xhr.status === 0) {
                            mensajeError = "Error de conexión. Verifica tu conexión a internet.";
                        } else if (xhr.status === 404) {
                            mensajeError = "Archivo no encontrado. Verifica la ruta del AJAX.";
                        } else if (xhr.status === 500) {
                            mensajeError = "Error del servidor. Revisa los logs.";
                        }
                        
                        swal("Error", mensajeError, "error");
                        document.getElementById('sincronizacion-status').innerHTML = '<span style="color: red;">❌ Error AJAX</span>';
                    }
                });
            } else {
                agregarLog("❌ Usuario canceló sincronización", 'error');
                document.getElementById('sincronizacion-status').innerHTML = '<span style="color: orange;">⚠️ Usuario canceló</span>';
            }
        }).catch((error) => {
            agregarLog("❌ Error en SweetAlert: " + error.message, 'error');
            document.getElementById('sincronizacion-status').innerHTML = '<span style="color: red;">❌ Error SweetAlert</span>';
        });
    }
    
    // Log inicial
    agregarLog("📋 Debug iniciado - Listo para probar");
    agregarLog("🔍 Verificando disponibilidad de librerías...");
    
    // Verificar jQuery
    if (typeof $ !== 'undefined') {
        agregarLog("✅ jQuery disponible", 'success');
    } else {
        agregarLog("❌ jQuery no disponible", 'error');
    }
    
    // Verificar SweetAlert
    if (typeof swal !== 'undefined') {
        agregarLog("✅ swal disponible", 'success');
    } else {
        agregarLog("❌ swal no disponible", 'error');
    }
    
    if (typeof Swal !== 'undefined') {
        agregarLog("✅ Swal disponible", 'success');
    } else {
        agregarLog("❌ Swal no disponible", 'error');
    }
    </script>
</body>
</html>
