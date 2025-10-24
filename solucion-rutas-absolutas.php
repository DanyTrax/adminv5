<?php
/*=============================================
SOLUCIÓN CON RUTAS ABSOLUTAS
=============================================*/

echo "🔧 Aplicando solución con rutas absolutas...\n\n";

// Obtener directorio raíz
$directorio_raiz = dirname(__FILE__);
echo "📁 Directorio raíz: $directorio_raiz\n\n";

// Archivos a modificar
$archivos = [
    'controladores/registro-descargas-simple.controlador.php',
    'ajax/registro-descargas-simple.ajax.php',
    'ajax/datatable-registro-descargas-simple.ajax.php'
];

foreach($archivos as $archivo) {
    echo "🔧 Modificando: $archivo\n";
    
    if(file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        $contenido_original = $contenido;
        
        // Determinar el directorio base según el archivo
        if(strpos($archivo, 'ajax/') === 0) {
            // Para archivos en ajax, usar __DIR__ . '/../'
            $directorio_base = '__DIR__ . "/../"';
        } else {
            // Para archivos en controladores, usar __DIR__ . '/'
            $directorio_base = '__DIR__ . "/"';
        }
        
        // Reemplazar require_once con rutas absolutas
        $contenido = str_replace(
            'require_once "modelos/registro-descargas-simple.modelo.php";',
            'require_once ' . $directorio_base . 'modelos/registro-descargas-simple.modelo.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../modelos/registro-descargas-simple.modelo.php";',
            'require_once __DIR__ . "/../modelos/registro-descargas-simple.modelo.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "controladores/registro-descargas-simple.controlador.php";',
            'require_once __DIR__ . "/../controladores/registro-descargas-simple.controlador.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../controladores/registro-descargas-simple.controlador.php";',
            'require_once __DIR__ . "/../controladores/registro-descargas-simple.controlador.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "modelos/conexion.php";',
            'require_once __DIR__ . "/../modelos/conexion.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../modelos/conexion.php";',
            'require_once __DIR__ . "/../modelos/conexion.php";',
            $contenido
        );
        
        // Solo escribir si hubo cambios
        if($contenido !== $contenido_original) {
            if(file_put_contents($archivo, $contenido)) {
                echo "✅ $archivo modificado\n";
            } else {
                echo "❌ Error al escribir $archivo\n";
            }
        } else {
            echo "ℹ️ $archivo no necesitaba modificación\n";
        }
    } else {
        echo "❌ $archivo no existe\n";
    }
}

echo "\n🧪 Probando rutas absolutas...\n";

// Probar desde diferentes contextos
$contextos = [
    'controladores' => 'controladores/registro-descargas-simple.controlador.php',
    'ajax' => 'ajax/registro-descargas-simple.ajax.php'
];

foreach($contextos as $contexto => $archivo) {
    echo "\n📡 Probando desde contexto: $contexto\n";
    
    if(file_exists($archivo)) {
        try {
            // Simular ejecución desde el contexto
            if($contexto === 'ajax') {
                chdir($directorio_raiz . '/ajax');
            } else {
                chdir($directorio_raiz . '/controladores');
            }
            
            echo "📁 Directorio actual: " . getcwd() . "\n";
            
            // Probar include del modelo
            if(file_exists('../modelos/registro-descargas-simple.modelo.php')) {
                include_once '../modelos/registro-descargas-simple.modelo.php';
                if(class_exists('ModeloRegistroDescargasSimple')) {
                    echo "✅ Modelo cargado desde $contexto\n";
                } else {
                    echo "❌ Modelo no se pudo cargar desde $contexto\n";
                }
            } else {
                echo "❌ Archivo del modelo no existe desde $contexto\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error desde $contexto: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Archivo $archivo no existe\n";
    }
}

// Volver al directorio raíz
chdir($directorio_raiz);

echo "\n🎯 Solución con rutas absolutas aplicada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar el módulo registro-descargas-simple\n";
echo "2. Verificar que no hay errores HTTP 500\n";
echo "3. Si persisten errores, verificar permisos\n";
?>
