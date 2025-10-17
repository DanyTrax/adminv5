<?php
echo "<h2>🔍 Buscando 'const perfilUsuario' en archivos JS</h2>";

$archivo = 'vistas/js/stock-transito.js';

if(file_exists($archivo)) {
    $contenido = file_get_contents($archivo);
    $lineas = explode("\n", $contenido);
    
    echo "<h3>📄 Archivo: $archivo</h3>";
    
    foreach($lineas as $numero => $linea) {
        if(stripos($linea, 'const perfilUsuario') !== false || 
           stripos($linea, 'const idUsuario') !== false) {
            echo "<p style='color: red;'><strong>LÍNEA " . ($numero + 1) . ":</strong> " . htmlspecialchars($linea) . "</p>";
        }
    }
    
    // Mostrar primeras 10 líneas para ver qué hay
    echo "<h4>Primeras 10 líneas del archivo:</h4>";
    echo "<pre>";
    for($i = 0; $i < min(10, count($lineas)); $i++) {
        echo ($i + 1) . ": " . htmlspecialchars($lineas[$i]) . "\n";
    }
    echo "</pre>";
    
} else {
    echo "<p>❌ Archivo no encontrado: $archivo</p>";
}
?>