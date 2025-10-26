<?php
/*=============================================
MODELO MEDIOS DE PAGO CENTRAL
=============================================*/

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

class ModeloMediosPagoCentral {
    
    /*=============================================
    OBTENER MEDIOS DE PAGO CENTRALES
    =============================================*/
    static public function mdlObtenerMediosPagoCentral() {
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    id,
                    codigo,
                    nombre,
                    descripcion,
                    tipo,
                    activo,
                    fecha_creacion,
                    fecha_actualizacion
                FROM medios_pago_central 
                ORDER BY codigo ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerMediosPagoCentral: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES PARA ESTADO
    =============================================*/
    static public function mdlObtenerSucursalesEstado() {
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    id,
                    codigo_sucursal,
                    nombre,
                    activo
                FROM sucursales 
                WHERE activo = 1
                ORDER BY nombre ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSucursalesEstado: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER ESTADO DE MEDIOS POR SUCURSAL
    =============================================*/
    static public function mdlObtenerEstadoMediosSucursal($sucursalId) {
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    mpc.id,
                    mpc.codigo,
                    mpc.nombre,
                    mpc.tipo,
                    COALESCE(mps.activo, 0) as activo
                FROM medios_pago_central mpc
                LEFT JOIN medios_pago_sucursal mps ON mpc.id = mps.medio_pago_id AND mps.sucursal_id = ?
                WHERE mpc.activo = 1
                ORDER BY mpc.codigo ASC
            ");
            
            $stmt->execute([$sucursalId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerEstadoMediosSucursal: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES DESTINO PARA ACTIVACIÓN
    =============================================*/
    static public function mdlObtenerSucursalesDestinoActivar() {
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    id,
                    codigo_sucursal,
                    nombre,
                    activo
                FROM sucursales 
                WHERE activo = 1
                ORDER BY nombre ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSucursalesDestinoActivar: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES DESTINO PARA DESACTIVACIÓN
    =============================================*/
    static public function mdlObtenerSucursalesDestinoDesactivar() {
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    id,
                    codigo_sucursal,
                    nombre,
                    activo
                FROM sucursales 
                WHERE activo = 1
                ORDER BY nombre ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSucursalesDestinoDesactivar: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER ESTADO COMPLETO
    =============================================*/
    static public function mdlObtenerEstadoCompleto() {
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    s.id,
                    s.nombre,
                    mpc.id as medio_id,
                    mpc.nombre as medio_nombre,
                    COALESCE(mps.activo, 0) as activo
                FROM sucursales s
                CROSS JOIN medios_pago_central mpc
                LEFT JOIN medios_pago_sucursal mps ON mpc.id = mps.medio_pago_id AND s.id = mps.sucursal_id
                WHERE s.activo = 1 AND mpc.activo = 1
                ORDER BY s.nombre, mpc.codigo
            ");
            
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agrupar por sucursal
            $sucursales = [];
            foreach ($resultados as $row) {
                $sucursalId = $row['id'];
                if (!isset($sucursales[$sucursalId])) {
                    $sucursales[$sucursalId] = [
                        'id' => $row['id'],
                        'nombre' => $row['nombre'],
                        'medios' => []
                    ];
                }
                
                $sucursales[$sucursalId]['medios'][] = [
                    'id' => $row['medio_id'],
                    'nombre' => $row['medio_nombre'],
                    'activo' => $row['activo']
                ];
            }
            
            return array_values($sucursales);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerEstadoCompleto: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER ESTADO DE MEDIO ESPECÍFICO
    =============================================*/
    static public function mdlObtenerEstadoMedio($medioId) {
        try {
            // Obtener datos del medio
            $stmtMedio = ConexionCentral::conectar()->prepare("
                SELECT id, codigo, nombre, tipo
                FROM medios_pago_central 
                WHERE id = ?
            ");
            $stmtMedio->execute([$medioId]);
            $medio = $stmtMedio->fetch(PDO::FETCH_ASSOC);
            
            // Obtener estado por sucursal
            $stmtSucursales = ConexionCentral::conectar()->prepare("
                SELECT 
                    s.id,
                    s.nombre,
                    COALESCE(mps.activo, 0) as activo
                FROM sucursales s
                LEFT JOIN medios_pago_sucursal mps ON s.id = mps.sucursal_id AND mps.medio_pago_id = ?
                WHERE s.activo = 1
                ORDER BY s.nombre
            ");
            $stmtSucursales->execute([$medioId]);
            $sucursales = $stmtSucursales->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'medio' => $medio,
                'sucursales' => $sucursales
            ];
        } catch (Exception $e) {
            error_log("Error en mdlObtenerEstadoMedio: " . $e->getMessage());
            return ['medio' => null, 'sucursales' => []];
        }
    }
    
    /*=============================================
    CREAR MEDIO DE PAGO
    =============================================*/
    static public function mdlCrearMedioPago($datos) {
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                INSERT INTO medios_pago_central (codigo, nombre, descripcion, tipo) 
                VALUES (:codigo, :nombre, :descripcion, :tipo)
            ");
            
            $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Medio de pago creado correctamente'];
            } else {
                return ['success' => false, 'error' => 'Error al crear medio de pago'];
            }
        } catch (Exception $e) {
            error_log("Error en mdlCrearMedioPago: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /*=============================================
    ACTIVAR MEDIOS EN SUCURSALES
    =============================================*/
    static public function mdlActivarMediosSucursales($mediosPago, $sucursales) {
        try {
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
            
            $activaciones = 0;
            
            foreach ($mediosPago as $medioId) {
                foreach ($sucursales as $sucursalId) {
                    // Verificar si ya existe la asignación
                    $stmtVerificar = $conexion->prepare("
                        SELECT id, activo FROM medios_pago_sucursal 
                        WHERE medio_pago_id = ? AND sucursal_id = ?
                    ");
                    $stmtVerificar->execute([$medioId, $sucursalId]);
                    $asignacion = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
                    
                    if ($asignacion) {
                        // Actualizar estado existente
                        if (!$asignacion['activo']) {
                            $stmtActualizar = $conexion->prepare("
                                UPDATE medios_pago_sucursal 
                                SET activo = 1, fecha_actualizacion = NOW()
                                WHERE id = ?
                            ");
                            $stmtActualizar->execute([$asignacion['id']]);
                            $activaciones++;
                        }
                    } else {
                        // Crear nueva asignación activa
                        $stmtInsertar = $conexion->prepare("
                            INSERT INTO medios_pago_sucursal (medio_pago_id, sucursal_id, activo) 
                            VALUES (?, ?, 1)
                        ");
                        $stmtInsertar->execute([$medioId, $sucursalId]);
                        $activaciones++;
                    }
                }
            }
            
            $conexion->commit();
            return ['success' => true, 'activaciones' => $activaciones];
            
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlActivarMediosSucursales: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /*=============================================
    DESACTIVAR MEDIOS EN SUCURSALES
    =============================================*/
    static public function mdlDesactivarMediosSucursales($mediosPago, $sucursales) {
        try {
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
            
            $desactivaciones = 0;
            
            foreach ($mediosPago as $medioId) {
                foreach ($sucursales as $sucursalId) {
                    // Verificar si existe la asignación
                    $stmtVerificar = $conexion->prepare("
                        SELECT id, activo FROM medios_pago_sucursal 
                        WHERE medio_pago_id = ? AND sucursal_id = ?
                    ");
                    $stmtVerificar->execute([$medioId, $sucursalId]);
                    $asignacion = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
                    
                    if ($asignacion && $asignacion['activo']) {
                        // Desactivar asignación existente
                        $stmtDesactivar = $conexion->prepare("
                            UPDATE medios_pago_sucursal 
                            SET activo = 0, fecha_actualizacion = NOW()
                            WHERE id = ?
                        ");
                        $stmtDesactivar->execute([$asignacion['id']]);
                        $desactivaciones++;
                    }
                }
            }
            
            $conexion->commit();
            return ['success' => true, 'desactivaciones' => $desactivaciones];
            
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlDesactivarMediosSucursales: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /*=============================================
    TOGGLE ESTADO DE MEDIO EN SUCURSAL
    =============================================*/
    static public function mdlToggleEstadoMedioSucursal($medioId, $sucursalId, $nuevoEstado) {
        try {
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
            
            // Verificar si existe la asignación
            $stmtVerificar = $conexion->prepare("
                SELECT id FROM medios_pago_sucursal 
                WHERE medio_pago_id = ? AND sucursal_id = ?
            ");
            $stmtVerificar->execute([$medioId, $sucursalId]);
            $asignacion = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
            
            if ($asignacion) {
                // Actualizar estado existente
                $stmtActualizar = $conexion->prepare("
                    UPDATE medios_pago_sucursal 
                    SET activo = ?, fecha_actualizacion = NOW()
                    WHERE id = ?
                ");
                $stmtActualizar->execute([$nuevoEstado, $asignacion['id']]);
            } else {
                // Crear nueva asignación
                $stmtInsertar = $conexion->prepare("
                    INSERT INTO medios_pago_sucursal (medio_pago_id, sucursal_id, activo) 
                    VALUES (?, ?, ?)
                ");
                $stmtInsertar->execute([$medioId, $sucursalId, $nuevoEstado]);
            }
            
            $conexion->commit();
            return ['success' => true, 'message' => 'Estado actualizado correctamente'];
            
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlToggleEstadoMedioSucursal: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /*=============================================
    ELIMINAR MEDIO DE PAGO
    =============================================*/
    static public function mdlEliminarMedioPago($id) {
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                DELETE FROM medios_pago_central WHERE id = ?
            ");
            
            if ($stmt->execute([$id])) {
                return ['success' => true, 'message' => 'Medio de pago eliminado correctamente'];
            } else {
                return ['success' => false, 'error' => 'Error al eliminar medio de pago'];
            }
        } catch (Exception $e) {
            error_log("Error en mdlEliminarMedioPago: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>
