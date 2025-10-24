<?php
/*=============================================
SINCRONIZAR MÓDULO REGISTRO DE DESCARGAS COMPLETO
=============================================*/

echo "🔄 Sincronizando Módulo Registro de Descargas Completo...\n\n";

// Archivos a sincronizar
$archivos = [
    'vistas/modulos/registro-descargas-simple.php',
    'controladores/registro-descargas-simple.controlador.php',
    'modelos/registro-descargas-simple.modelo.php',
    'ajax/registro-descargas-simple.ajax.php',
    'ajax/datatable-registro-descargas-simple.ajax.php'
];

$url_base = 'https://raw.githubusercontent.com/tu-usuario/tu-repo/main/';

foreach($archivos as $archivo) {
    echo "📁 Sincronizando: $archivo\n";
    
    $url = $url_base . $archivo;
    $contenido = file_get_contents($url);
    
    if($contenido !== false) {
        if(file_put_contents($archivo, $contenido)) {
            echo "✅ $archivo sincronizado correctamente\n";
        } else {
            echo "❌ Error al escribir $archivo\n";
        }
    } else {
        echo "❌ Error al descargar $archivo\n";
    }
}

echo "\n🎉 ¡Sincronización Completada!\n";
echo "📋 Archivos actualizados:\n";
foreach($archivos as $archivo) {
    echo "   ✅ $archivo\n";
}

echo "\n🔧 Próximos pasos:\n";
echo "1. Verificar que la tabla 'registro_descargas_stock_transito' existe\n";
echo "2. Probar el módulo en: registro-descargas-simple\n";
echo "3. Verificar que las estadísticas se cargan correctamente\n";
echo "4. Probar los filtros de búsqueda\n";
echo "5. Verificar que la tabla se muestra con datos\n";
?>
