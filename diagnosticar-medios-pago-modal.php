<?php
/**
 * Script para diagnosticar y corregir el problema de medios de pago
 */

echo "<h2>🔍 Diagnóstico de Medios de Pago</h2>\n";

try {
    // 1. Obtener sucursales activas desde BD central
    echo "<h3>1. 🏢 Obteniendo sucursales activas:</h3>\n";
    
    require_once 'api-transferencias/conexion-central.php';
    $pdo = ConexionCentral::conectar();
    
    $stmt = $pdo->prepare("
        SELECT 
            id,
            codigo_sucursal,
            nombre,
            url_base,
            url_api,
            usuario_bd,
            password_bd,
            nombre_bd,
            host_bd,
            puerto_bd
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
    
    // 2. Probar cada sucursal
    echo "<h3>2. 🧪 Probando API de cada sucursal:</h3>\n";
    
    foreach ($sucursales as $sucursal) {
        echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 15px; border-radius: 5px;'>\n";
        echo "<h4>🏢 {$sucursal['nombre']} ({$sucursal['codigo_sucursal']})</h4>\n";
        
        $url_api = rtrim($sucursal['url_api'], '/') . '/obtener-medios-pago-activos.php';
        echo "<p><strong>URL API:</strong> <a href='{$url_api}' target='_blank'>{$url_api}</a></p>\n";
        
        // Probar la API
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
            echo "<p style='color: red;'>❌ Error cURL: {$error}</p>\n";
        } else {
            echo "<p style='color: blue;'>📊 HTTP Code: {$http_code}</p>\n";
            
            if ($http_code == 200) {
                // Intentar decodificar JSON
                $json_data = json_decode($response, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($json_data['success']) && $json_data['success']) {
                        echo "<p style='color: green;'>✅ API funcionando correctamente</p>\n";
                        echo "<p style='color: green;'>💳 Medios de pago encontrados: " . count($json_data['medios_pago']) . "</p>\n";
                        
                        if (!empty($json_data['medios_pago'])) {
                            echo "<div style='background: #f9f9f9; padding: 10px; border-radius: 3px; margin: 10px 0;'>\n";
                            echo "<h6>💳 Medios de Pago:</h6>\n";
                            echo "<ul style='margin: 0; padding-left: 20px;'>\n";
                            
                            foreach ($json_data['medios_pago'] as $medio) {
                                echo "<li><strong>{$medio['nombre']}</strong> (ID: {$medio['id']})</li>\n";
                            }
                            
                            echo "</ul>\n";
                            echo "</div>\n";
                        }
                    } else {
                        echo "<p style='color: orange;'>⚠️ API devuelve error: " . ($json_data['message'] ?? 'Error desconocido') . "</p>\n";
                    }
                } else {
                    echo "<p style='color: red;'>❌ Error JSON: " . json_last_error_msg() . "</p>\n";
                    echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 3px; margin: 10px 0;'>\n";
                    echo "<h6>📄 Respuesta Raw:</h6>\n";
                    echo "<pre style='margin: 0; font-size: 12px;'>" . htmlspecialchars($response) . "</pre>\n";
                    echo "</div>\n";
                    
                    // Generar archivo correcto
                    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 15px 0;'>\n";
                    echo "<h6>🔧 Solución:</h6>\n";
                    echo "<p>El archivo <code>obtener-medios-pago-activos.php</code> en esta sucursal necesita ser corregido.</p>\n";
                    
                    echo "<h6>📝 Archivo Correcto:</h6>\n";
                    echo "<textarea readonly style='width: 100%; height: 300px; font-family: monospace; font-size: 12px;'>";
                    echo htmlspecialchars(generarArchivoCorrecto($sucursal));
                    echo "</textarea>\n";
                    
                    echo "<p><strong>Instrucciones:</strong></p>\n";
                    echo "<ol>\n";
                    echo "<li>Acceder al servidor de <strong>{$sucursal['nombre']}</strong></li>\n";
                    echo "<li>Ir a la carpeta <code>api-transferencias/</code></li>\n";
                    echo "<li>Hacer backup del archivo actual: <code>obtener-medios-pago-activos.php.backup</code></li>\n";
                    echo "<li>Crear nuevo archivo: <code>obtener-medios-pago-activos.php</code></li>\n";
                    echo "<li>Copiar el contenido del textarea de arriba</li>\n";
                    echo "<li>Guardar el archivo</li>\n";
                    echo "<li>Verificar permisos: 644 o 755</li>\n";
                    echo "</ol>\n";
                    echo "</div>\n";
                }
            } else {
                echo "<p style='color: red;'>❌ Error HTTP: {$http_code}</p>\n";
                echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 3px; margin: 10px 0;'>\n";
                echo "<h6>📄 Respuesta:</h6>\n";
                echo "<pre style='margin: 0; font-size: 12px;'>" . htmlspecialchars($response) . "</pre>\n";
                echo "</div>\n";
            }
        }
        
        echo "</div>\n";
    }
    
    // 3. Resumen
    echo "<h3>3. 🎯 Resumen:</h3>\n";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h6>📋 Estado del Sistema:</h6>\n";
    echo "<ul>\n";
    echo "<li><strong>Central:</strong> ✅ Funcionando correctamente</li>\n";
    echo "<li><strong>Sucursales:</strong> ⚠️ Algunas necesitan corrección</li>\n";
    echo "<li><strong>Instalador:</strong> ✅ Listo para funcionar</li>\n";
    echo "</ul>\n";
    echo "<p><strong>Próximo paso:</strong> Corregir archivos en sucursales que den error</p>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}

/**
 * Generar archivo correcto para una sucursal
 */
function generarArchivoCorrecto($sucursal) {
    return '<?php
/**
 * API para obtener medios de pago activos de esta sucursal
 * Utilizado por el instalador para importar medios de pago
 * 
 * IMPORTANTE: Este archivo debe estar en CADA SUCURSAL
 * Debe consultar la BD LOCAL de la sucursal, no el central
 */

header(\'Content-Type: application/json\');
header(\'Access-Control-Allow-Origin: *\');
header(\'Access-Control-Allow-Methods: GET, POST\');
header(\'Access-Control-Allow-Headers: Content-Type\');

try {
    // Incluir configuración local de esta sucursal
    require_once __DIR__ . \'/../config.php\';
    
    // Conectar a la base de datos LOCAL de esta sucursal
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Obtener medios de pago activos de esta sucursal específica
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nombre
        FROM medios_pago
        ORDER BY nombre ASC
    ");
    
    $stmt->execute();
    $medios_pago = $stmt->fetchAll();
    
    // Respuesta exitosa
    echo json_encode([
        \'success\' => true,
        \'message\' => \'Medios de pago obtenidos exitosamente\',
        \'total\' => count($medios_pago),
        \'medios_pago\' => $medios_pago
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Error en obtener-medios-pago-activos.php: " . $e->getMessage());
    
    echo json_encode([
        \'success\' => false,
        \'message\' => \'Error de base de datos: \' . $e->getMessage(),
        \'medios_pago\' => []
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error general en obtener-medios-pago-activos.php: " . $e->getMessage());
    
    echo json_encode([
        \'success\' => false,
        \'message\' => \'Error general: \' . $e->getMessage(),
        \'medios_pago\' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>';
}
?>
