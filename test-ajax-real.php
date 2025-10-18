<?php
// Test que simula exactamente la petición AJAX real

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test AJAX Real - Simulando petición del navegador\n";
echo "===================================================\n\n";

// Simular exactamente lo que envía el JavaScript
$_POST = ["idDespacho" => "20"]; // Nota: como string, no int

echo "📝 POST data: " . print_r($_POST, true) . "\n";
echo "📝 Tipo de idDespacho: " . gettype($_POST["idDespacho"]) . "\n\n";

// Verificar que el archivo existe y es accesible
if(!file_exists("ajax/despachos.ajax.php")) {
    echo "❌ ERROR: El archivo ajax/despachos.ajax.php no existe\n";
    exit;
}

echo "✅ Archivo ajax/despachos.ajax.php existe\n";

// Verificar permisos de lectura
if(!is_readable("ajax/despachos.ajax.php")) {
    echo "❌ ERROR: No se puede leer el archivo ajax/despachos.ajax.php\n";
    exit;
}

echo "✅ Archivo ajax/despachos.ajax.php es legible\n";

// Probar incluir el archivo
echo "🚀 Ejecutando ajax/despachos.ajax.php...\n";

try {
    // Capturar cualquier output
    ob_start();
    
    // Incluir el archivo
    include "ajax/despachos.ajax.php";
    
    // Obtener el output
    $output = ob_get_clean();
    
    echo "✅ Archivo ejecutado exitosamente\n";
    echo "📤 Output recibido:\n";
    echo $output . "\n\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "✅ JSON válido\n";
        echo "📋 Datos principales:\n";
        echo "   - ID: " . ($json['id'] ?? 'N/A') . "\n";
        echo "   - Número: " . ($json['numero_despacho'] ?? 'N/A') . "\n";
        echo "   - Estado: " . ($json['estado'] ?? 'N/A') . "\n";
    } else {
        echo "❌ JSON inválido\n";
        echo "Error JSON: " . json_last_error_msg() . "\n";
    }
    
} catch(ParseError $e) {
    echo "❌ Error de sintaxis PHP: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
} catch(Error $e) {
    echo "❌ Error fatal PHP: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
} catch(Exception $e) {
    echo "❌ Excepción: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
?>
