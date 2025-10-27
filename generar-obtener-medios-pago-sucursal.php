<?php
/**
 * Script para crear obtener-medios-pago-activos.php en cada sucursal
 * Este archivo debe estar en cada sucursal para consultar su BD local
 */

echo "<h2>🔧 Generando archivo obtener-medios-pago-activos.php para sucursales</h2>\n";

$contenido_archivo = '<?php
/**
 * API para obtener medios de pago activos de esta sucursal
 * Utilizado por el instalador para importar medios de pago
 */

header(\'Content-Type: application/json\');
header(\'Access-Control-Allow-Origin: *\');
header(\'Access-Control-Allow-Methods: GET, POST\');
header(\'Access-Control-Allow-Headers: Content-Type\');

try {
    // Incluir configuración local de esta sucursal
    require_once __DIR__ . \'/../config.php\';
    
    // Conectar a la base de datos local de esta sucursal
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Obtener medios de pago activos de esta sucursal
    $stmt = $pdo->prepare("
        SELECT 
            id,
            nombre,
            descripcion,
            activo,
            fecha_creacion
        FROM medios_pago
        WHERE activo = 1
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

// Guardar el archivo
$archivo_destino = 'api-transferencias/obtener-medios-pago-activos-sucursal.php';
file_put_contents($archivo_destino, $contenido_archivo);

echo "<p style='color: green;'>✅ Archivo generado: $archivo_destino</p>\n";

echo "<h3>📋 Instrucciones:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Copiar este archivo</strong> a cada sucursal en la ruta: <code>api-transferencias/obtener-medios-pago-activos.php</code></li>\n";
echo "<li><strong>Reemplazar</strong> el archivo existente en cada sucursal</li>\n";
echo "<li><strong>Verificar</strong> que cada sucursal tenga su propia tabla <code>medios_pago</code></li>\n";
echo "<li><strong>Probar</strong> la importación desde el instalador</li>\n";
echo "</ol>\n";

echo "<h3>🔍 Verificación:</h3>\n";
echo "<p>Para verificar que funciona, ejecuta:</p>\n";
echo "<pre>\n";
echo "curl https://sucursal.ejemplo.com/api-transferencias/obtener-medios-pago-activos.php\n";
echo "</pre>\n";

echo "<h3>⚠️ Nota Importante:</h3>\n";
echo "<p>Este archivo debe estar en <strong>CADA SUCURSAL</strong>, no en el central.</p>\n";
echo "<p>Cada sucursal debe consultar su propia tabla <code>medios_pago</code> local.</p>\n";
?>
