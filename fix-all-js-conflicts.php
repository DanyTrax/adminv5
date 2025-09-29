<?php
echo "<h2>🔧 Corrección automática de conflictos JS</h2>";

$archivosJS = [
    'vistas/js/stock-transito.js',
    'vistas/js/historico-transito.js',
    'vistas/js/despachos.js',
    'vistas/js/productos.js',
    'vistas/js/usuarios.js'
];

$cambiosRealizados = 0;

foreach ($archivosJS as $archivo) {
    if (file_exists($archivo)) {
        echo "<h3>🔍 Procesando: $archivo</h3>";
        
        $contenido = file_get_contents($archivo);
        $contenidoOriginal = $contenido;
        
        // Reemplazar const perfilUsuario
        $contenido = preg_replace(
            '/const\s+perfilUsuario\s*=\s*[\'"][^\'\"]*[\'"];?\s*/i',
            'var perfilUsuario = window.perfilUsuario || \'Invitado\';',
            $contenido
        );
        
        // Reemplazar const idUsuario
        $contenido = preg_replace(
            '/const\s+idUsuario\s*=\s*[^;]+;?\s*/i',
            'var idUsuario = window.idUsuario || 0;',
            $contenido
        );
        
        // También buscar declaraciones con PHP embebido
        $contenido = preg_replace(
            '/const\s+perfilUsuario\s*=\s*[\'"]<\?php[^>]*\?>[\'"];?\s*/i',
            'var perfilUsuario = window.perfilUsuario || \'Invitado\';',
            $contenido
        );
        
        $contenido = preg_replace(
            '/const\s+idUsuario\s*=\s*<\?php[^>]*\?>;?\s*/i',
            'var idUsuario = window.idUsuario || 0;',
            $contenido
        );
        
        if ($contenido !== $contenidoOriginal) {
            file_put_contents($archivo, $contenido);
            echo "<p style='color: green;'>✅ Archivo corregido</p>";
            $cambiosRealizados++;
        } else {
            echo "<p style='color: blue;'>ℹ️ No necesita cambios</p>";
        }
        
    } else {
        echo "<h3>❌ Archivo no encontrado: $archivo</h3>";
    }
}

echo "<hr>";
echo "<h3>📊 Resumen:</h3>";
echo "<p><strong>Archivos procesados:</strong> " . count($archivosJS) . "</p>";
echo "<p><strong>Archivos corregidos:</strong> $cambiosRealizados</p>";

if($cambiosRealizados > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin-top: 20px;'>";
    echo "<h4 style='color: #155724;'>✅ Correcciones aplicadas exitosamente</h4>";
    echo "<p style='color: #155724;'>Ahora ve a la página <strong>stock-transito</strong> y debería funcionar sin errores.</p>";
    echo "</div>";
}
?>