<?php
/*=============================================
CAPTURAR DATOS DE JAVASCRIPT EN TIEMPO REAL
=============================================*/

// Crear archivo de log para capturar datos
$logFile = "debug-javascript.log";

// Función para escribir en el log
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Capturar datos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    writeLog("=== DATOS POST RECIBIDOS ===");
    writeLog("POST data: " . print_r($_POST, true));
    writeLog("Raw input: " . file_get_contents('php://input'));
    writeLog("Headers: " . print_r(getallheaders(), true));
    writeLog("=== FIN DATOS POST ===");
    
    // Responder con JSON para JavaScript
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Datos capturados correctamente',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug JavaScript - Captura de Datos</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>🔍 Debug JavaScript - Captura de Datos</h1>
    
    <div id="debug-info">
        <h2>📋 Información de Debug:</h2>
        <p><strong>Archivo de log:</strong> <?php echo $logFile; ?></p>
        <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <div id="test-buttons">
        <h2>🧪 Botones de Prueba:</h2>
        
        <button id="btnTestSucursal">Probar Obtener Sucursal</button>
        <button id="btnTestUsuario">Probar Obtener Usuario</button>
        <button id="btnTestRegistro">Probar Registro Completo</button>
        
        <div id="results"></div>
    </div>
    
    <script>
    $(document).ready(function() {
        
        // Probar obtener sucursal
        $("#btnTestSucursal").click(function() {
            $("#results").append("<p>🔍 Probando obtener sucursal...</p>");
            
            $.ajax({
                url: "ajax/obtener-sucursal-actual.ajax.php",
                method: "GET",
                data: { accion: "obtener_sucursal_actual" },
                dataType: "json",
                success: function(respuesta) {
                    $("#results").append("<p>✅ Sucursal: " + JSON.stringify(respuesta) + "</p>");
                },
                error: function(xhr, status, error) {
                    $("#results").append("<p>❌ Error sucursal: " + error + "</p>");
                }
            });
        });
        
        // Probar obtener usuario
        $("#btnTestUsuario").click(function() {
            $("#results").append("<p>🔍 Probando obtener usuario...</p>");
            
            $.ajax({
                url: "ajax/obtener-usuario-actual.ajax.php",
                method: "GET",
                dataType: "json",
                success: function(respuesta) {
                    $("#results").append("<p>✅ Usuario: " + JSON.stringify(respuesta) + "</p>");
                },
                error: function(xhr, status, error) {
                    $("#results").append("<p>❌ Error usuario: " + error + "</p>");
                }
            });
        });
        
        // Probar registro completo
        $("#btnTestRegistro").click(function() {
            $("#results").append("<p>🔍 Probando registro completo...</p>");
            
            // Obtener datos de sucursal primero
            var sucursalId = "1";
            var sucursalNombre = "Sucursal Test";
            
            $.ajax({
                url: "ajax/obtener-sucursal-actual.ajax.php",
                method: "GET",
                data: { accion: "obtener_sucursal_actual" },
                dataType: "json",
                async: false,
                success: function(respuesta) {
                    if(respuesta.success) {
                        sucursalId = respuesta.sucursal.id;
                        sucursalNombre = respuesta.sucursal.nombre;
                        $("#results").append("<p>✅ Sucursal obtenida: " + sucursalNombre + "</p>");
                    }
                },
                error: function() {
                    $("#results").append("<p>⚠️ Usando sucursal por defecto</p>");
                }
            });
            
            // Obtener datos de usuario
            var usuarioId = "999";
            var usuarioNombre = "Usuario Test";
            
            $.ajax({
                url: "ajax/obtener-usuario-actual.ajax.php",
                method: "GET",
                dataType: "json",
                async: false,
                success: function(respuesta) {
                    if(respuesta.success) {
                        usuarioId = respuesta.usuario.id;
                        usuarioNombre = respuesta.usuario.nombre;
                        $("#results").append("<p>✅ Usuario obtenido: " + usuarioNombre + "</p>");
                    }
                },
                error: function() {
                    $("#results").append("<p>⚠️ Usando usuario por defecto</p>");
                }
            });
            
            // Enviar registro
            var datosRegistro = {
                accion: "registrar_descarga",
                codigo_producto: "DEBUG" + Date.now(),
                descripcion_producto: "Producto de debug desde JavaScript",
                cantidad_descargada: 1,
                usuario_id: usuarioId,
                usuario_nombre: usuarioNombre,
                sucursal_id: sucursalId,
                sucursal_nombre: sucursalNombre,
                transportador_id: "0",
                transportador_nombre: "Debug Test",
                numero_despacho: "",
                observaciones: "Prueba de debug - " + new Date().toLocaleString()
            };
            
            $("#results").append("<p>📤 Enviando datos: " + JSON.stringify(datosRegistro) + "</p>");
            
            $.ajax({
                url: "debug-javascript-capture.php",
                method: "POST",
                data: datosRegistro,
                dataType: "json",
                success: function(respuesta) {
                    $("#results").append("<p>✅ Registro enviado: " + JSON.stringify(respuesta) + "</p>");
                },
                error: function(xhr, status, error) {
                    $("#results").append("<p>❌ Error en registro: " + error + "</p>");
                }
            });
        });
    });
    </script>
</body>
</html>
