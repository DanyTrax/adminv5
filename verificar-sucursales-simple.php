<?php
/**
 * Script simplificado para verificar archivos en sucursales
 */

echo "<h2>🔍 Verificación Simplificada de Sucursales</h2>\n";

try {
    // 1. Obtener sucursales activas del central
    echo "<h3>🏢 Obteniendo sucursales activas del central:</h3>\n";
    
    $url_sucursales = 'https://pruebas2.acplasticos.com/api-transferencias/obtener-sucursales-activas.php';
    echo "<p><strong>URL:</strong> $url_sucursales</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_sucursales);
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
        exit;
    }
    
    if ($http_code !== 200) {
        echo "<p style='color: red;'>❌ HTTP Error: $http_code</p>\n";
        echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response) . "</p>\n";
        exit;
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['success']) || !$data['success']) {
        echo "<p style='color: red;'>❌ Error en respuesta de sucursales</p>\n";
        echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response) . "</p>\n";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Sucursales obtenidas: {$data['total']}</p>\n";
    
    // 2. Mostrar sucursales disponibles
    echo "<h3>🏢 Sucursales Disponibles:</h3>\n";
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
    
    // 3. Probar medios de pago de cada sucursal
    echo "<h3>💳 Probando Medios de Pago por Sucursal:</h3>\n";
    
    foreach ($data['sucursales'] as $sucursal) {
        echo "<h4>🔍 Sucursal: {$sucursal['nombre']} ({$sucursal['codigo_sucursal']})</h4>\n";
        
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
            echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response_medios) . "</p>\n";
        } else {
            $data_medios = json_decode($response_medios, true);
            if ($data_medios && isset($data_medios['success']) && $data_medios['success']) {
                echo "<p style='color: green;'>✅ Medios de pago: {$data_medios['total']}</p>\n";
                
                if (!empty($data_medios['medios_pago'])) {
                    echo "<ul>\n";
                    foreach ($data_medios['medios_pago'] as $medio) {
                        echo "<li><strong>{$medio['nombre']}</strong> - {$medio['descripcion']}</li>\n";
                    }
                    echo "</ul>\n";
                } else {
                    echo "<p style='color: orange;'>⚠️ No hay medios de pago en esta sucursal</p>\n";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Respuesta inválida</p>\n";
                echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response_medios) . "</p>\n";
            }
        }
        
        echo "<hr>\n";
    }
    
    echo "<h3>🎯 Conclusión:</h3>\n";
    echo "<p>Si alguna sucursal no devuelve medios de pago o devuelve error, necesita:</p>\n";
    echo "<ol>\n";
    echo "<li>Copiar el archivo <code>obtener-medios-pago-activos.php</code> correcto</li>\n";
    echo "<li>Reemplazar en la carpeta <code>api-transferencias/</code> de esa sucursal</li>\n";
    echo "<li>Verificar que consulte la BD local de la sucursal</li>\n";
    echo "</ol>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>\n";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>\n";
}
?>
