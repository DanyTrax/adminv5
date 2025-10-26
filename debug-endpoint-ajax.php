<?php
/*=============================================
CAPTURAR DATOS DEL ENDPOINT AJAX EN TIEMPO REAL
=============================================*/

// Habilitar mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Crear archivo de log para capturar datos
$logFile = "debug-endpoint-ajax.log";

// Función para escribir en el log
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Capturar datos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    writeLog("=== DATOS POST RECIBIDOS EN ENDPOINT AJAX ===");
    writeLog("POST data: " . print_r($_POST, true));
    writeLog("Raw input: " . file_get_contents('php://input'));
    writeLog("Headers: " . print_r(getallheaders(), true));
    writeLog("=== FIN DATOS POST ===");
    
    // Probar el controlador directamente
    try {
        writeLog("=== PROBANDO CONTROLADOR ===");
        
        // Incluir controlador
        require_once __DIR__ . "/../controladores/registro-descargas-simple.controlador.php";
        $controlador = new ControladorRegistroDescargasSimple();
        $resultado = $controlador->ctrRegistrarDescarga();
        
        writeLog("Resultado controlador: " . json_encode($resultado));
        
        if ($resultado["success"]) {
            writeLog("✅ Controlador funcionando correctamente");
        } else {
            writeLog("❌ Error en controlador: " . $resultado["error"]);
        }
        
    } catch (Exception $e) {
        writeLog("❌ Error con controlador: " . $e->getMessage());
        writeLog("Trace: " . $e->getTraceAsString());
    }
    
    // Responder con JSON para JavaScript
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Datos capturados correctamente',
        'timestamp' => date('Y-m-d H:i:s'),
        'log_file' => $logFile
    ]);
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Endpoint AJAX</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>🔍 Debug Endpoint AJAX</h1>
    
    <div id="debug-info">
        <h2>📋 Información de Debug:</h2>
        <p><strong>Archivo de log:</strong> <?php echo $logFile; ?></p>
        <p><strong>Timestamp:</strong> <span id="timestamp"></span></p>
        <p><strong>URL actual:</strong> <span id="url-actual"></span></p>
    </div>
    
    <div id="test-buttons">
        <h2>🧪 Botones de Prueba:</h2>
        
        <button id="btnTestEndpoint">Probar Endpoint AJAX</button>
        <button id="btnTestControlador">Probar Controlador Directo</button>
        <button id="btnVerLog">Ver Log</button>
        
        <div id="results"></div>
    </div>
    
    <script>
    $(document).ready(function() {
        // Mostrar información
        $("#timestamp").text(new Date().toLocaleString());
        $("#url-actual").text(window.location.href);
        
        // Probar endpoint AJAX
        $("#btnTestEndpoint").click(function() {
            $("#results").append("<p>🔍 Probando endpoint AJAX...</p>");
            
            var datosRegistro = {
                accion: "registrar_descarga",
                codigo_producto: "DEBUG" + Date.now(),
                descripcion_producto: "Producto de debug desde endpoint",
                cantidad_descargada: 1,
                usuario_id: "999",
                usuario_nombre: "Usuario Sistema",
                sucursal_id: "1",
                sucursal_nombre: "Sucursal 2",
                transportador_id: "0",
                transportador_nombre: "Debug Test",
                numero_despacho: "",
                observaciones: "Prueba de debug - " + new Date().toLocaleString()
            };
            
            $("#results").append("<p>📤 Enviando datos: " + JSON.stringify(datosRegistro) + "</p>");
            
            $.ajax({
                url: "debug-endpoint-ajax.php",
                method: "POST",
                data: datosRegistro,
                dataType: "json",
                success: function(respuesta) {
                    $("#results").append("<p>✅ Respuesta recibida: " + JSON.stringify(respuesta) + "</p>");
                    
                    Swal.fire({
                        type: "success",
                        title: "¡Éxito!",
                        text: "Datos capturados correctamente",
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
        
        // Probar controlador directo
        $("#btnTestControlador").click(function() {
            $("#results").append("<p>🔍 Probando controlador directo...</p>");
            
            $.ajax({
                url: "ajax/registro-descargas-simple.ajax.php",
                method: "POST",
                data: {
                    accion: "registrar_descarga",
                    codigo_producto: "CTRL" + Date.now(),
                    descripcion_producto: "Producto desde controlador directo",
                    cantidad_descargada: 1,
                    usuario_id: "999",
                    usuario_nombre: "Usuario Sistema",
                    sucursal_id: "1",
                    sucursal_nombre: "Sucursal 2",
                    transportador_id: "0",
                    transportador_nombre: "Debug Test",
                    numero_despacho: "",
                    observaciones: "Prueba controlador directo - " + new Date().toLocaleString()
                },
                dataType: "json",
                success: function(respuesta) {
                    $("#results").append("<p>✅ Controlador directo: " + JSON.stringify(respuesta) + "</p>");
                    
                    if(respuesta.success) {
                        Swal.fire({
                            type: "success",
                            title: "¡Éxito!",
                            text: "Controlador funcionando",
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
                    $("#results").append("<p>❌ Error controlador: " + error + "</p>");
                    
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
