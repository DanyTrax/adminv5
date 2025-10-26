<?php
/*=============================================
MODIFICAR JAVASCRIPT TEMPORALMENTE PARA DEBUG
=============================================*/

echo "🔧 MODIFICANDO JAVASCRIPT TEMPORALMENTE PARA DEBUG\n";
echo "================================================\n\n";

// Leer el archivo JavaScript actual
$archivoJS = "vistas/js/stock-transito-unificado.js";
$contenidoActual = file_get_contents($archivoJS);

echo "📋 Archivo actual: $archivoJS\n";
echo "📏 Tamaño: " . strlen($contenidoActual) . " caracteres\n\n";

// Buscar y reemplazar la URL del endpoint AJAX
$patron = '/url: "ajax\/registro-descargas-simple\.ajax\.php"/';
$reemplazo = 'url: "debug-ajax-real.php"';

$contenidoModificado = preg_replace($patron, $reemplazo, $contenidoActual);

if ($contenidoModificado !== $contenidoActual) {
    // Crear backup
    $backupFile = "vistas/js/stock-transito-unificado.js.backup";
    file_put_contents($backupFile, $contenidoActual);
    echo "✅ Backup creado: $backupFile\n";
    
    // Guardar archivo modificado
    file_put_contents($archivoJS, $contenidoModificado);
    echo "✅ Archivo modificado: $archivoJS\n";
    echo "📏 Nuevo tamaño: " . strlen($contenidoModificado) . " caracteres\n\n";
    
    echo "🎯 MODIFICACIÓN COMPLETADA\n";
    echo "========================\n";
    echo "✅ JavaScript ahora usa debug-ajax-real.php\n";
    echo "✅ Backup creado para restaurar después\n";
    echo "✅ Ahora puedes probar el registro desde el navegador\n\n";
    
    echo "📋 PRÓXIMOS PASOS:\n";
    echo "1. Ve al módulo 'Stock en Tránsito'\n";
    echo "2. Busca un producto\n";
    echo "3. Haz clic en 'Descargar Producto'\n";
    echo "4. Completa la modal\n";
    echo "5. Haz clic en 'Descargar'\n";
    echo "6. Revisa debug-ajax-real.log\n";
    echo "7. Ejecuta debug-ajax-real.php para ver el log\n\n";
    
    echo "🔄 PARA RESTAURAR:\n";
    echo "Ejecuta: php restaurar-javascript.php\n";
    
} else {
    echo "❌ No se encontró el patrón a reemplazar\n";
    echo "📋 Patrón buscado: url: \"ajax/registro-descargas-simple.ajax.php\"\n";
}

// Crear script para restaurar
$scriptRestaurar = '<?php
/*=============================================
RESTAURAR JAVASCRIPT ORIGINAL
=============================================*/

echo "🔄 RESTAURANDO JAVASCRIPT ORIGINAL\n";
echo "=================================\n\n";

$archivoJS = "vistas/js/stock-transito-unificado.js";
$backupFile = "vistas/js/stock-transito-unificado.js.backup";

if (file_exists($backupFile)) {
    $contenidoOriginal = file_get_contents($backupFile);
    file_put_contents($archivoJS, $contenidoOriginal);
    unlink($backupFile);
    
    echo "✅ JavaScript restaurado correctamente\n";
    echo "✅ Backup eliminado\n";
} else {
    echo "❌ No se encontró el archivo de backup\n";
}

echo "\n🎯 RESTAURACIÓN COMPLETADA\n";
?>';

file_put_contents("restaurar-javascript.php", $scriptRestaurar);
echo "✅ Script de restauración creado: restaurar-javascript.php\n";

?>
