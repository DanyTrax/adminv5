<?php
require_once __DIR__ . "/../src/AjaxAuth.php";
AjaxAuth::requireSession();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

$id = isset($_POST["recepcion_id"]) ? (int) $_POST["recepcion_id"] : 0;
if ($id <= 0) {
    echo json_encode(["success" => false, "error" => "ID inválido"]);
    exit;
}

try {
    $pdo = ConexionCentral::conectar();
    // Obtener ancla del grupo y devolver líneas del mismo día/usuario/sucursal
    $stmt = $pdo->prepare("SELECT fecha_descarga, usuario_id, sucursal_id FROM registro_descargas_stock_transito WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);
    $base = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$base) {
        echo json_encode(["success" => false, "error" => "Recepción no encontrada"]);
        exit;
    }
    $stmt = $pdo->prepare("
        SELECT codigo_producto, descripcion_producto, cantidad_descargada AS cantidad_recibida,
               transportador_nombre, 
               COALESCE(numero_despacho, '') AS sucursal_origen
        FROM registro_descargas_stock_transito
        WHERE DATE(fecha_descarga) = DATE(:fecha)
          AND usuario_id = :uid
          AND sucursal_id = :sid
        ORDER BY id ASC
    ");
    $stmt->execute([
        ":fecha" => $base["fecha_descarga"],
        ":uid" => $base["usuario_id"],
        ":sid" => $base["sucursal_id"],
    ]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "detalles" => $detalles]);
} catch (Throwable $e) {
    error_log("obtener-detalle-recepcion: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Error al cargar detalle"]);
}
