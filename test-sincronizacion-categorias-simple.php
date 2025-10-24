<?php
// Script simple para probar la sincronización de categorías
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Test Simple de Sincronización</h1>";

// Simular POST request
$_POST['accion'] = 'sincronizar';

echo "<h2>Ejecutando sincronización...</h2>";

try {
    // Capturar output
    ob_start();
    include __DIR__ . "/ajax/categorias-central.ajax.php";
    $output = ob_get_clean();
    
    echo "<h3>Respuesta del servidor:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Intentar decodificar JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<h3>JSON decodificado:</h3>";
        echo "<pre>";
        print_r($json);
        echo "</pre>";
    } else {
        echo "<h3>❌ No se pudo decodificar como JSON</h3>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>✅ Test completado</h2>";
?>
