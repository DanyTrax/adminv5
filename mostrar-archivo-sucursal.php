<?php
/**
 * Script para mostrar el archivo correcto que debe ir en SUC001
 */

echo "<h2>📄 Archivo Correcto para SUC001</h2>\n";

echo "<h3>🔧 Archivo: obtener-medios-pago-activos.php</h3>\n";
echo "<p><strong>Ubicación:</strong> api-transferencias/obtener-medios-pago-activos.php</p>\n";
echo "<p><strong>Propósito:</strong> Obtener medios de pago de la BD local de SUC001</p>\n";

echo "<h3>📝 Contenido Completo:</h3>\n";
echo "<pre style='background: #f5f5f5; padding: 15px; border: 1px solid #ccc; font-family: monospace; font-size: 12px; line-height: 1.4; max-height: 600px; overflow-y: auto;'>\n";
echo htmlspecialchars('<?php
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
?>');
echo "</pre>\n";

echo "<h3>🎯 Características del Archivo Correcto:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Consulta BD local:</strong> Usa config.php de la sucursal</li>\n";
echo "<li>✅ <strong>Tabla correcta:</strong> medios_pago (no medios_pago_central)</li>\n";
echo "<li>✅ <strong>Solo id y nombre:</strong> SELECT id, nombre FROM medios_pago</li>\n";
echo "<li>✅ <strong>Sin filtros:</strong> No usa WHERE activo = 1</li>\n";
echo "<li>✅ <strong>Respuesta JSON:</strong> Formato correcto para el instalador</li>\n";
echo "<li>✅ <strong>Manejo de errores:</strong> Try-catch con logging</li>\n";
echo "<li>✅ <strong>Headers CORS:</strong> Para permitir acceso desde el instalador</li>\n";
echo "</ul>\n";

echo "<h3>📋 Instrucciones de Instalación en SUC001:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Acceder al servidor de SUC001:</strong> pruebas.acrilicosinfinito.com</li>\n";
echo "<li><strong>Ir a la carpeta:</strong> api-transferencias/</li>\n";
echo "<li><strong>Hacer backup del archivo actual:</strong> obtener-medios-pago-activos.php.backup</li>\n";
echo "<li><strong>Reemplazar con el archivo correcto:</strong> Copiar el contenido de arriba</li>\n";
echo "<li><strong>Verificar permisos:</strong> 644 o 755</li>\n";
echo "<li><strong>Probar la API:</strong> https://pruebas.acrilicosinfinito.com/api-transferencias/obtener-medios-pago-activos.php</li>\n";
echo "</ol>\n";

echo "<h3>🧪 Prueba de Funcionamiento:</h3>\n";
echo "<p>Después de instalar, la API debe devolver:</p>\n";
echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #4caf50;'>\n";
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

echo "<h3>⚠️ Nota Importante:</h3>\n";
echo "<p>Este archivo debe estar en <strong>CADA SUCURSAL</strong>, no en el central.</p>\n";
echo "<p>Cada sucursal debe consultar su propia tabla <code>medios_pago</code> local.</p>\n";
echo "<p>El instalador conectará a cada sucursal para obtener sus medios de pago.</p>\n";
?>
