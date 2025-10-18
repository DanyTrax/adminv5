<?php
// Test completo del servidor para ver qué está pasando

echo "🔍 Test Servidor Completo - Verificar archivo en servidor\n";
echo "======================================================\n\n";

// Verificar si el archivo existe
$archivo = 'ajax/datatable-despachos.ajax.php';
echo "📁 Verificando archivo: $archivo\n";

if(file_exists($archivo)) {
    echo "✅ Archivo existe\n";
    
    // Verificar tamaño
    $tamano = filesize($archivo);
    echo "📏 Tamaño: " . number_format($tamano) . " bytes\n";
    
    // Verificar timestamp
    $timestamp = filemtime($archivo);
    $fecha = date('Y-m-d H:i:s', $timestamp);
    echo "🕒 Última modificación: $fecha\n";
    
    // Verificar contenido (primeras 15 líneas)
    echo "\n📋 Primeras 15 líneas del archivo:\n";
    $lineas = file($archivo);
    for($i = 0; $i < min(15, count($lineas)); $i++) {
        echo "   " . ($i+1) . ": " . trim($lineas[$i]) . "\n";
    }
    
    // Verificar si tiene ob_start()
    $contenido = file_get_contents($archivo);
    if(strpos($contenido, 'ob_start()') !== false) {
        echo "\n✅ Contiene ob_start() - Archivo actualizado\n";
    } else {
        echo "\n❌ NO contiene ob_start() - Archivo desactualizado\n";
    }
    
    // Verificar si tiene la ruta correcta
    if(strpos($contenido, 'require_once "modelos/conexion.php"') !== false) {
        echo "✅ Ruta correcta: modelos/conexion.php\n";
    } else {
        echo "❌ Ruta incorrecta o no encontrada\n";
    }
    
    // Verificar si tiene session_start condicional
    if(strpos($contenido, 'session_status() === PHP_SESSION_NONE') !== false) {
        echo "✅ Tiene session_start condicional\n";
    } else {
        echo "❌ NO tiene session_start condicional\n";
    }
    
} else {
    echo "❌ Archivo NO existe\n";
}

echo "\n🔍 Verificando permisos:\n";
if(file_exists($archivo)) {
    $permisos = fileperms($archivo);
    echo "📋 Permisos: " . decoct($permisos & 0777) . "\n";
    echo "📋 Lectura: " . (is_readable($archivo) ? "✅" : "❌") . "\n";
    echo "📋 Ejecución: " . (is_executable($archivo) ? "✅" : "❌") . "\n";
}

echo "\n🔍 Probando ejecución directa del archivo:\n";
try {
    ob_start();
    include $archivo;
    $output = ob_get_clean();
    
    echo "✅ Archivo ejecutado exitosamente\n";
    echo "📤 Output (primeros 200 caracteres):\n";
    echo substr($output, 0, 200) . "...\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "✅ JSON válido\n";
    } else {
        echo "❌ JSON inválido - Error: " . json_last_error_msg() . "\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error al ejecutar: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
?>
