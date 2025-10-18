<?php
// Test que simula exactamente la petición AJAX desde el navegador

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test AJAX Navegador - Simulación Real\n";
echo "========================================\n\n";

// Simular que estamos haciendo una petición AJAX real
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_SERVER['HTTP_ACCEPT'] = 'application/json, text/javascript, */*; q=0.01';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

// Simular datos POST como los envía el navegador
$_POST = ["idDespacho" => "20"];

echo "📝 Petición simulada:\n";
echo "   - Método: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "   - AJAX: " . $_SERVER['HTTP_X_REQUESTED_WITH'] . "\n";
echo "   - POST: " . print_r($_POST, true) . "\n\n";

// Simular sesión como si estuviéramos logueados
session_start();
$_SESSION['iniciarSesion'] = 'ok';
$_SESSION['perfil'] = 'Administrador';
$_SESSION['id'] = 1;
$_SESSION['nombre'] = 'Administrador';

echo "📋 Sesión simulada:\n";
echo "   - iniciarSesion: " . $_SESSION['iniciarSesion'] . "\n";
echo "   - perfil: " . $_SESSION['perfil'] . "\n";
echo "   - id: " . $_SESSION['id'] . "\n";
echo "   - nombre: " . $_SESSION['nombre'] . "\n\n";

// Probar el archivo AJAX
echo "🚀 Ejecutando ajax/despachos.ajax.php...\n";

try {
    ob_start();
    include "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "✅ Respuesta del servidor:\n";
    echo $output . "\n\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "✅ JSON válido - El botón debería funcionar en el navegador\n";
        echo "📋 Datos del despacho:\n";
        echo "   - ID: " . ($json['id'] ?? 'N/A') . "\n";
        echo "   - Número: " . ($json['numero_despacho'] ?? 'N/A') . "\n";
        echo "   - Estado: " . ($json['estado'] ?? 'N/A') . "\n";
    } else {
        echo "❌ JSON inválido - El botón no funcionará\n";
        echo "Error JSON: " . json_last_error_msg() . "\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
echo "\n💡 Si este test funciona, el problema está en la página web.\n";
echo "   Si no funciona, el problema está en el archivo AJAX.\n";
?>
