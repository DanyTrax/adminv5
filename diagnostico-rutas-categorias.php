<?php
// Script de diagnóstico profundo para el problema de rutas
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico Profundo de Rutas - Categorías Centrales</h1>";

// Verificar directorio actual
echo "<h2>📁 Información del Directorio Actual:</h2>";
echo "<p><strong>Directorio actual:</strong> " . getcwd() . "</p>";
echo "<p><strong>Archivo actual:</strong> " . __FILE__ . "</p>";

// Verificar rutas absolutas y relativas
$archivo_modelo = "modelos/categorias-central.modelo.php";
$archivo_modelo_absoluto = __DIR__ . "/" . $archivo_modelo;

echo "<h2>🔍 Verificación de Rutas:</h2>";
echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Método</th><th>Ruta</th><th>Existe</th><th>Es legible</th><th>Tamaño</th></tr>";

// Método 1: Ruta relativa
echo "<tr>";
echo "<td><strong>Ruta relativa</strong></td>";
echo "<td><code>$archivo_modelo</code></td>";
echo "<td>" . (file_exists($archivo_modelo) ? "✅ Sí" : "❌ No") . "</td>";
echo "<td>" . (is_readable($archivo_modelo) ? "✅ Sí" : "❌ No") . "</td>";
echo "<td>" . (file_exists($archivo_modelo) ? filesize($archivo_modelo) . " bytes" : "N/A") . "</td>";
echo "</tr>";

// Método 2: Ruta absoluta
echo "<tr>";
echo "<td><strong>Ruta absoluta</strong></td>";
echo "<td><code>$archivo_modelo_absoluto</code></td>";
echo "<td>" . (file_exists($archivo_modelo_absoluto) ? "✅ Sí" : "❌ No") . "</td>";
echo "<td>" . (is_readable($archivo_modelo_absoluto) ? "✅ Sí" : "❌ No") . "</td>";
echo "<td>" . (file_exists($archivo_modelo_absoluto) ? filesize($archivo_modelo_absoluto) . " bytes" : "N/A") . "</td>";
echo "</tr>";

echo "</table>";

// Verificar permisos del directorio modelos
echo "<h2>🔐 Verificación de Permisos:</h2>";
echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Directorio/Archivo</th><th>Permisos</th><th>Propietario</th><th>Grupo</th></tr>";

if (is_dir("modelos")) {
    $permisos = substr(sprintf('%o', fileperms("modelos")), -4);
    $propietario = posix_getpwuid(fileowner("modelos"));
    $grupo = posix_getgrgid(filegroup("modelos"));
    
    echo "<tr>";
    echo "<td><strong>modelos/</strong></td>";
    echo "<td>$permisos</td>";
    echo "<td>" . $propietario['name'] . "</td>";
    echo "<td>" . $grupo['name'] . "</td>";
    echo "</tr>";
} else {
    echo "<tr style='background: #f8d7da;'>";
    echo "<td><strong>modelos/</strong></td>";
    echo "<td colspan='3'>❌ Directorio no existe</td>";
    echo "</tr>";
}

if (file_exists($archivo_modelo)) {
    $permisos = substr(sprintf('%o', fileperms($archivo_modelo)), -4);
    $propietario = posix_getpwuid(fileowner($archivo_modelo));
    $grupo = posix_getgrgid(filegroup($archivo_modelo));
    
    echo "<tr>";
    echo "<td><strong>categorias-central.modelo.php</strong></td>";
    echo "<td>$permisos</td>";
    echo "<td>" . $propietario['name'] . "</td>";
    echo "<td>" . $grupo['name'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Verificar include_path
echo "<h2>📂 Include Path de PHP:</h2>";
$include_paths = explode(PATH_SEPARATOR, get_include_path());
echo "<ul>";
foreach ($include_paths as $path) {
    echo "<li><code>$path</code></li>";
}
echo "</ul>";

// Probar require_once con diferentes métodos
echo "<h2>🧪 Pruebas de Require:</h2>";

echo "<h3>Método 1: require_once con ruta relativa</h3>";
try {
    if (file_exists($archivo_modelo)) {
        echo "<p>✅ Archivo existe, intentando require_once...</p>";
        // No ejecutamos require_once aquí para evitar errores fatales
        echo "<p>⚠️ No ejecutado para evitar errores fatales</p>";
    } else {
        echo "<p>❌ Archivo no existe</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Método 2: Verificar contenido del archivo</h3>";
if (file_exists($archivo_modelo)) {
    $contenido = file_get_contents($archivo_modelo);
    if ($contenido) {
        echo "<p>✅ Archivo se puede leer</p>";
        echo "<p><strong>Primeras 100 caracteres:</strong></p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars(substr($contenido, 0, 100)) . "...";
        echo "</pre>";
    } else {
        echo "<p>❌ No se puede leer el contenido</p>";
    }
} else {
    echo "<p>❌ Archivo no existe</p>";
}

// Verificar si hay problemas de encoding o caracteres especiales
echo "<h2>🔤 Verificación de Encoding:</h2>";
if (file_exists($archivo_modelo)) {
    $encoding = mb_detect_encoding(file_get_contents($archivo_modelo));
    echo "<p><strong>Encoding detectado:</strong> $encoding</p>";
    
    // Verificar si hay caracteres especiales en la ruta
    $ruta_actual = realpath($archivo_modelo);
    echo "<p><strong>Ruta real:</strong> $ruta_actual</p>";
    
    if ($ruta_actual !== $archivo_modelo) {
        echo "<p>⚠️ <strong>Advertencia:</strong> La ruta real es diferente a la ruta relativa</p>";
    }
}

// Soluciones sugeridas
echo "<h2>🔧 Soluciones Sugeridas:</h2>";
echo "<ol>";

if (!file_exists($archivo_modelo)) {
    echo "<li><strong>Archivo no existe:</strong> Subir el archivo al servidor</li>";
} elseif (!is_readable($archivo_modelo)) {
    echo "<li><strong>Permisos incorrectos:</strong> Cambiar permisos del archivo a 644</li>";
} else {
    echo "<li><strong>Problema de rutas:</strong> Verificar que el controlador esté en el directorio correcto</li>";
    echo "<li><strong>Problema de include_path:</strong> Usar rutas absolutas en lugar de relativas</li>";
}

echo "<li><strong>Verificar estructura:</strong> Asegurar que la estructura de directorios sea correcta</li>";
echo "<li><strong>Reiniciar servidor web:</strong> Si es posible, reiniciar Apache/Nginx</li>";
echo "</ol>";

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; }
th { background: #007bff; color: white; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";
?>
