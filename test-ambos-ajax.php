<?php
// Test que verifica ambos archivos AJAX de despachos

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test Ambos AJAX de Despachos\n";
echo "==============================\n\n";

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

// Test 1: datatable-despachos.ajax.php
echo "🔍 Test 1: datatable-despachos.ajax.php\n";
echo "--------------------------------------\n";

try {
    ob_start();
    include "ajax/datatable-despachos.ajax.php";
    $output1 = ob_get_clean();
    
    echo "✅ Archivo ejecutado exitosamente\n";
    echo "📤 Output: " . substr($output1, 0, 200) . "...\n";
    
    $json1 = json_decode($output1, true);
    if($json1 !== null) {
        echo "✅ JSON válido\n";
    } else {
        echo "❌ JSON inválido\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: despachos.ajax.php
echo "🔍 Test 2: despachos.ajax.php\n";
echo "-----------------------------\n";

$_POST = ["idDespacho" => "20"];

try {
    ob_start();
    include "ajax/despachos.ajax.php";
    $output2 = ob_get_clean();
    
    echo "✅ Archivo ejecutado exitosamente\n";
    echo "📤 Output: " . substr($output2, 0, 200) . "...\n";
    
    $json2 = json_decode($output2, true);
    if($json2 !== null) {
        echo "✅ JSON válido\n";
    } else {
        echo "❌ JSON inválido\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🏁 Test completado\n";
?>
