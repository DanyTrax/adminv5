<?php

/**
 * SCRIPT DE DIAGNÓSTICO DE CONEXIONES
 * 
 * Este script verifica que las conexiones a las bases de datos estén funcionando correctamente
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico de Conexiones</h1>";

// Verificar archivos de conexión
echo "<h2>📁 Verificación de Archivos</h2>";

$archivos = [
    'api-transferencias/conexion-central.php' => 'Conexión a BD Central',
    'modelos/conexion.php' => 'Conexión a BD Local'
];

foreach($archivos as $archivo => $descripcion) {
    if(file_exists($archivo)) {
        echo "<p>✅ $descripcion: <code>$archivo</code> existe</p>";
    } else {
        echo "<p>❌ $descripcion: <code>$archivo</code> NO existe</p>";
    }
}

// Verificar conexión a BD Central
echo "<h2>🌐 Conexión a BD Central</h2>";

try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    
    if($conexionCentral) {
        echo "<p>✅ Conexión a BD Central: <strong>EXITOSA</strong></p>";
        
        // Probar una consulta simple
        $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM sucursales");
        $stmt->execute();
        $resultado = $stmt->fetch();
        echo "<p>📊 Sucursales registradas en BD Central: <strong>{$resultado['total']}</strong></p>";
        
    } else {
        echo "<p>❌ Conexión a BD Central: <strong>FALLIDA</strong></p>";
    }
    
} catch(Exception $e) {
    echo "<p>❌ Error en BD Central: <strong>" . $e->getMessage() . "</strong></p>";
}

// Verificar conexión a BD Local
echo "<h2>🏠 Conexión a BD Local</h2>";

try {
    require_once "modelos/conexion.php";
    $conexionLocal = Conexion::conectar();
    
    if($conexionLocal) {
        echo "<p>✅ Conexión a BD Local: <strong>EXITOSA</strong></p>";
        
        // Probar una consulta simple
        $stmt = $conexionLocal->prepare("SELECT COUNT(*) as total FROM productos");
        $stmt->execute();
        $resultado = $stmt->fetch();
        echo "<p>📊 Productos en BD Local: <strong>{$resultado['total']}</strong></p>";
        
    } else {
        echo "<p>❌ Conexión a BD Local: <strong>FALLIDA</strong></p>";
    }
    
} catch(Exception $e) {
    echo "<p>❌ Error en BD Local: <strong>" . $e->getMessage() . "</strong></p>";
}

// Verificar configuración de PHP
echo "<h2>⚙️ Configuración de PHP</h2>";

echo "<p>📋 Versión de PHP: <strong>" . phpversion() . "</strong></p>";
echo "<p>📋 Extensión PDO: " . (extension_loaded('pdo') ? "✅ Disponible" : "❌ No disponible") . "</p>";
echo "<p>📋 Extensión PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✅ Disponible" : "❌ No disponible") . "</p>";

// Verificar permisos de directorio
echo "<h2>🔐 Permisos de Directorio</h2>";

$directorios = [
    '.' => 'Directorio actual',
    'api-transferencias' => 'API Transferencias',
    'modelos' => 'Modelos',
    'logs' => 'Logs'
];

foreach($directorios as $dir => $descripcion) {
    if(is_dir($dir)) {
        $permisos = substr(sprintf('%o', fileperms($dir)), -4);
        $escribible = is_writable($dir) ? "✅ Escribible" : "❌ No escribible";
        echo "<p>$descripcion: <code>$dir</code> - Permisos: <strong>$permisos</strong> - $escribible</p>";
    } else {
        echo "<p>❌ $descripcion: <code>$dir</code> NO existe</p>";
    }
}

// Verificar archivos de configuración
echo "<h2>📄 Archivos de Configuración</h2>";

$configs = [
    'config.php' => 'Configuración principal',
    'api-transferencias/conexion-central.php' => 'Conexión central',
    'modelos/conexion.php' => 'Conexión local'
];

foreach($configs as $archivo => $descripcion) {
    if(file_exists($archivo)) {
        $tamaño = filesize($archivo);
        $modificado = date('Y-m-d H:i:s', filemtime($archivo));
        echo "<p>✅ $descripcion: <code>$archivo</code> - Tamaño: {$tamaño} bytes - Modificado: $modificado</p>";
    } else {
        echo "<p>❌ $descripcion: <code>$archivo</code> NO existe</p>";
    }
}

echo "<hr>";
echo "<p><strong>💡 Si hay errores, verifica:</strong></p>";
echo "<ul>";
echo "<li>Las credenciales de base de datos en los archivos de conexión</li>";
echo "<li>Que las bases de datos estén creadas y accesibles</li>";
echo "<li>Los permisos de los directorios</li>";
echo "<li>La configuración del servidor web</li>";
echo "</ul>";

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
p { margin: 5px 0; }
code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
ul { margin: 10px 0; padding-left: 20px; }
hr { margin: 20px 0; border: 1px solid #ddd; }
</style>
