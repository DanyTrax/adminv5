<?php
/*=============================================
CAPTURAR ERROR DE REGISTRO DESDE MÓDULO
=============================================*/

// Habilitar mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Crear archivo de log para capturar datos
$logFile = "debug-error-registro.log";

// Función para escribir en el log
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Capturar datos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    writeLog("=== ERROR DE REGISTRO DESDE MÓDULO ===");
    writeLog("POST data: " . print_r($_POST, true));
    writeLog("Raw input: " . file_get_contents('php://input'));
    writeLog("Headers: " . print_r(getallheaders(), true));
    writeLog("=== FIN ERROR DE REGISTRO ===");
    
    // Responder con JSON para JavaScript
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Error capturado correctamente',
        'timestamp' => date('Y-m-d H:i:s'),
        'log_file' => $logFile
    ]);
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Capturar Error de Registro</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>🔍 Capturar Error de Registro</h1>
    
    <div id="debug-info">
        <h2>📋 Información de Debug:</h2>
        <p><strong>Archivo de log:</strong> <?php echo $logFile; ?></p>
        <p><strong>Timestamp:</strong> <span id="timestamp"></span></p>
        <p><strong>URL actual:</strong> <span id="url-actual"></span></p>
    </div>
    
    <div id="test-buttons">
        <h2>🧪 Botones de Prueba:</h2>
        
        <button id="btnTestError">Probar Error</button>
        <button id="btnVerLog">Ver Log</button>
        
        <div id="results"></div>
    </div>
    
    <script>
    $(document).ready(function() {
        // Mostrar información
        $("#timestamp").text(new Date().toLocaleString());
        $("#url-actual").text(window.location.href);
        
        // Probar error
        $("#btnTestError").click(function() {
            $("#results").append("<p>🔍 Probando error...</p>");
            
            var datosRegistro = {
                accion: "registrar_descarga",
                codigo_producto: "ERROR" + Date.now(),
                descripcion_producto: "Producto de error",
                cantidad_descargada: 1,
                usuario_id: "999",
                usuario_nombre: "Usuario Sistema",
                sucursal_id: "1",
                sucursal_nombre: "Sucursal 2",
                transportador_id: "0",
                transportador_nombre: "Debug Test",
                numero_despacho: "",
                observaciones: "Prueba de error - " + new Date().toLocaleString()
            };
            
            $("#results").append("<p>📤 Enviando datos: " + JSON.stringify(datosRegistro) + "</p>");
            
            $.ajax({
                url: "debug-error-registro.php",
                method: "POST",
                data: datosRegistro,
                dataType: "json",
                success: function(respuesta) {
                    $("#results").append("<p>✅ Respuesta recibida: " + JSON.stringify(respuesta) + "</p>");
                    
                    Swal.fire({
                        type: "success",
                        title: "¡Éxito!",
                        text: "Error capturado",
                        showConfirmButton: false,
                        timer: 2000
                    });
                },
                error: function(xhr, status, error) {
                    $("#results").append("<p>❌ Error: " + error + "</p>");
                    
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
