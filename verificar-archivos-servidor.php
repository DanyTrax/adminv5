<?php
/**
 * Script para verificar qué archivos de usuarios centrales existen en el servidor
 */

echo "<h2>🔍 Verificación de Archivos de Usuarios Centrales</h2>";

$archivos = [
    'controladores/usuarios-central.controlador.php',
    'modelos/usuarios-central.modelo.php',
    'vistas/modulos/usuarios-central.php',
    'ajax/datatable-usuarios-central.ajax.php',
    'ajax/estadisticas-usuarios-central.ajax.php',
    'ajax/detalles-sincronizacion.ajax.php',
    'crear-tabla-usuarios-central.php'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Archivo</th><th>Existe</th><th>Tamaño</th><th>Última Modificación</th></tr>";

foreach($archivos as $archivo) {
    $existe = file_exists($archivo);
    $tamaño = $existe ? filesize($archivo) : 0;
    $fecha = $existe ? date('Y-m-d H:i:s', filemtime($archivo)) : 'N/A';
    
    $color = $existe ? '#d4edda' : '#f8d7da';
    $texto = $existe ? '✅ SÍ' : '❌ NO';
    
    echo "<tr style='background-color: $color;'>";
    echo "<td>$archivo</td>";
    echo "<td>$texto</td>";
    echo "<td>" . number_format($tamaño) . " bytes</td>";
    echo "<td>$fecha</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>📋 Resumen:</h3>";
$existentes = 0;
$faltantes = 0;

foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        $existentes++;
    } else {
        $faltantes++;
        echo "<p style='color: red;'>❌ FALTA: $archivo</p>";
    }
}

echo "<p><strong>Total existentes:</strong> $existentes</p>";
echo "<p><strong>Total faltantes:</strong> $faltantes</p>";

if($faltantes > 0) {
    echo "<h3>🚨 ACCIÓN REQUERIDA:</h3>";
    echo "<p>Hay archivos faltantes. Necesitas ejecutar en el servidor:</p>";
    echo "<pre>git pull origin main</pre>";
    echo "<p>O si hay conflictos:</p>";
    echo "<pre>git fetch origin main<br>git reset --hard origin/main</pre>";
} else {
    echo "<h3>✅ TODO CORRECTO:</h3>";
    echo "<p>Todos los archivos están presentes en el servidor.</p>";
}
?>
