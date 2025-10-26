<?php
/*=============================================
VERIFICAR JAVASCRIPT ACTUALIZADO
=============================================*/

echo "🔍 VERIFICANDO JAVASCRIPT ACTUALIZADO\n";
echo "====================================\n\n";

$archivoJS = "vistas/js/stock-transito-unificado.js";

if (file_exists($archivoJS)) {
    echo "✅ Archivo JavaScript encontrado: $archivoJS\n";
    
    $contenido = file_get_contents($archivoJS);
    echo "📏 Tamaño: " . strlen($contenido) . " caracteres\n\n";
    
    // Verificar si contiene debug-ajax-real.php
    if (strpos($contenido, 'debug-ajax-real.php') !== false) {
        echo "✅ JavaScript contiene debug-ajax-real.php\n";
        
        // Contar ocurrencias
        $ocurrencias = substr_count($contenido, 'debug-ajax-real.php');
        echo "📊 Ocurrencias encontradas: $ocurrencias\n";
        
        // Mostrar líneas donde aparece
        $lineas = explode("\n", $contenido);
        echo "📋 Líneas que contienen debug-ajax-real.php:\n";
        foreach($lineas as $num => $linea) {
            if (strpos($linea, 'debug-ajax-real.php') !== false) {
                echo "   Línea " . ($num + 1) . ": " . trim($linea) . "\n";
            }
        }
        
    } else {
        echo "❌ JavaScript NO contiene debug-ajax-real.php\n";
        
        // Verificar si contiene ajax/registro-descargas-simple.ajax.php
        if (strpos($contenido, 'ajax/registro-descargas-simple.ajax.php') !== false) {
            echo "❌ JavaScript aún contiene ajax/registro-descargas-simple.ajax.php\n";
            echo "📋 El archivo no se actualizó correctamente\n";
        }
    }
    
} else {
    echo "❌ Archivo JavaScript no encontrado: $archivoJS\n";
}

echo "\n🔍 VERIFICANDO ARCHIVOS DE DEBUG:\n";
$archivosDebug = [
    "debug-ajax-real.php",
    "leer-log-debug.php",
    "debug-ajax-real.log"
];

foreach($archivosDebug as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ $archivo existe\n";
        if ($archivo === "debug-ajax-real.log") {
            echo "   📏 Tamaño: " . filesize($archivo) . " bytes\n";
            echo "   📅 Modificado: " . date('Y-m-d H:i:s', filemtime($archivo)) . "\n";
        }
    } else {
        echo "❌ $archivo NO existe\n";
    }
}

echo "\n🎯 VERIFICACIÓN COMPLETADA\n";

?>
