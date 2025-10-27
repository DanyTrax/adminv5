<?php
/**
 * Script para verificar medios de pago en sucursales específicas
 */

echo "<h2>🔍 Verificación de Medios de Pago por Sucursal</h2>\n";

try {
    // Probar la API de sucursales activas
    $url_api_sucursales = 'https://pruebas2.acplasticos.com/api-transferencias/obtener-sucursales-activas.php';
    echo "<h3>🌐 Obteniendo sucursales activas:</h3>\n";
    echo "<p><strong>URL:</strong> $url_api_sucursales</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_api_sucursales);
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
    
    // Mostrar sucursales disponibles
    echo "<h3>🏢 Sucursales Disponibles:</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'>\n";
    echo "<th>ID</th><th>Código</th><th>Nombre</th><th>URL API</th>\n";
    echo "</tr>\n";
    
    foreach ($data['sucursales'] as $sucursal) {
        echo "<tr>\n";
        echo "<td>{$sucursal['id']}</td>\n";
        echo "<td>{$sucursal['codigo_sucursal']}</td>\n";
        echo "<td>{$sucursal['nombre']}</td>\n";
        echo "<td>{$sucursal['url_api']}</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // Probar medios de pago de cada sucursal
    echo "<h3>💳 Verificando Medios de Pago por Sucursal:</h3>\n";
    
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
                echo "<p style='color: green;'>✅ Medios de pago: {$data_medios['total']} disponibles</p>\n";
                
                if (!empty($data_medios['medios_pago'])) {
                    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>\n";
                    echo "<tr style='background: #e8f5e8;'>\n";
                    echo "<th>ID</th><th>Nombre</th><th>Descripción</th><th>Activo</th><th>Fecha Creación</th>\n";
                    echo "</tr>\n";
                    
                    foreach ($data_medios['medios_pago'] as $medio) {
                        echo "<tr>\n";
                        echo "<td>{$medio['id'] ?? 'N/A'}</td>\n";
                        echo "<td><strong>{$medio['nombre']}</strong></td>\n";
                        echo "<td>{$medio['descripcion'] ?? ''}</td>\n";
                        echo "<td>" . ($medio['activo'] ? '✅ Sí' : '❌ No') . "</td>\n";
                        echo "<td>{$medio['fecha_creacion'] ?? 'N/A'}</td>\n";
                        echo "</tr>\n";
                    }
                    
                    echo "</table>\n";
                } else {
                    echo "<p style='color: orange;'>⚠️ No hay medios de pago en esta sucursal</p>\n";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Respuesta inválida o sin medios de pago</p>\n";
                echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response_medios) . "</p>\n";
            }
        }
        
        echo "<hr>\n";
    }
    
    // Verificar medios de pago en BD local actual
    echo "<h3>🏠 Medios de Pago en BD Local Actual:</h3>\n";
    
    try {
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
        $medios_locales = $stmt->fetchAll();
        
        echo "<p><strong>Total medios de pago locales:</strong> " . count($medios_locales) . "</p>\n";
        
        if (!empty($medios_locales)) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr style='background: #f0f0f0;'>\n";
            echo "<th>ID</th><th>Nombre</th><th>Descripción</th><th>Activo</th><th>Fecha Creación</th>\n";
            echo "</tr>\n";
            
            foreach ($medios_locales as $medio) {
                echo "<tr>\n";
                echo "<td>{$medio['id']}</td>\n";
                echo "<td><strong>{$medio['nombre']}</strong></td>\n";
                echo "<td>{$medio['descripcion']}</td>\n";
                echo "<td>" . ($medio['activo'] ? '✅ Sí' : '❌ No') . "</td>\n";
                echo "<td>{$medio['fecha_creacion']}</td>\n";
                echo "</tr>\n";
            }
            
            echo "</table>\n";
        } else {
            echo "<p style='color: orange;'>⚠️ No hay medios de pago en BD local</p>\n";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error BD local: " . $e->getMessage() . "</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
