<?php
echo "<h2>📋 Error Log Reciente</h2>";
echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 500px; overflow-y: auto;'>";

// Buscar diferentes ubicaciones del error_log
$paths = [
    'error_log',
    '../error_log', 
    'ajax/error_log',
    '/home/epicosie/logs/error_log',
    ini_get('error_log')
];

$found = false;
foreach($paths as $path) {
    if(file_exists($path) && is_readable($path)) {
        echo "<strong>🔍 Archivo encontrado en: $path</strong>\n";
        echo "📅 Últimas 50 líneas:\n";
        echo "=================================\n";
        
        $lines = file($path);
        $lines = array_slice($lines, -50); // Últimas 50 líneas
        echo implode('', $lines);
        $found = true;
        break;
    }
}

if(!$found) {
    echo "❌ No se encontró el archivo error_log en ninguna ubicación común.\n";
    echo "📍 Ubicaciones buscadas:\n";
    foreach($paths as $path) {
        echo "   - $path\n";
    }
    
    echo "\n🔧 Para habilitar error logging, agrega estas líneas a tu config.php:\n";
    echo "ini_set('log_errors', 1);\n";
    echo "ini_set('error_log', 'error_log');\n";
}

echo "</pre>";

echo "<h3>🔧 Información PHP</h3>";
echo "<pre>";
echo "📂 Directorio actual: " . getcwd() . "\n";
echo "📝 Error log configurado: " . ini_get('error_log') . "\n";
echo "🔍 Display errors: " . ini_get('display_errors') . "\n";
echo "📋 Log errors: " . ini_get('log_errors') . "\n";
echo "</pre>";
?>