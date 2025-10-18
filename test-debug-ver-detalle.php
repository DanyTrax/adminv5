<?php
// Test de debug para ver exactamente qué está pasando

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Debug Ver Detalle - Simulando petición real\n";
echo "==============================================\n\n";

// Simular exactamente lo que hace la página web
$_POST = ["idDespacho" => 20];

echo "📝 POST data: " . print_r($_POST, true) . "\n\n";

// Verificar si el archivo existe
if(file_exists("ajax/despachos.ajax.php")) {
    echo "✅ Archivo ajax/despachos.ajax.php existe\n";
} else {
    echo "❌ Archivo ajax/despachos.ajax.php NO existe\n";
    exit;
}

// Verificar permisos
echo "📁 Permisos del archivo: " . substr(sprintf('%o', fileperms("ajax/despachos.ajax.php")), -4) . "\n";

// Verificar contenido del archivo
$contenido = file_get_contents("ajax/despachos.ajax.php");
echo "📏 Tamaño del archivo: " . strlen($contenido) . " bytes\n";

// Verificar si hay errores de sintaxis
$syntax_check = shell_exec("php -l ajax/despachos.ajax.php 2>&1");
echo "🔍 Verificación de sintaxis:\n" . $syntax_check . "\n";

// Probar incluir el archivo
echo "🚀 Intentando incluir el archivo...\n";
try {
    ob_start();
    include "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "✅ Archivo incluido exitosamente\n";
    echo "📤 Output: " . $output . "\n";
    
} catch(Exception $e) {
    echo "❌ Error al incluir archivo: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
} catch(Error $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Debug completado\n";
?>
