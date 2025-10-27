<?php
/**
 * Script para verificar medios de pago en SUC001
 */

echo "<h2>🔍 Verificación de Medios de Pago en SUC001</h2>\n";

try {
    // Probar sucursal SUC001
    echo "<h3>🏢 Probando sucursal SUC001:</h3>\n";
    
    $url_sucursal = 'https://pruebas.acrilicosinfinito.com/api-transferencias/obtener-medios-pago-activos.php';
    echo "<p><strong>URL:</strong> $url_sucursal</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_sucursal);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response_sucursal = curl_exec($ch);
    $http_code_sucursal = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>HTTP Code:</strong> $http_code_sucursal</p>\n";
    
    if ($http_code_sucursal === 200) {
        $data_sucursal = json_decode($response_sucursal, true);
        if ($data_sucursal && isset($data_sucursal['success']) && $data_sucursal['success']) {
            echo "<p style='color: green;'>✅ Sucursal SUC001 funcionando correctamente</p>\n";
            echo "<p><strong>Total medios de pago:</strong> {$data_sucursal['total']}</p>\n";
            
            if (!empty($data_sucursal['medios_pago'])) {
                echo "<h4>💳 Medios de Pago en SUC001:</h4>\n";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
                echo "<tr style='background: #f0f0f0;'>\n";
                echo "<th>ID</th><th>Nombre</th>\n";
                echo "</tr>\n";
                
                foreach ($data_sucursal['medios_pago'] as $medio) {
                    echo "<tr>\n";
                    echo "<td>{$medio['id']}</td>\n";
                    echo "<td><strong>{$medio['nombre']}</strong></td>\n";
                    echo "</tr>\n";
                }
                
                echo "</table>\n";
                
                echo "<h3>🎯 Flujo de Importación:</h3>\n";
                echo "<p><strong>SUC001 tiene:</strong> " . count($data_sucursal['medios_pago']) . " medios de pago</p>\n";
                echo "<p><strong>Central tiene:</strong> 7 medios de pago</p>\n";
                echo "<p><strong>Instalador debe:</strong> Importar de SUC001 al central</p>\n";
            }
        } else {
            echo "<p style='color: red;'>❌ Sucursal SUC001 no funciona correctamente</p>\n";
            echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response_sucursal) . "</p>\n";
        }
    } elseif ($http_code_sucursal === 500) {
        echo "<p style='color: red;'>❌ Error 500 en sucursal SUC001</p>\n";
        echo "<p><strong>Problema:</strong> El archivo obtener-medios-pago-activos.php en SUC001 no está corregido</p>\n";
        
        echo "<h3>🔧 Solución Requerida:</h3>\n";
        echo "<ol>\n";
        echo "<li><strong>Acceder al servidor de SUC001:</strong> pruebas.acrilicosinfinito.com</li>\n";
        echo "<li><strong>Ir a la carpeta:</strong> api-transferencias/</li>\n";
        echo "<li><strong>Reemplazar el archivo:</strong> obtener-medios-pago-activos.php</li>\n";
        echo "<li><strong>Con el archivo corregido:</strong> Que consulte solo id y nombre</li>\n";
        echo "</ol>\n";
        
        echo "<h3>📋 Archivo Correcto:</h3>\n";
        echo "<p>El archivo debe tener esta consulta:</p>\n";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>\n";
        echo htmlspecialchars('SELECT 
    id,
    nombre
FROM medios_pago
ORDER BY nombre ASC');
        echo "</pre>\n";
        
    } else {
        echo "<p style='color: orange;'>⚠️ HTTP Code inesperado: $http_code_sucursal</p>\n";
    }
    
    // Verificar medios de pago en el central
    echo "<h3>🏢 Medios de Pago en el Central:</h3>\n";
    
    $url_central = 'https://pruebas2.acplasticos.com/api-transferencias/obtener-medios-pago-activos.php';
    echo "<p><strong>URL:</strong> $url_central</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_central);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response_central = curl_exec($ch);
    $http_code_central = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code_central === 200) {
        $data_central = json_decode($response_central, true);
        if ($data_central && isset($data_central['success']) && $data_central['success']) {
            echo "<p style='color: green;'>✅ Central funcionando correctamente</p>\n";
            echo "<p><strong>Total medios de pago:</strong> {$data_central['total']}</p>\n";
        }
    }
    
    echo "<h3>🎯 Conclusión:</h3>\n";
    echo "<p>Para que el instalador funcione correctamente:</p>\n";
    echo "<ol>\n";
    echo "<li><strong>SUC001 debe tener:</strong> El archivo corregido obtener-medios-pago-activos.php</li>\n";
    echo "<li><strong>Instalador debe:</strong> Conectar a SUC001 y obtener sus medios de pago</li>\n";
    echo "<li><strong>Importar al central:</strong> Los medios de pago de SUC001</li>\n";
    echo "</ol>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
