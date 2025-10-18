<?php
// Test para diagnosticar las acciones de despachos

echo "🔍 Test Acciones Despachos Final - Diagnosticar botones\n";
echo "=====================================================\n\n";

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

// Test 1: Ver Despacho
echo "🔍 Test 1: Ver Despacho\n";
echo "----------------------\n";

$_POST = array();
$_POST['idDespacho'] = '21';

try {
    ob_start();
    include 'ajax/despachos.ajax.php';
    $output = ob_get_clean();
    
    echo "✅ Ver Despacho - Respuesta:\n";
    echo $output . "\n\n";
    
} catch(Exception $e) {
    echo "❌ Error Ver Despacho: " . $e->getMessage() . "\n\n";
}

// Test 2: Aceptar Despacho
echo "🔍 Test 2: Aceptar Despacho\n";
echo "--------------------------\n";

$_POST = array();
$_POST['aceptarDespacho'] = '21';

try {
    ob_start();
    include 'ajax/despachos.ajax.php';
    $output = ob_get_clean();
    
    echo "✅ Aceptar Despacho - Respuesta:\n";
    echo $output . "\n\n";
    
} catch(Exception $e) {
    echo "❌ Error Aceptar Despacho: " . $e->getMessage() . "\n\n";
}

// Test 3: Cancelar Despacho
echo "🔍 Test 3: Cancelar Despacho\n";
echo "----------------------------\n";

$_POST = array();
$_POST['cancelarDespacho'] = '21';
$_POST['motivoCancelacion'] = 'Prueba de cancelación';

try {
    ob_start();
    include 'ajax/despachos.ajax.php';
    $output = ob_get_clean();
    
    echo "✅ Cancelar Despacho - Respuesta:\n";
    echo $output . "\n\n";
    
} catch(Exception $e) {
    echo "❌ Error Cancelar Despacho: " . $e->getMessage() . "\n\n";
}

// Test 4: Eliminar Despacho
echo "🔍 Test 4: Eliminar Despacho\n";
echo "----------------------------\n";

$_POST = array();
$_POST['eliminarDespacho'] = '21';

try {
    ob_start();
    include 'ajax/despachos.ajax.php';
    $output = ob_get_clean();
    
    echo "✅ Eliminar Despacho - Respuesta:\n";
    echo $output . "\n\n";
    
} catch(Exception $e) {
    echo "❌ Error Eliminar Despacho: " . $e->getMessage() . "\n\n";
}

echo "🏁 Test completado\n";
?>
