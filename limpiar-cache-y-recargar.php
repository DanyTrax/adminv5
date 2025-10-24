<?php
/*=============================================
LIMPIAR CACHE Y FORZAR RECARGA COMPLETA
=============================================*/

echo "🧹 Limpiando cache y forzando recarga completa...\n\n";

// Limpiar OPcache si está habilitado
if(function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache limpiado\n";
} else {
    echo "ℹ️ OPcache no está habilitado\n";
}

// Limpiar cache de archivos
if(function_exists('clearstatcache')) {
    clearstatcache();
    echo "✅ Cache de archivos limpiado\n";
}

// Forzar recarga de archivos específicos
$archivos = [
    'modelos/registro-descargas-simple.modelo.php',
    'controladores/registro-descargas-simple.controlador.php',
    'ajax/registro-descargas-simple.ajax.php',
    'ajax/datatable-registro-descargas-simple.ajax.php'
];

echo "\n🔄 Forzando recarga de archivos...\n";
foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        // Forzar recarga tocando el archivo
        touch($archivo);
        echo "✅ $archivo - Recargado\n";
    } else {
        echo "❌ $archivo - No existe\n";
    }
}

// Verificar permisos
echo "\n🔐 Verificando permisos...\n";
foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        $permisos = fileperms($archivo);
        $permisos_oct = substr(sprintf('%o', $permisos), -4);
        echo "📁 $archivo - Permisos: $permisos_oct\n";
    }
}

echo "\n🎯 Cache limpiado y archivos recargados\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar el módulo registro-descargas-simple\n";
echo "2. Verificar que no hay errores HTTP 500\n";
echo "3. Revisar logs de error si persisten problemas\n";
?>
