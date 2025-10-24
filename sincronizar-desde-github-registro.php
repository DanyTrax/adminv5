<?php
/*=============================================
SINCRONIZAR MÓDULO REGISTRO DESDE GITHUB
=============================================*/

echo "🔄 Sincronizando módulo registro-descargas-simple desde GitHub...\n\n";

// URL base de GitHub
$url_base = 'https://raw.githubusercontent.com/DanyTrax/adminv5/main/';

// Archivos a sincronizar
$archivos = [
    'modelos/registro-descargas-simple.modelo.php',
    'controladores/registro-descargas-simple.controlador.php',
    'ajax/registro-descargas-simple.ajax.php',
    'ajax/datatable-registro-descargas-simple.ajax.php',
    'vistas/modulos/registro-descargas-simple.php'
];

foreach($archivos as $archivo) {
    echo "📥 Descargando: $archivo\n";
    
    $url = $url_base . $archivo;
    $contenido = file_get_contents($url);
    
    if($contenido !== false) {
        // Crear directorio si no existe
        $directorio = dirname($archivo);
        if(!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
            echo "📁 Directorio creado: $directorio\n";
        }
        
        if(file_put_contents($archivo, $contenido)) {
            echo "✅ $archivo sincronizado correctamente\n";
        } else {
            echo "❌ Error al escribir $archivo\n";
        }
    } else {
        echo "❌ Error al descargar $archivo\n";
        echo "🔗 URL: $url\n";
    }
}

echo "\n🎉 ¡Sincronización Completada!\n";
echo "📋 Archivos sincronizados:\n";
foreach($archivos as $archivo) {
    echo "   ✅ $archivo\n";
}

echo "\n🔧 Verificando archivos...\n";
foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        echo "✅ $archivo - OK\n";
    } else {
        echo "❌ $archivo - FALTA\n";
    }
}
?>
