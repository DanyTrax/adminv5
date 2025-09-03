<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ✅ DEBUG: Log de entrada
error_log("DEBUG: Iniciando sincronizar_catalogo.php");
error_log("DEBUG: Método recibido: " . ($_SERVER['REQUEST_METHOD'] ?? 'NONE'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    
    $input = file_get_contents('php://input');
    error_log("DEBUG: Datos recibidos (primeros 200 chars): " . substr($input, 0, 200));
    
    $datos = json_decode($input, true);
    
    if (!$datos) {
        error_log("DEBUG: Error al decodificar JSON: " . json_last_error_msg());
        echo json_encode(['success' => false, 'message' => 'JSON inválido: ' . json_last_error_msg()]);
        exit;
    }
    
    if (!isset($datos['accion']) || $datos['accion'] !== 'sincronizar_catalogo') {
        error_log("DEBUG: Acción incorrecta recibida: " . ($datos['accion'] ?? 'NONE'));
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        exit;
    }
    
    // ✅ CONECTAR A BD LOCAL DE ESTA SUCURSAL
    require_once "../modelos/conexion.php";
    $pdo = Conexion::conectar();
    error_log("DEBUG: Conectado a BD local exitosamente");
    
    // Procesar catálogo YA PROCESADO desde central
    $catalogoProcesado = $datos['catalogo'];
    $origen = $datos['origen']['codigo'] ?? 'CENTRAL';
    
    error_log("DEBUG: Productos a procesar: " . count($catalogoProcesado));
    error_log("DEBUG: Origen: " . $origen);
    
    // Estadísticas de proceso
    $productosActualizados = 0;
    $productosErrores = 0;
    $productosNuevos = 0;
    
    // ✅ APLICAR LA MISMA LÓGICA QUE TU SINCRONIZACIÓN LOCAL EXISTENTE
    foreach ($catalogoProcesado as $index => $productoProcesado) {
        
        try {
            
            // ✅ DEBUG: Log algunos productos
            if ($index < 3) {
                error_log("DEBUG: Procesando producto " . ($index + 1) . ": " . ($productoProcesado['codigo'] ?? 'SIN_CODIGO'));
            }
            
            // ✅ USAR LA MISMA ESTRUCTURA DE TU TABLA PRODUCTOS LOCAL
            $stmt = $pdo->prepare("
                INSERT INTO productos (
                    codigo, 
                    descripcion, 
                    id_categoria, 
                    precio_venta,
                    imagen,
                    es_divisible,
                    codigo_hijo_mitad,
                    codigo_hijo_tercio,
                    codigo_hijo_cuarto,
                    es_hijo,
                    codigo_padre,
                    tipo_division,
                    activo,
                    fecha_actualizacion
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW()
                ) 
                ON DUPLICATE KEY UPDATE 
                    descripcion = VALUES(descripcion),
                    id_categoria = VALUES(id_categoria),
                    precio_venta = VALUES(precio_venta),
                    imagen = VALUES(imagen),
                    es_divisible = VALUES(es_divisible),
                    codigo_hijo_mitad = VALUES(codigo_hijo_mitad),
                    codigo_hijo_tercio = VALUES(codigo_hijo_tercio),
                    codigo_hijo_cuarto = VALUES(codigo_hijo_cuarto),
                    es_hijo = VALUES(es_hijo),
                    codigo_padre = VALUES(codigo_padre),
                    tipo_division = VALUES(tipo_division),
                    activo = VALUES(activo),
                    fecha_actualizacion = NOW()
            ");
            
            // ✅ MAPEAR DATOS PROCESADOS A TU ESTRUCTURA LOCAL
            $resultadoInsert = $stmt->execute([
                $productoProcesado['codigo'] ?? '',
                $productoProcesado['descripcion'] ?? 'Producto sin descripción',
                $productoProcesado['id_categoria'] ?? 1,
                $productoProcesado['precio_venta'] ?? 0.00,
                $productoProcesado['imagen'] ?? null,
                $productoProcesado['es_divisible'] ?? 0,
                $productoProcesado['codigo_hijo_mitad'] ?? null,
                $productoProcesado['codigo_hijo_tercio'] ?? null,
                $productoProcesado['codigo_hijo_cuarto'] ?? null,
                $productoProcesado['es_hijo'] ?? 0,
                $productoProcesado['codigo_padre'] ?? null,
                $productoProcesado['tipo_division'] ?? null
            ]);
            
            if ($resultadoInsert) {
                // Verificar si fue inserción nueva o actualización
                if ($pdo->lastInsertId() > 0) {
                    $productosNuevos++;
                }
                $productosActualizados++;
                
                // ✅ DEBUG: Log producto exitoso
                if ($index < 3) {
                    error_log("DEBUG: Producto " . ($productoProcesado['codigo'] ?? 'SIN_CODIGO') . " procesado exitosamente");
                }
            }
            
        } catch (Exception $e) {
            $productosErrores++;
            error_log("ERROR procesando producto " . ($productoProcesado['codigo'] ?? 'sin código') . ": " . $e->getMessage());
        }
    }
    
    // ✅ DEBUG: Estadísticas finales
    error_log("DEBUG: Productos actualizados: $productosActualizados, Nuevos: $productosNuevos, Errores: $productosErrores");
    
    // ✅ RESPUESTA DETALLADA COMO TU SINCRONIZACIÓN ACTUAL
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
    error_log("ERROR CRÍTICO en sincronizar_catalogo.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>