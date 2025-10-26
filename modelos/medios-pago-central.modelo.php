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
<<<<<<< HEAD
    OBTENER SUCURSALES PARA ESTADO
    =============================================*/
    static public function mdlObtenerSucursalesEstado() {
=======
    OBTENER SUCURSALES PARA ASIGNACIÓN
    =============================================*/
    static public function mdlObtenerSucursalesAsignacion() {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
            error_log("Error en mdlObtenerSucursalesEstado: " . $e->getMessage());
=======
            error_log("Error en mdlObtenerSucursalesAsignacion: " . $e->getMessage());
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return [];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
    OBTENER ESTADO DE MEDIOS POR SUCURSAL
    =============================================*/
    static public function mdlObtenerEstadoMediosSucursal($sucursalId) {
=======
    OBTENER MEDIOS ASIGNADOS POR SUCURSAL
    =============================================*/
    static public function mdlObtenerMediosAsignadosSucursal($sucursalId) {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    mpc.id,
                    mpc.codigo,
                    mpc.nombre,
                    mpc.tipo,
<<<<<<< HEAD
                    COALESCE(mps.activo, 0) as activo
                FROM medios_pago_central mpc
                LEFT JOIN medios_pago_sucursal mps ON mpc.id = mps.medio_pago_id AND mps.sucursal_id = ?
                WHERE mpc.activo = 1
=======
                    mps.activo
                FROM medios_pago_central mpc
                INNER JOIN medios_pago_sucursal mps ON mpc.id = mps.medio_pago_id
                WHERE mps.sucursal_id = ?
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                ORDER BY mpc.codigo ASC
            ");
            
            $stmt->execute([$sucursalId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
<<<<<<< HEAD
            error_log("Error en mdlObtenerEstadoMediosSucursal: " . $e->getMessage());
=======
            error_log("Error en mdlObtenerMediosAsignadosSucursal: " . $e->getMessage());
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return [];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
    OBTENER SUCURSALES DESTINO PARA ACTIVACIÓN
    =============================================*/
    static public function mdlObtenerSucursalesDestinoActivar() {
=======
    OBTENER SUCURSALES DISPONIBLES PARA ASIGNACIÓN
    =============================================*/
    static public function mdlObtenerSucursalesDisponiblesAsignacion() {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
            error_log("Error en mdlObtenerSucursalesDestinoActivar: " . $e->getMessage());
=======
            error_log("Error en mdlObtenerSucursalesDisponiblesAsignacion: " . $e->getMessage());
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return [];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
    OBTENER SUCURSALES DESTINO PARA DESACTIVACIÓN
    =============================================*/
    static public function mdlObtenerSucursalesDestinoDesactivar() {
=======
    OBTENER SUCURSALES DESTINO PARA COPIA MASIVA
    =============================================*/
    static public function mdlObtenerSucursalesDestinoCopia() {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
            error_log("Error en mdlObtenerSucursalesDestinoDesactivar: " . $e->getMessage());
=======
            error_log("Error en mdlObtenerSucursalesDestinoCopia: " . $e->getMessage());
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return [];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
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
=======
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
    ACTIVAR MEDIOS EN SUCURSALES
    =============================================*/
    static public function mdlActivarMediosSucursales($mediosPago, $sucursales) {
=======
    ASIGNAR MEDIOS A SUCURSALES
    =============================================*/
    static public function mdlAsignarMediosSucursales($mediosPago, $sucursales) {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        try {
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
            
<<<<<<< HEAD
            $activaciones = 0;
=======
            $asignaciones = 0;
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            
            foreach ($mediosPago as $medioId) {
                foreach ($sucursales as $sucursalId) {
                    // Verificar si ya existe la asignación
                    $stmtVerificar = $conexion->prepare("
<<<<<<< HEAD
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
=======
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
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                    }
                }
            }
            
            $conexion->commit();
<<<<<<< HEAD
            return ['success' => true, 'activaciones' => $activaciones];
            
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlActivarMediosSucursales: " . $e->getMessage());
=======
            return ['success' => true, 'asignaciones' => $asignaciones];
            
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlAsignarMediosSucursales: " . $e->getMessage());
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
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
=======
    COPIAR MEDIOS MASIVO A BD LOCAL
    =============================================*/
    static public function mdlCopiarMediosMasivo($mediosPago, $sucursales) {
        try {
            $conexionCentral = ConexionCentral::conectar();
            $conexionCentral->beginTransaction();
            
            $copias = 0;
            
            foreach ($mediosPago as $medioId) {
                // Obtener datos del medio desde BD central
                $stmtMedio = $conexionCentral->prepare("
                    SELECT codigo, nombre, descripcion, tipo 
                    FROM medios_pago_central 
                    WHERE id = ?
                ");
                $stmtMedio->execute([$medioId]);
                $medio = $stmtMedio->fetch(PDO::FETCH_ASSOC);
                
                if ($medio) {
                    foreach ($sucursales as $sucursalId) {
                        // Obtener datos de conexión de la sucursal
                        $stmtSucursal = $conexionCentral->prepare("
                            SELECT host_bd, nombre_bd, usuario_bd, password_bd, puerto_bd
                            FROM sucursales 
                            WHERE id = ?
                        ");
                        $stmtSucursal->execute([$sucursalId]);
                        $sucursal = $stmtSucursal->fetch(PDO::FETCH_ASSOC);
                        
                        if ($sucursal) {
                            try {
                                // Conectar a BD local de la sucursal
                                $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8mb4";
                                $conexionLocal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
                                $conexionLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                
                                // Verificar si el medio ya existe en BD local
                                $stmtVerificar = $conexionLocal->prepare("
                                    SELECT id FROM medios_pago 
                                    WHERE nombre = ? AND activo = 1
                                ");
                                $stmtVerificar->execute([$medio['nombre']]);
                                
                                if (!$stmtVerificar->fetch()) {
                                    // Insertar medio en BD local
                                    $stmtInsertar = $conexionLocal->prepare("
                                        INSERT INTO medios_pago (nombre, activo) 
                                        VALUES (?, 1)
                                    ");
                                    $stmtInsertar->execute([$medio['nombre']]);
                                    $copias++;
                                }
                                
                                // Crear asignación en BD central
                                $stmtAsignar = $conexionCentral->prepare("
                                    INSERT IGNORE INTO medios_pago_sucursal (medio_pago_id, sucursal_id) 
                                    VALUES (?, ?)
                                ");
                                $stmtAsignar->execute([$medioId, $sucursalId]);
                                
                            } catch (Exception $e) {
                                error_log("Error conectando a sucursal {$sucursalId}: " . $e->getMessage());
                            }
                        }
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
                    }
                }
            }
            
<<<<<<< HEAD
            $conexion->commit();
            return ['success' => true, 'desactivaciones' => $desactivaciones];
            
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlDesactivarMediosSucursales: " . $e->getMessage());
=======
            $conexionCentral->commit();
            return ['success' => true, 'copias' => $copias];
            
        } catch (Exception $e) {
            $conexionCentral->rollBack();
            error_log("Error en mdlCopiarMediosMasivo: " . $e->getMessage());
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
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
=======
    SINCRONIZAR TODOS LOS MEDIOS A BD LOCAL
    =============================================*/
    static public function mdlSincronizarTodosMedios() {
        try {
            $conexionCentral = ConexionCentral::conectar();
            $conexionCentral->beginTransaction();
            
            // Obtener todos los medios de pago activos
            $stmtMedios = $conexionCentral->prepare("
                SELECT id, codigo, nombre, descripcion, tipo 
                FROM medios_pago_central 
                WHERE activo = 1
            ");
            $stmtMedios->execute();
            $medios = $stmtMedios->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener todas las sucursales activas
            $stmtSucursales = $conexionCentral->prepare("
                SELECT id, host_bd, nombre_bd, usuario_bd, password_bd, puerto_bd
                FROM sucursales 
                WHERE activo = 1
            ");
            $stmtSucursales->execute();
            $sucursales = $stmtSucursales->fetchAll(PDO::FETCH_ASSOC);
            
            $sincronizados = 0;
            
            foreach ($medios as $medio) {
                foreach ($sucursales as $sucursal) {
                    try {
                        // Conectar a BD local de la sucursal
                        $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8mb4";
                        $conexionLocal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
                        $conexionLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        // Verificar si el medio ya existe en BD local
                        $stmtVerificar = $conexionLocal->prepare("
                            SELECT id FROM medios_pago 
                            WHERE nombre = ? AND activo = 1
                        ");
                        $stmtVerificar->execute([$medio['nombre']]);
                        
                        if (!$stmtVerificar->fetch()) {
                            // Insertar medio en BD local
                            $stmtInsertar = $conexionLocal->prepare("
                                INSERT INTO medios_pago (nombre, activo) 
                                VALUES (?, 1)
                            ");
                            $stmtInsertar->execute([$medio['nombre']]);
                            $sincronizados++;
                        }
                        
                        // Crear asignación en BD central
                        $stmtAsignar = $conexionCentral->prepare("
                            INSERT IGNORE INTO medios_pago_sucursal (medio_pago_id, sucursal_id) 
                            VALUES (?, ?)
                        ");
                        $stmtAsignar->execute([$medio['id'], $sucursal['id']]);
                        
                    } catch (Exception $e) {
                        error_log("Error sincronizando medio {$medio['id']} con sucursal {$sucursal['id']}: " . $e->getMessage());
                    }
                }
            }
            
            $conexionCentral->commit();
            return ['success' => true, 'sincronizados' => $sincronizados];
            
        } catch (Exception $e) {
            $conexionCentral->rollBack();
            error_log("Error en mdlSincronizarTodosMedios: " . $e->getMessage());
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
=======
    
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
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
}
?>
