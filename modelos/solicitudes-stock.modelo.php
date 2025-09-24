<?php

require_once "conexion.php";
require_once __DIR__ . "/../api-transferencias/conexion-central.php";

class ModeloSolicitudesStock {

    /*=============================================
    CREAR SOLICITUD DE STOCK
    =============================================*/
    static public function mdlCrearSolicitud($tabla, $datos) {
        
        $stmt = ConexionCentral::conectar()->prepare("INSERT INTO $tabla(
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
            estado,
            fecha_solicitud,
            total_productos,
            total_cantidad,
            notificado_transportador,
            notificado_administrador
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
            'pendiente',
            NOW(),
            :total_productos,
            :total_cantidad,
            1,
            1
        )");

        $stmt->bindParam(":numero_solicitud", $datos["numero_solicitud"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo_sucursal_solicitante", $datos["codigo_sucursal_solicitante"], PDO::PARAM_STR);
        $stmt->bindParam(":nombre_sucursal_solicitante", $datos["nombre_sucursal_solicitante"], PDO::PARAM_STR);
        $stmt->bindParam(":usuario_solicitante", $datos["usuario_solicitante"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre_usuario_solicitante", $datos["nombre_usuario_solicitante"], PDO::PARAM_STR);
        $stmt->bindParam(":productos_solicitados", $datos["productos_solicitados"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_solicitud", $datos["tipo_solicitud"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo_remision", $datos["codigo_remision"], PDO::PARAM_STR);
        $stmt->bindParam(":nombre_cliente_remision", $datos["nombre_cliente_remision"], PDO::PARAM_STR);
        $stmt->bindParam(":detalle_adicional", $datos["detalle_adicional"], PDO::PARAM_STR);
        $stmt->bindParam(":total_productos", $datos["total_productos"], PDO::PARAM_INT);
        $stmt->bindParam(":total_cantidad", $datos["total_cantidad"], PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    MOSTRAR SOLICITUDES
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

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    MOSTRAR SOLICITUDES CON INFORMACIÓN DE USUARIOS
    =============================================*/
    static public function mdlMostrarSolicitudesCompletas($tabla) {
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT 
            s.*,
            u1.nombre as nombre_usuario_solicitante_actual,
            u1.usuario as usuario_solicitante_actual,
            u2.nombre as nombre_usuario_aprobacion_actual,
            u2.usuario as usuario_aprobacion_actual
            FROM $tabla s
            LEFT JOIN usuarios u1 ON s.usuario_solicitante = u1.id
            LEFT JOIN usuarios u2 ON s.usuario_aprobacion = u2.id
            ORDER BY s.fecha_solicitud DESC");
            
        $stmt->execute();
        return $stmt->fetchAll();

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    CONTAR SOLICITUDES PENDIENTES PARA NOTIFICACIONES
    =============================================*/
    static public function mdlContarSolicitudesPendientes($tabla) {
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT COUNT(*) as total FROM $tabla WHERE estado = 'pendiente'");
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        return $resultado["total"];

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    OBTENER SOLICITUDES PENDIENTES PARA NOTIFICACIONES
    =============================================*/
    static public function mdlObtenerSolicitudesPendientes($tabla, $limite = 5) {
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT 
            numero_solicitud,
            nombre_sucursal_solicitante,
            nombre_usuario_solicitante,
            fecha_solicitud,
            total_productos
            FROM $tabla 
            WHERE estado = 'pendiente' 
            ORDER BY fecha_solicitud DESC 
            LIMIT :limite");
            
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    ACTUALIZAR ESTADO DE SOLICITUD
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

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    MARCAR SOLICITUDES COMO VISTAS
    =============================================*/
    static public function mdlMarcarComoVista($tabla, $campo, $ids) {
        
        if(is_array($ids) && !empty($ids)) {
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $sql = "UPDATE $tabla SET $campo = 1 WHERE id IN ($placeholders)";
            
            $stmt = ConexionCentral::conectar()->prepare($sql);
            $stmt->execute($ids);
            
            return "ok";
        }
        
        return "error";

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    ELIMINAR SOLICITUD
    =============================================*/
    static public function mdlEliminarSolicitud($tabla, $datos) {
        
        $stmt = ConexionCentral::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $datos, PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    BUSCAR VENTAS PARA REMISIÓN
    =============================================*/
    static public function mdlBuscarVentasRemision($busqueda) {
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT 
            v.id,
            v.codigo,
            v.fecha_venta,
            v.total,
            c.nombre as nombre_cliente,
            c.documento as documento_cliente
            FROM ventas v 
            LEFT JOIN clientes c ON v.id_cliente = c.id 
            WHERE v.codigo LIKE :busqueda 
            OR c.nombre LIKE :busqueda 
            OR c.documento LIKE :busqueda
            ORDER BY v.fecha_venta DESC 
            LIMIT 10");
        
        $busqueda = "%" . $busqueda . "%";
        $stmt->bindParam(":busqueda", $busqueda, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    OBTENER PRODUCTOS DE UNA VENTA
    =============================================*/
    static public function mdlObtenerProductosVenta($codigoVenta) {
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT 
            p.id,
            p.codigo,
            p.descripcion,
            p.imagen,
            p.stock,
            p.precio_venta
            FROM ventas v
            INNER JOIN productos p ON FIND_IN_SET(p.id, v.productos)
            WHERE v.codigo = :codigo");
        
        $stmt->bindParam(":codigo", $codigoVenta, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    GENERAR NÚMERO DE SOLICITUD
    =============================================*/
    static public function mdlGenerarNumeroSolicitud($tabla) {
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT numero_solicitud FROM $tabla ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        if($resultado) {
            // Extraer el número del último registro (formato: SOL000001)
            $ultimoNumero = intval(substr($resultado["numero_solicitud"], 3));
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        // Formatear con ceros a la izquierda
        return "SOL" . str_pad($nuevoNumero, 6, "0", STR_PAD_LEFT);

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    REGISTRAR ACCIÓN EN LOG
    =============================================*/
    static public function mdlRegistrarLog($datos) {
        
        $stmt = ConexionCentral::conectar()->prepare("INSERT INTO solicitudes_stock_log(
            solicitud_id,
            usuario_id,
            accion,
            estado_anterior,
            estado_nuevo,
            comentario,
            fecha_accion,
            ip_usuario
        ) VALUES (
            :solicitud_id,
            :usuario_id,
            :accion,
            :estado_anterior,
            :estado_nuevo,
            :comentario,
            NOW(),
            :ip_usuario
        )");

        $stmt->bindParam(":solicitud_id", $datos["solicitud_id"], PDO::PARAM_INT);
        $stmt->bindParam(":usuario_id", $datos["usuario_id"], PDO::PARAM_INT);
        $stmt->bindParam(":accion", $datos["accion"], PDO::PARAM_STR);
        $stmt->bindParam(":estado_anterior", $datos["estado_anterior"], PDO::PARAM_STR);
        $stmt->bindParam(":estado_nuevo", $datos["estado_nuevo"], PDO::PARAM_STR);
        $stmt->bindParam(":comentario", $datos["comentario"], PDO::PARAM_STR);
        $stmt->bindParam(":ip_usuario", $datos["ip_usuario"], PDO::PARAM_STR);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    OBTENER ESTADÍSTICAS
    =============================================*/
    static public function mdlObtenerEstadisticas($tabla) {
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
            COUNT(CASE WHEN estado = 'aprobado' THEN 1 END) as aprobadas,
            COUNT(CASE WHEN estado = 'cancelado' THEN 1 END) as canceladas,
            COUNT(CASE WHEN DATE(fecha_solicitud) = CURDATE() THEN 1 END) as hoy,
            COUNT(CASE WHEN WEEK(fecha_solicitud) = WEEK(CURDATE()) THEN 1 END) as esta_semana
            FROM $tabla");
            
        $stmt->execute();
        return $stmt->fetch();

        $stmt->close();
        $stmt = null;
    }

    /*=============================================
    BUSCAR SOLICITUDES
    =============================================*/
    static public function mdlBuscarSolicitudes($tabla, $termino) {
        
        $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM $tabla 
            WHERE numero_solicitud LIKE :termino 
            OR nombre_sucursal_solicitante LIKE :termino 
            OR nombre_usuario_solicitante LIKE :termino 
            OR detalle_adicional LIKE :termino
            ORDER BY fecha_solicitud DESC");
        
        $termino = "%" . $termino . "%";
        $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();

        $stmt->close();
        $stmt = null;
    }
}