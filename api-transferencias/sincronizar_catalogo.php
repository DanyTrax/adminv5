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
    
    // ✅ VERIFICAR ESTADO DE LA SUCURSAL LOCAL ANTES DE PROCESAR
    $stmtSucursal = $pdo->prepare("
        SELECT activo, codigo_sucursal, nombre, fecha_actualizacion 
        FROM sucursal_local 
        WHERE activo = 1 
        LIMIT 1
    ");
    $stmtSucursal->execute();
    $sucursalInfo = $stmtSucursal->fetch(PDO::FETCH_ASSOC);
    
    if (!$sucursalInfo) {
        error_log("SYNC SKIP: Sucursal inactiva o no configurada - no se procesará sincronización");
        echo json_encode([
            'success' => false,
            'message' => 'Sucursal inactiva - sincronización omitida',
            'codigo_sucursal' => 'DESCONOCIDA',
            'estado' => 'inactiva',
            'timestamp' => date('Y-m-d H:i:s'),
            'razon' => 'La sucursal está desactivada en sucursal_local'
        ]);
        exit;
    }
    
    // ✅ VERIFICAR QUE LLEGUEN DATOS VÁLIDOS DEL CATÁLOGO
    if (!isset($datos['catalogo']) || !is_array($datos['catalogo']) || empty($datos['catalogo'])) {
        error_log("SYNC SKIP: No hay datos de catálogo para procesar - Sucursal: {$sucursalInfo['codigo_sucursal']}");
        echo json_encode([
            'success' => false,
            'message' => 'No hay datos de catálogo para sincronizar',
            'codigo_sucursal' => $sucursalInfo['codigo_sucursal'],
            'estado' => 'sin_datos',
            'timestamp' => date('Y-m-d H:i:s'),
            'razon' => 'El catálogo recibido está vacío'
        ]);
        exit;
    }
    
    // ✅ LOG DE INICIO DE SINCRONIZACIÓN REAL
    $origen = $datos['origen']['codigo'] ?? 'CENTRAL';
    $fechaInicioSync = date('Y-m-d H:i:s');
    error_log("SYNC START: Iniciando sincronización REAL - Sucursal: {$sucursalInfo['codigo_sucursal']}, Productos: " . count($datos['catalogo']) . ", Origen: {$origen}");
    
    $pdo->beginTransaction();
    
    // Procesar catálogo YA PROCESADO desde central
    $catalogoProcesado = $datos['catalogo'];
    
    // Estadísticas de proceso
    $productosActualizados = 0;
    $productosErrores = 0;
    $productosNuevos = 0;
    $productosEliminados = 0;
    
    // ✅ PASO 1: ACTUALIZAR/INSERTAR PRODUCTOS DEL CATÁLOGO MAESTRO
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
                    error_log("SYNC UPDATE: Código: {$productoLocal['codigo']}, Stock preservado: {$productoExistente['stock']}, Ventas: {$productoExistente['ventas']}");
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
                    error_log("SYNC NEW: Código: {$productoLocal['codigo']}, Descripción: {$productoLocal['descripcion']}");
                }
            }
            
        } catch (Exception $e) {
            $productosErrores++;
            error_log("ERROR procesando producto {$productoLocal['codigo']}: " . $e->getMessage());
        }
    }
    
    // ✅ PASO 2: ELIMINAR PRODUCTOS QUE YA NO ESTÁN EN CATÁLOGO MAESTRO
    try {
        
        // Obtener códigos de productos que llegaron en la sincronización
        $codigosRecibidos = array_column($catalogoProcesado, 'codigo');
        
        if (!empty($codigosRecibidos)) {
            
            // ✅ IDENTIFICAR PRODUCTOS A ELIMINAR
            $placeholders = str_repeat('?,', count($codigosRecibidos) - 1) . '?';
            
            $stmtIdentificar = $pdo->prepare("
                SELECT id, codigo, descripcion, stock 
                FROM productos 
                WHERE codigo_maestro IS NOT NULL 
                AND codigo NOT IN ($placeholders)
            ");
            $stmtIdentificar->execute($codigosRecibidos);
            $productosAEliminar = $stmtIdentificar->fetchAll(PDO::FETCH_ASSOC);
            
            // ✅ ELIMINAR PRODUCTOS QUE YA NO ESTÁN EN CATÁLOGO MAESTRO
            if (!empty($productosAEliminar)) {
                
                error_log("SYNC DELETE: Se eliminarán " . count($productosAEliminar) . " productos");
                
                foreach ($productosAEliminar as $productoEliminar) {
                    
                    try {
                        
                        if ($productoEliminar['stock'] > 0) {
                            error_log("WARNING: Eliminando producto con stock > 0 - Código: {$productoEliminar['codigo']}, Stock: {$productoEliminar['stock']}");
                        }
                        
                        $stmtEliminar = $pdo->prepare("DELETE FROM productos WHERE id = :id");
                        $resultadoEliminacion = $stmtEliminar->execute([':id' => $productoEliminar['id']]);
                        
                        if ($resultadoEliminacion) {
                            $productosEliminados++;
                        }
                        
                    } catch (Exception $e) {
                        error_log("ERROR eliminando producto {$productoEliminar['codigo']}: " . $e->getMessage());
                        $productosErrores++;
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        error_log("ERROR en proceso de eliminación: " . $e->getMessage());
    }
    
    // ✅ ACTUALIZAR FECHA DE ÚLTIMA SINCRONIZACIÓN SOLO SI SE PROCESÓ ALGO
    $totalProcesados = $productosNuevos + $productosActualizados + $productosEliminados;
    
    if ($totalProcesados > 0) {
        try {
            $stmtUpdateSucursal = $pdo->prepare("
                UPDATE sucursal_local 
                SET fecha_actualizacion = ? 
                WHERE codigo_sucursal = ?
            ");
            $stmtUpdateSucursal->execute([$fechaInicioSync, $sucursalInfo['codigo_sucursal']]);
            error_log("SYNC SUCCESS: Fecha actualizada a {$fechaInicioSync} para sucursal {$sucursalInfo['codigo_sucursal']}");
        } catch (Exception $e) {
            error_log("ERROR actualizando fecha sucursal: " . $e->getMessage());
        }
    } else {
        error_log("SYNC NO_CHANGES: No se actualizó fecha - no hubo cambios en sucursal {$sucursalInfo['codigo_sucursal']}");
    }
    
    $pdo->commit();
    
    // ✅ LOG FINAL CON ESTADÍSTICAS COMPLETAS
    error_log("SYNC COMPLETE: Sucursal {$sucursalInfo['codigo_sucursal']} - Nuevos: {$productosNuevos}, Actualizados: {$productosActualizados}, Eliminados: {$productosEliminados}, Errores: {$productosErrores}");
    
    echo json_encode([
        'success' => true,
        'message' => "Catálogo sincronizado desde {$origen}",
        'sucursal' => [
            'codigo' => $sucursalInfo['codigo_sucursal'],
            'nombre' => $sucursalInfo['nombre'],
            'estado' => 'activa',
            'fecha_actualizacion' => $totalProcesados > 0 ? $fechaInicioSync : $sucursalInfo['fecha_actualizacion']
        ],
        'estadisticas' => [
            'productos_procesados' => $productosActualizados,
            'productos_nuevos' => $productosNuevos,
            'productos_eliminados' => $productosEliminados,
            'productos_errores' => $productosErrores,
            'total_recibidos' => count($catalogoProcesado),
            'total_cambios' => $totalProcesados
        ],
        'detalles' => [
            'stock_preservado' => true,
            'duplicados_evitados' => true,
            'division_actualizada' => true,
            'eliminacion_automatica' => true,
            'fecha_actualizada' => $totalProcesados > 0
        ],
        'origen' => $origen,
        'timestamp' => $fechaInicioSync,
        'version_api' => '5.3'
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