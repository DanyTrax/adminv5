<?php
/*=============================================
CAPTURAR LLAMADA AJAX REAL DESDE NAVEGADOR
=============================================*/

// Habilitar mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Crear archivo de log para capturar datos
$logFile = "debug-ajax-real.log";

// Función para escribir en el log
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Capturar datos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    writeLog("=== LLAMADA AJAX REAL DESDE NAVEGADOR ===");
    writeLog("POST data: " . print_r($_POST, true));
    writeLog("Raw input: " . file_get_contents('php://input'));
    writeLog("Headers: " . print_r(getallheaders(), true));
    writeLog("=== FIN LLAMADA AJAX ===");
    
    // Procesar los datos con el endpoint real
    try {
        writeLog("=== PROCESANDO CON ENDPOINT REAL ===");
        
        // Incluir el endpoint AJAX real
        ob_start();
        include "ajax/registro-descargas-simple.ajax.php";
        $output = ob_get_contents();
        ob_end_clean();
        
        writeLog("Respuesta endpoint real: " . $output);
        
        // Responder con la respuesta del endpoint real
        header('Content-Type: application/json');
        echo $output;
        
    } catch (Exception $e) {
        writeLog("Error procesando endpoint real: " . $e->getMessage());
        
        // Responder con error
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Error procesando endpoint real: ' . $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Capturar Llamada AJAX Real</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>🔍 Capturar Llamada AJAX Real</h1>
    
    <div id="debug-info">
        <h2>📋 Información de Debug:</h2>
        <p><strong>Archivo de log:</strong> <?php echo $logFile; ?></p>
        <p><strong>Timestamp:</strong> <span id="timestamp"></span></p>
        <p><strong>URL actual:</strong> <span id="url-actual"></span></p>
    </div>
    
    <div id="test-buttons">
        <h2>🧪 Botones de Prueba:</h2>
        
        <button id="btnTestAjaxReal">Probar AJAX Real</button>
        <button id="btnTestAjaxEndpoint">Probar Endpoint AJAX</button>
        <button id="btnVerLog">Ver Log</button>
        
        <div id="results"></div>
    </div>
    
    <script>
    $(document).ready(function() {
        // Mostrar información
        $("#timestamp").text(new Date().toLocaleString());
        $("#url-actual").text(window.location.href);
        
        // Probar AJAX real (simulando lo que hace el JavaScript)
        $("#btnTestAjaxReal").click(function() {
            $("#results").append("<p>🔍 Probando AJAX real...</p>");
            
            var datosRegistro = {
                accion: "registrar_descarga",
                codigo_producto: "AJAX" + Date.now(),
                descripcion_producto: "Producto desde AJAX real",
                cantidad_descargada: 1,
                usuario_id: "999",
                usuario_nombre: "Usuario Sistema",
                sucursal_id: "1",
                sucursal_nombre: "Sucursal 2",
                transportador_id: "0",
                transportador_nombre: "Debug Test",
                numero_despacho: "",
                observaciones: "Prueba AJAX real - " + new Date().toLocaleString()
            };
            
            $("#results").append("<p>📤 Enviando datos: " + JSON.stringify(datosRegistro) + "</p>");
            
            $.ajax({
                url: "debug-ajax-real.php",
                method: "POST",
                data: datosRegistro,
                dataType: "json",
                success: function(respuesta) {
                    $("#results").append("<p>✅ Respuesta recibida: " + JSON.stringify(respuesta) + "</p>");
                    
                    Swal.fire({
                        type: "success",
                        title: "¡Éxito!",
                        text: "AJAX real funcionando",
                        showConfirmButton: false,
                        timer: 2000
                    });
                },
                error: function(xhr, status, error) {
                    $("#results").append("<p>❌ Error: " + error + "</p>");
                    $("#results").append("<p>📋 Status: " + status + "</p>");
                    $("#results").append("<p>📋 Response: " + xhr.responseText + "</p>");
                    
                    Swal.fire({
                        type: "error",
                        title: "Error",
                        text: "Error: " + error,
                        showConfirmButton: true
                    });
                }
            });
        });
        
        // Probar endpoint AJAX real
        $("#btnTestAjaxEndpoint").click(function() {
            $("#results").append("<p>🔍 Probando endpoint AJAX real...</p>");
            
            $.ajax({
                url: "ajax/registro-descargas-simple.ajax.php",
                method: "POST",
                data: {
                    accion: "registrar_descarga",
                    codigo_producto: "ENDPOINT" + Date.now(),
                    descripcion_producto: "Producto desde endpoint real",
                    cantidad_descargada: 1,
                    usuario_id: "999",
                    usuario_nombre: "Usuario Sistema",
                    sucursal_id: "1",
                    sucursal_nombre: "Sucursal 2",
                    transportador_id: "0",
                    transportador_nombre: "Debug Test",
                    numero_despacho: "",
                    observaciones: "Prueba endpoint real - " + new Date().toLocaleString()
                },
                dataType: "json",
                success: function(respuesta) {
                    $("#results").append("<p>✅ Endpoint AJAX: " + JSON.stringify(respuesta) + "</p>");
                    
                    if(respuesta.success) {
                        Swal.fire({
                            type: "success",
                            title: "¡Éxito!",
                            text: "Endpoint AJAX funcionando",
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            type: "error",
                            title: "Error",
                            text: "Error: " + respuesta.error,
                            showConfirmButton: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $("#results").append("<p>❌ Error endpoint: " + error + "</p>");
                    
                    Swal.fire({
                        type: "error",
                        title: "Error",
                        text: "Error: " + error,
                        showConfirmButton: true
                    });
                }
            });
        });
        
        // Ver log
        $("#btnVerLog").click(function() {
            $("#results").append("<p>🔍 Cargando log...</p>");
            
            $.ajax({
                url: "<?php echo $logFile; ?>",
                method: "GET",
                success: function(data) {
                    $("#results").append("<pre>" + data + "</pre>");
                },
                error: function() {
                    $("#results").append("<p>❌ No se pudo cargar el log</p>");
                }
            });
        });
    });
    </script>
</body>
</html>
