<?php
// Test para verificar que los archivos están actualizados

echo "🔍 Test Cache Buster - Verificar archivos actualizados\n";
echo "====================================================\n\n";

// Verificar timestamp de los archivos
$files = [
    'ajax/datatable-despachos.ajax.php',
    'ajax/despachos.ajax.php'
];

foreach($files as $file) {
    if(file_exists($file)) {
        $timestamp = filemtime($file);
        $date = date('Y-m-d H:i:s', $timestamp);
        echo "✅ $file - Última modificación: $date\n";
    } else {
        echo "❌ $file - No encontrado\n";
    }
}

echo "\n📋 Contenido de datatable-despachos.ajax.php (primeras 5 líneas):\n";
if(file_exists('ajax/datatable-despachos.ajax.php')) {
    $lines = file('ajax/datatable-despachos.ajax.php');
    for($i = 0; $i < 5; $i++) {
        echo "   " . ($i+1) . ": " . trim($lines[$i]) . "\n";
    }
}

echo "\n🏁 Test completado\n";
?>
