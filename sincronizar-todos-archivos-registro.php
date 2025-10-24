<?php
/*=============================================
SINCRONIZAR TODOS LOS ARCHIVOS DEL MÓDULO
=============================================*/

echo "🚀 Sincronizando TODOS los archivos del módulo registro-descargas-simple\n";
echo "================================================================\n\n";

// URLs base
$baseUrl = "https://raw.githubusercontent.com/DanyTrax/adminv5/main/";

// Archivos a sincronizar
$archivos = [
    // Modelos
    "modelos/registro-descargas-simple.modelo.php",
    
    // Controladores  
    "controladores/registro-descargas-simple.controlador.php",
    
    // AJAX
    "ajax/registro-descargas-simple.ajax.php",
    "ajax/datatable-registro-descargas-simple.ajax.php",
    
    // Vistas
    "vistas/modulos/registro-descargas-simple.php",
    "vistas/js/registro-descargas-simple.js",
    
    // Scripts de BD
    "crear-tabla-registro-descargas-simple.sql",
    "crear-tabla-registro-descargas-simple.php"
];

$exitosos = 0;
$errores = 0;

foreach ($archivos as $archivo) {
    echo "📥 Descargando: $archivo\n";
    
    $url = $baseUrl . $archivo;
    $contenido = file_get_contents($url);
    
    if ($contenido === false) {
        echo "❌ Error al descargar: $archivo\n";
        $errores++;
        continue;
    }
    
    // Crear directorio si no existe
    $directorio = dirname($archivo);
    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true);
        echo "📁 Directorio creado: $directorio\n";
    }
    
    // Guardar archivo
    if (file_put_contents($archivo, $contenido) === false) {
        echo "❌ Error al guardar: $archivo\n";
        $errores++;
        continue;
    }
    
    // Establecer permisos
    chmod($archivo, 0644);
    
    echo "✅ Archivo sincronizado: $archivo\n";
    $exitosos++;
}

echo "\n📊 RESUMEN DE SINCRONIZACIÓN:\n";
echo "✅ Archivos exitosos: $exitosos\n";
echo "❌ Archivos con error: $errores\n";

if ($errores == 0) {
    echo "\n🎉 ¡Sincronización completada exitosamente!\n";
    echo "\n🔧 PRÓXIMOS PASOS:\n";
    echo "1. Ejecutar: php crear-tabla-registro-descargas-simple.php\n";
    echo "2. Probar el módulo: registro-descargas-simple\n";
    echo "3. Verificar que no haya errores HTTP 500\n";
} else {
    echo "\n⚠️ Hubo errores en la sincronización. Revisar los archivos fallidos.\n";
}

echo "\n🔗 URL del módulo: registro-descargas-simple\n";
?>
