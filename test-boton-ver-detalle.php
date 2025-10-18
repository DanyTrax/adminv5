<?php
// Test que simula exactamente el clic del botón "Ver Detalle" desde la página web

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test Botón 'Ver Detalle' - Simulación Completa\n";
echo "================================================\n\n";

// Simular que estamos en la página de despachos
// Primero, simular la sesión como si estuviéramos logueados
session_start();
$_SESSION['perfil'] = 'Administrador';
$_SESSION['id'] = 1;
$_SESSION['nombre'] = 'Administrador';

echo "📋 Sesión simulada:\n";
echo "   - perfil: " . $_SESSION['perfil'] . "\n";
echo "   - id: " . $_SESSION['id'] . "\n";
echo "   - nombre: " . $_SESSION['nombre'] . "\n\n";

// Simular la petición AJAX exactamente como la hace el JavaScript
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_POST = ["idDespacho" => "20"];

echo "📝 Petición AJAX simulada:\n";
echo "   - Método: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "   - AJAX: " . $_SERVER['HTTP_X_REQUESTED_WITH'] . "\n";
echo "   - POST: " . print_r($_POST, true) . "\n\n";

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
        echo "✅ JSON válido - El botón debería funcionar\n";
        echo "📋 Datos del despacho:\n";
        echo "   - ID: " . ($json['id'] ?? 'N/A') . "\n";
        echo "   - Número: " . ($json['numero_despacho'] ?? 'N/A') . "\n";
        echo "   - Estado: " . ($json['estado'] ?? 'N/A') . "\n";
        echo "   - Productos: " . ($json['total_productos'] ?? 'N/A') . "\n";
        echo "   - Cantidad: " . ($json['total_cantidad'] ?? 'N/A') . "\n";
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
echo "\n💡 Si este test funciona, el botón 'Ver Detalle' debería funcionar en la página web.\n";
echo "   Si no funciona en la página web, el problema está en la sesión de la página.\n";
?>
