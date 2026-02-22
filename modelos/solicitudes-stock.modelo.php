<?php

require_once "conexion.php";

// ✅ INCLUIR CONEXIÓN CENTRAL CON RUTA CORRECTA
if (file_exists("api-transferencias/conexion-central.php")) {
    require_once __DIR__ . "/../api-transferencias/conexion-central.php";
} elseif (file_exists("../api-transferencias/conexion-central.php")) {
    require_once "../api-transferencias/conexion-central.php";
} elseif (file_exists(__DIR__ . "/../api-transferencias/conexion-central.php")) {
    require_once __DIR__ . "/../api-transferencias/conexion-central.php";
} else {
    die("Error: No se pudo encontrar el archivo conexion-central.php");
}

class ModeloSolicitudesStock {

/*=============================================
CREAR SOLICITUD DE STOCK - EN BASE CENTRAL
=============================================*/
static public function mdlCrearSolicitud($tabla, $datos) {

    try {
        // ✅ DEBUG: Log antes del INSERT
        error_log("=== DEBUG MODELO - CREAR SOLICITUD ===");
        error_log("Tabla: " . $tabla);
        error_log("Datos a insertar: " . json_encode($datos));
        
        // ✅ VERIFICAR CONEXIÓN CENTRAL
        $conexion = ConexionCentral::conectar();
        if(!$conexion) {
            error_log("ERROR: No se pudo conectar a la base central");
            return "error_conexion";
        }
        
        $stmt = $conexion->prepare("INSERT INTO $tabla(
            numero_solicitud, 
            codigo_sucursal_solicitante, 
            nombre_sucursal_solicitante, 
            usuario_solicitante, 
            nombre_usuario_solicitante, 
            productos_solicitados, 
            tipo_solicitud, 
            codigo_remision, 
            nombre_cliente_remision, 
            detalle_adicional, 
            total_productos, 
            total_cantidad
        ) VALUES (
            :numero_solicitud, 
            :codigo_sucursal_solicitante, 
            :nombre_sucursal_solicitante, 
            :usuario_solicitante, 
            :nombre_usuario_solicitante, 
            :productos_solicitados, 
            :tipo_solicitud, 
            :codigo_remision, 
            :nombre_cliente_remision, 
            :detalle_adicional, 
            :total_productos, 
            :total_cantidad
        )");

        // ✅ BIND PARAMETERS CON VERIFICACIÓN DE TIPOS
        $stmt->bindParam(":numero_solicitud", $datos["numero_solicitud"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo_sucursal_solicitante", $datos["codigo_sucursal_solicitante"], PDO::PARAM_STR);
        $stmt->bindParam(":nombre_sucursal_solicitante", $datos["nombre_sucursal_solicitante"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario_solicitante", $datos["usuario_solicitante"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre_usuario_solicitante", $datos["nombre_usuario_solicitante"], PDO::PARAM_STR);
        $stmt->bindParam(":productos_solicitados", $datos["productos_solicitados"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_solicitud", $datos["tipo_solicitud"], PDO::PARAM_STR);
        
        // ✅ MANEJAR VALORES NULOS CORRECTAMENTE
        $codigo_remision = $datos["codigo_remision"] ?: null;
        $nombre_cliente_remision = $datos["nombre_cliente_remision"] ?: null;
        $detalle_adicional = $datos["detalle_adicional"] ?: null;
        
        $stmt->bindParam(":codigo_remision", $codigo_remision, PDO::PARAM_STR);
        $stmt->bindParam(":nombre_cliente_remision", $nombre_cliente_remision, PDO::PARAM_STR);
        $stmt->bindParam(":detalle_adicional", $detalle_adicional, PDO::PARAM_STR);
        $stmt->bindParam(":total_productos", $datos["total_productos"], PDO::PARAM_INT);
        $stmt->bindParam(":total_cantidad", $datos["total_cantidad"], PDO::PARAM_INT);

        // ✅ EJECUTAR Y VERIFICAR
        if($stmt->execute()){
            $insertId = $conexion->lastInsertId();
            error_log("SUCCESS: Solicitud creada con ID: " . $insertId);
            return "ok";
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("ERROR SQL: " . json_encode($errorInfo));
            return "error_sql: " . $errorInfo[2];
        }

    } catch(Exception $e) {
        error_log("EXCEPCIÓN en mdlCrearSolicitud: " . $e->getMessage());
        return "error_excepcion: " . $e->getMessage();
    }

    $stmt = null;
}

    /*=============================================
    MOSTRAR SOLICITUDES - DESDE BASE CENTRAL
    =============================================*/
    static public function mdlMostrarSolicitudes($tabla, $item, $valor) {

        if($item != null) {

            $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY fecha_solicitud DESC");

            $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetch();

        } else {

            $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM $tabla ORDER BY fecha_solicitud DESC");

            $stmt->execute();

            return $stmt->fetchAll();
        }

        
        $stmt = null;
    }

    /*=============================================
    MOSTRAR SOLICITUDES COMPLETAS - DESDE BASE CENTRAL
    =============================================*/
    static public function mdlMostrarSolicitudesCompletas($tabla) {

        $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM $tabla ORDER BY fecha_solicitud DESC");

        $stmt->execute();

        return $stmt->fetchAll();

        
        $stmt = null;
    }

    /*=============================================
    ACTUALIZAR ESTADO DE SOLICITUD - EN BASE CENTRAL
    =============================================*/
    static public function mdlActualizarEstadoSolicitud($tabla, $datos) {

        $stmt = ConexionCentral::conectar()->prepare("UPDATE $tabla SET 
            estado = :estado,
            usuario_aprobacion = :usuario_aprobacion,
            nombre_usuario_aprobacion = :nombre_usuario_aprobacion,
            fecha_aprobacion = NOW(),
            observaciones_aprobacion = :observaciones_aprobacion,
            motivo_cancelacion = :motivo_cancelacion
            WHERE id = :id");

        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario_aprobacion", $datos["usuario_aprobacion"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre_usuario_aprobacion", $datos["nombre_usuario_aprobacion"], PDO::PARAM_STR);
        $stmt->bindParam(":observaciones_aprobacion", $datos["observaciones_aprobacion"], PDO::PARAM_STR);
        $stmt->bindParam(":motivo_cancelacion", $datos["motivo_cancelacion"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if($stmt->execute()){
            return "ok";
        } else {
            return "error";
        }

        
        $stmt = null;
    }

    /*=============================================
    VERIFICAR Y FINALIZAR SOLICITUD - cuando todos los productos ya fueron despachados
    =============================================*/
    static public function mdlVerificarYFinalizarSolicitud($idSolicitud, $idDespachoRecienAceptado = null) {
        try {
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
        
            $stmt = $conexion->prepare("SELECT productos_solicitados, estado FROM solicitudes_stock WHERE id = ?");
            $stmt->execute([$idSolicitud]);
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$solicitud || $solicitud['estado'] === 'finalizado' || $solicitud['estado'] === 'cancelado') {
                $conexion->rollBack();
                return false;
            }
            
            $productosSolicitados = json_decode($solicitud['productos_solicitados'] ?? '[]', true);
            if (!is_array($productosSolicitados) || empty($productosSolicitados)) {
                $conexion->rollBack();
                return false;
            }
            
            // Incluir despachos en_transito, entregado, Y el despacho recién aceptado (por si aún no se ve el UPDATE)
            if ($idDespachoRecienAceptado) {
                $stmt = $conexion->prepare("
                    SELECT productos_despacho FROM despachos 
                    WHERE id_solicitud_origen = ? 
                    AND (estado IN ('en_transito', 'entregado') OR id = ?)
                ");
                $stmt->execute([$idSolicitud, $idDespachoRecienAceptado]);
            } else {
                $stmt = $conexion->prepare("
                    SELECT productos_despacho FROM despachos 
                    WHERE id_solicitud_origen = ? AND estado IN ('en_transito', 'entregado')
                ");
                $stmt->execute([$idSolicitud]);
            }
            $despachos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $cantidadDespachadaPorProducto = [];
            foreach ($despachos as $d) {
                $productos = json_decode($d['productos_despacho'] ?? '[]', true);
                if (is_array($productos)) {
                    foreach ($productos as $p) {
                        $cod = $p['codigo'] ?? $p['codigo_producto'] ?? '';
                        if ($cod) {
                            $cantidadDespachadaPorProducto[$cod] = ($cantidadDespachadaPorProducto[$cod] ?? 0) + (int)($p['cantidad'] ?? 0);
                        }
                    }
                }
            }
            
            $completa = true;
            foreach ($productosSolicitados as $producto) {
                $cod = $producto['codigo'] ?? $producto['codigo_producto'] ?? '';
                $cantSolicitada = (int)($producto['cantidad'] ?? 0);
                $cantDespachada = $cantidadDespachadaPorProducto[$cod] ?? 0;
                if ($cantDespachada < $cantSolicitada) {
                    $completa = false;
                    break;
                }
            }
            
            if ($completa) {
                $stmt = $conexion->prepare("UPDATE solicitudes_stock SET estado = 'finalizado', fecha_actualizacion = NOW() WHERE id = ?");
                $stmt->execute([$idSolicitud]);
            }
            
            $conexion->commit();
            return $completa;
        } catch (Exception $e) {
            if (isset($conexion) && $conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("Error mdlVerificarYFinalizarSolicitud: " . $e->getMessage());
            return false;
        }
    }

    /*=============================================
    ELIMINAR SOLICITUD - DE BASE CENTRAL
    =============================================*/
    static public function mdlEliminarSolicitud($tabla, $datos) {

        $stmt = ConexionCentral::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

        $stmt->bindParam(":id", $datos, PDO::PARAM_INT);

        if($stmt->execute()){
            return "ok";
        } else {
            return "error";
        }

        
        $stmt = null;
    }

    /*=============================================
    GENERAR NÚMERO DE SOLICITUD - BASE CENTRAL
    =============================================*/
    static public function mdlGenerarNumeroSolicitud($tabla) {

        // Obtener el último número de solicitud
        $stmt = ConexionCentral::conectar()->prepare("SELECT numero_solicitud FROM $tabla ORDER BY id DESC LIMIT 1");

        $stmt->execute();

        $ultimaSolicitud = $stmt->fetch();

        if($ultimaSolicitud) {
            // Extraer el número y sumarle 1
            $ultimoNumero = intval(substr($ultimaSolicitud["numero_solicitud"], 3)); // Quitar SOL
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }

        // Formatear con ceros a la izquierda
        $numeroFormateado = "SOL" . str_pad($nuevoNumero, 6, "0", STR_PAD_LEFT);

        
        $stmt = null;

        return $numeroFormateado;
    }

    /*=============================================
    BUSCAR VENTAS PARA REMISIÓN - EN BASE LOCAL (NO CENTRAL)
    =============================================*/
    static public function mdlBuscarVentasRemision($busqueda) {
        
        // ✅ LAS VENTAS ESTÁN EN LA BASE LOCAL DE CADA SUCURSAL
        try {
            $stmt = Conexion::conectar()->prepare("SELECT 
                v.id,
                v.codigo,
                v.fecha,
                v.total,
                c.nombre as nombre_cliente,
                c.documento as documento_cliente
                FROM ventas v 
                LEFT JOIN clientes c ON v.id_cliente = c.id 
                WHERE v.codigo LIKE :busqueda 
                OR c.nombre LIKE :busqueda 
                OR c.documento LIKE :busqueda
                ORDER BY v.fecha DESC 
                LIMIT 10");
            
            $busqueda = "%" . $busqueda . "%";
            $stmt->bindParam(":busqueda", $busqueda, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(Exception $e) {
            error_log("Error buscando ventas: " . $e->getMessage());
            return array();
        }

        
        $stmt = null;
    }

    /*=============================================
    OBTENER PRODUCTOS DE UNA VENTA - EN BASE LOCAL
    =============================================*/
    static public function mdlObtenerProductosVenta($codigoVenta) {
        
        try {
            $stmt = Conexion::conectar()->prepare("SELECT 
                productos
                FROM ventas 
                WHERE codigo = :codigo");
            
            $stmt->bindParam(":codigo", $codigoVenta, PDO::PARAM_STR);
            $stmt->execute();
            
            $venta = $stmt->fetch();
            
            if($venta) {
                $productos = json_decode($venta["productos"], true);
                return $productos;
            } else {
                return array();
            }
            
        } catch(Exception $e) {
            error_log("Error obteniendo productos de venta: " . $e->getMessage());
            return array();
        }

        
        $stmt = null;
    }

    /*=============================================
    CONTAR SOLICITUDES PENDIENTES - BASE CENTRAL
    =============================================*/
    static public function mdlContarSolicitudesPendientes($tabla) {

        $stmt = ConexionCentral::conectar()->prepare("SELECT COUNT(*) as total FROM $tabla WHERE estado = 'pendiente'");

        $stmt->execute();

        $resultado = $stmt->fetch();

        
        $stmt = null;

        return $resultado["total"];
    }

    /*=============================================
    OBTENER SOLICITUDES PENDIENTES - BASE CENTRAL
    =============================================*/
    static public function mdlObtenerSolicitudesPendientes($tabla, $limite) {

        $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 'pendiente' ORDER BY fecha_solicitud DESC LIMIT $limite");

        $stmt->execute();

        return $stmt->fetchAll();

        
        $stmt = null;
    }

    /*=============================================
    MARCAR SOLICITUDES COMO VISTAS - BASE CENTRAL
    =============================================*/
    static public function mdlMarcarComoVista($tabla, $campo, $ids) {

        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        $stmt = ConexionCentral::conectar()->prepare("UPDATE $tabla SET $campo = 1 WHERE id IN ($placeholders)");

        if($stmt->execute($ids)){
            return "ok";
        } else {
            return "error";
        }

        
        $stmt = null;
    }

    /*=============================================
    OBTENER ESTADÍSTICAS - BASE CENTRAL
    =============================================*/
    static public function mdlObtenerEstadisticas($tabla) {

        $stmt = ConexionCentral::conectar()->prepare("SELECT 
            COUNT(*) as total_solicitudes,
            SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
            SUM(CASE WHEN estado = 'aprobado' THEN 1 ELSE 0 END) as aprobadas,
            SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as canceladas,
            SUM(total_productos) as total_productos_solicitados,
            SUM(total_cantidad) as total_cantidad_solicitada
            FROM $tabla");

        $stmt->execute();

        return $stmt->fetch();

        
        $stmt = null;
    }

    /*=============================================
    REGISTRAR LOG DE AUDITORÍA - BASE CENTRAL
    =============================================*/
    static public function mdlRegistrarLog($datos) {

        $stmt = ConexionCentral::conectar()->prepare("INSERT INTO log_solicitudes_stock(
            solicitud_id,
            usuario_id,
            accion,
            estado_anterior,
            estado_nuevo,
            comentario,
            ip_usuario,
            fecha_accion
        ) VALUES (
            :solicitud_id,
            :usuario_id,
            :accion,
            :estado_anterior,
            :estado_nuevo,
            :comentario,
            :ip_usuario,
            NOW()
        )");

        $stmt->bindParam(":solicitud_id", $datos["solicitud_id"], PDO::PARAM_INT);
        $stmt->bindParam(":usuario_id", $datos["usuario_id"], PDO::PARAM_INT);
        $stmt->bindParam(":accion", $datos["accion"], PDO::PARAM_STR);
        $stmt->bindParam(":estado_anterior", $datos["estado_anterior"], PDO::PARAM_STR);
        $stmt->bindParam(":estado_nuevo", $datos["estado_nuevo"], PDO::PARAM_STR);
        $stmt->bindParam(":comentario", $datos["comentario"], PDO::PARAM_STR);
        $stmt->bindParam(":ip_usuario", $datos["ip_usuario"], PDO::PARAM_STR);

        if($stmt->execute()){
            return "ok";
        } else {
            return "error";
        }

        
        $stmt = null;
    }

    /*=============================================
    BUSCAR PRODUCTOS EN CATÁLOGO - BASE LOCAL
    =============================================*/
    static public function mdlBuscarProductosCatalogo($busqueda) {
        
        try {
            // ✅ BUSCAR EN LA BASE LOCAL DE LA SUCURSAL
            $stmt = Conexion::conectar()->prepare("SELECT 
                id, codigo, descripcion, stock, precio_venta
                FROM productos 
                WHERE codigo LIKE :busqueda 
                OR descripcion LIKE :busqueda
                ORDER BY codigo 
                LIMIT 20");
            
            $busqueda = "%" . $busqueda . "%";
            $stmt->bindParam(":busqueda", $busqueda, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch(Exception $e) {
            error_log("Error buscando productos: " . $e->getMessage());
            return array();
        }

        
        $stmt = null;
    }

    /*=============================================
    OBTENER PRODUCTO POR ID - BASE LOCAL
    =============================================*/
    static public function mdlObtenerProducto($id) {
        
        try {
            $stmt = Conexion::conectar()->prepare("SELECT 
                id, codigo, descripcion, stock, precio_venta
                FROM productos 
                WHERE id = :id");
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch(Exception $e) {
            error_log("Error obteniendo producto: " . $e->getMessage());
            return false;
        }

        
        $stmt = null;
    }

    /*=============================================
    VERIFICAR CONEXIONES
    =============================================*/
    static public function mdlVerificarConexiones() {
        
        $resultado = array(
            "conexion_local" => false,
            "conexion_central" => false,
            "tabla_usuarios_local" => false,
            "tabla_solicitudes_central" => false
        );

        try {
            // Verificar conexión local
            $conexionLocal = Conexion::conectar();
            if($conexionLocal) {
                $resultado["conexion_local"] = true;
                
                // Verificar tabla usuarios
                $stmt = $conexionLocal->prepare("SHOW TABLES LIKE 'usuarios'");
                $stmt->execute();
                if($stmt->fetch()) {
                    $resultado["tabla_usuarios_local"] = true;
                }
            }

            // Verificar conexión central
            $conexionCentral = ConexionCentral::conectar();
            if($conexionCentral) {
                $resultado["conexion_central"] = true;
                
                // Verificar tabla solicitudes
                $stmt = $conexionCentral->prepare("SHOW TABLES LIKE 'solicitudes_stock'");
                $stmt->execute();
                if($stmt->fetch()) {
                    $resultado["tabla_solicitudes_central"] = true;
                }
            }

        } catch(Exception $e) {
            error_log("Error verificando conexiones: " . $e->getMessage());
        }

        return $resultado;
    }

    /*=============================================
    OBTENER SOLICITUDES POR SUCURSAL - BASE CENTRAL
    =============================================*/
    static public function mdlObtenerSolicitudesPorSucursal($codigoSucursal, $limite = 10) {
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM solicitudes_stock 
            WHERE codigo_sucursal_solicitante = :codigo_sucursal 
            ORDER BY fecha_solicitud DESC 
            LIMIT $limite");

        $stmt->bindParam(":codigo_sucursal", $codigoSucursal, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll();

        
        $stmt = null;
    }

    /*=============================================
    OBTENER SOLICITUDES POR USUARIO - BASE CENTRAL
    =============================================*/
    static public function mdlObtenerSolicitudesPorUsuario($idUsuario, $limite = 10) {
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM solicitudes_stock 
            WHERE usuario_solicitante = :id_usuario 
            ORDER BY fecha_solicitud DESC 
            LIMIT $limite");

        $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();

        
        $stmt = null;
    }
}