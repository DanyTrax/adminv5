<?php
/*=============================================
LEER LOG DE DEBUG AJAX REAL
=============================================*/

echo "📋 LEYENDO LOG DE DEBUG AJAX REAL\n";
echo "=================================\n\n";

$logFile = "debug-ajax-real.log";

if (file_exists($logFile)) {
    echo "✅ Archivo de log encontrado: $logFile\n";
    echo "📏 Tamaño: " . filesize($logFile) . " bytes\n\n";
    
    $contenido = file_get_contents($logFile);
    
    if (!empty($contenido)) {
        echo "📋 CONTENIDO DEL LOG:\n";
        echo "====================\n";
        echo $contenido;
        echo "\n====================\n";
    } else {
        echo "❌ El archivo de log está vacío\n";
    }
    
} else {
    echo "❌ Archivo de log no encontrado: $logFile\n";
    
    // Verificar directorio actual
    echo "📁 Directorio actual: " . getcwd() . "\n";
    echo "📁 Archivos en directorio:\n";
    $archivos = scandir('.');
    foreach($archivos as $archivo) {
        if (strpos($archivo, 'debug') !== false || strpos($archivo, 'log') !== false) {
            echo "   - $archivo\n";
        }
    }
}

echo "\n🎯 LECTURA COMPLETADA\n";

?>
