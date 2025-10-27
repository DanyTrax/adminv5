<?php
/**
 * Script para probar el instalador con medios de pago
 */

echo "<h2>🧪 Prueba del Instalador con Medios de Pago</h2>\n";

try {
    // Simular el proceso del instalador
    echo "<h3>🔍 Simulando proceso del instalador:</h3>\n";
    
    // 1. Obtener sucursales activas
    echo "<p><strong>1. Obteniendo sucursales activas...</strong></p>\n";
    
    $url_sucursales = 'https://pruebas2.acplasticos.com/api-transferencias/obtener-sucursales-activas.php';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_sucursales);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response_sucursales = curl_exec($ch);
    $http_code_sucursales = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code_sucursales === 200) {
        $data_sucursales = json_decode($response_sucursales, true);
        if ($data_sucursales && isset($data_sucursales['success']) && $data_sucursales['success']) {
            echo "<p style='color: green;'>✅ Sucursales obtenidas: {$data_sucursales['total']}</p>\n";
            
            // 2. Probar medios de pago de cada sucursal
            echo "<p><strong>2. Probando medios de pago de cada sucursal...</strong></p>\n";
            
            foreach ($data_sucursales['sucursales'] as $sucursal) {
                echo "<h4>🔍 Sucursal: {$sucursal['nombre']} ({$sucursal['codigo_sucursal']})</h4>\n";
                
                $url_medios = rtrim($sucursal['url_api'], '/') . '/obtener-medios-pago-activos.php';
                echo "<p><strong>URL:</strong> $url_medios</p>\n";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url_medios);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response_medios = curl_exec($ch);
                $http_code_medios = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code_medios === 200) {
                    $data_medios = json_decode($response_medios, true);
                    if ($data_medios && isset($data_medios['success']) && $data_medios['success']) {
                        echo "<p style='color: green;'>✅ Medios de pago: {$data_medios['total']}</p>\n";
                        
                        if (!empty($data_medios['medios_pago'])) {
                            echo "<ul>\n";
                            foreach ($data_medios['medios_pago'] as $medio) {
                                echo "<li><strong>{$medio['nombre']}</strong> - {$medio['descripcion']}</li>\n";
                            }
                            echo "</ul>\n";
                        }
                    } else {
                        echo "<p style='color: red;'>❌ Error en respuesta de medios de pago</p>\n";
                    }
                } else {
                    echo "<p style='color: red;'>❌ HTTP Error: $http_code_medios</p>\n";
                    echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response_medios) . "</p>\n";
                }
                
                echo "<hr>\n";
            }
            
            echo "<h3>🎯 Conclusión:</h3>\n";
            echo "<p>Si todas las sucursales devuelven medios de pago correctamente, el instalador funcionará.</p>\n";
            echo "<p>Si alguna sucursal da error, necesita el archivo corregido.</p>\n";
            
        } else {
            echo "<p style='color: red;'>❌ Error al obtener sucursales</p>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ Error HTTP: $http_code_sucursales</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
