<?php
/**
 * AJAX Transportador Móvil - Datos para vista móvil del transportador
 * Acciones: resumen, solicitudes, despachos, stock, descargas
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Solo transportador
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok" || $_SESSION["perfil"] != "Transportador") {
    echo json_encode(["error" => "Acceso no autorizado"]);
    exit;
}

require_once "../modelos/conexion.php";
require_once "../api-transferencias/conexion-central.php";
require_once "../controladores/solicitudes-stock.controlador.php";
require_once "../controladores/despachos.controlador.php";
require_once "../controladores/stock-transito.controlador.php";

$accion = $_POST["accion"] ?? $_GET["accion"] ?? "";
$transportadorId = (int) $_SESSION["id"];

try {
    switch ($accion) {

        /*=============================================
        RESUMEN - Dashboard con contadores
        =============================================*/
        case "resumen":
            $conexionCentral = ConexionCentral::conectar();
            
            // Solicitudes pendientes
            $stmt = $conexionCentral->prepare("SELECT COUNT(*) FROM solicitudes_stock WHERE estado = 'pendiente'");
            $stmt->execute();
            $solicitudesPendientes = (int) $stmt->fetchColumn();

            // Despachos pendientes de aceptar (asignados a este transportador o sin asignar)
            $despachos = ControladorDespachos::ctrMostrarDespachosTransportador($transportadorId);
            $despachosPendientes = 0;
            $productosEnCamion = 0;
            foreach ($despachos as $d) {
                if ($d["estado"] == "pendiente") $despachosPendientes++;
                if ($d["estado"] == "en_transito" && ($d["transportador_id"] ?? 0) == $transportadorId) {
                    $productosEnCamion += (int) ($d["total_productos"] ?? 0);
                }
            }

            // Stock en camión (productos únicos)
            $stockCamion = ControladorStockTransito::ctrMostrarStockPorTransportador($transportadorId);
            $totalProductosCamion = 0;
            foreach ($stockCamion as $productos) {
                $totalProductosCamion += count($productos);
            }

            // Últimas descargas (hoy) del transportador (con detalle adicional del despacho)
            $stmt = $conexionCentral->prepare("
                SELECT r.codigo_producto, r.descripcion_producto, r.cantidad_descargada, r.usuario_nombre, r.sucursal_nombre, 
                       r.numero_despacho, DATE_FORMAT(r.fecha_descarga, '%H:%i') as hora,
                       TRIM(d.detalle_adicional) as detalle_adicional
                FROM registro_descargas_stock_transito r
                LEFT JOIN despachos d ON d.numero_despacho = r.numero_despacho
                WHERE r.transportador_id = ? AND DATE(r.fecha_descarga) = CURDATE()
                ORDER BY r.fecha_descarga DESC LIMIT 5
            ");
            $stmt->execute([$transportadorId]);
            $ultimasDescargas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "data" => [
                    "solicitudes_pendientes" => $solicitudesPendientes,
                    "despachos_pendientes" => $despachosPendientes,
                    "productos_en_camion" => $totalProductosCamion,
                    "ultimas_descargas" => $ultimasDescargas
                ]
            ]);
            break;

        /*=============================================
        SOLICITUDES PENDIENTES
        =============================================*/
        case "solicitudes":
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT id, numero_solicitud, nombre_sucursal_solicitante, nombre_usuario_solicitante, 
                       tipo_solicitud, total_productos, fecha_solicitud,
                       TIMESTAMPDIFF(MINUTE, fecha_solicitud, NOW()) as minutos_desde
                FROM solicitudes_stock 
                WHERE estado = 'pendiente' 
                ORDER BY fecha_solicitud DESC
            ");
            $stmt->execute();
            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(["success" => true, "data" => $solicitudes]);
            break;

        /*=============================================
        DESPACHOS (pendientes + en tránsito)
        =============================================*/
        case "despachos":
            $despachos = ControladorDespachos::ctrMostrarDespachosTransportador($transportadorId);
            $pendientes = [];
            $enTransito = [];
            foreach ($despachos as $d) {
                $item = [
                    "id" => $d["id"],
                    "numero_despacho" => $d["numero_despacho"],
                    "sucursal_origen" => $d["sucursal_origen"] ?? "",
                    "nombre_usuario_creador" => $d["nombre_usuario_creador"] ?? "",
                    "detalle_adicional" => trim($d["detalle_adicional"] ?? ""),
                    "total_productos" => (int) ($d["total_productos"] ?? 0),
                    "total_cantidad" => (int) ($d["total_cantidad"] ?? 0),
                    "estado" => $d["estado"],
                    "fecha_creacion" => $d["fecha_creacion"] ?? ""
                ];
                if ($d["estado"] == "pendiente") {
                    $pendientes[] = $item;
                } elseif ($d["estado"] == "en_transito" && ($d["transportador_id"] ?? 0) == $transportadorId) {
                    $enTransito[] = $item;
                }
            }
            echo json_encode([
                "success" => true,
                "data" => [
                    "pendientes" => $pendientes,
                    "en_transito" => $enTransito
                ]
            ]);
            break;

        /*=============================================
        STOCK EN CAMIÓN
        =============================================*/
        case "stock":
            $despachos = ControladorStockTransito::ctrMostrarStockPorTransportador($transportadorId);
            $resultado = [];
            foreach ($despachos as $numeroDespacho => $productos) {
                $primer = $productos[0] ?? [];
                $resultado[] = [
                    "numero_despacho" => $numeroDespacho,
                    "sucursal_origen" => $primer["sucursal_origen"] ?? "",
                    "productos" => array_map(function ($p) {
                        return [
                            "codigo_producto" => $p["codigo_producto"],
                            "descripcion_producto" => $p["descripcion_producto"],
                            "cantidad_disponible" => (int) ($p["cantidad_disponible"] ?? 0)
                        ];
                    }, $productos)
                ];
            }
            echo json_encode(["success" => true, "data" => $resultado]);
            break;

        /*=============================================
        MIS DESCARGAS - Filtradas por transportador
        =============================================*/
        case "descargas":
            $dias = (int) ($_POST["dias"] ?? $_GET["dias"] ?? 7);
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT r.codigo_producto, r.descripcion_producto, r.cantidad_descargada, 
                       r.usuario_nombre, r.sucursal_nombre, r.numero_despacho,
                       DATE_FORMAT(r.fecha_descarga, '%d/%m/%Y %H:%i') as fecha_hora,
                       r.fecha_descarga, TRIM(d.detalle_adicional) as detalle_adicional
                FROM registro_descargas_stock_transito r
                LEFT JOIN despachos d ON d.numero_despacho = r.numero_despacho
                WHERE r.transportador_id = ? AND r.fecha_descarga >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY r.fecha_descarga DESC
            ");
            $stmt->execute([$transportadorId, $dias]);
            $descargas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(["success" => true, "data" => $descargas]);
            break;

        default:
            echo json_encode(["error" => "Acción no válida: $accion"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
