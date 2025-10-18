<?php
// Test específico para el botón "Ver Detalle"

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";

echo "🔍 Test Botón 'Ver Detalle'\n";
echo "============================\n\n";

// Simular la petición AJAX del botón "Ver Detalle"
$_POST = ["idDespacho" => 20];

echo "📝 Simulando: Ver Detalle del Despacho ID 20\n";
echo "--------------------------------------------\n";

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
        echo "📋 Campos principales:\n";
        echo "   - ID: " . ($json['id'] ?? 'N/A') . "\n";
        echo "   - Número: " . ($json['numero_despacho'] ?? 'N/A') . "\n";
        echo "   - Estado: " . ($json['estado'] ?? 'N/A') . "\n";
        echo "   - Productos: " . ($json['total_productos'] ?? 'N/A') . "\n";
        echo "   - Cantidad total: " . ($json['total_cantidad'] ?? 'N/A') . "\n";
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
