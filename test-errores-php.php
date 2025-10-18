<?php
// Test para capturar todos los errores PHP

echo "🔍 Test Errores PHP - Capturar todos los errores\n";
echo "==============================================\n\n";

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Verificar configuración de errores
echo "📋 Configuración de errores:\n";
echo "   - error_reporting: " . error_reporting() . "\n";
echo "   - display_errors: " . ini_get('display_errors') . "\n";
echo "   - log_errors: " . ini_get('log_errors') . "\n";
echo "   - error_log: " . ini_get('error_log') . "\n\n";

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
        echo "📋 Datos encontrados:\n";
        echo "   - Total registros: " . count($json['data']) . "\n";
        if(count($json['data']) > 0) {
            echo "   - Primer registro: " . $json['data'][0][1] . " (" . $json['data'][0][2] . ")\n";
        }
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
