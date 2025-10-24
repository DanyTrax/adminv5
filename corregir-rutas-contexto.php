<?php
/*=============================================
CORREGIR RUTAS POR CONTEXTO DE EJECUCIÓN
=============================================*/

echo "🔧 Corrigiendo rutas por contexto de ejecución...\n\n";

// Obtener directorio raíz del proyecto
$directorio_raiz = dirname(__FILE__);
echo "📁 Directorio raíz: $directorio_raiz\n\n";

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
            'require_once "' . $directorio_raiz . '/modelos/registro-descargas-simple.modelo.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../modelos/registro-descargas-simple.modelo.php";',
            'require_once "' . $directorio_raiz . '/modelos/registro-descargas-simple.modelo.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "controladores/registro-descargas-simple.controlador.php";',
            'require_once "' . $directorio_raiz . '/controladores/registro-descargas-simple.controlador.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../controladores/registro-descargas-simple.controlador.php";',
            'require_once "' . $directorio_raiz . '/controladores/registro-descargas-simple.controlador.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "modelos/conexion.php";',
            'require_once "' . $directorio_raiz . '/modelos/conexion.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../modelos/conexion.php";',
            'require_once "' . $directorio_raiz . '/modelos/conexion.php";',
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

echo "\n🧪 Probando rutas corregidas...\n";

// Probar desde diferentes contextos
echo "\n📡 Probando desde contexto raíz...\n";
try {
    include_once $directorio_raiz . '/modelos/registro-descargas-simple.modelo.php';
    if(class_exists('ModeloRegistroDescargasSimple')) {
        echo "✅ Modelo cargado desde raíz\n";
    } else {
        echo "❌ Modelo no se pudo cargar desde raíz\n";
    }
} catch (Exception $e) {
    echo "❌ Error desde raíz: " . $e->getMessage() . "\n";
}

echo "\n📡 Probando desde contexto ajax...\n";
// Simular contexto ajax
$directorio_ajax = $directorio_raiz . '/ajax';
chdir($directorio_ajax);
echo "📁 Directorio actual: " . getcwd() . "\n";

try {
    include_once $directorio_raiz . '/modelos/registro-descargas-simple.modelo.php';
    if(class_exists('ModeloRegistroDescargasSimple')) {
        echo "✅ Modelo cargado desde contexto ajax\n";
    } else {
        echo "❌ Modelo no se pudo cargar desde contexto ajax\n";
    }
} catch (Exception $e) {
    echo "❌ Error desde contexto ajax: " . $e->getMessage() . "\n";
}

// Volver al directorio raíz
chdir($directorio_raiz);

echo "\n🎯 Rutas corregidas por contexto\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar el módulo registro-descargas-simple\n";
echo "2. Verificar que no hay errores HTTP 500\n";
echo "3. Si persisten errores, verificar permisos de archivos\n";
?>
