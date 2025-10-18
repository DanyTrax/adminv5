<?php
// Test que simula exactamente la petición HTTP del navegador

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test HTTP Real - Simulando petición del navegador\n";
echo "==================================================\n\n";

// Simular headers HTTP del navegador
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Test Browser)';
$_SERVER['HTTP_ACCEPT'] = 'application/json, text/javascript, */*; q=0.01';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

// Simular datos POST exactamente como los envía el navegador
$_POST = ["idDespacho" => "20"];

echo "📝 Headers simulados:\n";
echo "   - REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "   - CONTENT_TYPE: " . $_SERVER['CONTENT_TYPE'] . "\n";
echo "   - X_REQUESTED_WITH: " . $_SERVER['HTTP_X_REQUESTED_WITH'] . "\n";
echo "   - POST data: " . print_r($_POST, true) . "\n\n";

// Verificar si hay sesión activa
if(session_status() === PHP_SESSION_NONE) {
    echo "⚠️ No hay sesión activa, iniciando...\n";
    session_start();
} else {
    echo "✅ Sesión ya está activa\n";
}

echo "📋 Datos de sesión:\n";
echo "   - perfil: " . ($_SESSION['perfil'] ?? 'No definido') . "\n";
echo "   - id: " . ($_SESSION['id'] ?? 'No definido') . "\n";
echo "   - nombre: " . ($_SESSION['nombre'] ?? 'No definido') . "\n\n";

// Probar incluir el archivo
echo "🚀 Ejecutando ajax/despachos.ajax.php...\n";

try {
    ob_start();
    include "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "✅ Archivo ejecutado exitosamente\n";
    echo "📤 Output recibido:\n";
    echo $output . "\n\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "✅ JSON válido\n";
        echo "📋 Datos principales:\n";
        echo "   - ID: " . ($json['id'] ?? 'N/A') . "\n";
        echo "   - Número: " . ($json['numero_despacho'] ?? 'N/A') . "\n";
        echo "   - Estado: " . ($json['estado'] ?? 'N/A') . "\n";
    } else {
        echo "❌ JSON inválido\n";
        echo "Error JSON: " . json_last_error_msg() . "\n";
    }
    
} catch(ParseError $e) {
    echo "❌ Error de sintaxis PHP: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
} catch(Error $e) {
    echo "❌ Error fatal PHP: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
} catch(Exception $e) {
    echo "❌ Excepción: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
?>
