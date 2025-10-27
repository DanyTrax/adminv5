<?php
/**
 * Script para diagnosticar error 500 en API de medios de pago
 */

echo "<h2>🔍 Diagnóstico de Error 500</h2>\n";

try {
    // Probar la API de medios de pago de la sucursal
    $url_api = 'https://pruebas.acrilicosinfinito.com/api-transferencias/obtener-medios-pago-activos.php';
    echo "<p><strong>Probando URL:</strong> $url_api</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    echo "<h3>📊 Resultado del Diagnóstico:</h3>\n";
    echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
    echo "<p><strong>URL Final:</strong> {$info['url']}</p>\n";
    echo "<p><strong>Tiempo Total:</strong> {$info['total_time']} segundos</p>\n";
    
    if ($error) {
        echo "<p style='color: red;'>❌ Error cURL: $error</p>\n";
    } else {
        echo "<p style='color: green;'>✅ Sin errores cURL</p>\n";
    }
    
    echo "<h3>📝 Respuesta Completa:</h3>\n";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>\n";
    echo htmlspecialchars($response);
    echo "</pre>\n";
    
    // Intentar decodificar JSON
    if ($response) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<h3>✅ JSON Válido:</h3>\n";
            echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #4caf50;'>\n";
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo "</pre>\n";
        } else {
            echo "<h3>❌ JSON Inválido:</h3>\n";
            echo "<p><strong>Error JSON:</strong> " . json_last_error_msg() . "</p>\n";
        }
    }
    
    echo "<h3>🔧 Solución Requerida:</h3>\n";
    echo "<ol>\n";
    echo "<li><strong>Acceder al servidor de la sucursal:</strong> pruebas.acrilicosinfinito.com</li>\n";
    echo "<li><strong>Ir a la carpeta:</strong> api-transferencias/</li>\n";
    echo "<li><strong>Reemplazar el archivo:</strong> obtener-medios-pago-activos.php</li>\n";
    echo "<li><strong>Con el archivo correcto:</strong> obtener-medios-pago-activos.php (ya generado)</li>\n";
    echo "</ol>\n";
    
    echo "<h3>📋 Archivo Correcto:</h3>\n";
    echo "<p>El archivo <code>obtener-medios-pago-activos.php</code> ya está generado en el proyecto.</p>\n";
    echo "<p>Debe consultar la BD local de la sucursal, no el central.</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
