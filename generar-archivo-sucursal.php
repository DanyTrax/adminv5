<?php
/**
 * Script para generar el archivo correcto para SUC001
 */

echo "<h2>📄 Generador de Archivo Correcto para SUC001</h2>\n";

echo "<h3>🔧 Archivo: obtener-medios-pago-activos.php</h3>\n";
echo "<p><strong>Para instalar en:</strong> https://pruebas.acrilicosinfinito.com/api-transferencias/</p>\n";

echo "<h3>📝 Contenido Completo del Archivo:</h3>\n";
echo "<textarea style='width: 100%; height: 400px; font-family: monospace; font-size: 12px;' readonly>\n";
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
echo "</textarea>\n";

echo "<h3>📋 Instrucciones de Instalación:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Acceder al servidor de SUC001:</strong> pruebas.acrilicosinfinito.com</li>\n";
echo "<li><strong>Ir a la carpeta:</strong> api-transferencias/</li>\n";
echo "<li><strong>Hacer backup del archivo actual:</strong> obtener-medios-pago-activos.php.backup</li>\n";
echo "<li><strong>Crear nuevo archivo:</strong> obtener-medios-pago-activos.php</li>\n";
echo "<li><strong>Copiar el contenido:</strong> Del textarea de arriba</li>\n";
echo "<li><strong>Guardar el archivo</strong></li>\n";
echo "<li><strong>Verificar permisos:</strong> 644 o 755</li>\n";
echo "</ol>\n";

echo "<h3>🧪 Prueba de Funcionamiento:</h3>\n";
echo "<p>Después de instalar, probar esta URL:</p>\n";
echo "<p><strong>URL:</strong> <a href='https://pruebas.acrilicosinfinito.com/api-transferencias/obtener-medios-pago-activos.php' target='_blank'>https://pruebas.acrilicosinfinito.com/api-transferencias/obtener-medios-pago-activos.php</a></p>\n";

echo "<h3>✅ Respuesta Esperada:</h3>\n";
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

echo "<h3>🎯 Después de la Instalación:</h3>\n";
echo "<p>Una vez instalado el archivo correcto:</p>\n";
echo "<ol>\n";
echo "<li><strong>Probar la URL:</strong> Debe devolver JSON con medios de pago</li>\n";
echo "<li><strong>Ejecutar verificación:</strong> verificar-sucursal-suc001.php</li>\n";
echo "<li><strong>Probar instalador:</strong> Debe importar medios de SUC001</li>\n";
echo "</ol>\n";

echo "<h3>⚠️ Nota Importante:</h3>\n";
echo "<p>Este archivo debe estar en <strong>CADA SUCURSAL</strong>, no en el central.</p>\n";
echo "<p>Cada sucursal debe consultar su propia tabla <code>medios_pago</code> local.</p>\n";
echo "<p>El instalador conectará a cada sucursal para obtener sus medios de pago.</p>\n";
?>