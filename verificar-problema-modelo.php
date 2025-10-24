<?php
/*=============================================
VERIFICAR PROBLEMA CON MODELO
=============================================*/

echo "🔍 Verificando problema con modelo registro-descargas-simple...\n\n";

$archivo = 'modelos/registro-descargas-simple.modelo.php';

echo "📁 Verificando archivo: $archivo\n";

if(file_exists($archivo)) {
    echo "✅ Archivo EXISTE\n";
    echo "📏 Tamaño: " . filesize($archivo) . " bytes\n";
    echo "🔐 Permisos: " . substr(sprintf('%o', fileperms($archivo)), -4) . "\n";
    echo "📅 Modificado: " . date('Y-m-d H:i:s', filemtime($archivo)) . "\n";
    echo "📖 Legible: " . (is_readable($archivo) ? "SÍ" : "NO") . "\n";
    
    // Verificar contenido
    $contenido = file_get_contents($archivo);
    echo "📄 Contenido:\n";
    echo "   - Tamaño: " . strlen($contenido) . " caracteres\n";
    echo "   - Contiene PHP: " . (strpos($contenido, '<?php') !== false ? "SÍ" : "NO") . "\n";
    echo "   - Contiene clase: " . (strpos($contenido, 'class ModeloRegistroDescargasSimple') !== false ? "SÍ" : "NO") . "\n";
    
    // Mostrar primeras líneas
    $lineas = explode("\n", $contenido);
    echo "📋 Primeras 10 líneas:\n";
    for($i = 0; $i < min(10, count($lineas)); $i++) {
        echo "   " . ($i + 1) . ": " . trim($lineas[$i]) . "\n";
    }
    
} else {
    echo "❌ Archivo NO EXISTE\n";
    
    // Verificar directorio
    if(is_dir('modelos')) {
        echo "📁 Directorio 'modelos' existe\n";
        $archivos = scandir('modelos');
        echo "📋 Archivos en directorio:\n";
        foreach($archivos as $arch) {
            if($arch != '.' && $arch != '..') {
                echo "   - $arch\n";
            }
        }
    } else {
        echo "❌ Directorio 'modelos' NO existe\n";
    }
}

echo "\n🔍 Verificando include path...\n";
echo "Include Path: " . get_include_path() . "\n";

echo "\n🧪 Probando include directo...\n";
try {
    if(file_exists($archivo)) {
        echo "Intentando include: $archivo\n";
        include_once $archivo;
        echo "✅ Include exitoso\n";
        
        if(class_exists('ModeloRegistroDescargasSimple')) {
            echo "✅ Clase ModeloRegistroDescargasSimple existe\n";
        } else {
            echo "❌ Clase ModeloRegistroDescargasSimple NO existe\n";
        }
    } else {
        echo "❌ No se puede incluir - archivo no existe\n";
    }
} catch (Exception $e) {
    echo "❌ Error en include: " . $e->getMessage() . "\n";
}

echo "\n🔍 Verificando controlador...\n";
$controlador = 'controladores/registro-descargas-simple.controlador.php';
if(file_exists($controlador)) {
    echo "✅ Controlador existe\n";
    
    // Verificar línea 8 del controlador
    $contenido_ctl = file_get_contents($controlador);
    $lineas_ctl = explode("\n", $contenido_ctl);
    if(isset($lineas_ctl[7])) {
        echo "📋 Línea 8 del controlador: " . trim($lineas_ctl[7]) . "\n";
    }
} else {
    echo "❌ Controlador NO existe\n";
}

echo "\n🎯 Verificación completada\n";
?>
