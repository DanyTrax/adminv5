<?php
/*=============================================
CORREGIR RUTAS DE ARCHIVOS REGISTRO DE DESCARGAS
=============================================*/

echo "🔧 Corrigiendo rutas de archivos...\n\n";

// Obtener directorio actual
$directorio_actual = getcwd();
echo "📁 Directorio actual: $directorio_actual\n\n";

// Archivos a corregir
$archivos = [
    'controladores/registro-descargas-simple.controlador.php',
    'ajax/registro-descargas-simple.ajax.php',
    'ajax/datatable-registro-descargas-simple.ajax.php'
];

foreach($archivos as $archivo) {
    echo "🔧 Corrigiendo: $archivo\n";
    
    if(file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        $contenido_original = $contenido;
        
        // Reemplazar rutas relativas por absolutas
        $contenido = str_replace(
            'require_once "modelos/registro-descargas-simple.modelo.php";',
            'require_once "' . $directorio_actual . '/modelos/registro-descargas-simple.modelo.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../modelos/registro-descargas-simple.modelo.php";',
            'require_once "' . $directorio_actual . '/modelos/registro-descargas-simple.modelo.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "controladores/registro-descargas-simple.controlador.php";',
            'require_once "' . $directorio_actual . '/controladores/registro-descargas-simple.controlador.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../controladores/registro-descargas-simple.controlador.php";',
            'require_once "' . $directorio_actual . '/controladores/registro-descargas-simple.controlador.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "modelos/conexion.php";',
            'require_once "' . $directorio_actual . '/modelos/conexion.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../modelos/conexion.php";',
            'require_once "' . $directorio_actual . '/modelos/conexion.php";',
            $contenido
        );
        
        // Solo escribir si hubo cambios
        if($contenido !== $contenido_original) {
            if(file_put_contents($archivo, $contenido)) {
                echo "✅ $archivo corregido\n";
            } else {
                echo "❌ Error al escribir $archivo\n";
            }
        } else {
            echo "ℹ️ $archivo no necesitaba corrección\n";
        }
    } else {
        echo "❌ $archivo no existe\n";
    }
}

echo "\n🎯 Rutas corregidas\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar el módulo registro-descargas-simple\n";
echo "2. Verificar que no hay errores HTTP 500\n";
echo "3. Si persisten errores, reiniciar PHP-FPM desde cPanel\n";
?>
