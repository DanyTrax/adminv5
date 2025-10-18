<?php
// Test para verificar configuración del servidor

echo "🔍 Test Servidor Config Final - Verificar configuración\n";
echo "=====================================================\n\n";

echo "📋 Configuración PHP:\n";
echo "   - Versión PHP: " . phpversion() . "\n";
echo "   - Error reporting: " . error_reporting() . "\n";
echo "   - Display errors: " . ini_get('display_errors') . "\n";
echo "   - Log errors: " . ini_get('log_errors') . "\n";
echo "   - Output buffering: " . ini_get('output_buffering') . "\n";
echo "   - Headers sent: " . (headers_sent() ? 'Sí' : 'No') . "\n\n";

echo "📋 Headers enviados:\n";
$headers = headers_list();
if(empty($headers)) {
    echo "   - Ningún header enviado aún\n";
} else {
    foreach($headers as $header) {
        echo "   - " . $header . "\n";
    }
}
echo "\n";

echo "📋 Variables de servidor:\n";
echo "   - REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'No definido') . "\n";
echo "   - HTTP_X_REQUESTED_WITH: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'No definido') . "\n";
echo "   - HTTP_ACCEPT: " . ($_SERVER['HTTP_ACCEPT'] ?? 'No definido') . "\n";
echo "   - HTTP_REFERER: " . ($_SERVER['HTTP_REFERER'] ?? 'No definido') . "\n";
echo "   - HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'No definido') . "\n";
echo "   - REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'No definido') . "\n";
echo "   - SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'No definido') . "\n";
echo "   - QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'No definido') . "\n\n";

echo "📋 Sesión:\n";
if(session_status() === PHP_SESSION_ACTIVE) {
    echo "   - Sesión activa: Sí\n";
    echo "   - ID de sesión: " . session_id() . "\n";
    echo "   - Datos de sesión: " . print_r($_SESSION, true) . "\n";
} else {
    echo "   - Sesión activa: No\n";
}
echo "\n";

echo "📋 Output buffer:\n";
echo "   - Nivel de buffer: " . ob_get_level() . "\n";
echo "   - Contenido del buffer: " . ob_get_contents() . "\n\n";

echo "🏁 Test completado\n";
?>
