<?php
// Test para capturar exactamente qué causa el error 500

echo "🔍 Test Capturar Error 500 - Debuggear error específico\n";
echo "====================================================\n\n";

// Simular exactamente la petición del navegador
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_SERVER['HTTP_ACCEPT'] = 'application/json, text/javascript, */*; q=0.01';
$_SERVER['HTTP_REFERER'] = 'https://pruebas.acrilicosinfinito.com/despachos';
$_SERVER['HTTP_HOST'] = 'pruebas.acrilicosinfinito.com';
$_SERVER['REQUEST_URI'] = '/ajax/datatable-despachos.ajax.php?_=' . (time() * 1000);
$_GET['_'] = time() * 1000;

// Simular sesión del navegador
session_start();
$_SESSION['iniciarSesion'] = 'ok';
$_SESSION['perfil'] = 'Administrador';
$_SESSION['id'] = 1;
$_SESSION['nombre'] = 'Administrador';

echo "📝 Petición simulada:\n";
echo "   - Método: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "   - AJAX: " . $_SERVER['HTTP_X_REQUESTED_WITH'] . "\n";
echo "   - Referer: " . $_SERVER['HTTP_REFERER'] . "\n";
echo "   - Timestamp: " . $_GET['_'] . "\n\n";

echo "📋 Sesión simulada:\n";
echo "   - perfil: " . $_SESSION['perfil'] . "\n";
echo "   - id: " . $_SESSION['id'] . "\n";
echo "   - nombre: " . $_SESSION['nombre'] . "\n\n";

// Habilitar reporte de errores detallado
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "🚀 Ejecutando ajax/datatable-despachos.ajax.php...\n";

try {
    // Capturar output y errores
    ob_start();
    
    // Incluir el archivo
    include 'ajax/datatable-despachos.ajax.php';
    
    $output = ob_get_clean();
    
    echo "✅ Archivo ejecutado exitosamente\n";
    echo "📤 Output completo:\n";
    echo $output . "\n\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "✅ JSON válido\n";
        echo "📋 Total registros: " . count($json['data']) . "\n";
    } else {
        echo "❌ JSON inválido - Error: " . json_last_error_msg() . "\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch(Error $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Test completado\n";
?>
