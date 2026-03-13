<?php
/**
 * Herramienta de verificación: compara datos de solicitudes entre lista y notificaciones
 * Uso: POST con accion=verificar (o GET para prueba rápida)
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
    echo json_encode(['success' => false, 'error' => 'Solo Administrador puede verificar']);
    exit;
}

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    // Total y últimas solicitudes (como la LISTA)
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total, 
               MAX(id) as max_id,
               MAX(numero_solicitud) as max_numero
        FROM solicitudes_stock
    ");
    $stmt->execute();
    $resLista = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Últimas 15 por ID (las más recientes)
    $stmt = $conexion->prepare("
        SELECT id, numero_solicitud, estado, fecha_solicitud 
        FROM solicitudes_stock 
        ORDER BY id DESC 
        LIMIT 15
    ");
    $stmt->execute();
    $ultimas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pendientes (como NOTIFICACIONES)
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total FROM solicitudes_stock WHERE estado = 'pendiente'
    ");
    $stmt->execute();
    $pendientes = (int) $stmt->fetchColumn();
    
    $stmt = $conexion->prepare("
        SELECT id, numero_solicitud, nombre_sucursal_solicitante 
        FROM solicitudes_stock 
        WHERE estado = 'pendiente' 
        ORDER BY id DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $pendientesLista = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'lista' => [
            'total' => (int)($resLista['total'] ?? 0),
            'max_id' => (int)($resLista['max_id'] ?? 0),
            'max_numero' => $resLista['max_numero'] ?? '-',
            'ultimas_15' => $ultimas
        ],
        'notificaciones' => [
            'pendientes' => $pendientes,
            'lista_pendientes' => $pendientesLista
        ],
        'mensaje' => 'Si max_numero en lista es menor que los números en notificaciones, hay un problema de conexión o base de datos diferente.'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
