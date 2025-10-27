<?php
/**
 * Script de prueba para verificar la importación de medios de pago
 * desde sucursales activas de la BD local
 */

require_once 'config.php';

try {
    // Conectar a la base de datos
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<h2>🔍 Verificación de Sucursales Activas</h2>\n";
    
    // Obtener sucursales activas
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            s.codigo_sucursal,
            s.nombre,
            s.url_base,
            s.url_api,
            s.activo
        FROM sucursales s
        WHERE s.activo = 1
        ORDER BY s.nombre ASC
    ");
    
    $stmt->execute();
    $sucursales_activas = $stmt->fetchAll();
    
    echo "<p><strong>Total de sucursales activas:</strong> " . count($sucursales_activas) . "</p>\n";
    
    if (empty($sucursales_activas)) {
        echo "<p style='color: orange;'>⚠️ No hay sucursales activas en la BD local</p>\n";
        exit;
    }
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'>\n";
    echo "<th>Código</th><th>Nombre</th><th>URL Base</th><th>URL API</th><th>Estado</th>\n";
    echo "</tr>\n";
    
    foreach ($sucursales_activas as $sucursal) {
        echo "<tr>\n";
        echo "<td>{$sucursal['codigo_sucursal']}</td>\n";
        echo "<td>{$sucursal['nombre']}</td>\n";
        echo "<td>{$sucursal['url_base']}</td>\n";
        echo "<td>{$sucursal['url_api']}</td>\n";
        echo "<td>" . ($sucursal['activo'] ? '✅ Activo' : '❌ Inactivo') . "</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    echo "<h2>💳 Verificación de Medios de Pago Actuales</h2>\n";
    
    // Obtener medios de pago actuales
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nombre,
            descripcion,
            activo,
            fecha_creacion
        FROM medios_pago
        ORDER BY nombre ASC
    ");
    
    $stmt->execute();
    $medios_actuales = $stmt->fetchAll();
    
    echo "<p><strong>Total de medios de pago actuales:</strong> " . count($medios_actuales) . "</p>\n";
    
    if (!empty($medios_actuales)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background: #f0f0f0;'>\n";
        echo "<th>ID</th><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Fecha Creación</th>\n";
        echo "</tr>\n";
        
        foreach ($medios_actuales as $medio) {
            echo "<tr>\n";
            echo "<td>{$medio['id']}</td>\n";
            echo "<td>{$medio['nombre']}</td>\n";
            echo "<td>{$medio['descripcion']}</td>\n";
            echo "<td>" . ($medio['activo'] ? '✅ Activo' : '❌ Inactivo') . "</td>\n";
            echo "<td>{$medio['fecha_creacion']}</td>\n";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay medios de pago configurados</p>\n";
    }
    
    echo "<h2>🔧 Prueba de Conexión a APIs</h2>\n";
    
    foreach ($sucursales_activas as $sucursal) {
        echo "<h3>Probando: {$sucursal['nombre']}</h3>\n";
        
        $url_api = rtrim($sucursal['url_api'], '/') . '/obtener-medios-pago-activos.php';
        echo "<p><strong>URL API:</strong> $url_api</p>\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_api);
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
                echo "<p style='color: green;'>✅ Conexión exitosa - {$data['total']} medios de pago disponibles</p>\n";
            } else {
                echo "<p style='color: orange;'>⚠️ Respuesta inválida</p>\n";
            }
        }
        
        echo "<hr>\n";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de base de datos: " . $e->getMessage() . "</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
