<?php
require_once __DIR__ . "/../src/AjaxAuth.php";
AjaxAuth::requireProfiles(["Administrador"]);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

try {
    $stmt = ConexionCentral::conectar()->query("SELECT id, nombre, codigo_sucursal FROM sucursales WHERE activo = 1 ORDER BY nombre");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "sucursales" => $rows]);
} catch (Throwable $e) {
    echo json_encode(["success" => true, "sucursales" => []]);
}
