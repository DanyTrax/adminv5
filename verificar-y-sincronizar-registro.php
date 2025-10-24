<?php
/*=============================================
VERIFICAR Y SINCRONIZAR MÓDULO REGISTRO DE DESCARGAS
=============================================*/

echo "🔍 Verificando archivos del módulo registro-descargas-simple...\n\n";

// Archivos requeridos
$archivos = [
    'modelos/registro-descargas-simple.modelo.php',
    'controladores/registro-descargas-simple.controlador.php',
    'ajax/registro-descargas-simple.ajax.php',
    'ajax/datatable-registro-descargas-simple.ajax.php',
    'vistas/modulos/registro-descargas-simple.php'
];

$archivos_faltantes = [];
$archivos_existentes = [];

foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        $archivos_existentes[] = $archivo;
        echo "✅ $archivo - EXISTE\n";
    } else {
        $archivos_faltantes[] = $archivo;
        echo "❌ $archivo - NO EXISTE\n";
    }
}

echo "\n📊 Resumen:\n";
echo "✅ Archivos existentes: " . count($archivos_existentes) . "\n";
echo "❌ Archivos faltantes: " . count($archivos_faltantes) . "\n";

if(count($archivos_faltantes) > 0) {
    echo "\n🔄 Sincronizando archivos faltantes desde GitHub...\n";
    
    // URL base de GitHub (ajustar según tu repositorio)
    $url_base = 'https://raw.githubusercontent.com/DanyTrax/adminv5/main/';
    
    foreach($archivos_faltantes as $archivo) {
        echo "📥 Descargando: $archivo\n";
        
        $url = $url_base . $archivo;
        $contenido = file_get_contents($url);
        
        if($contenido !== false) {
            // Crear directorio si no existe
            $directorio = dirname($archivo);
            if(!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
                echo "📁 Directorio creado: $directorio\n";
            }
            
            if(file_put_contents($archivo, $contenido)) {
                echo "✅ $archivo sincronizado correctamente\n";
            } else {
                echo "❌ Error al escribir $archivo\n";
            }
        } else {
            echo "❌ Error al descargar $archivo desde $url\n";
        }
    }
}

echo "\n🔍 Verificando tabla de base de datos...\n";

// Verificar conexión a base de datos
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
        echo "🔧 Ejecutar: crear-tabla-registro-descargas-simple.php\n";
    }
} catch (Exception $e) {
    echo "❌ Error de conexión a base de datos: " . $e->getMessage() . "\n";
}

echo "\n🎯 Próximos pasos:\n";
echo "1. Verificar que todos los archivos existen\n";
echo "2. Crear la tabla si no existe\n";
echo "3. Probar el módulo en: registro-descargas-simple\n";
echo "4. Verificar que no hay errores HTTP 500\n";
?>
