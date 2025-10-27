<?php
/**
 * Script de diagnóstico para medios de pago
 * Verifica qué está pasando con la importación
 */

echo "<h2>🔍 Diagnóstico de Importación de Medios de Pago</h2>\n";

try {
    // 1. Verificar medios de pago en BD central
    echo "<h3>🏢 Medios de Pago en BD Central:</h3>\n";
    
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
    
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nombre,
            descripcion,
            activo,
            fecha_creacion
        FROM medios_pago
        ORDER BY id ASC
    ");
    
    $stmt->execute();
    $medios_central = $stmt->fetchAll();
    
    echo "<p><strong>Total medios de pago en central:</strong> " . count($medios_central) . "</p>\n";
    
    if (!empty($medios_central)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background: #f0f0f0;'>\n";
        echo "<th>ID</th><th>Nombre</th><th>Descripción</th><th>Activo</th><th>Fecha Creación</th>\n";
        echo "</tr>\n";
        
        foreach ($medios_central as $medio) {
            echo "<tr>\n";
            echo "<td>{$medio['id']}</td>\n";
            echo "<td><strong>{$medio['nombre']}</strong></td>\n";
            echo "<td>{$medio['descripcion']}</td>\n";
            echo "<td>" . ($medio['activo'] ? '✅ Sí' : '❌ No') . "</td>\n";
            echo "<td>{$medio['fecha_creacion']}</td>\n";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
    }
    
    // 2. Probar API del central
    echo "<h3>🌐 Probando API del Central:</h3>\n";
    
    $url_api_central = 'https://pruebas2.acplasticos.com/api-transferencias/obtener-medios-pago-activos.php';
    echo "<p><strong>URL:</strong> $url_api_central</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_api_central);
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
    } else {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p style='color: green;'>✅ API Central responde: {$data['total']} medios de pago</p>\n";
            
            if (!empty($data['medios_pago'])) {
                echo "<ul>\n";
                foreach ($data['medios_pago'] as $medio) {
                    echo "<li><strong>{$medio['nombre']}</strong> - {$medio['descripcion']}</li>\n";
                }
                echo "</ul>\n";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Respuesta inválida del central</p>\n";
        }
    }
    
    // 3. Verificar sucursales activas
    echo "<h3>🏢 Sucursales Activas:</h3>\n";
    
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
    } elseif ($http_code !== 200) {
        echo "<p style='color: red;'>❌ HTTP Error: $http_code</p>\n";
    } else {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p style='color: green;'>✅ Sucursales obtenidas: {$data['total']}</p>\n";
            
            foreach ($data['sucursales'] as $sucursal) {
                echo "<h4>🔍 Probando sucursal: {$sucursal['nombre']}</h4>\n";
                
                $url_medios_sucursal = rtrim($sucursal['url_api'], '/') . '/obtener-medios-pago-activos.php';
                echo "<p><strong>URL Medios:</strong> $url_medios_sucursal</p>\n";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url_medios_sucursal);
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
        } else {
            echo "<p style='color: orange;'>⚠️ No se pudieron obtener sucursales</p>\n";
        }
    }
    
    echo "<h3>🎯 CONCLUSIÓN:</h3>\n";
    echo "<p>El problema es que cada sucursal necesita tener su propio archivo <code>obtener-medios-pago-activos.php</code> que consulte su BD local.</p>\n";
    echo "<p>Actualmente, todas las sucursales están devolviendo los medios de pago del central.</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
