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
    OBTENER SUCURSALES PARA ASIGNACIÓN
    =============================================*/
    static public function mdlObtenerSucursalesAsignacion() {
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
            error_log("Error en mdlObtenerSucursalesAsignacion: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER MEDIOS ASIGNADOS POR SUCURSAL
    =============================================*/
    static public function mdlObtenerMediosAsignadosSucursal($sucursalId) {
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    mpc.id,
                    mpc.codigo,
                    mpc.nombre,
                    mpc.tipo,
                    mps.activo
                FROM medios_pago_central mpc
                INNER JOIN medios_pago_sucursal mps ON mpc.id = mps.medio_pago_id
                WHERE mps.sucursal_id = ?
                ORDER BY mpc.codigo ASC
            ");
            
            $stmt->execute([$sucursalId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerMediosAsignadosSucursal: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES DISPONIBLES PARA ASIGNACIÓN
    =============================================*/
    static public function mdlObtenerSucursalesDisponiblesAsignacion() {
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
            error_log("Error en mdlObtenerSucursalesDisponiblesAsignacion: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES DESTINO PARA COPIA MASIVA
    =============================================*/
    static public function mdlObtenerSucursalesDestinoCopia() {
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
            error_log("Error en mdlObtenerSucursalesDestinoCopia: " . $e->getMessage());
            return [];
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
    ASIGNAR MEDIOS A SUCURSALES
    =============================================*/
    static public function mdlAsignarMediosSucursales($mediosPago, $sucursales) {
        try {
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
            
            $asignaciones = 0;
            
            foreach ($mediosPago as $medioId) {
                foreach ($sucursales as $sucursalId) {
                    // Verificar si ya existe la asignación
                    $stmtVerificar = $conexion->prepare("
                        SELECT id FROM medios_pago_sucursal 
                        WHERE medio_pago_id = ? AND sucursal_id = ?
                    ");
                    $stmtVerificar->execute([$medioId, $sucursalId]);
                    
                    if (!$stmtVerificar->fetch()) {
                        // Crear nueva asignación
                        $stmtAsignar = $conexion->prepare("
                            INSERT INTO medios_pago_sucursal (medio_pago_id, sucursal_id) 
                            VALUES (?, ?)
                        ");
                        $stmtAsignar->execute([$medioId, $sucursalId]);
                        $asignaciones++;
                    }
                }
            }
            
            $conexion->commit();
            return ['success' => true, 'asignaciones' => $asignaciones];
            
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlAsignarMediosSucursales: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /*=============================================
    COPIAR MEDIOS MASIVO
    =============================================*/
    static public function mdlCopiarMediosMasivo($mediosPago, $sucursales) {
        try {
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
            
            $copias = 0;
            
            foreach ($mediosPago as $medioId) {
                foreach ($sucursales as $sucursalId) {
                    // Verificar si ya existe la asignación
                    $stmtVerificar = $conexion->prepare("
                        SELECT id FROM medios_pago_sucursal 
                        WHERE medio_pago_id = ? AND sucursal_id = ?
                    ");
                    $stmtVerificar->execute([$medioId, $sucursalId]);
                    
                    if (!$stmtVerificar->fetch()) {
                        // Crear nueva asignación
                        $stmtCopiar = $conexion->prepare("
                            INSERT INTO medios_pago_sucursal (medio_pago_id, sucursal_id) 
                            VALUES (?, ?)
                        ");
                        $stmtCopiar->execute([$medioId, $sucursalId]);
                        $copias++;
                    }
                }
            }
            
            $conexion->commit();
            return ['success' => true, 'copias' => $copias];
            
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlCopiarMediosMasivo: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /*=============================================
    SINCRONIZAR TODOS LOS MEDIOS
    =============================================*/
    static public function mdlSincronizarTodosMedios() {
        try {
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
            
            // Obtener todos los medios de pago activos
            $stmtMedios = $conexion->prepare("
                SELECT id FROM medios_pago_central WHERE activo = 1
            ");
            $stmtMedios->execute();
            $medios = $stmtMedios->fetchAll(PDO::FETCH_COLUMN);
            
            // Obtener todas las sucursales activas
            $stmtSucursales = $conexion->prepare("
                SELECT id FROM sucursales WHERE activo = 1
            ");
            $stmtSucursales->execute();
            $sucursales = $stmtSucursales->fetchAll(PDO::FETCH_COLUMN);
            
            $sincronizados = 0;
            
            foreach ($medios as $medioId) {
                foreach ($sucursales as $sucursalId) {
                    // Verificar si ya existe la asignación
                    $stmtVerificar = $conexion->prepare("
                        SELECT id FROM medios_pago_sucursal 
                        WHERE medio_pago_id = ? AND sucursal_id = ?
                    ");
                    $stmtVerificar->execute([$medioId, $sucursalId]);
                    
                    if (!$stmtVerificar->fetch()) {
                        // Crear nueva asignación
                        $stmtSincronizar = $conexion->prepare("
                            INSERT INTO medios_pago_sucursal (medio_pago_id, sucursal_id) 
                            VALUES (?, ?)
                        ");
                        $stmtSincronizar->execute([$medioId, $sucursalId]);
                        $sincronizados++;
                    }
                }
            }
            
            $conexion->commit();
            return ['success' => true, 'sincronizados' => $sincronizados];
            
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlSincronizarTodosMedios: " . $e->getMessage());
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
    
    /*=============================================
    DESASIGNAR MEDIO DE SUCURSAL
    =============================================*/
    static public function mdlDesasignarMedioSucursal($medioId, $sucursalId) {
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                DELETE FROM medios_pago_sucursal 
                WHERE medio_pago_id = ? AND sucursal_id = ?
            ");
            
            if ($stmt->execute([$medioId, $sucursalId])) {
                return ['success' => true, 'message' => 'Medio de pago desasignado correctamente'];
            } else {
                return ['success' => false, 'error' => 'Error al desasignar medio de pago'];
            }
        } catch (Exception $e) {
            error_log("Error en mdlDesasignarMedioSucursal: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>
