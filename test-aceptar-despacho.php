<?php
// Test específico para el botón "Aceptar Despacho"

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular sesión
session_start();
$_SESSION["perfil"] = "Transportador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Transportador Test";

echo "🔍 Test Botón 'Aceptar Despacho'\n";
echo "=================================\n\n";

// Simular la petición AJAX del botón "Aceptar Despacho"
$_POST = ["aceptarDespacho" => 20];

echo "📝 Simulando: Aceptar Despacho ID 20\n";
echo "------------------------------------\n";

try {
    ob_start();
    include "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "✅ Respuesta recibida:\n";
    echo $output . "\n\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json) {
        echo "✅ JSON válido - Estructura correcta\n";
        echo "📋 Resultado:\n";
        echo "   - Success: " . ($json['success'] ? 'true' : 'false') . "\n";
        echo "   - Message: " . ($json['message'] ?? 'N/A') . "\n";
        echo "   - Error: " . ($json['error'] ?? 'N/A') . "\n";
    } else {
        echo "❌ JSON inválido\n";
        echo "Error JSON: " . json_last_error_msg() . "\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
?>
