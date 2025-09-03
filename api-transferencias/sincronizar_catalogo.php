<?php
header('Content-Type: application/json; charset=utf-8');
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
    
    // ✅ CONECTAR A BD LOCAL DE ESTA SUCURSAL
    require_once "../modelos/conexion.php";
    $pdo = Conexion::conectar();
    
    $pdo->beginTransaction();
    
    // Procesar catálogo YA PROCESADO desde central
    $catalogoProcesado = $datos['catalogo'];
    $origen = $datos['origen']['codigo'] ?? 'CENTRAL';
    
    // Estadísticas de proceso
    $productosActualizados = 0;
    $productosErrores = 0;
    $productosNuevos = 0;
    
    // ✅ APLICAR LA MISMA LÓGICA EXACTA DE TU SINCRONIZACIÓN LOCAL
    foreach ($catalogoProcesado as $index => $productoLocal) {
        
        try {
            
            // ✅ USAR LA MISMA QUERY EXACTA DE TU MÉTODO LOCAL
            $stmt = $pdo->prepare("
                INSERT INTO productos (
                    id_categoria, 
                    codigo, 
                    codigo_maestro, 
                    descripcion, 
                    imagen, 
                    stock, 
                    precio_venta, 
                    ventas, 
                    es_divisible, 
                    nombre_mitad, 
                    precio_mitad, 
                    nombre_tercio, 
                    precio_tercio, 
                    nombre_cuarto, 
                    precio_cuarto
                ) VALUES (
                    :id_categoria, :codigo, :codigo_maestro, :descripcion, :imagen,
                    :stock, :precio_venta, :ventas, :es_divisible,
                    :nombre_mitad, :precio_mitad, :nombre_tercio, :precio_tercio, :nombre_cuarto, :precio_cuarto
                ) ON DUPLICATE KEY UPDATE
                    id_categoria = VALUES(id_categoria),
                    descripcion = VALUES(descripcion),
                    imagen = VALUES(imagen),
                    precio_venta = VALUES(precio_venta),
                    es_divisible = VALUES(es_divisible),
                    nombre_mitad = VALUES(nombre_mitad),
                    precio_mitad = VALUES(precio_mitad),
                    nombre_tercio = VALUES(nombre_tercio),
                    precio_tercio = VALUES(precio_tercio),
                    nombre_cuarto = VALUES(nombre_cuarto),
                    precio_cuarto = VALUES(precio_cuarto)
            ");
            
            // ✅ MAPEAR DATOS EXACTAMENTE COMO TU ESTRUCTURA LOCAL
            $resultadoInsert = $stmt->execute([
                ':id_categoria' => $productoLocal['id_categoria'],
                ':codigo' => $productoLocal['codigo'],
                ':codigo_maestro' => $productoLocal['codigo_maestro'],
                ':descripcion' => $productoLocal['descripcion'],
                ':imagen' => $productoLocal['imagen'],
                ':stock' => $productoLocal['stock'],
                ':precio_venta' => $productoLocal['precio_venta'],
                ':ventas' => $productoLocal['ventas'],
                ':es_divisible' => $productoLocal['es_divisible'],
                ':nombre_mitad' => $productoLocal['nombre_mitad'],
                ':precio_mitad' => $productoLocal['precio_mitad'],
                ':nombre_tercio' => $productoLocal['nombre_tercio'],
                ':precio_tercio' => $productoLocal['precio_tercio'],
                ':nombre_cuarto' => $productoLocal['nombre_cuarto'],
                ':precio_cuarto' => $productoLocal['precio_cuarto']
            ]);
            
            if ($resultadoInsert) {
                // Verificar si fue inserción nueva o actualización
                if ($pdo->lastInsertId() > 0) {
                    $productosNuevos++;
                }
                $productosActualizados++;
            }
            
        } catch (Exception $e) {
            $productosErrores++;
            error_log("ERROR procesando producto " . ($productoLocal['codigo'] ?? 'sin código') . ": " . $e->getMessage());
        }
    }
    
    $pdo->commit();
    
    // ✅ RESPUESTA IDÉNTICA A TU SINCRONIZACIÓN LOCAL
    echo json_encode([
        'success' => true,
        'message' => "Catálogo sincronizado exitosamente desde {$origen}",
        'estadisticas' => [
            'productos_procesados' => $productosActualizados,
            'productos_nuevos' => $productosNuevos,
            'productos_errores' => $productosErrores,
            'total_recibidos' => count($catalogoProcesado)
        ],
        'origen' => $origen,
        'timestamp' => date('Y-m-d H:i:s'),
        'version_api' => '5.0'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("ERROR CRÍTICO en sincronizar_catalogo.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>