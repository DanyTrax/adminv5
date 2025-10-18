<?php
// Test para detectar output temprano

echo "🔍 Test Output Temprano - Detectar qué está enviando output antes\n";
echo "==============================================================\n\n";

// Verificar si hay output antes de incluir el archivo
echo "📋 Verificando output antes de incluir datatable-despachos.ajax.php:\n";
echo "   - headers_sent(): " . (headers_sent() ? "Sí" : "No") . "\n";
if(headers_sent($file, $line)) {
    echo "   - Headers enviados desde: $file línea $line\n";
}
echo "   - ob_get_level(): " . ob_get_level() . "\n";
echo "   - ob_get_length(): " . ob_get_length() . "\n\n";

// Simular sesión
session_start();
$_SESSION['iniciarSesion'] = 'ok';
$_SESSION['perfil'] = 'Administrador';
$_SESSION['id'] = 1;
$_SESSION['nombre'] = 'Administrador';

echo "📋 Después de session_start():\n";
echo "   - headers_sent(): " . (headers_sent() ? "Sí" : "No") . "\n";
if(headers_sent($file, $line)) {
    echo "   - Headers enviados desde: $file línea $line\n";
}
echo "   - ob_get_level(): " . ob_get_level() . "\n";
echo "   - ob_get_length(): " . ob_get_length() . "\n\n";

// Probar el archivo datatable
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
    } else {
        echo "❌ JSON inválido - Error: " . json_last_error_msg() . "\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
} catch(Error $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
?>
