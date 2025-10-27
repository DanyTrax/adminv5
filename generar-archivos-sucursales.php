<?php
/**
 * Script para generar archivo correcto para sucursales
 */

echo "<h2>🔧 Generador de Archivo Correcto</h2>\n";

try {
    // 1. Obtener sucursales activas
    echo "<h3>1. 🏢 Obteniendo sucursales activas:</h3>\n";
    
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
    
    // 2. Generar archivo para cada sucursal
    echo "<h3>2. 📝 Generando archivos correctos:</h3>\n";
    
    foreach ($sucursales as $sucursal) {
        echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 15px; border-radius: 5px;'>\n";
        echo "<h4>🏢 {$sucursal['nombre']} ({$sucursal['codigo_sucursal']})</h4>\n";
        
        $archivo_correcto = generarArchivoCorrecto();
        
        echo "<div style='background: #f9f9f9; padding: 10px; border-radius: 3px; margin: 10px 0;'>\n";
        echo "<h6>📄 Archivo: obtener-medios-pago-activos.php</h6>\n";
        echo "<textarea readonly style='width: 100%; height: 200px; font-family: monospace; font-size: 12px;'>";
        echo htmlspecialchars($archivo_correcto);
        echo "</textarea>\n";
        echo "</div>\n";
        
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 3px; margin: 10px 0;'>\n";
        echo "<h6>📋 Instrucciones de Instalación:</h6>\n";
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
        
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 3px; margin: 10px 0;'>\n";
        echo "<h6>🧪 Prueba de Funcionamiento:</h6>\n";
        echo "<p>Después de instalar, probar esta URL:</p>\n";
        echo "<p><a href='{$sucursal['url_api']}obtener-medios-pago-activos.php' target='_blank'>{$sucursal['url_api']}obtener-medios-pago-activos.php</a></p>\n";
        echo "<p><strong>Respuesta esperada:</strong></p>\n";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px; font-size: 12px;'>";
        echo htmlspecialchars('{
    "success": true,
    "message": "Medios de pago obtenidos exitosamente",
    "total": 3,
    "medios_pago": [
        {
            "id": "1",
            "nombre": "Efectivo"
        },
        {
            "id": "2",
            "nombre": "Tarjeta de Crédito"
        }
    ]
}');
        echo "</pre>\n";
        echo "</div>\n";
        
        echo "</div>\n";
    }
    
    // 3. Resumen
    echo "<h3>3. 🎯 Resumen:</h3>\n";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h6>📋 Próximos Pasos:</h6>\n";
    echo "<ol>\n";
    echo "<li><strong>Instalar archivos:</strong> En cada sucursal que dé error</li>\n";
    echo "<li><strong>Probar APIs:</strong> Verificar que devuelvan JSON válido</li>\n";
    echo "<li><strong>Probar instalador:</strong> El modal debería funcionar correctamente</li>\n";
    echo "<li><strong>Importar medios:</strong> Seleccionar sucursales y importar</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}

/**
 * Generar archivo correcto
 */
function generarArchivoCorrecto() {
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
