<?php
/**
 * Script de prueba simple para verificar la API de medios de pago
 */

echo "<h2>🧪 Prueba Simple de API de Medios de Pago</h2>\n";

try {
    // Probar la API local
    $url_api = 'https://pruebas2.acplasticos.com/api-transferencias/obtener-medios-pago-activos.php';
    echo "<p><strong>Probando URL:</strong> $url_api</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<h3>📊 Resultado:</h3>\n";
    echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
    
    if ($error) {
        echo "<p style='color: red;'>❌ Error cURL: $error</p>\n";
    } elseif ($http_code !== 200) {
        echo "<p style='color: red;'>❌ HTTP Error: $http_code</p>\n";
        echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response) . "</p>\n";
    } else {
        echo "<p style='color: green;'>✅ Conexión exitosa</p>\n";
        
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p style='color: green;'>✅ API funcionando correctamente</p>\n";
            echo "<p><strong>Total medios de pago:</strong> {$data['total']}</p>\n";
            
            if (!empty($data['medios_pago'])) {
                echo "<h3>💳 Medios de Pago Encontrados:</h3>\n";
                echo "<ul>\n";
                foreach ($data['medios_pago'] as $medio) {
                    echo "<li><strong>{$medio['nombre']}</strong> - {$medio['descripcion']}</li>\n";
                }
                echo "</ul>\n";
            } else {
                echo "<p style='color: orange;'>⚠️ No hay medios de pago en esta sucursal</p>\n";
            }
        } else {
            echo "<p style='color: red;'>❌ API no funciona correctamente</p>\n";
            echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response) . "</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}

echo "<h3>🎯 Estado del Sistema:</h3>\n";
echo "<p>El archivo <code>obtener-medios-pago-activos.php</code> ha sido corregido para consultar la BD local.</p>\n";
echo "<p>Ahora el instalador debería poder importar medios de pago correctos de cada sucursal.</p>\n";
?>
