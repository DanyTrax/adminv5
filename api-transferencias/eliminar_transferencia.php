<?php
header('Content-Type: application/json');
require_once "conexion-central.php";
$id_transferencia = $_POST['id_transferencia'] ?? 0;
$pdo = ConexionCentral::conectar();
try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM transferencia_items WHERE id_transferencia = :id")->execute([':id' => $id_transferencia]);
    $pdo->prepare("DELETE FROM log_transferencia WHERE id_transferencia = :id")->execute([':id' => $id_transferencia]);
    $pdo->prepare("DELETE FROM transferencias WHERE id = :id")->execute([':id' => $id_transferencia]);
    $pdo->commit();
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}