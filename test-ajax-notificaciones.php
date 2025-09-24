<?php
session_start();

// Simular petición AJAX
$_POST['accion'] = 'obtener_pendientes';

// Capturar la salida del AJAX
ob_start();
include 'ajax/notificaciones-solicitudes.ajax.php';
$respuesta = ob_get_clean();

// Mostrar resultado
echo "<h2>🔍 PRUEBA RESPUESTA AJAX NOTIFICACIONES</h2>";
echo "<style>body{font-family:Arial;} pre{background:#f0f0f0;padding:10px;border-radius:5px;}</style>";

echo "<h3>Respuesta Raw:</h3>";
echo "<pre>" . htmlspecialchars($respuesta) . "</pre>";

echo "<h3>¿Es JSON válido?</h3>";
$json = json_decode($respuesta, true);

if($json !== null) {
    echo "<p style='color:green;'>✅ JSON válido</p>";
    echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p style='color:red;'>❌ JSON inválido - Error: " . json_last_error_msg() . "</p>";
}
?>