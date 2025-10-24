<?php
/*=============================================
DESCARGAR ARCHIVOS REGISTRO DESDE GITHUB
=============================================*/

echo "📥 Descargando archivos del módulo registro-descargas-simple...\n\n";

// URL base de GitHub
$url_base = 'https://raw.githubusercontent.com/DanyTrax/adminv5/main/';

// Archivos a descargar
$archivos = [
    'modelos/registro-descargas-simple.modelo.php',
    'controladores/registro-descargas-simple.controlador.php',
    'ajax/registro-descargas-simple.ajax.php',
    'ajax/datatable-registro-descargas-simple.ajax.php',
    'vistas/modulos/registro-descargas-simple.php',
    'vistas/js/registro-descargas-simple.js'
];

$descargados = 0;
$errores = 0;

foreach($archivos as $archivo) {
    echo "📥 Descargando: $archivo\n";
    
    $url = $url_base . $archivo;
    
    // Usar cURL para descargar
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $contenido = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if($contenido !== false && $http_code == 200) {
        // Crear directorio si no existe
        $directorio = dirname($archivo);
        if(!is_dir($directorio)) {
            if(mkdir($directorio, 0755, true)) {
                echo "📁 Directorio creado: $directorio\n";
            } else {
                echo "❌ Error al crear directorio: $directorio\n";
                $errores++;
                continue;
            }
        }
        
        // Escribir archivo
        if(file_put_contents($archivo, $contenido)) {
            echo "✅ $archivo descargado correctamente\n";
            $descargados++;
        } else {
            echo "❌ Error al escribir: $archivo\n";
            $errores++;
        }
    } else {
        echo "❌ Error al descargar: $archivo (HTTP: $http_code)\n";
        echo "🔗 URL: $url\n";
        $errores++;
    }
}

echo "\n📊 Resumen de descarga:\n";
echo "✅ Archivos descargados: $descargados\n";
echo "❌ Errores: $errores\n";

if($descargados > 0) {
    echo "\n🔍 Verificando archivos descargados...\n";
    foreach($archivos as $archivo) {
        if(file_exists($archivo)) {
            $tamaño = filesize($archivo);
            echo "✅ $archivo - OK ($tamaño bytes)\n";
        } else {
            echo "❌ $archivo - NO EXISTE\n";
        }
    }
}

echo "\n🎯 Próximos pasos:\n";
echo "1. Verificar que todos los archivos existen\n";
echo "2. Probar el módulo: registro-descargas-simple\n";
echo "3. Verificar que no hay errores HTTP 500\n";
?>
