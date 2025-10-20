<?php
// Script para probar el endpoint AJAX de solicitudes
echo "<h2>🧪 Prueba del Endpoint AJAX de Solicitudes</h2>";

// Simular POST request
$_POST['buscarSolicitudes'] = true;
$_POST['termino'] = 'SOL';

echo "<h3>📥 Datos enviados:</h3>";
echo "buscarSolicitudes: " . ($_POST['buscarSolicitudes'] ? 'true' : 'false') . "<br>";
echo "termino: " . $_POST['termino'] . "<br><br>";

echo "<h3>📤 Respuesta del endpoint:</h3>";
echo "<pre>";

// Capturar la salida del endpoint
ob_start();
include 'ajax/productos-despacho.ajax.php';
$output = ob_get_clean();

echo $output;
echo "</pre>";

// Intentar decodificar JSON
$json = json_decode($output, true);
if ($json) {
    echo "<h3>📋 JSON decodificado:</h3>";
    echo "<pre>";
    print_r($json);
    echo "</pre>";
} else {
    echo "<h3>❌ Error: No se pudo decodificar JSON</h3>";
    echo "JSON Error: " . json_last_error_msg() . "<br>";
}
?>
