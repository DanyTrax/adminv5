<?php
/**
 * Script para verificar el flujo correcto del instalador
 */

echo "<h2>🔍 Verificación del Flujo Correcto del Instalador</h2>\n";

try {
    // 1. Obtener sucursales activas desde BD central
    echo "<h3>1. 🏢 Obteniendo sucursales activas desde BD Central:</h3>\n";
    
    require_once 'api-transferencias/conexion-central.php';
    $pdo = ConexionCentral::conectar();
    
    $stmt = $pdo->prepare("
        SELECT 
            id,
            codigo_sucursal,
            nombre,
            url_base,
            url_api
        FROM sucursales
        WHERE activo = 1
        ORDER BY nombre ASC
    ");
    
    $stmt->execute();
    $sucursales = $stmt->fetchAll();
    
    if (empty($sucursales)) {
        echo "<p style='color: red;'>❌ No hay sucursales activas</p>\n";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Sucursales activas encontradas: " . count($sucursales) . "</p>\n";
    
    // Mostrar sucursales
    echo "<h4>📋 Lista de Sucursales Activas:</h4>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'>\n";
    echo "<th>ID</th><th>Código</th><th>Nombre</th><th>URL Base</th><th>URL API</th>\n";
    echo "</tr>\n";
    
    foreach ($sucursales as $sucursal) {
        echo "<tr>\n";
        echo "<td>{$sucursal['id']}</td>\n";
        echo "<td><strong>{$sucursal['codigo_sucursal']}</strong></td>\n";
        echo "<td>{$sucursal['nombre']}</td>\n";
        echo "<td>{$sucursal['url_base']}</td>\n";
        echo "<td>{$sucursal['url_api']}</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // 2. Simular el proceso del instalador
    echo "<h3>2. 🧪 Simulando proceso del instalador:</h3>\n";
    echo "<p><strong>Flujo correcto:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Obtener sucursales activas desde BD central ✅</li>\n";
    echo "<li>Conectar a cada sucursal usando su URL API</li>\n";
    echo "<li>Consultar BD local de cada sucursal para obtener medios_pago</li>\n";
    echo "<li>Importar medios de pago al central</li>\n";
    echo "</ol>\n";
    
    // 3. Probar conexión a cada sucursal
    echo "<h3>3. 🔗 Probando conexión a cada sucursal:</h3>\n";
    
    foreach ($sucursales as $sucursal) {
        echo "<h4>🔍 Sucursal: {$sucursal['nombre']} ({$sucursal['codigo_sucursal']})</h4>\n";
        
        // Construir URL de la API de medios de pago
        $url_medios = rtrim($sucursal['url_api'], '/') . '/obtener-medios-pago-activos.php';
        echo "<p><strong>URL API:</strong> $url_medios</p>\n";
        
        echo "<p><strong>Proceso:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Conectar a: {$sucursal['url_api']}</li>\n";
        echo "<li>Consultar BD local de: {$sucursal['nombre']}</li>\n";
        echo "<li>Obtener tabla: medios_pago</li>\n";
        echo "<li>Devolver JSON con medios de pago locales</li>\n";
        echo "</ul>\n";
        
        // Probar la API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_medios);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p><strong>Resultado:</strong> HTTP Code $http_code</p>\n";
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['success']) && $data['success']) {
                echo "<p style='color: green;'>✅ API funcionando correctamente</p>\n";
                echo "<p><strong>Medios de pago encontrados:</strong> {$data['total']}</p>\n";
                
                if (!empty($data['medios_pago'])) {
                    echo "<h5>💳 Medios de Pago en {$sucursal['nombre']}:</h5>\n";
                    echo "<ul>\n";
                    foreach ($data['medios_pago'] as $medio) {
                        echo "<li><strong>{$medio['nombre']}</strong> (ID: {$medio['id']})</li>\n";
                    }
                    echo "</ul>\n";
                }
                
                echo "<p style='color: green;'>✅ Esta sucursal está lista para importación</p>\n";
                
            } else {
                echo "<p style='color: red;'>❌ API no funciona correctamente</p>\n";
                echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response) . "</p>\n";
            }
        } elseif ($http_code === 500) {
            echo "<p style='color: red;'>❌ Error 500 - Archivo API necesita corrección</p>\n";
            echo "<p><strong>Problema:</strong> El archivo obtener-medios-pago-activos.php en esta sucursal está mal</p>\n";
            echo "<p><strong>Solución:</strong> Corregir el archivo para que consulte BD local</p>\n";
        } else {
            echo "<p style='color: orange;'>⚠️ HTTP Code inesperado: $http_code</p>\n";
        }
        
        echo "<hr>\n";
    }
    
    // 4. Conclusión
    echo "<h3>4. 🎯 Conclusión:</h3>\n";
    echo "<p><strong>Flujo correcto del instalador:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>✅ Obtener sucursales activas desde BD central</li>\n";
    echo "<li>✅ Conectar a cada sucursal usando su URL API</li>\n";
    echo "<li>✅ Consultar BD local de cada sucursal</li>\n";
    echo "<li>✅ Obtener medios_pago de cada sucursal</li>\n";
    echo "<li>✅ Importar al central</li>\n";
    echo "</ol>\n";
    
    echo "<p><strong>Problema identificado:</strong></p>\n";
    echo "<p>El archivo <code>obtener-medios-pago-activos.php</code> en cada sucursal debe:</p>\n";
    echo "<ul>\n";
    echo "<li>Consultar la BD LOCAL de esa sucursal</li>\n";
    echo "<li>No consultar la BD central</li>\n";
    echo "<li>Devolver medios_pago de esa sucursal específica</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
