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
    
    // ✅ DEBUG: Log inicial
    error_log("DEBUG SYNC: Iniciando sincronización de " . count($catalogoProcesado) . " productos desde {$origen}");
    
    foreach ($catalogoProcesado as $index => $productoLocal) {
        
        try {
            
            // ✅ VERIFICAR SI EL PRODUCTO EXISTE BASÁNDOSE EN EL CÓDIGO ÚNICO
            $stmtExistente = $pdo->prepare("
                SELECT id, stock, ventas, precio_venta, descripcion 
                FROM productos 
                WHERE codigo = :codigo
            ");
            $stmtExistente->execute([':codigo' => $productoLocal['codigo']]);
            $productoExistente = $stmtExistente->fetch(PDO::FETCH_ASSOC);
            
            if ($productoExistente) {
                
                // ✅ PRODUCTO EXISTE - ACTUALIZAR PRESERVANDO STOCK Y VENTAS
                $stmt = $pdo->prepare("
                    UPDATE productos SET
                        id_categoria = :id_categoria,
                        codigo_maestro = :codigo_maestro,
                        descripcion = :descripcion,
                        imagen = :imagen,
                        precio_venta = :precio_venta,
                        es_divisible = :es_divisible,
                        nombre_mitad = :nombre_mitad,
                        precio_mitad = :precio_mitad,
                        nombre_tercio = :nombre_tercio,
                        precio_tercio = :precio_tercio,
                        nombre_cuarto = :nombre_cuarto,
                        precio_cuarto = :precio_cuarto
                    WHERE codigo = :codigo
                ");
                
                $resultado = $stmt->execute([
                    ':id_categoria' => $productoLocal['id_categoria'],
                    ':codigo_maestro' => $productoLocal['codigo_maestro'],
                    ':descripcion' => $productoLocal['descripcion'],
                    ':imagen' => $productoLocal['imagen'],
                    ':precio_venta' => $productoLocal['precio_venta'],
                    ':es_divisible' => $productoLocal['es_divisible'],
                    ':nombre_mitad' => $productoLocal['nombre_mitad'],
                    ':precio_mitad' => $productoLocal['precio_mitad'] ?? 0,
                    ':nombre_tercio' => $productoLocal['nombre_tercio'],
                    ':precio_tercio' => $productoLocal['precio_tercio'] ?? 0,
                    ':nombre_cuarto' => $productoLocal['nombre_cuarto'],
                    ':precio_cuarto' => $productoLocal['precio_cuarto'] ?? 0,
                    ':codigo' => $productoLocal['codigo']
                ]);
                
                if ($resultado && $stmt->rowCount() > 0) {
                    $productosActualizados++;
                }
                
                // ✅ DEBUG para productos actualizados
                if ($index < 3) {
                    error_log("DEBUG SYNC: ACTUALIZADO - Código: {$productoLocal['codigo']}, Stock preservado: {$productoExistente['stock']}, Ventas: {$productoExistente['ventas']}");
                }
                
            } else {
                
                // ✅ PRODUCTO NUEVO - INSERTAR CON STOCK Y VENTAS EN 0
                $stmt = $pdo->prepare("
                    INSERT INTO productos (
                        id_categoria, codigo, codigo_maestro, descripcion, imagen,
                        stock, precio_venta, ventas, es_divisible,
                        nombre_mitad, precio_mitad, nombre_tercio, precio_tercio,
                        nombre_cuarto, precio_cuarto
                    ) VALUES (
                        :id_categoria, :codigo, :codigo_maestro, :descripcion, :imagen,
                        0, :precio_venta, 0, :es_divisible,
                        :nombre_mitad, :precio_mitad, :nombre_tercio, :precio_tercio,
                        :nombre_cuarto, :precio_cuarto
                    )
                ");
                
                $resultado = $stmt->execute([
                    ':id_categoria' => $productoLocal['id_categoria'],
                    ':codigo' => $productoLocal['codigo'],
                    ':codigo_maestro' => $productoLocal['codigo_maestro'],
                    ':descripcion' => $productoLocal['descripcion'],
                    ':imagen' => $productoLocal['imagen'],
                    ':precio_venta' => $productoLocal['precio_venta'],
                    ':es_divisible' => $productoLocal['es_divisible'],
                    ':nombre_mitad' => $productoLocal['nombre_mitad'],
                    ':precio_mitad' => $productoLocal['precio_mitad'] ?? 0,
                    ':nombre_tercio' => $productoLocal['nombre_tercio'],
                    ':precio_tercio' => $productoLocal['precio_tercio'] ?? 0,
                    ':nombre_cuarto' => $productoLocal['nombre_cuarto'],
                    ':precio_cuarto' => $productoLocal['precio_cuarto'] ?? 0
                ]);
                
                if ($resultado) {
                    $productosNuevos++;
                }
                
                // ✅ DEBUG para productos nuevos
                if ($index < 3) {
                    error_log("DEBUG SYNC: NUEVO - Código: {$productoLocal['codigo']}, Descripción: {$productoLocal['descripcion']}");
                }
            }
            
        } catch (Exception $e) {
            $productosErrores++;
            error_log("ERROR procesando producto {$productoLocal['codigo']}: " . $e->getMessage());
        }
    }
    
    $pdo->commit();
    
    // ✅ LOG FINAL
    error_log("DEBUG SYNC: Finalizando - Nuevos: {$productosNuevos}, Actualizados: {$productosActualizados}, Errores: {$productosErrores}");
    
    echo json_encode([
        'success' => true,
        'message' => "Catálogo sincronizado desde {$origen}",
        'estadisticas' => [
            'productos_procesados' => $productosActualizados,
            'productos_nuevos' => $productosNuevos,
            'productos_errores' => $productosErrores,
            'total_recibidos' => count($catalogoProcesado)
        ],
        'detalles' => [
            'stock_preservado' => true,
            'duplicados_evitados' => true,
            'division_actualizada' => true
        ],
        'origen' => $origen,
        'timestamp' => date('Y-m-d H:i:s'),
        'version_api' => '5.1'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("ERROR CRÍTICO en sincronizar_catalogo.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>