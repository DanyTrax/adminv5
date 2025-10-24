<!DOCTYPE html>
<html>
<head>
    <title>Test Botón Sincronización Categorías</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        #logs { background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>🔍 Test Botón Sincronización Categorías</h1>
    
    <div class="test-section info">
        <h3>1. Test de SweetAlert2</h3>
        <button onclick="testSweetAlert()">Probar SweetAlert2</button>
    </div>
    
    <div class="test-section info">
        <h3>2. Test de AJAX</h3>
        <button onclick="testAjax()">Probar AJAX</button>
    </div>
    
    <div class="test-section info">
        <h3>3. Test de Sincronización Completa</h3>
        <button onclick="testSincronizacion()">Probar Sincronización</button>
    </div>
    
    <div id="logs"></div>

    <script>
    function agregarLog(mensaje, tipo = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logDiv = document.createElement('div');
        logDiv.innerHTML = `[${timestamp}] ${mensaje}`;
        logDiv.style.color = tipo === 'error' ? 'red' : tipo === 'success' ? 'green' : 'black';
        document.getElementById('logs').appendChild(logDiv);
    }
    
    function testSweetAlert() {
        agregarLog("🧪 Probando SweetAlert2...");
        
        Swal.fire({
            title: "Test SweetAlert2",
            text: "¿Funciona correctamente?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí",
            cancelButtonText: "No"
        }).then((result) => {
            if (result.isConfirmed) {
                agregarLog("✅ SweetAlert2 funciona correctamente", 'success');
            } else {
                agregarLog("❌ Usuario canceló", 'error');
            }
        }).catch((error) => {
            agregarLog("❌ Error en SweetAlert2: " + error.message, 'error');
        });
    }
    
    function testAjax() {
        agregarLog("🧪 Probando AJAX...");
        
        $.ajax({
            url: "ajax/categorias-central.ajax.php",
            method: "POST",
            data: {
                accion: "obtener"
            },
            dataType: "json",
            success: function(respuesta) {
                agregarLog("✅ AJAX exitoso: " + JSON.stringify(respuesta), 'success');
            },
            error: function(xhr, status, error) {
                agregarLog("❌ Error AJAX: " + status + " - " + error, 'error');
                agregarLog("❌ Response: " + xhr.responseText, 'error');
            }
        });
    }
    
    function testSincronizacion() {
        agregarLog("🧪 Probando sincronización completa...");
        
        Swal.fire({
            title: "¿Sincronizar Categorías?",
            text: "Esto actualizará las categorías en todas las sucursales activas. ¿Continuar?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#5cb85c",
            confirmButtonText: "Sí, Sincronizar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                agregarLog("✅ Usuario confirmó sincronización", 'success');
                
                // Mostrar loading
                Swal.fire({
                    title: "Sincronizando...",
                    text: "Por favor espera mientras se sincronizan las categorías",
                    icon: "info",
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Ejecutar AJAX
                $.ajax({
                    url: "ajax/categorias-central.ajax.php",
                    method: "POST",
                    data: {
                        accion: "sincronizar"
                    },
                    dataType: "json",
                    timeout: 30000,
                    success: function(respuesta) {
                        agregarLog("📥 Respuesta recibida: " + JSON.stringify(respuesta), 'success');
                        
                        if (respuesta && respuesta.success) {
                            Swal.fire({
                                title: "Sincronización Completada",
                                text: respuesta.message + "\nSucursales sincronizadas: " + respuesta.sucursales_sincronizadas + "/" + respuesta.total_sucursales,
                                icon: "success",
                                confirmButtonText: "Aceptar"
                            });
                            agregarLog("✅ Sincronización exitosa", 'success');
                        } else {
                            Swal.fire("Error", respuesta ? respuesta.message : "Respuesta inválida", "error");
                            agregarLog("❌ Error en respuesta: " + (respuesta ? respuesta.message : "Respuesta inválida"), 'error');
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
                        
                        Swal.fire("Error", mensajeError, "error");
                    }
                });
            } else {
                agregarLog("❌ Usuario canceló sincronización", 'error');
            }
        }).catch((error) => {
            agregarLog("❌ Error en SweetAlert: " + error.message, 'error');
        });
    }
    
    // Log inicial
    agregarLog("📋 Test iniciado - Listo para probar");
    </script>
</body>
</html>
