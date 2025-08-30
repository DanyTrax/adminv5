<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

// Recibimos los datos por POST
$id_solicitud = $_POST['id_solicitud'] ?? 0;
$nuevo_estado = $_POST['nuevo_estado'] ?? '';
$usuario_accion = $_POST['usuario_accion'] ?? '';
$sucursal_accion = $_POST['sucursal_accion'] ?? '';
$accion_log = $_POST['accion_log'] ?? 'Estado actualizado a ' . $nuevo_estado; // Mensaje por defecto para el log

if (empty($id_solicitud) || empty($nuevo_estado) || empty($usuario_accion) || empty($sucursal_accion)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
    exit();
}

$pdo = ConexionCentral::conectar();

try {
    // Iniciamos una transacción para asegurar que ambas operaciones (actualizar y registrar) se completen
    $pdo->beginTransaction();

    // 1. Actualizar el estado en la tabla de solicitudes
    $stmt_update = $pdo->prepare(
        "UPDATE solicitudes_transferencia SET estado = :estado WHERE id = :id"
    );
    $stmt_update->bindParam(":estado", $nuevo_estado, PDO::PARAM_STR);
    $stmt_update->bindParam(":id", $id_solicitud, PDO::PARAM_INT);
    $stmt_update->execute();

    // 2. Insertar un registro en la tabla de log
    $stmt_log = $pdo->prepare(
        "INSERT INTO log_transferencia (id_solicitud, usuario_accion, sucursal_accion, accion)
         VALUES (:id_solicitud, :usuario, :sucursal, :accion)"
    );
    $stmt_log->bindParam(":id_solicitud", $id_solicitud, PDO::PARAM_INT);
    $stmt_log->bindParam(":usuario", $usuario_accion, PDO::PARAM_STR);
    $stmt_log->bindParam(":sucursal", $sucursal_accion, PDO::PARAM_STR);
    $stmt_log->bindParam(":accion", $accion_log, PDO::PARAM_STR);
    $stmt_log->execute();
    
    // Si todo fue bien, confirmamos los cambios
    $pdo->commit();

    echo json_encode(['status' => 'ok', 'message' => 'Estado actualizado con éxito.']);

} catch (Exception $e) {
    // Si algo falla, revertimos todos los cambios
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}