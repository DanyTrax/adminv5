<?php
echo "<h2>🔧 Corrección masiva de session_start()</h2>";

$directorio = "ajax/";
$archivos = glob($directorio . "*.php");

$archivosCambiados = 0;
$errores = [];

foreach($archivos as $archivo) {
    try {
        $contenido = file_get_contents($archivo);
        
        // Buscar session_start() al inicio del archivo (después de <?php)
        $patron = '/(<\?php\s*\n?\s*)session_start\(\)\s*;/';
        
        if(preg_match($patron, $contenido)) {
            echo "<p>🔍 Procesando: <code>$archivo</code></p>";
            
            // Reemplazar session_start() con la versión mejorada
            $nuevoContenido = preg_replace(
                $patron,
                '$1// Iniciar sesión solo si no está ya iniciada' . "\n" . 
                'if (session_status() === PHP_SESSION_NONE) {' . "\n" . 
                '    session_start();' . "\n" . 
                '}',
                $contenido
            );
            
            if($nuevoContenido !== $contenido) {
                file_put_contents($archivo, $nuevoContenido);
                echo "<p style='color: green;'>✅ Corregido: <code>$archivo</code></p>";
                $archivosCambiados++;
            }
        }
        
    } catch(Exception $e) {
        $errores[] = "Error en $archivo: " . $e->getMessage();
        echo "<p style='color: red;'>❌ Error en <code>$archivo</code>: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>📊 Resumen:</h3>";
echo "<ul>";
echo "<li><strong>Archivos procesados:</strong> " . count($archivos) . "</li>";
echo "<li><strong>Archivos corregidos:</strong> $archivosCambiados</li>";
echo "<li><strong>Errores:</strong> " . count($errores) . "</li>";
echo "</ul>";

if(count($errores) > 0) {
    echo "<h4>⚠️ Errores encontrados:</h4>";
    echo "<ul>";
    foreach($errores as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<p style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
echo "💡 <strong>Nota:</strong> Después de ejecutar esta corrección, prueba de nuevo el debug y la página de stock-transito.";
echo "</p>";
?>