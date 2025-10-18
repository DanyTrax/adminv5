<?php

require_once "conexion.php";

class ModeloStockTransito {

    /*=============================================
    MOSTRAR STOCK EN TRÁNSITO
    =============================================*/
    static public function mdlMostrarStockTransito($tabla, $item, $valor, $transportador = null) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            if($item != null) {
                $stmt = ConexionCentral::conectar()->prepare("
                    SELECT * FROM $tabla 
                    WHERE $item = :$item 
                    AND cantidad_disponible > 0
                    ORDER BY fecha_carga DESC
                ");
                $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetch();
                
            } else {
                
                $sql = "SELECT * FROM $tabla WHERE cantidad_disponible > 0";
                
                // Filtrar por transportador si es necesario
                if($transportador != null) {
                    $sql .= " AND transportador_id = :transportador";
                }
                
                $sql .= " ORDER BY nombre_transportador ASC, codigo_producto ASC";
                
                $stmt = ConexionCentral::conectar()->prepare($sql);
                
                if($transportador != null) {
                    $stmt->bindParam(":transportador", $transportador, PDO::PARAM_INT);
                }
                
                $stmt->execute();
                return $stmt->fetchAll();
            }

        } catch(Exception $e) {
            return [];
        }
    }

    /*=============================================
    AGREGAR STOCK EN TRÁNSITO (DESDE DESPACHO ACEPTADO)
    =============================================*/
    static public function mdlAgregarStockTransito($productos, $despacho, $sessionTransportador) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();

            foreach($productos as $producto) {
                
                // Verificar si ya existe el producto para este transportador
                $stmt = $conexion->prepare("
                    SELECT id, cantidad_disponible 
                    FROM stock_transito 
                    WHERE codigo_producto = :codigo 
                    AND transportador_id = :transportador_id
                ");
                
                $stmt->bindParam(":codigo", $producto["codigo"], PDO::PARAM_STR);
                $stmt->bindParam(":transportador_id", $sessionTransportador["id"], PDO::PARAM_INT);
                $stmt->execute();
                
                $existente = $stmt->fetch();
                
                if($existente) {
                    // ✅ SUMAR A LA CANTIDAD EXISTENTE
                    $stmt = $conexion->prepare("
                        UPDATE stock_transito 
                        SET cantidad_disponible = cantidad_disponible + :cantidad,
                            observaciones = CONCAT(IFNULL(observaciones, ''), '; Agregado desde despacho: " . $despacho["numero_despacho"] . "')
                        WHERE id = :id
                    ");
                    
                    $stmt->bindParam(":cantidad", $producto["cantidad"], PDO::PARAM_INT);
                    $stmt->bindParam(":id", $existente["id"], PDO::PARAM_INT);
                    
                } else {
                    // ✅ CREAR NUEVO REGISTRO
                    $stmt = $conexion->prepare("
                        INSERT INTO stock_transito 
                        (codigo_producto, descripcion_producto, cantidad_disponible, 
                        transportador_id, nombre_transportador, sucursal_origen, 
                        id_despacho_origen, numero_despacho_origen, observaciones) 
                        VALUES 
                        (:codigo, :descripcion, :cantidad, :transportador_id, :nombre_transportador, 
                        :sucursal_origen, :id_despacho, :numero_despacho, :observaciones)
                    ");
                    
                    $stmt->bindParam(":codigo", $producto["codigo"], PDO::PARAM_STR);
                    $stmt->bindParam(":descripcion", $producto["descripcion"], PDO::PARAM_STR);
                    $stmt->bindParam(":cantidad", $producto["cantidad"], PDO::PARAM_INT);
                    $stmt->bindParam(":transportador_id", $sessionTransportador["id"], PDO::PARAM_INT);
                    $stmt->bindParam(":nombre_transportador", $sessionTransportador["nombre"], PDO::PARAM_STR);
                    $stmt->bindParam(":sucursal_origen", $despacho["nombre_sucursal_origen"], PDO::PARAM_STR);
                    $stmt->bindParam(":id_despacho", $despacho["id"], PDO::PARAM_INT);
                    $stmt->bindParam(":numero_despacho", $despacho["numero_despacho"], PDO::PARAM_STR);
                    $stmt->bindParam(":observaciones", $producto["observacion"], PDO::PARAM_STR);
                }
                
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
    CREAR SOLICITUD DE DESCARGA
    =============================================*/
    static public function mdlCrearSolicitudDescarga($tabla, $datos) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                INSERT INTO $tabla 
                (id_stock_transito, codigo_producto, cantidad_solicitada, sucursal_destino, 
                id_usuario_solicitante, nombre_usuario_solicitante, transportador_id, 
                nombre_transportador, observaciones) 
                VALUES 
                (:id_stock_transito, :codigo_producto, :cantidad_solicitada, :sucursal_destino,
                :id_usuario_solicitante, :nombre_usuario_solicitante, :transportador_id,
                :nombre_transportador, :observaciones)
            ");

            $stmt->bindParam(":id_stock_transito", $datos["id_stock_transito"], PDO::PARAM_INT);
            $stmt->bindParam(":codigo_producto", $datos["codigo_producto"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad_solicitada", $datos["cantidad_solicitada"], PDO::PARAM_INT);
            $stmt->bindParam(":sucursal_destino", $datos["sucursal_destino"], PDO::PARAM_STR);
            $stmt->bindParam(":id_usuario_solicitante", $datos["id_usuario_solicitante"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre_usuario_solicitante", $datos["nombre_usuario_solicitante"], PDO::PARAM_STR);
            $stmt->bindParam(":transportador_id", $datos["transportador_id"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre_transportador", $datos["nombre_transportador"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

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
    DESCONTAR STOCK EN TRÁNSITO
    =============================================*/
    static public function mdlDescontarStockTransito($idStockTransito, $cantidad) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                UPDATE stock_transito 
                SET cantidad_disponible = cantidad_disponible - :cantidad 
                WHERE id = :id 
                AND cantidad_disponible >= :cantidad
            ");
            
            $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
            $stmt->bindParam(":id", $idStockTransito, PDO::PARAM_INT);
            
            return $stmt->execute() && $stmt->rowCount() > 0;

        } catch(Exception $e) {
            return false;
        }
    }

    /*=============================================
    AGREGAR STOCK LOCAL (AL CONFIRMAR DESCARGA)
    =============================================*/
    static public function mdlAgregarStockLocal($codigoProducto, $cantidad) {
        
        try {
            $conexion = Conexion::conectar();
            
            $stmt = $conexion->prepare("
                UPDATE productos 
                SET stock = stock + :cantidad 
                WHERE codigo = :codigo
            ");
            
            $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
            $stmt->bindParam(":codigo", $codigoProducto, PDO::PARAM_STR);
            
            return $stmt->execute();

        } catch(Exception $e) {
            return false;
        }
    }

    /*=============================================
    OBTENER SOLICITUD DE DESCARGA
    =============================================*/
    static public function mdlObtenerSolicitudDescarga($idSolicitud) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT * FROM solicitudes_descarga 
                WHERE id = :id
            ");
            
            $stmt->bindParam(":id", $idSolicitud, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();

        } catch(Exception $e) {
            return false;
        }
    }

    /*=============================================
    ACTUALIZAR ESTADO DE SOLICITUD
    =============================================*/
    static public function mdlActualizarEstadoSolicitud($idSolicitud, $estado, $observacionesConfirmacion = null, $motivoRechazo = null) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $sql = "UPDATE solicitudes_descarga 
                    SET estado = :estado, 
                        fecha_respuesta = CURRENT_TIMESTAMP";
            
            if($observacionesConfirmacion !== null) {
                $sql .= ", observaciones = CONCAT(IFNULL(observaciones, ''), '; Confirmación: ', :observaciones_confirmacion)";
            }
            
            if($motivoRechazo !== null) {
                $sql .= ", motivo_rechazo = :motivo_rechazo";
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = ConexionCentral::conectar()->prepare($sql);
            
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id", $idSolicitud, PDO::PARAM_INT);
            
            if($observacionesConfirmacion !== null) {
                $stmt->bindParam(":observaciones_confirmacion", $observacionesConfirmacion, PDO::PARAM_STR);
            }
            
            if($motivoRechazo !== null) {
                $stmt->bindParam(":motivo_rechazo", $motivoRechazo, PDO::PARAM_STR);
            }
            
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
    CONTAR SOLICITUDES PENDIENTES
    =============================================*/
    static public function mdlContarSolicitudesPendientes($codigoProducto, $transportadorId) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT COUNT(*) as total 
                FROM solicitudes_descarga 
                WHERE codigo_producto = :codigo_producto 
                AND transportador_id = :transportador_id 
                AND estado = 'pendiente'
            ");
            
            $stmt->bindParam(":codigo_producto", $codigoProducto, PDO::PARAM_STR);
            $stmt->bindParam(":transportador_id", $transportadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return $resultado["total"] ?? 0;

        } catch(Exception $e) {
            return 0;
        }
    }

    /*=============================================
    OBTENER CANTIDAD SOLICITADA PENDIENTE
    =============================================*/
    static public function mdlObtenerCantidadSolicitadaPendiente($codigoProducto, $transportadorId) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT SUM(cantidad_solicitada) as total_solicitado
                FROM solicitudes_descarga 
                WHERE codigo_producto = :codigo_producto 
                AND transportador_id = :transportador_id 
                AND estado = 'pendiente'
            ");
            
            $stmt->bindParam(":codigo_producto", $codigoProducto, PDO::PARAM_STR);
            $stmt->bindParam(":transportador_id", $transportadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return intval($resultado["total_solicitado"] ?? 0);

        } catch(Exception $e) {
            return 0;
        }
    }

    /*=============================================
    OBTENER SOLICITUDES PENDIENTES DE UN TRANSPORTADOR
    =============================================*/
    static public function mdlObtenerSolicitudesPendientesTransportador($transportadorId) {
        
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT sd.*, st.descripcion_producto, st.sucursal_origen
                FROM solicitudes_descarga sd
                INNER JOIN stock_transito st ON sd.id_stock_transito = st.id
                WHERE sd.transportador_id = :transportador_id 
                AND sd.estado = 'pendiente'
                ORDER BY sd.fecha_solicitud DESC
            ");
            
            $stmt->bindParam(":transportador_id", $transportadorId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();

        } catch(Exception $e) {
            return [];
        }
    }
}