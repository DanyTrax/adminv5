<?php

require_once "conexion.php";

class ModeloDespachos {

/*=============================================
CREAR DESPACHO CON MANEJO DE CONCURRENCIA
=============================================*/
static public function mdlCrearDespacho($tabla, $datos) {
    
    try {
        require_once "../api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        
        // ✅ INICIAR TRANSACCIÓN PARA TODA LA OPERACIÓN
        $conexion->beginTransaction();
        
        try {
            $stmt = $conexion->prepare("
                INSERT INTO $tabla (
                    numero_despacho, 
                    id_solicitud_origen,
                    nombre_sucursal_origen, 
                    id_usuario_creador,
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
                    :nombre_sucursal_origen,
                    :id_usuario_creador,
                    :nombre_usuario_creador,
                    :productos_despacho,
                    :total_productos,
                    :total_cantidad,
                    :detalle_adicional,
                    'pendiente',
                    NOW()
                )
            ");

            $stmt->bindParam(":numero_despacho", $datos["numero_despacho"], PDO::PARAM_STR);
            $stmt->bindParam(":id_solicitud_origen", $datos["id_solicitud_origen"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre_sucursal_origen", $datos["nombre_sucursal_origen"], PDO::PARAM_STR);
            $stmt->bindParam(":id_usuario_creador", $datos["id_usuario_creador"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre_usuario_creador", $datos["nombre_usuario_creador"], PDO::PARAM_STR);
            $stmt->bindParam(":productos_despacho", $datos["productos_despacho"], PDO::PARAM_STR);
            $stmt->bindParam(":total_productos", $datos["total_productos"], PDO::PARAM_INT);
            $stmt->bindParam(":total_cantidad", $datos["total_cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":detalle_adicional", $datos["detalle_adicional"], PDO::PARAM_STR);

            error_log("🔍 Intentando insertar despacho: " . $datos["numero_despacho"]);

            if($stmt->execute()) {
                $insertId = $conexion->lastInsertId();
                
                // ✅ CONFIRMAR TRANSACCIÓN SOLO SI TODO SALIÓ BIEN
                $conexion->commit();
                
                error_log("✅ Despacho creado exitosamente. ID: " . $insertId);
                return $insertId;
                
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ Error en INSERT: " . print_r($errorInfo, true));
                
                // ✅ CANCELAR TRANSACCIÓN
                $conexion->rollBack();
                return "error: " . $errorInfo[2];
            }

        } catch(Exception $e) {
            // ✅ CANCELAR TRANSACCIÓN EN CASO DE ERROR
            $conexion->rollBack();
            throw $e;
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
GENERAR NÚMERO DE DESPACHO ÚNICO CON BLOQUEO - VERSIÓN MULTI-USUARIO
=============================================*/
static public function mdlGenerarNumeroDespacho() {
    
    try {
        require_once "../api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        
        // ✅ INICIAR TRANSACCIÓN PARA BLOQUEAR TABLA
        $conexion->beginTransaction();
        
        try {
            // ✅ BLOQUEAR TABLA PARA EVITAR CONCURRENCIA
            $lockStmt = $conexion->prepare("LOCK TABLES despachos WRITE");
            $lockStmt->execute();
            
            // Obtener el último número de despacho DENTRO DEL BLOQUEO
            $stmt = $conexion->prepare("
                SELECT numero_despacho 
                FROM despachos 
                WHERE numero_despacho LIKE 'DESP%' 
                ORDER BY id DESC 
                LIMIT 1
                FOR UPDATE
            ");
            
            $stmt->execute();
            $ultimoDespacho = $stmt->fetch();
            
            if($ultimoDespacho) {
                // Extraer el número del último despacho (ejemplo: DESP000001 -> 1)
                $ultimoNumero = (int) substr($ultimoDespacho["numero_despacho"], 4);
                $nuevoNumero = $ultimoNumero + 1;
            } else {
                // Si no hay despachos previos, empezar desde 1
                $nuevoNumero = 1;
            }
            
            // Formatear con ceros a la izquierda (6 dígitos)
            $numeroDespacho = "DESP" . str_pad($nuevoNumero, 6, "0", STR_PAD_LEFT);
            
            // ✅ VERIFICAR QUE NO EXISTE (DOBLE VERIFICACIÓN)
            $verificarStmt = $conexion->prepare("
                SELECT COUNT(*) as existe 
                FROM despachos 
                WHERE numero_despacho = :numero
            ");
            $verificarStmt->bindParam(":numero", $numeroDespacho);
            $verificarStmt->execute();
            $existe = $verificarStmt->fetch();
            
            if($existe["existe"] > 0) {
                // Si existe, generar con timestamp como respaldo
                $numeroDespacho = "DESP" . date("YmdHis") . rand(100, 999);
                error_log("⚠️ Número duplicado detectado, usando respaldo: " . $numeroDespacho);
            }
            
            // ✅ DESBLOQUEAR TABLA
            $unlockStmt = $conexion->prepare("UNLOCK TABLES");
            $unlockStmt->execute();
            
            // ✅ CONFIRMAR TRANSACCIÓN
            $conexion->commit();
            
            // Debug: Log del número generado
            error_log("🔢 Número de despacho generado (multi-usuario): " . $numeroDespacho);
            error_log("🔍 Último número encontrado: " . ($ultimoDespacho ? $ultimoDespacho["numero_despacho"] : "ninguno"));
            error_log("🔢 Nuevo número calculado: " . $nuevoNumero);
            
            return $numeroDespacho;
            
        } catch(Exception $e) {
            // ✅ EN CASO DE ERROR, DESBLOQUEAR Y CANCELAR TRANSACCIÓN
            try {
                $conexion->prepare("UNLOCK TABLES")->execute();
                $conexion->rollBack();
            } catch(Exception $rollbackError) {
                error_log("❌ Error en rollback: " . $rollbackError->getMessage());
            }
            
            throw $e; // Re-lanzar la excepción original
        }
        
    } catch(Exception $e) {
        error_log("❌ Error generando número de despacho (multi-usuario): " . $e->getMessage());
        
        // ✅ GENERAR NÚMERO DE RESPALDO ÚNICO BASADO EN TIMESTAMP + PROCESO
        $numeroRespaldo = "DESP" . date("YmdHis") . getmypid() . rand(10, 99);
        error_log("🔄 Usando número de respaldo único: " . $numeroRespaldo);
        
        return $numeroRespaldo;
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