<?php
/*=============================================
LIMPIAR CACHE COMPLETO Y FORZAR RECARGA
=============================================*/

echo "🧹 Limpiando cache completo del servidor...\n\n";

// 1. Limpiar OPcache
if(function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache limpiado\n";
} else {
    echo "ℹ️ OPcache no está habilitado\n";
}

// 2. Limpiar cache de archivos
if(function_exists('clearstatcache')) {
    clearstatcache();
    echo "✅ Cache de archivos limpiado\n";
}

// 3. Limpiar cache de realpath
if(function_exists('clearstatcache')) {
    clearstatcache(true);
    echo "✅ Cache de realpath limpiado\n";
}

// 4. Forzar recarga de archivos específicos
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

// 5. Verificar permisos
echo "\n🔐 Verificando permisos...\n";
foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        $permisos = fileperms($archivo);
        $permisos_oct = substr(sprintf('%o', $permisos), -4);
        echo "📁 $archivo - Permisos: $permisos_oct\n";
        
        // Verificar si es legible
        if(is_readable($archivo)) {
            echo "   ✅ Legible\n";
        } else {
            echo "   ❌ No legible\n";
        }
    }
}

// 6. Probar include directo
echo "\n🧪 Probando include directo...\n";
try {
    // Probar include del modelo
    if(file_exists('modelos/registro-descargas-simple.modelo.php')) {
        include_once 'modelos/registro-descargas-simple.modelo.php';
        if(class_exists('ModeloRegistroDescargasSimple')) {
            echo "✅ ModeloRegistroDescargasSimple cargado correctamente\n";
        } else {
            echo "❌ ModeloRegistroDescargasSimple no se pudo cargar\n";
        }
    }
    
    // Probar include del controlador
    if(file_exists('controladores/registro-descargas-simple.controlador.php')) {
        include_once 'controladores/registro-descargas-simple.controlador.php';
        if(class_exists('ControladorRegistroDescargasSimple')) {
            echo "✅ ControladorRegistroDescargasSimple cargado correctamente\n";
        } else {
            echo "❌ ControladorRegistroDescargasSimple no se pudo cargar\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error al cargar clases: " . $e->getMessage() . "\n";
}

// 7. Verificar contenido de archivos
echo "\n🔍 Verificando contenido de archivos...\n";
foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        if(strpos($contenido, '<?php') !== false) {
            echo "✅ $archivo - Contiene código PHP\n";
        } else {
            echo "❌ $archivo - No contiene código PHP\n";
        }
        
        // Verificar si contiene la clase esperada
        if($archivo == 'modelos/registro-descargas-simple.modelo.php') {
            if(strpos($contenido, 'class ModeloRegistroDescargasSimple') !== false) {
                echo "   ✅ Contiene clase ModeloRegistroDescargasSimple\n";
            } else {
                echo "   ❌ No contiene clase ModeloRegistroDescargasSimple\n";
            }
        }
        
        if($archivo == 'controladores/registro-descargas-simple.controlador.php') {
            if(strpos($contenido, 'class ControladorRegistroDescargasSimple') !== false) {
                echo "   ✅ Contiene clase ControladorRegistroDescargasSimple\n";
            } else {
                echo "   ❌ No contiene clase ControladorRegistroDescargasSimple\n";
            }
        }
    }
}

echo "\n🎯 Cache limpiado y archivos recargados\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar el módulo registro-descargas-simple\n";
echo "2. Verificar que no hay errores HTTP 500\n";
echo "3. Si persisten errores, reiniciar PHP-FPM desde cPanel\n";
?>
