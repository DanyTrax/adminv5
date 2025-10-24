<?php
/*=============================================
SINCRONIZAR MODELO ESPECÍFICO DESDE GITHUB
=============================================*/

echo "🔧 Sincronizando modelo específico desde GitHub...\n\n";

// URL específica del modelo
$url_modelo = 'https://raw.githubusercontent.com/DanyTrax/adminv5/main/modelos/registro-descargas-simple.modelo.php';

echo "📥 Descargando modelo desde GitHub...\n";
echo "🔗 URL: $url_modelo\n\n";

// Usar cURL para descargar
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_modelo);
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
    echo "✅ Modelo descargado correctamente\n";
    echo "📏 Tamaño: " . strlen($contenido) . " bytes\n";
    
    // Verificar que contiene la clase
    if(strpos($contenido, 'class ModeloRegistroDescargasSimple') !== false) {
        echo "✅ Contiene la clase ModeloRegistroDescargasSimple\n";
    } else {
        echo "❌ NO contiene la clase ModeloRegistroDescargasSimple\n";
    }
    
    // Crear directorio si no existe
    if(!is_dir('modelos')) {
        if(mkdir('modelos', 0755, true)) {
            echo "📁 Directorio 'modelos' creado\n";
        } else {
            echo "❌ Error al crear directorio 'modelos'\n";
            exit;
        }
    }
    
    // Escribir archivo
    if(file_put_contents('modelos/registro-descargas-simple.modelo.php', $contenido)) {
        echo "✅ Archivo escrito correctamente\n";
        
        // Verificar que se escribió
        if(file_exists('modelos/registro-descargas-simple.modelo.php')) {
            echo "✅ Archivo existe en el servidor\n";
            echo "📏 Tamaño en servidor: " . filesize('modelos/registro-descargas-simple.modelo.php') . " bytes\n";
            echo "🔐 Permisos: " . substr(sprintf('%o', fileperms('modelos/registro-descargas-simple.modelo.php')), -4) . "\n";
        } else {
            echo "❌ Archivo NO existe después de escribir\n";
        }
    } else {
        echo "❌ Error al escribir el archivo\n";
    }
} else {
    echo "❌ Error al descargar modelo\n";
    echo "HTTP Code: $http_code\n";
    if($error) {
        echo "cURL Error: $error\n";
    }
}

echo "\n🔍 Verificando archivos del módulo...\n";
$archivos = [
    'modelos/registro-descargas-simple.modelo.php',
    'controladores/registro-descargas-simple.controlador.php',
    'ajax/registro-descargas-simple.ajax.php',
    'ajax/datatable-registro-descargas-simple.ajax.php'
];

foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        echo "✅ $archivo - EXISTE\n";
    } else {
        echo "❌ $archivo - NO EXISTE\n";
    }
}

echo "\n🧪 Probando include del modelo...\n";
try {
    if(file_exists('modelos/registro-descargas-simple.modelo.php')) {
        include_once 'modelos/registro-descargas-simple.modelo.php';
        if(class_exists('ModeloRegistroDescargasSimple')) {
            echo "✅ ModeloRegistroDescargasSimple cargado correctamente\n";
        } else {
            echo "❌ ModeloRegistroDescargasSimple no se pudo cargar\n";
        }
    } else {
        echo "❌ Archivo del modelo no existe\n";
    }
} catch (Exception $e) {
    echo "❌ Error al cargar modelo: " . $e->getMessage() . "\n";
}

echo "\n🎯 Sincronización completada\n";
?>
