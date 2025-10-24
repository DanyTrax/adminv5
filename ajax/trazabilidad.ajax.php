<?php

session_start();
require_once "../api-transferencias/conexion-central.php";

/*=============================================
FUNCIONES DE TRAZABILIDAD
=============================================*/

class TrazabilidadAjax {
    
    /*=============================================
    BUSCAR POR DESPACHO
    =============================================*/
    public function buscarPorDespacho() {
        try {
            $numero_despacho = $_POST["numero_despacho"];
            
            if(empty($numero_despacho)) {
                echo json_encode(["success" => false, "error" => "Número de despacho requerido"]);
                return;
            }
            
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    tm.*,
                    u.nombre as usuario_origen_nombre,
                    t.nombre as transportador_nombre,
                    ud.nombre as usuario_destino_nombre
                FROM trazabilidad_movimientos tm
                LEFT JOIN usuarios u ON tm.usuario_origen_id = u.id
                LEFT JOIN transportadores t ON tm.transportador_id = t.id
                LEFT JOIN usuarios ud ON tm.usuario_destino_id = ud.id
                WHERE tm.numero_despacho = ? 
                ORDER BY tm.fecha_aceptacion DESC
            ");
            $stmt->execute([$numero_despacho]);
            $movimientos = $stmt->fetchAll();
            
            if(empty($movimientos)) {
                echo json_encode(["success" => false, "error" => "No se encontraron movimientos para el despacho: " . $numero_despacho]);
                return;
            }
            
            // Obtener historial de descargas para cada movimiento
            foreach($movimientos as &$movimiento) {
                $stmt = ConexionCentral::conectar()->prepare("
                    SELECT 
                        td.*,
                        u.nombre as usuario_descarga_nombre
                    FROM trazabilidad_descargas td
                    LEFT JOIN usuarios u ON td.usuario_descarga_id = u.id
                    WHERE td.trazabilidad_movimiento_id = ?
                    ORDER BY td.fecha_descarga ASC
                ");
                $stmt->execute([$movimiento['id']]);
                $movimiento['descargas'] = $stmt->fetchAll();
            }
            
            echo json_encode([
                "success" => true, 
                "data" => $movimientos,
                "despacho" => $numero_despacho
            ]);
            
        } catch(Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    }
    
    /*=============================================
    BUSCAR POR PRODUCTO
    =============================================*/
    public function buscarPorProducto() {
        try {
            $producto_codigo = $_POST["producto_codigo"];
            
            if(empty($producto_codigo)) {
                echo json_encode(["success" => false, "error" => "Código de producto requerido"]);
                return;
            }
            
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    tm.*,
                    u.nombre as usuario_origen_nombre,
                    t.nombre as transportador_nombre,
                    ud.nombre as usuario_destino_nombre
                FROM trazabilidad_movimientos tm
                LEFT JOIN usuarios u ON tm.usuario_origen_id = u.id
                LEFT JOIN transportadores t ON tm.transportador_id = t.id
                LEFT JOIN usuarios ud ON tm.usuario_destino_id = ud.id
                WHERE tm.producto_codigo = ? 
                ORDER BY tm.fecha_aceptacion DESC
            ");
            $stmt->execute([$producto_codigo]);
            $movimientos = $stmt->fetchAll();
            
            if(empty($movimientos)) {
                echo json_encode(["success" => false, "error" => "No se encontraron movimientos para el producto: " . $producto_codigo]);
                return;
            }
            
            // Obtener historial de descargas para cada movimiento
            foreach($movimientos as &$movimiento) {
                $stmt = ConexionCentral::conectar()->prepare("
                    SELECT 
                        td.*,
                        u.nombre as usuario_descarga_nombre
                    FROM trazabilidad_descargas td
                    LEFT JOIN usuarios u ON td.usuario_descarga_id = u.id
                    WHERE td.trazabilidad_movimiento_id = ?
                    ORDER BY td.fecha_descarga ASC
                ");
                $stmt->execute([$movimiento['id']]);
                $movimiento['descargas'] = $stmt->fetchAll();
            }
            
            echo json_encode([
                "success" => true, 
                "data" => $movimientos,
                "producto" => $producto_codigo
            ]);
            
        } catch(Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    }
    
    /*=============================================
    REGISTRAR ACEPTACIÓN DE DESPACHO
    =============================================*/
    public function registrarAceptacionDespacho() {
        try {
            $numero_despacho = $_POST["numero_despacho"];
            $transportador_id = $_POST["transportador_id"];
            
            if(empty($numero_despacho) || empty($transportador_id)) {
                echo json_encode(["success" => false, "error" => "Datos requeridos faltantes"]);
                return;
            }
            
            // Obtener datos del despacho
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT * FROM despachos WHERE numero_despacho = ?
            ");
            $stmt->execute([$numero_despacho]);
            $despacho = $stmt->fetch();
            
            if(!$despacho) {
                echo json_encode(["success" => false, "error" => "Despacho no encontrado"]);
                return;
            }
            
            $productos = json_decode($despacho['productos_despacho'], true);
            
            if(empty($productos)) {
                echo json_encode(["success" => false, "error" => "No hay productos en el despacho"]);
                return;
            }
            
            // Registrar cada producto en trazabilidad
            foreach($productos as $producto) {
                $stmt = ConexionCentral::conectar()->prepare("
                    INSERT INTO trazabilidad_movimientos (
                        numero_despacho, producto_codigo, producto_descripcion,
                        cantidad_total, cantidad_pendiente, cantidad_descargada,
                        sucursal_origen, usuario_origen_id, fecha_salida,
                        transportador_id, fecha_aceptacion, estado
                    ) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, NOW(), 'en_transito')
                ");
                $stmt->execute([
                    $numero_despacho, 
                    $producto['codigo'], 
                    $producto['descripcion'],
                    $producto['cantidad'], 
                    $producto['cantidad'], 
                    0,
                    $despacho['sucursal_origen'], 
                    $despacho['usuario_origen_id'], 
                    $despacho['fecha_creacion'],
                    $transportador_id
                ]);
            }
            
            echo json_encode(["success" => true, "message" => "Trazabilidad registrada correctamente"]);
            
        } catch(Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    }
    
    /*=============================================
    REGISTRAR DESCARGA DE PRODUCTO
    =============================================*/
    public function registrarDescarga() {
        try {
            $producto_codigo = $_POST["producto_codigo"];
            $cantidad = $_POST["cantidad"];
            $usuario_id = $_POST["usuario_id"];
            $sucursal = $_POST["sucursal"];
            
            if(empty($producto_codigo) || empty($cantidad) || empty($usuario_id) || empty($sucursal)) {
                echo json_encode(["success" => false, "error" => "Datos requeridos faltantes"]);
                return;
            }
            
            // Obtener movimientos del producto ordenados por fecha_aceptacion DESC (LIFO)
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT * FROM trazabilidad_movimientos 
                WHERE producto_codigo = ? AND cantidad_pendiente > 0 
                ORDER BY fecha_aceptacion DESC
            ");
            $stmt->execute([$producto_codigo]);
            $movimientos = $stmt->fetchAll();
            
            if(empty($movimientos)) {
                echo json_encode(["success" => false, "error" => "No hay stock disponible para el producto: " . $producto_codigo]);
                return;
            }
            
            $cantidad_restante = $cantidad;
            $descargas_registradas = [];
            
            foreach($movimientos as $movimiento) {
                if($cantidad_restante <= 0) break;
                
                $cantidad_disponible = $movimiento['cantidad_pendiente'];
                $cantidad_a_descargar = min($cantidad_restante, $cantidad_disponible);
                
                // Registrar descarga
                $stmt = ConexionCentral::conectar()->prepare("
                    INSERT INTO trazabilidad_descargas (
                        trazabilidad_movimiento_id, cantidad_descargada, 
                        usuario_descarga_id, sucursal_descarga, fecha_descarga
                    ) VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $movimiento['id'], 
                    $cantidad_a_descargar, 
                    $usuario_id, 
                    $sucursal
                ]);
                
                // Actualizar cantidades
                $stmt = ConexionCentral::conectar()->prepare("
                    UPDATE trazabilidad_movimientos 
                    SET cantidad_pendiente = cantidad_pendiente - ?, 
                        cantidad_descargada = cantidad_descargada + ?,
                        estado = CASE 
                            WHEN cantidad_pendiente - ? = 0 THEN 'entregado'
                            ELSE 'parcial'
                        END
                    WHERE id = ?
                ");
                $stmt->execute([
                    $cantidad_a_descargar, 
                    $cantidad_a_descargar, 
                    $cantidad_a_descargar, 
                    $movimiento['id']
                ]);
                
                $descargas_registradas[] = [
                    'despacho' => $movimiento['numero_despacho'],
                    'cantidad' => $cantidad_a_descargar
                ];
                
                $cantidad_restante -= $cantidad_a_descargar;
            }
            
            echo json_encode([
                "success" => true, 
                "message" => "Descarga registrada correctamente",
                "descargas" => $descargas_registradas,
                "cantidad_restante" => $cantidad_restante
            ]);
            
        } catch(Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    }
    
    /*=============================================
    OBTENER REPORTES
    =============================================*/
    public function obtenerReportes() {
        try {
            $reportes = [];
            
            // Productos más movidos
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    producto_codigo,
                    producto_descripcion,
                    COUNT(*) as total_movimientos,
                    SUM(cantidad_total) as total_cantidad
                FROM trazabilidad_movimientos 
                GROUP BY producto_codigo, producto_descripcion
                ORDER BY total_movimientos DESC
                LIMIT 10
            ");
            $stmt->execute();
            $reportes['productos_mas_movidos'] = $stmt->fetchAll();
            
            // Usuarios más activos
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    u.nombre,
                    COUNT(td.id) as total_descargas,
                    SUM(td.cantidad_descargada) as total_cantidad
                FROM trazabilidad_descargas td
                LEFT JOIN usuarios u ON td.usuario_descarga_id = u.id
                GROUP BY td.usuario_descarga_id, u.nombre
                ORDER BY total_descargas DESC
                LIMIT 10
            ");
            $stmt->execute();
            $reportes['usuarios_mas_activos'] = $stmt->fetchAll();
            
            // Despachos incompletos
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    numero_despacho,
                    COUNT(*) as total_productos,
                    SUM(CASE WHEN estado = 'entregado' THEN 1 ELSE 0 END) as productos_entregados,
                    SUM(CASE WHEN estado = 'parcial' THEN 1 ELSE 0 END) as productos_parciales,
                    SUM(CASE WHEN estado = 'en_transito' THEN 1 ELSE 0 END) as productos_pendientes
                FROM trazabilidad_movimientos 
                GROUP BY numero_despacho
                HAVING productos_entregados < total_productos
                ORDER BY productos_pendientes DESC
            ");
            $stmt->execute();
            $reportes['despachos_incompletos'] = $stmt->fetchAll();
            
            echo json_encode([
                "success" => true, 
                "data" => $reportes
            ]);
            
        } catch(Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    }
}

/*=============================================
PROCESAR PETICIONES
=============================================*/
if(isset($_POST["accion"])) {
    $trazabilidad = new TrazabilidadAjax();
    
    switch($_POST["accion"]) {
        case "buscar_por_despacho":
            $trazabilidad->buscarPorDespacho();
            break;
        case "buscar_por_producto":
            $trazabilidad->buscarPorProducto();
            break;
        case "registrar_aceptacion":
            $trazabilidad->registrarAceptacionDespacho();
            break;
        case "registrar_descarga":
            $trazabilidad->registrarDescarga();
            break;
        case "obtener_reportes":
            $trazabilidad->obtenerReportes();
            break;
        default:
            echo json_encode(["success" => false, "error" => "Acción no reconocida"]);
            break;
    }
} else {
    echo json_encode(["success" => false, "error" => "No se especificó acción"]);
}

?>
