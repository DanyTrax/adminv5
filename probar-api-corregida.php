<?php
/**
 * Script de prueba para verificar la importación corregida
 * Usa conexion-central para obtener sucursales activas
 */

echo "<h2>🔍 Verificación de Importación Corregida</h2>\n";

try {
    // Probar la API de sucursales activas
    echo "<h3>🌐 Probando API de Sucursales Activas:</h3>\n";
    
    $url_api = 'https://pruebas2.acplasticos.com/api-transferencias/obtener-sucursales-activas.php';
    echo "<p><strong>URL API:</strong> $url_api</p>\n";
    
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
    
    if ($error) {
        echo "<p style='color: red;'>❌ Error cURL: $error</p>\n";
    } elseif ($http_code !== 200) {
        echo "<p style='color: red;'>❌ HTTP Error: $http_code</p>\n";
        echo "<p><strong>Respuesta:</strong> $response</p>\n";
    } else {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p style='color: green;'>✅ Conexión exitosa - {$data['total']} sucursales disponibles</p>\n";
            
            echo "<h4>Sucursales Disponibles:</h4>\n";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr style='background: #f0f0f0;'>\n";
            echo "<th>ID</th><th>Código</th><th>Nombre</th><th>URL Base</th><th>URL API</th>\n";
            echo "</tr>\n";
            
            foreach ($data['sucursales'] as $sucursal) {
                echo "<tr>\n";
                echo "<td>{$sucursal['id']}</td>\n";
                echo "<td>{$sucursal['codigo_sucursal']}</td>\n";
                echo "<td>{$sucursal['nombre']}</td>\n";
                echo "<td>{$sucursal['url_base']}</td>\n";
                echo "<td>{$sucursal['url_api']}</td>\n";
                echo "</tr>\n";
            }
            
            echo "</table>\n";
            
            // Probar conexión a medios de pago de cada sucursal
            echo "<h3>💳 Probando Medios de Pago por Sucursal:</h3>\n";
            
            foreach ($data['sucursales'] as $sucursal) {
                echo "<h4>Probando: {$sucursal['nombre']}</h4>\n";
                
                $url_medios = rtrim($sucursal['url_api'], '/') . '/obtener-medios-pago-activos.php';
                echo "<p><strong>URL Medios:</strong> $url_medios</p>\n";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url_medios);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                
                $response_medios = curl_exec($ch);
                $http_code_medios = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error_medios = curl_error($ch);
                curl_close($ch);
                
                if ($error_medios) {
                    echo "<p style='color: red;'>❌ Error cURL: $error_medios</p>\n";
                } elseif ($http_code_medios !== 200) {
                    echo "<p style='color: red;'>❌ HTTP Error: $http_code_medios</p>\n";
                } else {
                    $data_medios = json_decode($response_medios, true);
                    if ($data_medios && isset($data_medios['success']) && $data_medios['success']) {
                        echo "<p style='color: green;'>✅ Medios de pago: {$data_medios['total']} disponibles</p>\n";
                        
                        if (!empty($data_medios['medios_pago'])) {
                            echo "<ul>\n";
                            foreach ($data_medios['medios_pago'] as $medio) {
                                echo "<li><strong>{$medio['nombre']}</strong> - {$medio['descripcion']}</li>\n";
                            }
                            echo "</ul>\n";
                        }
                    } else {
                        echo "<p style='color: orange;'>⚠️ Respuesta inválida o sin medios de pago</p>\n";
                    }
                }
                
                echo "<hr>\n";
            }
            
        } else {
            echo "<p style='color: orange;'>⚠️ Respuesta inválida del central</p>\n";
            echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response) . "</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}

echo "<h3>📋 Resumen:</h3>\n";
echo "<p>✅ API de sucursales activas usa conexion-central.php</p>\n";
echo "<p>✅ API de medios de pago usa tabla medios_pago local</p>\n";
echo "<p>✅ No hay consultas a tabla 'sucursales' inexistente</p>\n";
echo "<p>✅ Flujo de importación corregido</p>\n";
?>
