<?php
/**
 * Script de prueba para verificar la API de sucursales activas
 */

echo "<h2>🔍 Prueba de API de Sucursales Activas</h2>\n";

try {
    // Probar la API directamente
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
    
    echo "<h3>📊 Resultado de la Prueba:</h3>\n";
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
            echo "<p><strong>Total sucursales:</strong> " . ($data['total'] ?? 0) . "</p>\n";
            
            if (isset($data['sucursales']) && is_array($data['sucursales'])) {
                echo "<h3>🏢 Sucursales Encontradas:</h3>\n";
                
                if (empty($data['sucursales'])) {
                    echo "<p style='color: orange;'>⚠️ No hay sucursales activas</p>\n";
                } else {
                    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
                    echo "<tr style='background: #f0f0f0;'>\n";
                    echo "<th>ID</th><th>Código</th><th>Nombre</th><th>Dirección</th><th>URL Base</th><th>URL API</th><th>Activo</th>\n";
                    echo "</tr>\n";
                    
                    foreach ($data['sucursales'] as $sucursal) {
                        echo "<tr>\n";
                        echo "<td>{$sucursal['id']}</td>\n";
                        echo "<td>{$sucursal['codigo_sucursal']}</td>\n";
                        echo "<td>{$sucursal['nombre']}</td>\n";
                        echo "<td>{$sucursal['direccion']}</td>\n";
                        echo "<td>{$sucursal['url_base']}</td>\n";
                        echo "<td>{$sucursal['url_api']}</td>\n";
                        echo "<td>" . ($sucursal['activo'] ? '✅ Sí' : '❌ No') . "</td>\n";
                        echo "</tr>\n";
                    }
                    
                    echo "</table>\n";
                }
            } else {
                echo "<p style='color: red;'>❌ No hay array de sucursales en la respuesta</p>\n";
            }
        } else {
            echo "<p style='color: red;'>❌ No se pudo decodificar JSON</p>\n";
            echo "<p><strong>Respuesta raw:</strong> " . htmlspecialchars($response) . "</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}

echo "<h3>🔧 Prueba Directa de Conexión a BD Central:</h3>\n";

try {
    // Probar conexión directa a BD central
    require_once 'api-transferencias/conexion-central.php';
    
    $pdo = ConexionCentral::conectar();
    echo "<p style='color: green;'>✅ Conexión a BD central exitosa</p>\n";
    
    // Probar consulta directa
    $stmt = $pdo->prepare("
        SELECT 
            id,
            codigo_sucursal,
            nombre,
            activo
        FROM sucursales
        WHERE activo = 1
        ORDER BY nombre ASC
    ");
    
    $stmt->execute();
    $sucursales_directas = $stmt->fetchAll();
    
    echo "<p><strong>Sucursales activas en BD central:</strong> " . count($sucursales_directas) . "</p>\n";
    
    if (!empty($sucursales_directas)) {
        echo "<ul>\n";
        foreach ($sucursales_directas as $sucursal) {
            echo "<li><strong>{$sucursal['nombre']}</strong> ({$sucursal['codigo_sucursal']}) - ID: {$sucursal['id']}</li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay sucursales activas en la BD central</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en conexión directa: " . $e->getMessage() . "</p>\n";
}
?>
