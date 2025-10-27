<?php
/**
 * API para obtener medios de pago activos de esta sucursal
 * Utilizado por el instalador para importar medios de pago
 * 
 * IMPORTANTE: Este archivo debe estar en CADA SUCURSAL, no en el central
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Incluir configuración local de esta sucursal
    require_once __DIR__ . '/../config.php';
    
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
        'success' => true,
        'message' => 'Medios de pago obtenidos exitosamente',
        'total' => count($medios_pago),
        'medios_pago' => $medios_pago
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Error en obtener-medios-pago-activos.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage(),
        'medios_pago' => []
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error general en obtener-medios-pago-activos.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error general: ' . $e->getMessage(),
        'medios_pago' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>
