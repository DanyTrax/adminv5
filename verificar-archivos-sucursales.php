<?php
/**
 * Script para verificar que cada sucursal tenga su archivo obtener-medios-pago-activos.php correcto
 */

echo "<h2>🔍 Verificación de Archivos en Sucursales</h2>\n";

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
        exit;
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['success']) || !$data['success']) {
        echo "<p style='color: red;'>❌ Error en respuesta de sucursales</p>\n";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Sucursales obtenidas: {$data['total']}</p>\n";
    
    // 2. Verificar cada sucursal
    echo "<h3>🔍 Verificando archivo obtener-medios-pago-activos.php en cada sucursal:</h3>\n";
    
    foreach ($data['sucursales'] as $sucursal) {
        echo "<h4>🏢 Sucursal: {$sucursal['nombre']} ({$sucursal['codigo_sucursal']})</h4>\n";
        echo "<p><strong>URL Base:</strong> {$sucursal['url_base']}</p>\n";
        echo "<p><strong>URL API:</strong> {$sucursal['url_api']}</p>\n";
        
        // Probar el archivo obtener-medios-pago-activos.php
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
                echo "<p style='color: green;'>✅ Archivo funciona - Medios de pago: {$data_medios['total']}</p>\n";
                
                if (!empty($data_medios['medios_pago'])) {
                    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>\n";
                    echo "<tr style='background: #e8f5e8;'>\n";
                    echo "<th>ID</th><th>Nombre</th><th>Descripción</th><th>Activo</th>\n";
                    echo "</tr>\n";
                    
                    foreach ($data_medios['medios_pago'] as $medio) {
                        echo "<tr>\n";
                        echo "<td>{$medio['id'] ?? 'N/A'}</td>\n";
                        echo "<td><strong>{$medio['nombre']}</strong></td>\n";
                        echo "<td>{$medio['descripcion'] ?? ''}</td>\n";
                        echo "<td>" . ($medio['activo'] ? '✅ Sí' : '❌ No') . "</td>\n";
                        echo "</tr>\n";
                    }
                    
                    echo "</table>\n";
                } else {
                    echo "<p style='color: orange;'>⚠️ No hay medios de pago en esta sucursal</p>\n";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Archivo existe pero respuesta inválida</p>\n";
                echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response_medios) . "</p>\n";
            }
        }
        
        echo "<hr>\n";
    }
    
    // 3. Instrucciones para corregir
    echo "<h3>🔧 Instrucciones para Corregir:</h3>\n";
    echo "<ol>\n";
    echo "<li><strong>Para cada sucursal que no funcione:</strong></li>\n";
    echo "<ul>\n";
    echo "<li>Acceder al servidor de la sucursal</li>\n";
    echo "<li>Ir a la carpeta <code>api-transferencias/</code></li>\n";
    echo "<li>Reemplazar <code>obtener-medios-pago-activos.php</code> con el archivo correcto</li>\n";
    echo "<li>Verificar que consulte la BD local de la sucursal</li>\n";
    echo "</ul>\n";
    echo "<li><strong>Archivo correcto:</strong> Debe consultar <code>medios_pago</code> de la BD local</li>\n";
    echo "<li><strong>Verificar:</strong> Cada sucursal debe tener su propia tabla <code>medios_pago</code></li>\n";
    echo "</ol>\n";
    
    echo "<h3>📋 Archivo Correcto para Copiar:</h3>\n";
    echo "<p>Usar el archivo <code>api-transferencias/obtener-medios-pago-activos-sucursal.php</code></p>\n";
    echo "<p>Renombrarlo como <code>obtener-medios-pago-activos.php</code> en cada sucursal</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
