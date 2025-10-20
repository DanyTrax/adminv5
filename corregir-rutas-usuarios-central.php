<?php
/**
 * Script para corregir las rutas en usuarios-central.php desde el navegador
 */

echo "<h2>🔧 Corrección de Rutas - Usuarios Centrales</h2>";

$archivo = "vistas/modulos/usuarios-central.php";

if(file_exists($archivo)) {
    echo "<p><strong>Archivo encontrado:</strong> $archivo</p>";
    
    // Leer el contenido actual
    $contenido = file_get_contents($archivo);
    
    // Verificar si ya está corregido
    if(strpos($contenido, '__DIR__ . "/../../controladores/usuarios-central.controlador.php"') !== false) {
        echo "<p style='color: green;'>✅ El archivo ya está corregido</p>";
    } else {
        // Corregir la ruta
        $contenidoCorregido = str_replace(
            'require_once "../controladores/usuarios-central.controlador.php";',
            'require_once __DIR__ . "/../../controladores/usuarios-central.controlador.php";',
            $contenido
        );
        
        // Escribir el archivo corregido
        if(file_put_contents($archivo, $contenidoCorregido)) {
            echo "<p style='color: green;'>✅ Archivo corregido exitosamente</p>";
            echo "<p><strong>Cambio realizado:</strong></p>";
            echo "<pre>require_once \"../controladores/usuarios-central.controlador.php\";</pre>";
            echo "<p>↓</p>";
            echo "<pre>require_once __DIR__ . \"/../../controladores/usuarios-central.controlador.php\";</pre>";
        } else {
            echo "<p style='color: red;'>❌ Error al escribir el archivo</p>";
        }
    }
    
    // Mostrar las primeras líneas del archivo
    echo "<h3>📄 Primeras líneas del archivo:</h3>";
    $lineas = explode("\n", $contenido);
    for($i = 0; $i < 5; $i++) {
        if(isset($lineas[$i])) {
            echo "<pre>" . ($i + 1) . ": " . htmlspecialchars($lineas[$i]) . "</pre>";
        }
    }
    
} else {
    echo "<p style='color: red;'>❌ Archivo no encontrado: $archivo</p>";
}

echo "<h3>🧪 Próximo paso:</h3>";
echo "<p>Después de ejecutar este script, prueba acceder a:</p>";
echo "<p><a href='usuarios-central' target='_blank'>usuarios-central</a></p>";
?>
