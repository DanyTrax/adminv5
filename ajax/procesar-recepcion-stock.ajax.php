<?php
session_start();

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $conexion = ConexionCentral::conectar();
    $conexion->beginTransaction();
    
    $idStockTransito = intval($_POST['idStockTransito']);
    $cantidadRecibir = intval($_POST['cantidadRecibir']);
    $observaciones = $_POST['observaciones'] ?? '';
    
    $usuarioId = $_SESSION['id'];
    $usuarioNombre = $_SESSION['nombre'];
    $sucursalUsuario = $_SESSION['sucursal'] ?? 'Sucursal Principal';
    
    // 1. Verificar que existe el stock en tránsito
    $stmt = $conexion->prepare("
        SELECT * FROM stock_transito 
        WHERE id = ? AND cantidad_disponible >= ?
    ");
    $stmt->execute([$idStockTransito, $cantidadRecibir]);
    $stockTransito = $stmt->fetch();
    
    if (!$stockTransito) {
        throw new Exception('Stock no disponible o cantidad insuficiente');
    }
    
    // 2. Buscar recepción existente en la última hora
    $fechaLimite = date('Y-m-d H:i:s', strtotime('-1 hour'));
    $stmt = $conexion->prepare("
        SELECT id FROM recepciones_stock_transito 
        WHERE usuario_receptor_id = ? 
        AND sucursal_receptor = ? 
        AND fecha_recepcion >= ?
        ORDER BY fecha_recepcion DESC 
        LIMIT 1
    ");
    $stmt->execute([$usuarioId, $sucursalUsuario, $fechaLimite]);
    $recepcionExistente = $stmt->fetch();
    
    if ($recepcionExistente) {
        // Usar recepción existente
        $recepcionId = $recepcionExistente['id'];
        
        // Actualizar totales
        $stmt = $conexion->prepare("
            UPDATE recepciones_stock_transito 
            SET total_productos = total_productos + 1,
                total_cantidad = total_cantidad + ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$cantidadRecibir, $recepcionId]);
        
    } else {
        // Crear nueva recepción
        $stmt = $conexion->prepare("
            INSERT INTO recepciones_stock_transito (
                fecha_recepcion, usuario_receptor_id, usuario_receptor_nombre,
                sucursal_receptor, total_productos, total_cantidad, observaciones
            ) VALUES (?, ?, ?, ?, 1, ?, ?)
        ");
        $stmt->execute([
            date('Y-m-d H:i:s'), $usuarioId, $usuarioNombre,
            $sucursalUsuario, $cantidadRecibir, $observaciones
        ]);
        $recepcionId = $conexion->lastInsertId();
    }
    
    // 3. Insertar detalle de la recepción
    $stmt = $conexion->prepare("
        INSERT INTO detalle_recepciones_stock_transito (
            recepcion_id, id_stock_transito_original, codigo_producto,
            descripcion_producto, cantidad_recibida, transportador_id,
            transportador_nombre, sucursal_origen
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $recepcionId, $idStockTransito, $stockTransito['codigo_producto'],
        $stockTransito['descripcion_producto'], $cantidadRecibir,
        $stockTransito['transportador_id'], $stockTransito['nombre_transportador'],
        $stockTransito['sucursal_origen']
    ]);
    
    // 4. Actualizar cantidad en stock_transito
    $nuevaCantidad = $stockTransito['cantidad_disponible'] - $cantidadRecibir;
    $idDespachoOrigen = $stockTransito['id_despacho_origen'] ?? null;
    
    if ($nuevaCantidad <= 0) {
        // Eliminar registro si llega a cero
        $stmt = $conexion->prepare("DELETE FROM stock_transito WHERE id = ?");
        $stmt->execute([$idStockTransito]);
    } else {
        // Actualizar cantidad
        $stmt = $conexion->prepare("
            UPDATE stock_transito 
            SET cantidad_disponible = ? 
            WHERE id = ?
        ");
        $stmt->execute([$nuevaCantidad, $idStockTransito]);
    }
    
    // 4b. Si el despacho ya no tiene stock en tránsito, marcarlo como entregado
    if ($idDespachoOrigen) {
        $stmt = $conexion->prepare("
            SELECT COALESCE(SUM(cantidad_disponible), 0) as total_restante
            FROM stock_transito WHERE id_despacho_origen = ?
        ");
        $stmt->execute([$idDespachoOrigen]);
        $totalRestante = (float)($stmt->fetch()['total_restante'] ?? 0);
        if ($totalRestante == 0) {
            $stmt = $conexion->prepare("UPDATE despachos SET estado = 'entregado', fecha_actualizacion = NOW() WHERE id = ?");
            $stmt->execute([$idDespachoOrigen]);
        }
    }
    
    // 5. Actualizar stock en productos (sumar al inventario local)
    $stmt = $conexion->prepare("
        UPDATE productos 
        SET stock = stock + ? 
        WHERE codigo = ?
    ");
    $stmt->execute([$cantidadRecibir, $stockTransito['codigo_producto']]);
    
    $conexion->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Recepción procesada exitosamente',
        'recepcion_id' => $recepcionId,
        'cantidad_recibida' => $cantidadRecibir,
        'nueva_cantidad_transito' => $nuevaCantidad
    ]);
    
} catch (Exception $e) {
    $conexion->rollBack();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>