<?php
/**
 * Script para corregir todas las rutas relativas en archivos AJAX
 */

echo "<h2>🔧 Corrección Masiva de Rutas AJAX</h2>";

$archivos = [
    "ajax/datatable-usuarios-central.ajax.php",
    "ajax/estadisticas-usuarios-central.ajax.php",
    "ajax/detalles-sincronizacion.ajax.php"
];

$cambios = 0;

foreach($archivos as $archivo) {
    echo "<h3>📄 Procesando: $archivo</h3>";
    
    if(file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        $contenidoOriginal = $contenido;
        
        // Corregir rutas relativas
        $contenido = str_replace(
            'require_once "../modelos/usuarios-central.modelo.php";',
            'require_once __DIR__ . "/../modelos/usuarios-central.modelo.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../controladores/usuarios-central.controlador.php";',
            'require_once __DIR__ . "/../controladores/usuarios-central.controlador.php";',
            $contenido
        );
        
        $contenido = str_replace(
            'require_once "../api-transferencias/conexion-central.php";',
            'require_once __DIR__ . "/../api-transferencias/conexion-central.php";',
            $contenido
        );
        
        if($contenido !== $contenidoOriginal) {
            if(file_put_contents($archivo, $contenido)) {
                echo "<p style='color: green;'>✅ Archivo corregido</p>";
                $cambios++;
            } else {
                echo "<p style='color: red;'>❌ Error al escribir archivo</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Archivo ya estaba correcto</p>";
        }
        
        // Mostrar primeras líneas
        $lineas = explode("\n", $contenido);
        echo "<details><summary>Ver primeras líneas</summary><pre>";
        for($i = 0; $i < 10; $i++) {
            if(isset($lineas[$i])) {
                echo ($i + 1) . ": " . htmlspecialchars($lineas[$i]) . "\n";
            }
        }
        echo "</pre></details>";
        
    } else {
        echo "<p style='color: red;'>❌ Archivo no encontrado</p>";
    }
}

echo "<h3>📊 Resumen:</h3>";
echo "<p><strong>Archivos procesados:</strong> " . count($archivos) . "</p>";
echo "<p><strong>Archivos modificados:</strong> $cambios</p>";

if($cambios > 0) {
    echo "<p style='color: green;'>✅ Corrección completada</p>";
    echo "<h3>🧪 Próximo paso:</h3>";
    echo "<p>Prueba acceder a <a href='usuarios-central' target='_blank'>usuarios-central</a></p>";
} else {
    echo "<p style='color: blue;'>ℹ️ Todos los archivos ya estaban correctos</p>";
}
?>
