<?php
// Test directo del archivo datatable-despachos.ajax.php

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test DataTable Directo\n";
echo "========================\n\n";

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

// Probar el archivo datatable directamente
echo "🚀 Ejecutando ajax/datatable-despachos.ajax.php...\n";

try {
    ob_start();
    include "ajax/datatable-despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "✅ Archivo ejecutado exitosamente\n";
    echo "📤 Output completo:\n";
    echo $output . "\n\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "✅ JSON válido - DataTables debería funcionar\n";
        echo "📋 Datos encontrados:\n";
        echo "   - Total registros: " . count($json['data']) . "\n";
        if(count($json['data']) > 0) {
            echo "   - Primer registro: " . $json['data'][0][1] . " (" . $json['data'][0][2] . ")\n";
        }
    } else {
        echo "❌ JSON inválido - DataTables no funcionará\n";
        echo "Error JSON: " . json_last_error_msg() . "\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
?>
