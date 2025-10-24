<?php
/*=============================================
DESCARGAR VERSIÓN LIMPIA DESDE GITHUB
=============================================*/

echo "🔄 Descargando versión limpia desde GitHub...\n\n";

// URL del archivo en GitHub (versión específica)
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
    
    // Verificar sintaxis básica
    $llaves_abiertas = substr_count($contenido, '{');
    $llaves_cerradas = substr_count($contenido, '}');
    echo "📋 Llaves abiertas: $llaves_abiertas\n";
    echo "📋 Llaves cerradas: $llaves_cerradas\n";
    
    if($llaves_abiertas === $llaves_cerradas) {
        echo "✅ Llaves balanceadas correctamente\n";
    } else {
        echo "❌ Llaves NO balanceadas\n";
        exit;
    }
    
    // Crear backup del archivo actual
    if(file_exists('vistas/js/stock-transito-unificado.js')) {
        $backup = 'vistas/js/stock-transito-unificado.js.backup.' . date('Y-m-d-H-i-s');
        if(copy('vistas/js/stock-transito-unificado.js', $backup)) {
            echo "💾 Backup creado: $backup\n";
        }
    }
    
    // Escribir archivo limpio
    if(file_put_contents('vistas/js/stock-transito-unificado.js', $contenido)) {
        echo "✅ Archivo limpio escrito correctamente\n";
        
        // Verificar que se escribió
        if(file_exists('vistas/js/stock-transito-unificado.js')) {
            echo "✅ Archivo existe en el servidor\n";
            echo "📏 Tamaño en servidor: " . filesize('vistas/js/stock-transito-unificado.js') . " bytes\n";
            echo "🔐 Permisos: " . substr(sprintf('%o', fileperms('vistas/js/stock-transito-unificado.js')), -4) . "\n";
            
            // Verificar líneas
            $lineas = explode("\n", $contenido);
            echo "📏 Líneas: " . count($lineas) . "\n";
            
            // Mostrar últimas líneas
            echo "\n📋 Últimas 5 líneas:\n";
            for($i = max(0, count($lineas) - 5); $i < count($lineas); $i++) {
                echo "   " . ($i + 1) . ": " . trim($lineas[$i]) . "\n";
            }
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

echo "\n🎯 Versión limpia descargada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar stock-transito\n";
echo "2. Verificar que no hay errores de sintaxis\n";
echo "3. Si funciona, aplicar corrección manual\n";
?>
