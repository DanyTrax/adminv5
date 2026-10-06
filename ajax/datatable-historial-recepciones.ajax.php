<?php
/**
 * DataTable: historial de recepciones (agrupado por fecha/usuario/sucursal)
 */
require_once __DIR__ . "/../src/AjaxAuth.php";
AjaxAuth::requireSession();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();
    $stmt = $pdo->query("
        SELECT 
            MIN(id) AS id_grupo,
            DATE(fecha_descarga) AS fecha_recepcion,
            usuario_nombre,
            sucursal_nombre,
            COUNT(*) AS total_productos,
            COALESCE(SUM(cantidad_descargada), 0) AS total_cantidad,
            GROUP_CONCAT(DISTINCT LEFT(observaciones, 80) SEPARATOR '; ') AS observaciones
        FROM registro_descargas_stock_transito
        GROUP BY DATE(fecha_descarga), usuario_id, sucursal_id, usuario_nombre, sucursal_nombre
        ORDER BY fecha_recepcion DESC
        LIMIT 500
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = [];
    $i = 1;
    foreach ($rows as $r) {
        $id = (int) $r["id_grupo"];
        $data[] = [
            $i++,
            htmlspecialchars((string) $r["fecha_recepcion"], ENT_QUOTES, "UTF-8"),
            htmlspecialchars((string) $r["usuario_nombre"], ENT_QUOTES, "UTF-8"),
            htmlspecialchars((string) $r["sucursal_nombre"], ENT_QUOTES, "UTF-8"),
            (int) $r["total_productos"],
            (int) $r["total_cantidad"],
            htmlspecialchars((string) ($r["observaciones"] ?? ""), ENT_QUOTES, "UTF-8"),
            '<button class="btn btn-info btn-xs btnVerDetalleRecepcion" recepcionId="' . $id . '"><i class="fa fa-eye"></i></button>'
        ];
    }
    echo json_encode(["data" => $data]);
} catch (Throwable $e) {
    error_log("datatable-historial-recepciones: " . $e->getMessage());
    echo json_encode(["data" => []]);
}
