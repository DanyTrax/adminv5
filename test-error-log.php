<?php
// Test para verificar logs de error

echo "🔍 Test Error Log - Verificar errores del servidor\n";
echo "===============================================\n\n";

// Verificar si hay archivo de error log
$error_log = 'error_log';
if(file_exists($error_log)) {
    echo "📁 Archivo error_log encontrado\n";
    
    // Leer las últimas 20 líneas
    $lineas = file($error_log);
    $ultimas = array_slice($lineas, -20);
    
    echo "📋 Últimas 20 líneas del error_log:\n";
    foreach($ultimas as $i => $linea) {
        echo "   " . ($i+1) . ": " . trim($linea) . "\n";
    }
} else {
    echo "❌ No se encontró archivo error_log\n";
}

// Verificar configuración de PHP
echo "\n🔍 Configuración de PHP:\n";
echo "📋 display_errors: " . ini_get('display_errors') . "\n";
echo "📋 error_reporting: " . ini_get('error_reporting') . "\n";
echo "📋 log_errors: " . ini_get('log_errors') . "\n";
echo "📋 error_log: " . ini_get('error_log') . "\n";

// Verificar si hay warnings o errores
echo "\n🔍 Probando ejecución con captura de errores:\n";
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    ob_start();
    include 'ajax/datatable-despachos.ajax.php';
    $output = ob_get_clean();
    
    echo "✅ Ejecución exitosa\n";
    echo "📤 Output: " . substr($output, 0, 100) . "...\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} catch(Error $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
?>
