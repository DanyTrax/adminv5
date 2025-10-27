<?php
/**
 * Script de prueba para verificar la API de medios de pago
 */

echo "<h2>🧪 Prueba de API de Medios de Pago</h2>\n";

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
        echo "<p><strong>JSON válido:</strong> " . (json_last_error() === JSON_ERROR_NONE ? '✅ Sí' : '❌ No') . "</p>\n";
        
        if ($data) {
            echo "<p><strong>Success:</strong> " . ($data['success'] ? '✅ Sí' : '❌ No') . "</p>\n";
            echo "<p><strong>Mensaje:</strong> " . ($data['message'] ?? 'N/A') . "</p>\n";
            echo "<p><strong>Total medios:</strong> " . ($data['total'] ?? 0) . "</p>\n";
            
            if (isset($data['medios_pago']) && is_array($data['medios_pago'])) {
                echo "<h3>💳 Medios de Pago Encontrados:</h3>\n";
                
                if (empty($data['medios_pago'])) {
                    echo "<p style='color: orange;'>⚠️ No hay medios de pago</p>\n";
                } else {
                    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
                    echo "<tr style='background: #f0f0f0;'>\n";
                    echo "<th>ID</th><th>Nombre</th><th>Descripción</th><th>Activo</th>\n";
                    echo "</tr>\n";
                    
                    foreach ($data['medios_pago'] as $medio) {
                        echo "<tr>\n";
                        echo "<td>{$medio['id'] ?? 'N/A'}</td>\n";
                        echo "<td><strong>{$medio['nombre']}</strong></td>\n";
                        echo "<td>{$medio['descripcion'] ?? ''}</td>\n";
                        echo "<td>" . ($medio['activo'] ? '✅ Sí' : '❌ No') . "</td>\n";
                        echo "</tr>\n";
                    }
                    
                    echo "</table>\n";
                }
            } else {
                echo "<p style='color: red;'>❌ No hay array de medios de pago</p>\n";
            }
        } else {
            echo "<p style='color: red;'>❌ No se pudo decodificar JSON</p>\n";
            echo "<p><strong>Respuesta raw:</strong> " . htmlspecialchars($response) . "</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}

echo "<h3>📋 Instrucciones:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Si la API funciona:</strong> El instalador debería poder importar medios de pago</li>\n";
echo "<li><strong>Si hay error:</strong> Necesitas copiar el archivo correcto a cada sucursal</li>\n";
echo "<li><strong>Archivo correcto:</strong> <code>obtener-medios-pago-activos.php</code> (ya generado)</li>\n";
echo "</ol>\n";
?>
