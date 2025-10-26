<?php
/*=============================================
LEER LOG DE REGISTRO REAL
=============================================*/

echo "📋 LEYENDO LOG DE REGISTRO REAL\n";
echo "==============================\n\n";

$logFile = "debug-registro-real.log";

if (file_exists($logFile)) {
    echo "✅ Archivo de log encontrado: $logFile\n";
    echo "📏 Tamaño: " . filesize($logFile) . " bytes\n";
    echo "📅 Modificado: " . date('Y-m-d H:i:s', filemtime($logFile)) . "\n\n";
    
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
