<?php
/*=============================================
DIAGNOSTICAR ARCHIVOS REGISTRO DE DESCARGAS
=============================================*/

echo "🔍 Diagnóstico completo de archivos registro-descargas-simple...\n\n";

// Información del sistema
echo "📋 Información del Sistema:\n";
echo "Directorio actual: " . getcwd() . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Include Path: " . get_include_path() . "\n\n";

// Archivos requeridos
$archivos = [
    'modelos/registro-descargas-simple.modelo.php',
    'controladores/registro-descargas-simple.controlador.php',
    'ajax/registro-descargas-simple.ajax.php',
    'ajax/datatable-registro-descargas-simple.ajax.php',
    'vistas/modulos/registro-descargas-simple.php',
    'vistas/js/registro-descargas-simple.js'
];

echo "📁 Verificando archivos:\n";
foreach($archivos as $archivo) {
    echo "\n--- $archivo ---\n";
    
    if(file_exists($archivo)) {
        echo "✅ EXISTE\n";
        echo "📏 Tamaño: " . filesize($archivo) . " bytes\n";
        echo "🔐 Permisos: " . substr(sprintf('%o', fileperms($archivo)), -4) . "\n";
        echo "📅 Modificado: " . date('Y-m-d H:i:s', filemtime($archivo)) . "\n";
        echo "📖 Legible: " . (is_readable($archivo) ? "SÍ" : "NO") . "\n";
        
        // Verificar contenido básico
        $contenido = file_get_contents($archivo);
        if(strpos($contenido, '<?php') !== false) {
            echo "✅ Contiene código PHP\n";
        } else {
            echo "⚠️ No contiene código PHP\n";
        }
        
        // Verificar clases
        if(strpos($contenido, 'class ') !== false) {
            echo "✅ Contiene clases\n";
        } else {
            echo "⚠️ No contiene clases\n";
        }
    } else {
        echo "❌ NO EXISTE\n";
        
        // Verificar si el directorio padre existe
        $directorio = dirname($archivo);
        if(is_dir($directorio)) {
            echo "📁 Directorio padre existe: $directorio\n";
            
            // Listar archivos en el directorio
            $archivos_dir = scandir($directorio);
            echo "📋 Archivos en directorio:\n";
            foreach($archivos_dir as $archivo_dir) {
                if($archivo_dir != '.' && $archivo_dir != '..') {
                    echo "   - $archivo_dir\n";
                }
            }
        } else {
            echo "❌ Directorio padre NO existe: $directorio\n";
        }
    }
}

echo "\n🔍 Verificando directorios:\n";
$directorios = ['modelos', 'controladores', 'ajax', 'vistas/modulos', 'vistas/js'];
foreach($directorios as $dir) {
    if(is_dir($dir)) {
        echo "✅ $dir - EXISTE\n";
    } else {
        echo "❌ $dir - NO EXISTE\n";
    }
}

echo "\n🔍 Verificando tabla de base de datos:\n";
try {
    require_once "modelos/conexion.php";
    $stmt = Conexion::conectar()->prepare("SHOW TABLES LIKE 'registro_descargas_stock_transito'");
    $stmt->execute();
    $tabla_existe = $stmt->fetch();
    
    if($tabla_existe) {
        echo "✅ Tabla 'registro_descargas_stock_transito' existe\n";
        
        // Contar registros
        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
        $stmt->execute();
        $total = $stmt->fetch()['total'];
        echo "📊 Total de registros: $total\n";
    } else {
        echo "❌ Tabla 'registro_descargas_stock_transito' NO existe\n";
    }
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}

echo "\n🎯 Recomendaciones:\n";
echo "1. Si faltan archivos, ejecutar: descargar-archivos-registro.php\n";
echo "2. Si falta la tabla, ejecutar: crear-tabla-registro-descargas-simple.php\n";
echo "3. Si hay problemas de permisos, verificar permisos de archivos\n";
echo "4. Si persisten errores, limpiar cache del servidor\n";
?>
