<?php
/*=============================================
RESTAURAR STOCK TRANSITO JS DESDE GITHUB
=============================================*/

echo "🔄 Restaurando stock-transito-unificado.js desde GitHub...\n\n";

// URL del archivo en GitHub
$url = 'https://raw.githubusercontent.com/DanyTrax/adminv5/main/vistas/js/stock-transito-unificado.js';

echo "📥 Descargando desde: $url\n";

// Usar cURL para descargar
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$contenido = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if($contenido !== false && $http_code == 200) {
    echo "✅ Archivo descargado correctamente\n";
    echo "📏 Tamaño: " . strlen($contenido) . " bytes\n";
    
    // Crear directorio si no existe
    if(!is_dir('vistas/js')) {
        if(mkdir('vistas/js', 0755, true)) {
            echo "📁 Directorio 'vistas/js' creado\n";
        } else {
            echo "❌ Error al crear directorio 'vistas/js'\n";
            exit;
        }
    }
    
    // Escribir archivo
    if(file_put_contents('vistas/js/stock-transito-unificado.js', $contenido)) {
        echo "✅ Archivo restaurado correctamente\n";
        
        // Verificar que se escribió
        if(file_exists('vistas/js/stock-transito-unificado.js')) {
            echo "✅ Archivo existe en el servidor\n";
            echo "📏 Tamaño en servidor: " . filesize('vistas/js/stock-transito-unificado.js') . " bytes\n";
            echo "🔐 Permisos: " . substr(sprintf('%o', fileperms('vistas/js/stock-transito-unificado.js')), -4) . "\n";
        } else {
            echo "❌ Archivo NO existe después de escribir\n";
        }
    } else {
        echo "❌ Error al escribir el archivo\n";
    }
} else {
    echo "❌ Error al descargar archivo\n";
    echo "HTTP Code: $http_code\n";
    if($error) {
        echo "cURL Error: $error\n";
    }
}

echo "\n🎯 Archivo restaurado\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar stock-transito\n";
echo "2. Verificar que los botones funcionan\n";
echo "3. Si funciona, aplicar corrección manual\n";
?>
