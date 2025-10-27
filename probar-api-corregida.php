<?php
/**
 * Script de prueba para verificar la API de medios de pago corregida
 */

echo "<h2>🧪 Prueba de API de Medios de Pago Corregida</h2>\n";

try {
    // Probar la API local corregida
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
                    echo "<p style='color: orange;'>⚠️ No hay medios de pago en esta sucursal</p>\n";
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
    
    echo "<h3>🎯 Prueba de la Sucursal SUC001:</h3>\n";
    echo "<p>Ahora vamos a probar la sucursal SUC001 que antes daba error 500:</p>\n";
    
    $url_sucursal = 'https://pruebas.acrilicosinfinito.com/api-transferencias/obtener-medios-pago-activos.php';
    echo "<p><strong>URL Sucursal:</strong> $url_sucursal</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_sucursal);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response_sucursal = curl_exec($ch);
    $http_code_sucursal = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error_sucursal = curl_error($ch);
    curl_close($ch);
    
    echo "<p><strong>HTTP Code Sucursal:</strong> $http_code_sucursal</p>\n";
    
    if ($error_sucursal) {
        echo "<p style='color: red;'>❌ Error cURL Sucursal: $error_sucursal</p>\n";
    } elseif ($http_code_sucursal === 500) {
        echo "<p style='color: red;'>❌ Aún hay error 500 en la sucursal</p>\n";
        echo "<p><strong>Necesitas hacer pull de los cambios al cPanel de la sucursal</strong></p>\n";
    } elseif ($http_code_sucursal === 200) {
        echo "<p style='color: green;'>✅ ¡Sucursal funcionando correctamente!</p>\n";
        
        $data_sucursal = json_decode($response_sucursal, true);
        if ($data_sucursal && isset($data_sucursal['success']) && $data_sucursal['success']) {
            echo "<p><strong>Medios de pago en sucursal:</strong> {$data_sucursal['total']}</p>\n";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ HTTP Code inesperado: $http_code_sucursal</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}

echo "<h3>📋 Instrucciones:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Hacer pull de los cambios:</strong> git pull origin main</li>\n";
echo "<li><strong>Verificar que el archivo se actualizó:</strong> api-transferencias/obtener-medios-pago-activos.php</li>\n";
echo "<li><strong>Probar la API:</strong> Debe devolver medios de pago de la BD local</li>\n";
echo "<li><strong>Probar el instalador:</strong> Debe importar medios correctos</li>\n";
echo "</ol>\n";
?>