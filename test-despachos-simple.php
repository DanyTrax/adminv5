<?php
// Test simple para diagnosticar error 500

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";

echo "🔍 Test Simple de Despachos\n";
echo "==========================\n\n";

// Test 1: Ver Despacho
echo "📝 Test 1: Ver Despacho ID 20\n";
echo "-----------------------------\n";

$_POST = ["idDespacho" => 20];

try {
    ob_start();
    include "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "✅ Respuesta: " . $output . "\n";
    echo "¿Es JSON válido? " . (json_decode($output) ? "✅ Sí" : "❌ No") . "\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
?>
