<?php
/**
 * Script de prueba final para verificar que todo funciona
 */

echo "<h2>🧪 Prueba Final del Sistema de Medios de Pago</h2>\n";

try {
    // 1. Verificar estructura de la tabla
    echo "<h3>1. 🔍 Verificando estructura de la tabla medios_pago:</h3>\n";
    
    require_once 'config.php';
    
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    $stmt = $pdo->query("DESCRIBE medios_pago");
    $columnas = $stmt->fetchAll();
    
    $tiene_descripcion = false;
    foreach ($columnas as $columna) {
        if ($columna['Field'] === 'descripcion') {
            $tiene_descripcion = true;
            break;
        }
    }
    
    echo "<p><strong>¿Tiene columna 'descripcion'?</strong> " . ($tiene_descripcion ? '✅ Sí' : '❌ No') . "</p>\n";
    
    if (!$tiene_descripcion) {
        echo "<p style='color: red;'>❌ Necesitas ejecutar: agregar-columna-descripcion.php</p>\n";
        exit;
    }
    
    // 2. Probar la API local
    echo "<h3>2. 🧪 Probando API local:</h3>\n";
    
    $url_api = 'https://pruebas2.acplasticos.com/api-transferencias/obtener-medios-pago-activos.php';
    echo "<p><strong>URL:</strong> $url_api</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p style='color: green;'>✅ API local funcionando correctamente</p>\n";
            echo "<p><strong>Total medios de pago:</strong> {$data['total']}</p>\n";
            
            if (!empty($data['medios_pago'])) {
                echo "<h4>💳 Medios de Pago Encontrados:</h4>\n";
                echo "<ul>\n";
                foreach ($data['medios_pago'] as $medio) {
                    echo "<li><strong>{$medio['nombre']}</strong> - {$medio['descripcion']}</li>\n";
                }
                echo "</ul>\n";
            }
        } else {
            echo "<p style='color: red;'>❌ API local no funciona correctamente</p>\n";
            echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response) . "</p>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ Error HTTP: $http_code</p>\n";
    }
    
    // 3. Probar sucursal SUC001
    echo "<h3>3. 🏢 Probando sucursal SUC001:</h3>\n";
    
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
        } else {
            echo "<p style='color: red;'>❌ Sucursal SUC001 no funciona correctamente</p>\n";
            echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response_sucursal) . "</p>\n";
        }
    } elseif ($http_code_sucursal === 500) {
        echo "<p style='color: red;'>❌ Error 500 en sucursal SUC001</p>\n";
        echo "<p><strong>Necesitas hacer pull de los cambios en la sucursal</strong></p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ HTTP Code inesperado: $http_code_sucursal</p>\n";
    }
    
    // 4. Conclusión
    echo "<h3>4. 🎯 Conclusión:</h3>\n";
    
    if ($http_code === 200 && $http_code_sucursal === 200) {
        echo "<p style='color: green; font-weight: bold;'>✅ ¡Sistema completamente funcional!</p>\n";
        echo "<p>El instalador ahora puede importar medios de pago correctamente de todas las sucursales.</p>\n";
    } elseif ($http_code === 200 && $http_code_sucursal === 500) {
        echo "<p style='color: orange; font-weight: bold;'>⚠️ Sistema parcialmente funcional</p>\n";
        echo "<p>El central funciona, pero la sucursal SUC001 necesita el archivo corregido.</p>\n";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Sistema necesita más correcciones</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
