<?php

require_once "conexion.php";

class ModeloDespachos {

/*=============================================
CREAR DESPACHO - VERSIÓN CORREGIDA CON NOMBRES CORRECTOS
=============================================*/
static public function mdlCrearDespacho($tabla, $datos) {
    
    try {
        // Usar conexión central
        require_once "../api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        
        $stmt = $conexion->prepare("
            INSERT INTO $tabla (
                numero_despacho, 
                id_solicitud_origen,
                sucursal_origen, 
                usuario_creador,
                nombre_usuario_creador,
                productos_despacho, 
                total_productos,
                total_cantidad,
                detalle_adicional,
                estado,
                fecha_creacion
            ) VALUES (
                :numero_despacho,
                :id_solicitud_origen,
                :sucursal_origen,
                :usuario_creador,
                :nombre_usuario_creador,
                :productos_despacho,
                :total_productos,
                :total_cantidad,
                :detalle_adicional,
                'pendiente',
                NOW()
            )
        ");

        // NOMBRES CORREGIDOS EN LOS BIND:
        $stmt->bindParam(":numero_despacho", $datos["numero_despacho"], PDO::PARAM_STR);
        $stmt->bindParam(":id_solicitud_origen", $datos["id_solicitud_origen"], PDO::PARAM_INT);
        $stmt->bindParam(":sucursal_origen", $datos["nombre_sucursal_origen"], PDO::PARAM_STR);     // ✅ CORREGIDO
        $stmt->bindParam(":usuario_creador", $datos["id_usuario_creador"], PDO::PARAM_INT);        // ✅ CORREGIDO
        $stmt->bindParam(":nombre_usuario_creador", $datos["nombre_usuario_creador"], PDO::PARAM_STR);
        $stmt->bindParam(":productos_despacho", $datos["productos_despacho"], PDO::PARAM_STR);
        $stmt->bindParam(":total_productos", $datos["total_productos"], PDO::PARAM_INT);
        $stmt->bindParam(":total_cantidad", $datos["total_cantidad"], PDO::PARAM_INT);
        $stmt->bindParam(":detalle_adicional", $datos["detalle_adicional"], PDO::PARAM_STR);

        // Debug: Log SQL y parámetros
        error_log("🔍 SQL INSERT CORREGIDO: " . $stmt->queryString);
        error_log("🔍 Datos a insertar: " . print_r($datos, true));

        if($stmt->execute()) {
            $insertId = $conexion->lastInsertId();
            error_log("✅ INSERT exitoso. ID generado: " . $insertId);
            return $insertId;
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("❌ Error en INSERT: " . print_r($errorInfo, true));
            return "error";
        }

    } catch(Exception $e) {
        error_log("❌ Excepción en mdlCrearDespacho: " . $e->getMessage());
        return "error: " . $e->getMessage();
    }
}

    /*=============================================
    MOSTRAR DESPACHOS
    =============================================*/
    static public function mdlMostrarDespachos($tabla, $item, $valor) {
        
        try {
            require_once "../api-transferencias/conexion-central.php";
            
            if($item != null) {
                $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY fecha_creacion DESC");
                $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetch();
            } else {
                $stmt = ConexionCentral::conectar()->prepare("SELECT * FROM $tabla ORDER BY fecha_creacion DESC");
                $stmt->execute();
                return $stmt->fetchAll();
            }

        } catch(Exception $e) {
            return [];
        }
    }

    /*=============================================
    GENERAR NÚMERO DE DESPACHO
    =============================================*/
    static public function mdlGenerarNumeroDespacho() {
        
        try {
            require_once "../api-transferencias/conexion-central.php";
            
            // Actualizar secuencia
            $stmt = ConexionCentral::conectar()->prepare("
                UPDATE secuencia_despachos 
                SET ultimo_numero = ultimo_numero + 1 
                WHERE id = 1
            ");
            $stmt->execute();
            
            // Obtener nuevo número
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT ultimo_numero FROM secuencia_despachos WHERE id = 1
            ");
            $stmt->execute();
            $resultado = $stmt->fetch();
            
            $numero = $resultado['ultimo_numero'];
            return "DESP" . str_pad($numero, 6, "0", STR_PAD_LEFT);
            
        } catch(Exception $e) {
            return "DESP000001"; // Fallback
        }
    }

    /*=============================================
    ACTUALIZAR ESTADO DESPACHO
    =============================================*/
    static public function mdlActualizarEstadoDespacho($tabla, $datos) {
        
        try {
            require_once "../api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                UPDATE $tabla 
                SET estado = :estado,
                    id_transportador_asignado = :id_transportador,
                    nombre_transportador_asignado = :nombre_transportador,
                    fecha_aceptacion = :fecha_aceptacion
                WHERE id = :id
            ");

            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":id_transportador", $datos["id_transportador"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre_transportador", $datos["nombre_transportador"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_aceptacion", $datos["fecha_aceptacion"], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

            return $stmt->execute();

        } catch(Exception $e) {
            return false;
        }
    }

    /*=============================================
    VERIFICAR STOCK LOCAL DISPONIBLE
    =============================================*/
    static public function mdlVerificarStockLocal($codigoProducto, $cantidadRequerida) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT stock FROM productos 
                WHERE codigo = :codigo AND stock >= :cantidad
            ");
            
            $stmt->bindParam(":codigo", $codigoProducto, PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $cantidadRequerida, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch() ? true : false;

        } catch(Exception $e) {
            return false;
        }
    }

    /*=============================================
    DESCONTAR STOCK LOCAL
    =============================================*/
    static public function mdlDescontarStockLocal($productos) {
        
        try {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();

            foreach($productos as $producto) {
                $stmt = $conexion->prepare("
                    UPDATE productos 
                    SET stock = stock - :cantidad 
                    WHERE codigo = :codigo
                ");
                
                $stmt->bindParam(":cantidad", $producto["cantidad"], PDO::PARAM_INT);
                $stmt->bindParam(":codigo", $producto["codigo"], PDO::PARAM_STR);
                
                if(!$stmt->execute()) {
                    $conexion->rollBack();
                    return false;
                }
            }

            $conexion->commit();
            return true;

        } catch(Exception $e) {
            if($conexion) {
                $conexion->rollBack();
            }
            return false;
        }
    }
    /*=============================================
BORRAR DESPACHO
=============================================*/
static public function mdlBorrarDespacho($tabla, $id) {
    
    try {
        require_once "../api-transferencias/conexion-central.php";
        
        $stmt = ConexionCentral::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

    } catch(Exception $e) {
        return "error";
    }
}

/*=============================================
CANCELAR DESPACHO
=============================================*/
static public function mdlCancelarDespacho($tabla, $datos) {
    
    try {
        require_once "../api-transferencias/conexion-central.php";
        
        $stmt = ConexionCentral::conectar()->prepare("
            UPDATE $tabla 
            SET estado = :estado, 
                motivo_cancelacion = :motivo_cancelacion 
            WHERE id = :id
        ");

        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":motivo_cancelacion", $datos["motivo_cancelacion"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

    } catch(Exception $e) {
        return "error";
    }
}

/*=============================================
OBTENER TRANSPORTADORES ACTIVOS
=============================================*/
static public function mdlObtenerTransportadores() {
    
    try {
        $stmt = Conexion::conectar()->prepare("
            SELECT id, nombre 
            FROM usuarios 
            WHERE perfil = 'Transportador' 
            AND estado = 1 
            ORDER BY nombre ASC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();

    } catch(Exception $e) {
        return [];
    }
}
}