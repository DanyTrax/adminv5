<?php

require_once "conexion.php";

class ModeloDespachos {

/*=============================================
CREAR DESPACHO - SQL CORREGIDO PARA TABLA REAL
=============================================*/
static public function mdlCrearDespacho($tabla, $datos) {
    
    try {
        // Usar conexión central
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        
        $stmt = $conexion->prepare("
            INSERT INTO $tabla (
                numero_despacho, 
                id_solicitud_origen,
                sucursal_origen, 
                sucursal_creador,
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
                :sucursal_creador,
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

        $stmt->bindParam(":numero_despacho", $datos["numero_despacho"], PDO::PARAM_STR);
        $idSol = $datos["id_solicitud_origen"];
        if ($idSol === null || $idSol === '') {
            $stmt->bindValue(":id_solicitud_origen", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":id_solicitud_origen", (int)$idSol, PDO::PARAM_INT);
        }
        $stmt->bindParam(":sucursal_origen", $datos["nombre_sucursal_origen"], PDO::PARAM_STR);
        $stmt->bindParam(":sucursal_creador", $datos["nombre_sucursal_origen"], PDO::PARAM_STR); // ✅ AGREGADO
        $stmt->bindParam(":usuario_creador", $datos["id_usuario_creador"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre_usuario_creador", $datos["nombre_usuario_creador"], PDO::PARAM_STR);
        $stmt->bindParam(":productos_despacho", $datos["productos_despacho"], PDO::PARAM_STR);
        $stmt->bindParam(":total_productos", $datos["total_productos"], PDO::PARAM_INT);
        $stmt->bindParam(":total_cantidad", $datos["total_cantidad"], PDO::PARAM_INT);
        $stmt->bindParam(":detalle_adicional", $datos["detalle_adicional"], PDO::PARAM_STR);

        // Debug: Log SQL y parámetros
        error_log("🔍 SQL INSERT FINAL: " . $stmt->queryString);
        error_log("🔍 Datos a insertar: " . print_r($datos, true));

        if($stmt->execute()) {
            $insertId = $conexion->lastInsertId();
            error_log("✅ INSERT exitoso. ID generado: " . $insertId);
            // Registrar en historial
            self::mdlRegistrarHistorialDespacho(
                $insertId,
                $datos["numero_despacho"],
                "creado",
                null,
                "pendiente",
                "Despacho creado desde " . ($datos["nombre_sucursal_origen"] ?? "sucursal")
            );
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
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
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
            error_log("Error en mdlMostrarDespachos: " . $e->getMessage());
            return false;
        }
    }

/*=============================================
GENERAR NÚMERO DE DESPACHO CON SELECT FOR UPDATE - VERSIÓN SIMPLE
=============================================*/
static public function mdlGenerarNumeroDespacho() {
    
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        
        // ✅ INICIAR TRANSACCIÓN
        $conexion->beginTransaction();
        
        try {
            // ✅ OBTENER SIGUIENTE NÚMERO CON BLOQUEO
            $stmt = $conexion->prepare("
                SELECT COALESCE(MAX(CAST(SUBSTRING(numero_despacho, 5) AS UNSIGNED)), 0) + 1 as siguiente_numero
                FROM despachos 
                WHERE numero_despacho REGEXP '^DESP[0-9]{6}$'
                FOR UPDATE
            ");
            
            $stmt->execute();
            $resultado = $stmt->fetch();
            $siguienteNumero = $resultado["siguiente_numero"];
            
            // Formatear número
            $numeroDespacho = "DESP" . str_pad($siguienteNumero, 6, "0", STR_PAD_LEFT);
            
            // ✅ CONFIRMAR TRANSACCIÓN
            $conexion->commit();
            
            error_log("🔢 Número generado con bloqueo: " . $numeroDespacho);
            
            return $numeroDespacho;
            
        } catch(Exception $e) {
            // ✅ CANCELAR TRANSACCIÓN EN CASO DE ERROR
            $conexion->rollBack();
            throw $e;
        }
        
    } catch(Exception $e) {
        error_log("❌ Error en generación con bloqueo: " . $e->getMessage());
        
        // ✅ RESPALDO: TIMESTAMP + ÚNICO
        $numeroRespaldo = "DESP" . date("YmdHis") . str_pad(rand(1, 999), 3, "0", STR_PAD_LEFT);
        error_log("🔄 Número de respaldo: " . $numeroRespaldo);
        
        return $numeroRespaldo;
    }
}

    /*=============================================
    ACTUALIZAR ESTADO DESPACHO
    =============================================*/
    static public function mdlActualizarEstadoDespacho($tabla, $datos) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            error_log("🔍 mdlActualizarEstadoDespacho - Datos recibidos: " . print_r($datos, true));
            
            $stmt = ConexionCentral::conectar()->prepare("
                UPDATE $tabla 
                SET estado = :estado,
                    transportador_id = :id_transportador,
                    nombre_transportador = :nombre_transportador,
                    fecha_actualizacion = :fecha_aceptacion
                WHERE id = :id
            ");

            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
            $stmt->bindParam(":id_transportador", $datos["id_transportador"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre_transportador", $datos["nombre_transportador"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_aceptacion", $datos["fecha_aceptacion"], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

            $resultado = $stmt->execute();
            
            if($resultado) {
                error_log("✅ mdlActualizarEstadoDespacho - UPDATE exitoso");
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ mdlActualizarEstadoDespacho - Error SQL: " . print_r($errorInfo, true));
                return false;
            }

        } catch(Exception $e) {
            error_log("❌ mdlActualizarEstadoDespacho - Excepción: " . $e->getMessage());
            return false;
        }
    }

    /*=============================================
    VERIFICAR STOCK LOCAL DISPONIBLE (usa conexión local actual)
    =============================================*/
    static public function mdlVerificarStockLocal($codigoProducto, $cantidadRequerida) {
        return self::mdlVerificarStockEnSucursal(Conexion::conectar(), $codigoProducto, $cantidadRequerida);
    }

    /*=============================================
    VERIFICAR STOCK EN SUCURSAL (conexión explícita - para sucursal_origen)
    =============================================*/
    static public function mdlVerificarStockEnSucursal($pdo, $codigoProducto, $cantidadRequerida) {
        try {
            $stmt = $pdo->prepare("
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
    DESCONTAR STOCK LOCAL (usa conexión local actual)
    =============================================*/
    static public function mdlDescontarStockLocal($productos) {
        return self::mdlDescontarStockEnSucursal(Conexion::conectar(), $productos);
    }

    /*=============================================
    DESCONTAR STOCK EN SUCURSAL (conexión explícita - para sucursal_origen)
    El caller debe gestionar beginTransaction/commit/rollBack
    =============================================*/
    static public function mdlDescontarStockEnSucursal($pdo, $productos) {
        try {
            foreach($productos as $producto) {
                $stmt = $pdo->prepare("
                    UPDATE productos 
                    SET stock = stock - :cantidad 
                    WHERE codigo = :codigo
                ");
                $stmt->bindParam(":cantidad", $producto["cantidad"], PDO::PARAM_INT);
                $stmt->bindParam(":codigo", $producto["codigo"], PDO::PARAM_STR);
                if(!$stmt->execute()) {
                    return false;
                }
            }
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    /*=============================================
    OBTENER STOCK EN TRÁNSITO POR DESPACHO (para devolver a sucursal origen)
    Retorna array de {codigo, cantidad} con cantidad_disponible > 0
    =============================================*/
    static public function mdlObtenerStockTransitoPorDespacho($idDespacho) {
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT codigo_producto AS codigo, SUM(cantidad_disponible) AS cantidad
                FROM stock_transito
                WHERE id_despacho_origen = :id AND cantidad_disponible > 0
                GROUP BY codigo_producto
            ");
            $stmt->bindParam(":id", $idDespacho, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("mdlObtenerStockTransitoPorDespacho: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    DEVOLVER STOCK EN SUCURSAL (inverso de mdlDescontarStockEnSucursal)
    Suma cantidad al stock de productos en la sucursal.
    El caller debe gestionar beginTransaction/commit/rollBack
    =============================================*/
    static public function mdlDevolverStockEnSucursal($pdo, $productos) {
        try {
            foreach($productos as $producto) {
                $stmt = $pdo->prepare("
                    UPDATE productos 
                    SET stock = stock + :cantidad 
                    WHERE codigo = :codigo
                ");
                $stmt->bindParam(":cantidad", $producto["cantidad"], PDO::PARAM_INT);
                $stmt->bindParam(":codigo", $producto["codigo"], PDO::PARAM_STR);
                if(!$stmt->execute()) {
                    return false;
                }
            }
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

/*=============================================
BORRAR DESPACHO
=============================================*/
static public function mdlBorrarDespacho($tabla, $item, $valor) {
    
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        
        $stmt = ConexionCentral::conectar()->prepare("DELETE FROM $tabla WHERE $item = :valor");
        $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
        
        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
        
    } catch(Exception $e) {
        return "error: " . $e->getMessage();
    }
}

/*=============================================
ACTUALIZAR DESPACHO - VERSIÓN CORREGIDA
=============================================*/
static public function mdlActualizarDespacho($tabla, $datos, $item, $valor) {
    
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        
        // Construir SQL dinámicamente
        $campos = [];
        $valoresArray = [];
        
        foreach($datos as $key => $value) {
            $campos[] = "`$key` = ?";
            $valoresArray[] = $value;
        }
        
        // Agregar el valor de la condición al final
        $valoresArray[] = $valor;
        
        $sql = "UPDATE `$tabla` SET " . implode(", ", $campos) . " WHERE `$item` = ?";
        
        error_log("🔍 SQL UPDATE: " . $sql);
        error_log("🔍 Valores: " . print_r($valoresArray, true));
        
        $stmt = ConexionCentral::conectar()->prepare($sql);
        
        if($stmt->execute($valoresArray)) {
            error_log("✅ UPDATE exitoso");
            return "ok";
        } else {
            $errorInfo = $stmt->errorInfo();
            $errorMessage = "Error SQL: " . $errorInfo[2] . " (Código: " . $errorInfo[1] . ")";
            error_log("❌ Error en UPDATE: " . $errorMessage);
            error_log("❌ SQL: " . $sql);
            error_log("❌ Valores: " . print_r($valoresArray, true));
            return "error: " . $errorMessage;
        }
        
    } catch(Exception $e) {
        error_log("❌ Excepción en mdlActualizarDespacho: " . $e->getMessage());
        return "error: " . $e->getMessage();
    }
}

/*=============================================
CANCELAR DESPACHO
=============================================*/
static public function mdlCancelarDespacho($tabla, $datos) {
    
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        
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
/*=============================================
REGISTRAR EN HISTORIAL DE DESPACHO
=============================================*/
static public function mdlRegistrarHistorialDespacho($idDespacho, $numeroDespacho, $evento, $estadoAnterior = null, $estadoNuevo = null, $observaciones = null) {
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        $stmt = $conexion->prepare("
            INSERT INTO historial_despachos 
            (id_despacho, numero_despacho, evento, estado_anterior, estado_nuevo, usuario_id, usuario_nombre, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $usuarioId = $_SESSION["id"] ?? null;
        $usuarioNombre = $_SESSION["nombre"] ?? "Sistema";
        $stmt->execute([
            $idDespacho, $numeroDespacho, $evento,
            $estadoAnterior, $estadoNuevo,
            $usuarioId, $usuarioNombre,
            $observaciones
        ]);
        return true;
    } catch (Exception $e) {
        error_log("❌ mdlRegistrarHistorialDespacho: " . $e->getMessage());
        return false;
    }
}

/*=============================================
OBTENER HISTORIAL DE DESPACHO
=============================================*/
static public function mdlObtenerHistorialDespacho($idDespacho) {
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        $stmt = ConexionCentral::conectar()->prepare("
            SELECT * FROM historial_despachos 
            WHERE id_despacho = ? 
            ORDER BY fecha_registro ASC
        ");
        $stmt->execute([$idDespacho]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/*=============================================
OBTENER DESCARGAS DE STOCK EN TRÁNSITO POR DESPACHO
Registros de dónde y quién descargó productos de este despacho
=============================================*/
static public function mdlObtenerDescargasPorDespacho($numeroDespacho) {
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        // Verificar que la tabla existe
        $stmtCheck = $conexion->prepare("SHOW TABLES LIKE 'registro_descargas_stock_transito'");
        $stmtCheck->execute();
        if (!$stmtCheck->fetch()) {
            return [];
        }
        // Buscar por numero_despacho: exacto, al inicio (DESP-001 (Suc: 3)), o en medio/fin (..., DESP-001 (...))
        $stmt = $conexion->prepare("
            SELECT 
                id, codigo_producto, descripcion_producto, cantidad_descargada,
                usuario_nombre, sucursal_nombre, numero_despacho,
                fecha_descarga, observaciones, created_at
            FROM registro_descargas_stock_transito 
            WHERE numero_despacho = ?
               OR numero_despacho LIKE CONCAT(?, ' (%')
               OR numero_despacho LIKE CONCAT('%, ', ?, ' (%')
               OR numero_despacho LIKE CONCAT('%, ', ?, ')')
            ORDER BY fecha_descarga ASC
        ");
        $stmt->execute([$numeroDespacho, $numeroDespacho, $numeroDespacho, $numeroDespacho]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("mdlObtenerDescargasPorDespacho: " . $e->getMessage());
        return [];
    }
}

/*=============================================
OBTENER NOMBRE DE SUCURSAL LOCAL
=============================================*/
static public function mdlObtenerSucursalLocal() {
    
    try {
        $stmt = Conexion::conectar()->prepare("
            SELECT nombre 
            FROM sucursal_local 
            LIMIT 1
        ");
        
        $stmt->execute();
        $sucursal = $stmt->fetch();
        
        if($sucursal && !empty($sucursal["nombre"])) {
            error_log("✅ Sucursal local obtenida desde BD: " . $sucursal["nombre"]);
            return $sucursal["nombre"];
        } else {
            error_log("❌ No se encontró sucursal en tabla sucursal_local");
            return "Sucursal Local";
        }
        
    } catch(Exception $e) {
        error_log("❌ Error obteniendo sucursal local: " . $e->getMessage());
        return "Sucursal Local";
    }
}
}