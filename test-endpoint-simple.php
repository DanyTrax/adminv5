<?php
// Script simple para probar el endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Test Simple del Endpoint</h2>";

// Simular POST
$_POST['buscarSolicitudes'] = true;
$_POST['termino'] = 'SOL';

echo "<h3>📥 Datos enviados:</h3>";
echo "buscarSolicitudes: " . ($_POST['buscarSolicitudes'] ? 'true' : 'false') . "<br>";
echo "termino: " . $_POST['termino'] . "<br><br>";

echo "<h3>📤 Respuesta:</h3>";
echo "<pre>";

// Capturar salida
ob_start();
include 'ajax/productos-despacho.ajax.php';
$output = ob_get_clean();

echo htmlspecialchars($output);
echo "</pre>";

// Verificar si es JSON válido
$json = json_decode($output, true);
if ($json) {
    echo "<h3>✅ JSON válido:</h3>";
    echo "<pre>";
    print_r($json);
    echo "</pre>";
} else {
    echo "<h3>❌ JSON inválido:</h3>";
    echo "Error: " . json_last_error_msg() . "<br>";
}
?>
