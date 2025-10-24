<?php
/*=============================================
PROBAR ENDPOINTS REGISTRO DE DESCARGAS
=============================================*/

echo "🧪 Probando endpoints del módulo registro-descargas-simple...\n\n";

// Simular POST data
$_POST['accion'] = 'obtener_estadisticas';

echo "📡 Probando endpoint: ajax/registro-descargas-simple.ajax.php\n";
echo "📋 Acción: obtener_estadisticas\n\n";

// Capturar output
ob_start();

try {
    // Incluir el archivo AJAX
    include 'ajax/registro-descargas-simple.ajax.php';
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "✅ Endpoint ejecutado correctamente\n";
    echo "📤 Respuesta:\n";
    echo $output . "\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "✅ Respuesta JSON válida\n";
        if(isset($json['success'])) {
            echo "✅ Campo 'success' presente: " . ($json['success'] ? 'true' : 'false') . "\n";
        }
        if(isset($json['data'])) {
            echo "✅ Campo 'data' presente\n";
        }
    } else {
        echo "❌ Respuesta no es JSON válido\n";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Error al ejecutar endpoint: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Probar endpoint DataTable
$_POST['accion'] = '';
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;
$_POST['search'] = ['value' => ''];

echo "📡 Probando endpoint: ajax/datatable-registro-descargas-simple.ajax.php\n";
echo "📋 Parámetros DataTable\n\n";

ob_start();

try {
    include 'ajax/datatable-registro-descargas-simple.ajax.php';
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "✅ Endpoint DataTable ejecutado correctamente\n";
    echo "📤 Respuesta:\n";
    echo $output . "\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "✅ Respuesta JSON válida\n";
        if(isset($json['draw'])) {
            echo "✅ Campo 'draw' presente: " . $json['draw'] . "\n";
        }
        if(isset($json['recordsTotal'])) {
            echo "✅ Campo 'recordsTotal' presente: " . $json['recordsTotal'] . "\n";
        }
        if(isset($json['data'])) {
            echo "✅ Campo 'data' presente\n";
        }
    } else {
        echo "❌ Respuesta no es JSON válido\n";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Error al ejecutar endpoint DataTable: " . $e->getMessage() . "\n";
}

echo "\n🎯 Pruebas completadas\n";
echo "🔧 Si los endpoints funcionan aquí pero no en el navegador, el problema es de cache del servidor\n";
?>
