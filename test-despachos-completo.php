<?php
// Test completo de todas las acciones de despachos

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";

echo "🔍 Test Completo de Despachos\n";
echo "============================\n\n";

// Test 1: Ver Despacho
echo "📝 Test 1: Ver Despacho\n";
echo "----------------------\n";

$_POST = ["idDespacho" => 1];
ob_start();
include "ajax/despachos.ajax.php";
$output1 = ob_get_clean();

echo "Respuesta: " . $output1 . "\n";
echo "¿Es JSON válido? " . (json_decode($output1) ? "✅ Sí" : "❌ No") . "\n\n";

// Test 2: Aceptar Despacho
echo "📝 Test 2: Aceptar Despacho\n";
echo "---------------------------\n";

$_POST = ["aceptarDespacho" => 1];
ob_start();
include "ajax/despachos.ajax.php";
$output2 = ob_get_clean();

echo "Respuesta: " . $output2 . "\n";
echo "¿Es JSON válido? " . (json_decode($output2) ? "✅ Sí" : "❌ No") . "\n\n";

// Test 3: Cancelar Despacho
echo "📝 Test 3: Cancelar Despacho\n";
echo "----------------------------\n";

$_POST = ["cancelarDespacho" => 1, "motivoCancelacion" => "Test de cancelación"];
ob_start();
include "ajax/despachos.ajax.php";
$output3 = ob_get_clean();

echo "Respuesta: " . $output3 . "\n";
echo "¿Es JSON válido? " . (json_decode($output3) ? "✅ Sí" : "❌ No") . "\n\n";

// Test 4: Eliminar Despacho
echo "📝 Test 4: Eliminar Despacho\n";
echo "----------------------------\n";

$_POST = ["eliminarDespacho" => 1];
ob_start();
include "ajax/despachos.ajax.php";
$output4 = ob_get_clean();

echo "Respuesta: " . $output4 . "\n";
echo "¿Es JSON válido? " . (json_decode($output4) ? "✅ Sí" : "❌ No") . "\n\n";

echo "🏁 Test completado\n";
?>
