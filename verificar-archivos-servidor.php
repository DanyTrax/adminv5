<?php
// Script para verificar qué archivos del sistema de categorías centrales están en el servidor
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Verificar Archivos del Sistema de Categorías Centrales</h1>";

$archivos_requeridos = [
    'modelos/categorias-central.modelo.php',
    'controladores/categorias-central.controlador.php',
    'ajax/categorias-central.ajax.php',
    'vistas/modulos/categorias-central.php',
    'vistas/js/categorias-central.js'
];

echo "<h2>📋 Verificación de Archivos:</h2>";
echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Archivo</th><th>Estado</th><th>Acción</th></tr>";

$archivos_faltantes = [];

foreach ($archivos_requeridos as $archivo) {
    if (file_exists($archivo)) {
        $tamaño = filesize($archivo);
        $fecha = date('Y-m-d H:i:s', filemtime($archivo));
        echo "<tr style='background: #d4edda;'>";
        echo "<td><strong>$archivo</strong></td>";
        echo "<td>✅ Existe ($tamaño bytes, $fecha)</td>";
        echo "<td>✅ OK</td>";
        echo "</tr>";
    } else {
        echo "<tr style='background: #f8d7da;'>";
        echo "<td><strong>$archivo</strong></td>";
        echo "<td>❌ No existe</td>";
        echo "<td>⚠️ FALTA</td>";
        echo "</tr>";
        $archivos_faltantes[] = $archivo;
    }
}

echo "</table>";

if (empty($archivos_faltantes)) {
    echo "<h2>✅ Todos los archivos están presentes</h2>";
    echo "<p>El sistema de categorías centrales debería funcionar correctamente.</p>";
} else {
    echo "<h2>❌ Archivos faltantes:</h2>";
    echo "<ul>";
    foreach ($archivos_faltantes as $archivo) {
        echo "<li><strong>$archivo</strong></li>";
    }
    echo "</ul>";
    
    echo "<h3>🔧 Soluciones:</h3>";
    echo "<ol>";
    echo "<li><strong>Actualizar el servidor:</strong> Ejecutar <code>git pull origin main</code> en el servidor</li>";
    echo "<li><strong>Subir archivos manualmente:</strong> Usar SFTP o cPanel File Manager</li>";
    echo "<li><strong>Verificar permisos:</strong> Asegurar que los archivos tengan permisos de lectura</li>";
    echo "</ol>";
}

// Verificar también si la tabla categorias_central existe
echo "<h2>🗄️ Verificar Tabla categorias_central:</h2>";

try {
    require_once "api-transferencias/conexion-central.php";
    $pdo = ConexionCentral::conectar();
    
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'categorias_central'");
    $stmt->execute();
    $tabla = $stmt->fetch();
    
    if ($tabla) {
        echo "<p>✅ Tabla 'categorias_central' existe en la BD central</p>";
        
        // Contar registros
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categorias_central");
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>📊 Total de categorías: " . $resultado['total'] . "</p>";
    } else {
        echo "<p>❌ Tabla 'categorias_central' NO existe en la BD central</p>";
        echo "<p>🔧 <strong>Solución:</strong> Ejecutar <code>crear-tabla-categorias-central.php</code></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error al conectar con BD central: " . $e->getMessage() . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; }
th { background: #007bff; color: white; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
</style>";
?>
