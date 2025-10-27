<?php
/**
 * Script para verificar todas las sucursales con sus URLs correctas
 */

echo "<h2>🔍 Verificación de Todas las Sucursales</h2>\n";

try {
    // 1. Obtener todas las sucursales activas desde la BD CENTRAL
    echo "<h3>1. 🔍 Obteniendo sucursales activas desde BD Central:</h3>\n";
    
    // Incluir conexion-central para conectar a BD central
    require_once 'api-transferencias/conexion-central.php';
    
    // Conectar a la base de datos central
    $pdo = ConexionCentral::conectar();
    
    // Obtener todas las sucursales activas
    $stmt = $pdo->prepare("
        SELECT 
            id,
            codigo_sucursal,
            nombre,
            url_base,
            url_api,
            activo
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
    
    // Mostrar tabla de sucursales
    echo "<h3>2. 📋 Tabla de Sucursales:</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'>\n";
    echo "<th>ID</th><th>Código</th><th>Nombre</th><th>URL Base</th><th>URL API</th><th>Activo</th>\n";
    echo "</tr>\n";
    
    foreach ($sucursales as $sucursal) {
        echo "<tr>\n";
        echo "<td>{$sucursal['id']}</td>\n";
        echo "<td><strong>{$sucursal['codigo_sucursal']}</strong></td>\n";
        echo "<td>{$sucursal['nombre']}</td>\n";
        echo "<td>{$sucursal['url_base']}</td>\n";
        echo "<td>{$sucursal['url_api']}</td>\n";
        echo "<td>" . ($sucursal['activo'] ? '✅ Sí' : '❌ No') . "</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // 3. Probar medios de pago de cada sucursal
    echo "<h3>3. 🧪 Probando medios de pago de cada sucursal:</h3>\n";
    
    foreach ($sucursales as $sucursal) {
        echo "<h4>🔍 Sucursal: {$sucursal['nombre']} ({$sucursal['codigo_sucursal']})</h4>\n";
        
        // Construir URL correcta usando url_api
        $url_medios = rtrim($sucursal['url_api'], '/') . '/obtener-medios-pago-activos.php';
        echo "<p><strong>URL construida:</strong> $url_medios</p>\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_medios);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response_medios = curl_exec($ch);
        $http_code_medios = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error_medios = curl_error($ch);
        curl_close($ch);
        
        if ($error_medios) {
            echo "<p style='color: red;'>❌ Error cURL: $error_medios</p>\n";
        } elseif ($http_code_medios === 200) {
            $data_medios = json_decode($response_medios, true);
            if ($data_medios && isset($data_medios['success']) && $data_medios['success']) {
                echo "<p style='color: green;'>✅ Medios de pago: {$data_medios['total']}</p>\n";
                
                if (!empty($data_medios['medios_pago'])) {
                    echo "<ul>\n";
                    foreach ($data_medios['medios_pago'] as $medio) {
                        echo "<li><strong>{$medio['nombre']}</strong> (ID: {$medio['id']})</li>\n";
                    }
                    echo "</ul>\n";
                }
            } else {
                echo "<p style='color: red;'>❌ Error en respuesta de medios de pago</p>\n";
                echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response_medios) . "</p>\n";
            }
        } elseif ($http_code_medios === 500) {
            echo "<p style='color: red;'>❌ Error 500 - Archivo necesita corrección</p>\n";
            echo "<p><strong>Problema:</strong> El archivo obtener-medios-pago-activos.php necesita ser corregido</p>\n";
        } else {
            echo "<p style='color: orange;'>⚠️ HTTP Code inesperado: $http_code_medios</p>\n";
            echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response_medios) . "</p>\n";
        }
        
        echo "<hr>\n";
    }
    
    // 4. Conclusión
    echo "<h3>4. 🎯 Conclusión:</h3>\n";
    echo "<p>Para que el instalador funcione correctamente:</p>\n";
    echo "<ol>\n";
    echo "<li><strong>Cada sucursal debe tener:</strong> El archivo corregido obtener-medios-pago-activos.php</li>\n";
    echo "<li><strong>Instalador debe:</strong> Conectar a cada sucursal usando su URL API correcta</li>\n";
    echo "<li><strong>Importar al central:</strong> Los medios de pago de las sucursales seleccionadas</li>\n";
    echo "</ol>\n";
    
    echo "<h3>📋 URLs Correctas Identificadas:</h3>\n";
    foreach ($sucursales as $sucursal) {
        $url_medios = rtrim($sucursal['url_api'], '/') . '/obtener-medios-pago-activos.php';
        echo "<p><strong>{$sucursal['nombre']}:</strong> $url_medios</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
