<?php
// Test de configuración del servidor web

echo "🔍 Test Servidor Web - Verificar configuración\n";
echo "=============================================\n\n";

// Verificar información del servidor
echo "📋 Información del servidor:\n";
echo "   - PHP Version: " . phpversion() . "\n";
echo "   - Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'No disponible') . "\n";
echo "   - Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'No disponible') . "\n";
echo "   - Server Port: " . ($_SERVER['SERVER_PORT'] ?? 'No disponible') . "\n";
echo "   - Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'No disponible') . "\n";
echo "   - Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'No disponible') . "\n";
echo "   - Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'No disponible') . "\n\n";

// Verificar módulos de PHP
echo "📋 Módulos de PHP:\n";
$modulos = get_loaded_extensions();
$modulos_importantes = ['pdo', 'pdo_mysql', 'json', 'session', 'mbstring'];
foreach($modulos_importantes as $modulo) {
    echo "   - $modulo: " . (in_array($modulo, $modulos) ? "✅" : "❌") . "\n";
}
echo "\n";

// Verificar configuración de errores
echo "📋 Configuración de errores:\n";
echo "   - display_errors: " . ini_get('display_errors') . "\n";
echo "   - error_reporting: " . ini_get('error_reporting') . "\n";
echo "   - log_errors: " . ini_get('log_errors') . "\n";
echo "   - error_log: " . ini_get('error_log') . "\n";
echo "   - output_buffering: " . ini_get('output_buffering') . "\n";
echo "   - zlib.output_compression: " . ini_get('zlib.output_compression') . "\n\n";

// Verificar headers
echo "📋 Headers:\n";
echo "   - headers_sent(): " . (headers_sent() ? "Sí" : "No") . "\n";
if(headers_sent($file, $line)) {
    echo "   - Headers enviados desde: $file línea $line\n";
}
echo "\n";

// Verificar output buffering
echo "📋 Output Buffering:\n";
echo "   - ob_get_level(): " . ob_get_level() . "\n";
echo "   - ob_get_length(): " . ob_get_length() . "\n\n";

// Verificar sesión
echo "📋 Sesión:\n";
echo "   - session_status(): " . session_status() . "\n";
echo "   - session_id(): " . session_id() . "\n";
echo "   - session_name(): " . session_name() . "\n\n";

// Verificar archivos
echo "📋 Archivos:\n";
$archivos = [
    'ajax/datatable-despachos.ajax.php',
    'ajax/despachos.ajax.php',
    'modelos/conexion.php',
    'api-transferencias/conexion-central.php'
];

foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        echo "   ✅ $archivo - Existe\n";
    } else {
        echo "   ❌ $archivo - No existe\n";
    }
}

echo "\n🏁 Test completado\n";
?>
