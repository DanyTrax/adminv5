<?php
/**
 * Script para probar directamente el AJAX de usuarios centrales
 */

echo "<h2>🧪 Prueba Directa del AJAX - Usuarios Centrales</h2>";

// Simular la sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";

echo "<h3>📋 Información de sesión:</h3>";
echo "<p><strong>Perfil:</strong> " . $_SESSION["perfil"] . "</p>";
echo "<p><strong>ID:</strong> " . $_SESSION["id"] . "</p>";
echo "<p><strong>Nombre:</strong> " . $_SESSION["nombre"] . "</p>";

echo "<h3>🔍 Probando AJAX datatable-usuarios-central.ajax.php:</h3>";

try {
    // Capturar la salida del AJAX
    ob_start();
    
    // Simular la petición AJAX
    $_POST["mostrarUsuariosCentral"] = true;
    
    // Incluir el archivo AJAX
    include "ajax/datatable-usuarios-central.ajax.php";
    
    $output = ob_get_clean();
    
    echo "<h4>📤 Salida del AJAX:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // Intentar decodificar como JSON
    echo "<h4>🔍 Análisis JSON:</h4>";
    
    $json = json_decode($output, true);
    
    if(json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'>✅ JSON válido</p>";
        echo "<p><strong>Estructura:</strong></p>";
        echo "<pre>" . print_r($json, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ JSON inválido</p>";
        echo "<p><strong>Error JSON:</strong> " . json_last_error_msg() . "</p>";
        
        // Mostrar caracteres problemáticos
        echo "<p><strong>Caracteres especiales:</strong></p>";
        $chars = str_split($output);
        $problematic = [];
        foreach($chars as $i => $char) {
            if(ord($char) > 127 || $char === "\0") {
                $problematic[] = "Posición $i: '" . $char . "' (ASCII: " . ord($char) . ")";
            }
        }
        if(!empty($problematic)) {
            echo "<pre>" . implode("\n", array_slice($problematic, 0, 20)) . "</pre>";
        }
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error al ejecutar AJAX: " . $e->getMessage() . "</p>";
    echo "<p><strong>Trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>🔍 Probando AJAX estadisticas-usuarios-central.ajax.php:</h3>";

try {
    ob_start();
    
    $_POST["obtenerEstadisticas"] = true;
    
    include "ajax/estadisticas-usuarios-central.ajax.php";
    
    $output = ob_get_clean();
    
    echo "<h4>📤 Salida del AJAX de estadísticas:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    $json = json_decode($output, true);
    
    if(json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'>✅ JSON de estadísticas válido</p>";
    } else {
        echo "<p style='color: red;'>❌ JSON de estadísticas inválido: " . json_last_error_msg() . "</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error en estadísticas: " . $e->getMessage() . "</p>";
}

echo "<h3>📋 Próximos pasos:</h3>";
echo "<p>1. Revisa la salida del AJAX arriba</p>";
echo "<p>2. Si hay errores, esos son los que necesitamos corregir</p>";
echo "<p>3. Si el JSON es válido, el problema puede estar en el JavaScript</p>";
?>
