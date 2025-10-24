<?php
/*=============================================
PROBAR ENDPOINT AJAX ESPECÍFICO
=============================================*/

echo "🧪 Probando endpoint AJAX específico...\n\n";

// Simular petición AJAX
$_POST['accion'] = 'obtener_estadisticas';

echo "📡 Probando: ajax/registro-descargas-simple.ajax.php\n";
echo "📋 Acción: obtener_estadisticas\n\n";

// Cambiar al directorio ajax para simular el contexto
$directorio_original = getcwd();
$directorio_ajax = $directorio_original . '/ajax';

echo "📁 Directorio original: $directorio_original\n";
echo "📁 Cambiando a: $directorio_ajax\n";

if(is_dir($directorio_ajax)) {
    chdir($directorio_ajax);
    echo "✅ Cambiado a directorio ajax\n";
    echo "📁 Directorio actual: " . getcwd() . "\n";
    
    // Verificar archivos desde este contexto
    echo "\n🔍 Verificando archivos desde contexto ajax:\n";
    $archivos_verificar = [
        '../modelos/registro-descargas-simple.modelo.php',
        '../controladores/registro-descargas-simple.controlador.php',
        'registro-descargas-simple.ajax.php'
    ];
    
    foreach($archivos_verificar as $archivo) {
        if(file_exists($archivo)) {
            echo "✅ $archivo - EXISTE\n";
        } else {
            echo "❌ $archivo - NO EXISTE\n";
        }
    }
    
    // Probar include desde este contexto
    echo "\n🧪 Probando include desde contexto ajax:\n";
    try {
        if(file_exists('../modelos/registro-descargas-simple.modelo.php')) {
            include_once '../modelos/registro-descargas-simple.modelo.php';
            if(class_exists('ModeloRegistroDescargasSimple')) {
                echo "✅ Modelo cargado correctamente\n";
            } else {
                echo "❌ Modelo no se pudo cargar\n";
            }
        } else {
            echo "❌ Archivo del modelo no existe\n";
        }
    } catch (Exception $e) {
        echo "❌ Error al cargar modelo: " . $e->getMessage() . "\n";
    }
    
    // Probar endpoint AJAX
    echo "\n📡 Ejecutando endpoint AJAX...\n";
    ob_start();
    try {
        include 'registro-descargas-simple.ajax.php';
        $output = ob_get_contents();
        ob_end_clean();
        
        echo "✅ Endpoint ejecutado\n";
        echo "📤 Respuesta:\n";
        echo $output . "\n";
        
        // Verificar JSON
        $json = json_decode($output, true);
        if($json !== null) {
            echo "✅ Respuesta JSON válida\n";
        } else {
            echo "❌ Respuesta no es JSON válido\n";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ Error al ejecutar endpoint: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "❌ Directorio ajax no existe\n";
}

// Volver al directorio original
chdir($directorio_original);
echo "\n📁 Regresado a: " . getcwd() . "\n";

echo "\n🎯 Prueba completada\n";
?>
