<?php
/**
 * Script para verificar el contenido del archivo actual en la sucursal
 */

echo "<h2>🔍 Verificación de Archivo en Sucursal</h2>\n";

try {
    // Intentar acceder al archivo actual
    $url_archivo = 'https://pruebas.acrilicosinfinito.com/api-transferencias/obtener-medios-pago-activos.php';
    echo "<p><strong>URL del archivo:</strong> $url_archivo</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_archivo);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<h3>📊 Estado del Archivo:</h3>\n";
    echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
    
    if ($error) {
        echo "<p style='color: red;'>❌ Error cURL: $error</p>\n";
    } else {
        echo "<p style='color: green;'>✅ Archivo accesible</p>\n";
    }
    
    // Probar con método GET normal
    echo "<h3>🔍 Probando Método GET:</h3>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_archivo);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
    
    if ($http_code === 500) {
        echo "<p style='color: red;'>❌ Error 500 - Problema en el servidor</p>\n";
        echo "<p><strong>Posibles causas:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Error de sintaxis en PHP</li>\n";
        echo "<li>Problema de conexión a BD</li>\n";
        echo "<li>Archivo corrupto o incompleto</li>\n";
        echo "<li>Permisos incorrectos</li>\n";
        echo "</ul>\n";
    } elseif ($http_code === 200) {
        echo "<p style='color: green;'>✅ Archivo funciona correctamente</p>\n";
        
        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            echo "<p><strong>Success:</strong> " . ($data['success'] ? '✅ Sí' : '❌ No') . "</p>\n";
            echo "<p><strong>Mensaje:</strong> " . ($data['message'] ?? 'N/A') . "</p>\n";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ HTTP Code inesperado: $http_code</p>\n";
    }
    
    echo "<h3>📝 Respuesta Raw:</h3>\n";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc; max-height: 300px; overflow-y: auto;'>\n";
    echo htmlspecialchars($response);
    echo "</pre>\n";
    
    echo "<h3>🔧 Acción Requerida:</h3>\n";
    echo "<p>Si hay error 500, necesitas:</p>\n";
    echo "<ol>\n";
    echo "<li><strong>Acceder al servidor:</strong> pruebas.acrilicosinfinito.com</li>\n";
    echo "<li><strong>Ir a:</strong> api-transferencias/obtener-medios-pago-activos.php</li>\n";
    echo "<li><strong>Reemplazar con:</strong> El archivo correcto del proyecto</li>\n";
    echo "<li><strong>Verificar:</strong> Que consulte BD local de la sucursal</li>\n";
    echo "</ol>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
