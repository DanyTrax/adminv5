<?php
// Test que simula exactamente la petición AJAX de DataTables

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test DataTable AJAX - Simulación DataTables\n";
echo "==============================================\n\n";

// Simular headers de DataTables
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_SERVER['HTTP_ACCEPT'] = 'application/json, text/javascript, */*; q=0.01';

// Simular parámetros de DataTables
$_GET['_'] = time() * 1000; // Timestamp para evitar caché

echo "📝 Petición simulada:\n";
echo "   - Método: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "   - AJAX: " . $_SERVER['HTTP_X_REQUESTED_WITH'] . "\n";
echo "   - Timestamp: " . $_GET['_'] . "\n\n";

// Simular sesión
session_start();
$_SESSION['iniciarSesion'] = 'ok';
$_SESSION['perfil'] = 'Administrador';
$_SESSION['id'] = 1;
$_SESSION['nombre'] = 'Administrador';

echo "📋 Sesión simulada:\n";
echo "   - perfil: " . $_SESSION['perfil'] . "\n";
echo "   - id: " . $_SESSION['id'] . "\n";
echo "   - nombre: " . $_SESSION['nombre'] . "\n\n";

// Probar el archivo datatable
echo "🚀 Ejecutando ajax/datatable-despachos.ajax.php...\n";

try {
    ob_start();
    include "ajax/datatable-despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "✅ Archivo ejecutado exitosamente\n";
    echo "📤 Output completo:\n";
    echo $output . "\n\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "✅ JSON válido - DataTables debería funcionar\n";
        echo "📋 Estructura de datos:\n";
        echo "   - Total registros: " . count($json['data']) . "\n";
        if(count($json['data']) > 0) {
            echo "   - Primer registro: " . $json['data'][0][1] . " (" . $json['data'][0][2] . ")\n";
            echo "   - Estado: " . $json['data'][0][4] . "\n";
        }
    } else {
        echo "❌ JSON inválido - DataTables no funcionará\n";
        echo "Error JSON: " . json_last_error_msg() . "\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
?>
