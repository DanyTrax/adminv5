<?php
/*=============================================
PROBAR AJAX DATATABLE
=============================================*/

echo "<h2>🔍 Probar AJAX DataTable</h2>";

// Simular parámetros POST de DataTable
$_POST = [
    'draw' => 1,
    'start' => 0,
    'length' => 10,
    'search' => ['value' => ''],
    'producto' => '',
    'usuario' => '',
    'fecha_desde' => '',
    'fecha_hasta' => ''
];

echo "<h3>1. Parámetros POST simulados:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>2. Ejecutando AJAX DataTable...</h3>";

// Capturar la salida del AJAX
ob_start();
include "ajax/datatable-registro-descargas-simple.ajax.php";
$output = ob_get_clean();

echo "<h3>3. Respuesta del AJAX:</h3>";
echo "<pre>";
echo htmlspecialchars($output);
echo "</pre>";

echo "<h3>4. Respuesta decodificada:</h3>";
$json = json_decode($output, true);
if($json) {
    echo "<pre>";
    print_r($json);
    echo "</pre>";
} else {
    echo "❌ Error al decodificar JSON<br>";
    echo "Error JSON: " . json_last_error_msg() . "<br>";
}
?>
