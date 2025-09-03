<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    
    $input = file_get_contents('php://input');
    $datos = json_decode($input, true);
    
    if (!$datos || !isset($datos['accion']) || $datos['accion'] !== 'sincronizar_catalogo') {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    require_once "conexion-central.php";
    $pdo = ConexionCentral::conectar();
    
    // Procesar catálogo recibido
    $catalogo = $datos['catalogo'];
    $origen = $datos['origen']['codigo'] ?? 'DESCONOCIDO';
    
    // Actualizar productos locales
    $productosActualizados = 0;
    
    foreach ($catalogo as $producto) {
        
        $stmt = $pdo->prepare("REPLACE INTO productos (
            codigo, descripcion, categoria, precio_compra, precio_venta,
            stock, unidad_medida, activo, fecha_actualizacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())");
        
        $stmt->execute([
            $producto['codigo'],
            $producto['descripcion'],
            $producto['categoria'],
            $producto['precio_compra'],
            $producto['precio_venta'],
            $producto['stock'],
            $producto['unidad_medida']
        ]);
        
        $productosActualizados++;
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Catálogo sincronizado correctamente",
        'productos_procesados' => $productosActualizados,
        'origen' => $origen,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log("Error en sincronizar_catalogo.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>