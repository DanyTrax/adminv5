<?php
/**
 * Script para generar el archivo obtener-medios-pago-activos.php correcto
 * Este archivo debe estar en CADA SUCURSAL
 */

echo "<h2>🔧 Generando Archivo Correcto para Sucursales</h2>\n";

$contenido_archivo = '<?php
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
$archivo_destino = 'obtener-medios-pago-activos.php';
file_put_contents($archivo_destino, $contenido_archivo);

echo "<p style='color: green;'>✅ Archivo generado: $archivo_destino</p>\n";

echo "<h3>📋 Instrucciones de Instalación:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Para cada sucursal:</strong></li>\n";
echo "<ul>\n";
echo "<li>Acceder al servidor de la sucursal</li>\n";
echo "<li>Ir a la carpeta <code>api-transferencias/</code></li>\n";
echo "<li>Reemplazar el archivo <code>obtener-medios-pago-activos.php</code> existente</li>\n";
echo "<li>Verificar que el archivo consulte la BD local de la sucursal</li>\n";
echo "</ul>\n";
echo "<li><strong>Verificar funcionamiento:</strong></li>\n";
echo "<ul>\n";
echo "<li>Probar: <code>https://sucursal.ejemplo.com/api-transferencias/obtener-medios-pago-activos.php</code></li>\n";
echo "<li>Debe devolver JSON con medios de pago de esa sucursal específica</li>\n";
echo "</ul>\n";
echo "</ol>\n";

echo "<h3>🎯 Arquitectura Correcta:</h3>\n";
echo "<ul>\n";
echo "<li><strong>BD Central:</strong> Tabla <code>sucursales</code> (datos de sucursales activas)</li>\n";
echo "<li><strong>BD Local de cada sucursal:</strong> Tabla <code>medios_pago</code> (medios específicos)</li>\n";
echo "<li><strong>Instalador:</strong> Obtiene sucursales del central, conecta a cada sucursal</li>\n";
echo "<li><strong>Cada sucursal:</strong> Devuelve sus propios medios de pago locales</li>\n";
echo "</ul>\n";

echo "<h3>⚠️ Nota Importante:</h3>\n";
echo "<p>Este archivo debe estar en <strong>CADA SUCURSAL</strong>, no en el central.</p>\n";
echo "<p>Cada sucursal debe consultar su propia tabla <code>medios_pago</code> local.</p>\n";
?>
