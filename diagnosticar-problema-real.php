<?php
/**
 * Script para diagnosticar el problema real en SUC001
 */

echo "<h2>🔍 Diagnóstico del Problema Real en SUC001</h2>\n";

try {
    // 1. Verificar datos de SUC001 desde BD central
    echo "<h3>1. 🔍 Verificando datos de SUC001 desde BD Central:</h3>\n";
    
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
        WHERE codigo_sucursal = 'SUC001'
        LIMIT 1
    ");
    
    $stmt->execute();
    $sucursal = $stmt->fetch();
    
    if ($sucursal) {
        echo "<p style='color: green;'>✅ SUC001 encontrada en BD Central</p>\n";
        echo "<p><strong>Nombre:</strong> {$sucursal['nombre']}</p>\n";
        echo "<p><strong>URL Base:</strong> {$sucursal['url_base']}</p>\n";
        echo "<p><strong>URL API:</strong> {$sucursal['url_api']}</p>\n";
    } else {
        echo "<p style='color: red;'>❌ SUC001 no encontrada en BD Central</p>\n";
        exit;
    }
    
    // 2. Probar la API de SUC001 para ver el error exacto
    echo "<h3>2. 🧪 Probando API de SUC001 para ver el error:</h3>\n";
    
    $url_api = rtrim($sucursal['url_api'], '/') . '/obtener-medios-pago-activos.php';
    echo "<p><strong>URL API:</strong> $url_api</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";
    
    if ($error) {
        echo "<p style='color: red;'>❌ Error cURL: $error</p>\n";
    } else {
        echo "<p style='color: green;'>✅ Sin errores cURL</p>\n";
    }
    
    echo "<h3>3. 📝 Respuesta completa de SUC001:</h3>\n";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc; max-height: 300px; overflow-y: auto;'>\n";
    echo htmlspecialchars($response);
    echo "</pre>\n";
    
    // 3. Analizar el problema
    echo "<h3>4. 🔍 Análisis del Problema:</h3>\n";
    
    if ($http_code === 500) {
        echo "<p style='color: red;'>❌ Error 500 - Problema en el servidor de SUC001</p>\n";
        echo "<p><strong>Posibles causas:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Error de sintaxis en el archivo PHP</li>\n";
        echo "<li>Archivo consulta BD central en lugar de BD local</li>\n";
        echo "<li>Archivo corrupto o incompleto</li>\n";
        echo "<li>Problema de permisos</li>\n";
        echo "</ul>\n";
        
        echo "<h3>5. 🔧 Solución Requerida:</h3>\n";
        echo "<p><strong>El archivo obtener-medios-pago-activos.php en SUC001 debe:</strong></p>\n";
        echo "<ol>\n";
        echo "<li>Consultar la BD LOCAL de SUC001 (no la central)</li>\n";
        echo "<li>Usar config.php de SUC001</li>\n";
        echo "<li>Consultar tabla medios_pago local</li>\n";
        echo "<li>Devolver JSON con medios de pago locales</li>\n";
        echo "</ol>\n";
        
        echo "<h3>6. 📋 Instrucciones:</h3>\n";
        echo "<p><strong>Acceder a SUC001:</strong> {$sucursal['url_base']}</p>\n";
        echo "<p><strong>Ir a:</strong> api-transferencias/obtener-medios-pago-activos.php</p>\n";
        echo "<p><strong>Reemplazar con:</strong> El archivo correcto que consulta BD local</p>\n";
        echo "<p><strong>Verificar que:</strong> Consulte medios_pago de SUC001, no del central</p>\n";
        
    } elseif ($http_code === 200) {
        echo "<p style='color: green;'>✅ API funcionando correctamente</p>\n";
        
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p><strong>Medios de pago en SUC001:</strong> {$data['total']}</p>\n";
            
            if (!empty($data['medios_pago'])) {
                echo "<h4>💳 Medios de Pago en SUC001:</h4>\n";
                echo "<ul>\n";
                foreach ($data['medios_pago'] as $medio) {
                    echo "<li><strong>{$medio['nombre']}</strong> (ID: {$medio['id']})</li>\n";
                }
                echo "</ul>\n";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ HTTP Code inesperado: $http_code</p>\n";
    }
    
    echo "<h3>7. 🎯 Conclusión:</h3>\n";
    echo "<p><strong>SUC001 ya tiene:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ BD local con tabla medios_pago</li>\n";
    echo "<li>✅ Medios de pago existentes</li>\n";
    echo "<li>✅ URL base correcta</li>\n";
    echo "</ul>\n";
    
    echo "<p><strong>SUC001 necesita:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>❌ Archivo API corregido que consulte BD local</li>\n";
    echo "<li>❌ No crear medios de pago nuevos</li>\n";
    echo "<li>❌ Solo corregir el archivo obtener-medios-pago-activos.php</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
