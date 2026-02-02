<?php

require_once __DIR__ . "/../api-transferencias/conexion-central.php";
require_once "conexion.php";

class ModeloUsuariosCentral {

    /*=============================================
    CREAR USUARIO EN BD CENTRAL
    =============================================*/
    static public function mdlCrearUsuarioCentral($datos) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            // Log de datos recibidos para debugging
            error_log("Datos recibidos en mdlCrearUsuarioCentral: " . json_encode($datos));
            
            // Verificar si el usuario ya existe
            $stmt = $conexion->prepare("
                SELECT id FROM usuarios_central 
                WHERE usuario = :usuario
            ");
            $stmt->bindParam(":usuario", $datos['usuario'], PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'error' => 'El usuario ya existe en el sistema central'
                ];
            }
            
            // Insertar usuario en usuarios_central
            $stmt = $conexion->prepare("
                INSERT INTO usuarios_central (
                    nombre, usuario, password, perfil, foto, 
                    telefono, direccion, activo, 
                    sincronizado, fecha_creacion
                ) VALUES (
                    :nombre, :usuario, :password, :perfil, :foto,
                    :telefono, :direccion, 1,
                    0, NOW()
                )
            ");
            
            $foto = !empty($datos['foto']) ? $datos['foto'] : 'vistas/img/usuarios/default/anonymous.png';
            
            // Limpiar y validar el campo perfil
            $perfil = trim($datos['perfil']);
            $perfilesValidos = ['Administrador', 'Especial', 'Vendedor', 'Contador', 'Transportador', 'Limitado'];
            
            if (!in_array($perfil, $perfilesValidos)) {
                error_log("Perfil inválido recibido: '" . $perfil . "'");
                return [
                    'success' => false,
                    'error' => 'Perfil no válido: ' . $perfil
                ];
            }
            
            // Log del perfil procesado
            error_log("Perfil procesado: '" . $perfil . "' (longitud: " . strlen($perfil) . ")");
            
            $stmt->bindValue(":nombre", $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindValue(":usuario", $datos['usuario'], PDO::PARAM_STR);
            $stmt->bindValue(":password", $datos['password'], PDO::PARAM_STR);
            $stmt->bindValue(":perfil", $perfil, PDO::PARAM_STR);
            $stmt->bindValue(":foto", $foto, PDO::PARAM_STR);
            $stmt->bindValue(":telefono", $datos['telefono'], PDO::PARAM_STR);
            $stmt->bindValue(":direccion", $datos['direccion'], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Usuario central creado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error creando usuario central'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlCrearUsuarioCentral: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno del servidor'
            ];
        }
    }

    /*=============================================
    OBTENER USUARIOS CENTRALES
    =============================================*/
    static public function mdlObtenerUsuariosCentral($item = null, $valor = null) {
        
        try {
            $sql = "
                SELECT 
                    uc.*
                FROM usuarios_central uc
                WHERE uc.activo = 1
            ";
            
            $params = [];
            
            if ($item != null) {
                $sql .= " AND uc.$item = ?";
                $params[] = $valor;
            }
            
            $sql .= " ORDER BY uc.fecha_creacion DESC";
            
            $stmt = ConexionCentral::conectar()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerUsuariosCentral: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    OBTENER SUCURSALES CENTRALES
    =============================================*/
    static public function mdlObtenerSucursalesCentral() {
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    id, nombre, codigo_sucursal, activo,
                    direccion, telefono, email, url_base, url_api,
                    es_principal, fecha_registro, fecha_actualizacion
                FROM sucursales 
                WHERE activo = 1 
                ORDER BY nombre
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSucursalesCentral: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    OBTENER USUARIOS PENDIENTES DE SINCRONIZACIÓN
    =============================================*/
    static public function mdlObtenerUsuariosPendientesSincronizacion() {
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    uc.*,
                    s.nombre as nombre_sucursal,
                    s.codigo_sucursal
                FROM usuarios_central uc
                LEFT JOIN sucursales s ON uc.sucursal_id = s.id
                WHERE uc.activo = 1 AND uc.sincronizado = 0
                ORDER BY uc.fecha_creacion ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerUsuariosPendientesSincronizacion: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    OBTENER SUCURSALES DESTINO PARA SINCRONIZACIÓN
    =============================================*/
    static public function mdlObtenerSucursalesDestino($sucursalId) {
        
        try {
            // Obtener todas las sucursales excepto la principal del usuario
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT id, nombre, codigo_sucursal, url_api
                FROM sucursales 
                WHERE activo = 1 AND id != ?
                ORDER BY nombre
            ");
            
            $stmt->execute([$sucursalId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSucursalesDestino: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    SINCRONIZAR USUARIO CON SUCURSAL
    =============================================*/
    static public function mdlSincronizarUsuarioSucursal($usuario, $sucursal) {
        
        try {
            // Aquí implementarías la lógica para sincronizar con la sucursal específica
            // Por ahora, simulamos la sincronización
            
            // Registrar en tabla de sincronización
            $stmt = ConexionCentral::conectar()->prepare("
                INSERT INTO sincronizacion_usuarios 
                (usuario_central_id, sucursal_destino_id, estado, fecha_sincronizacion)
                VALUES (?, ?, 'sincronizado', NOW())
                ON DUPLICATE KEY UPDATE 
                estado = 'sincronizado', 
                fecha_sincronizacion = NOW()
            ");
            
            $stmt->execute([$usuario['id'], $sucursal['id']]);
            
            return [
                'success' => true,
                'message' => "Usuario sincronizado en {$sucursal['nombre']}"
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlSincronizarUsuarioSucursal: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /*=============================================
    MARCAR USUARIO COMO SINCRONIZADO
    =============================================*/
    static public function mdlMarcarUsuarioSincronizado($usuarioId) {
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                UPDATE usuarios_central 
                SET sincronizado = 1, fecha_sincronizacion = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([$usuarioId]);
            return "ok";
            
        } catch (Exception $e) {
            error_log("Error en mdlMarcarUsuarioSincronizado: " . $e->getMessage());
            return "error";
        }
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DE SINCRONIZACIÓN
    =============================================*/
    static public function mdlObtenerEstadisticasSincronizacion() {
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_usuarios,
                    SUM(CASE WHEN sincronizado = 1 THEN 1 ELSE 0 END) as sincronizados,
                    SUM(CASE WHEN sincronizado = 0 THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos
                FROM usuarios_central
            ");
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Obtener estadísticas de errores de sincronización
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT COUNT(*) as errores
                FROM sincronizacion_usuarios 
                WHERE estado = 'error'
            ");
            
            $stmt->execute();
            $errores = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return array_merge($resultado, $errores);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerEstadisticasSincronizacion: " . $e->getMessage());
            return [
                'total_usuarios' => 0,
                'sincronizados' => 0,
                'pendientes' => 0,
                'inactivos' => 0,
                'errores' => 0
            ];
        }
    }

    /*=============================================
    IMPORTAR USUARIO INDIVIDUAL
    =============================================*/
    static public function mdlImportarUsuarioIndividual($usuario) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            // Verificar si la columna id_local existe
            $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios_central LIKE 'id_local'");
            $stmt->execute();
            $idLocalExiste = $stmt->fetch();
            
            // Verificar si el usuario ya existe
            if ($idLocalExiste) {
                // Si existe id_local, verificar por usuario o id_local
                $stmt = $conexion->prepare("
                    SELECT id FROM usuarios_central 
                    WHERE usuario = :usuario OR id_local = :id_local
                ");
                $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                $stmt->bindParam(":id_local", $usuario['id'], PDO::PARAM_INT);
            } else {
                // Si no existe id_local, solo verificar por usuario
                $stmt = $conexion->prepare("
                    SELECT id FROM usuarios_central 
                    WHERE usuario = :usuario
                ");
                $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
            }
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'error' => 'El usuario ya existe en el sistema central'
                ];
            }
            
            // Insertar usuario en usuarios_central
            if ($idLocalExiste) {
                // Si existe id_local, incluirlo en el INSERT
                $stmt = $conexion->prepare("
                    INSERT INTO usuarios_central (
                        nombre, usuario, password, perfil, foto, 
                        telefono, direccion, activo, 
                        sincronizado, fecha_creacion, id_local
                    ) VALUES (
                        :nombre, :usuario, :password, :perfil, :foto,
                        :telefono, :direccion, 1,
                        0, NOW(), :id_local
                    )
                ");
            } else {
                // Si no existe id_local, no incluirlo en el INSERT
                $stmt = $conexion->prepare("
                    INSERT INTO usuarios_central (
                        nombre, usuario, password, perfil, foto, 
                        telefono, direccion, activo, 
                        sincronizado, fecha_creacion
                    ) VALUES (
                        :nombre, :usuario, :password, :perfil, :foto,
                        :telefono, :direccion, 1,
                        0, NOW()
                    )
                ");
            }
            
            $stmt->bindParam(":nombre", $usuario['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
            $stmt->bindParam(":password", $usuario['password'], PDO::PARAM_STR);
            $stmt->bindParam(":perfil", $usuario['perfil'], PDO::PARAM_STR);
            $stmt->bindParam(":foto", $usuario['foto'], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $usuario['telefono'], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $usuario['direccion'], PDO::PARAM_STR);
            if ($idLocalExiste) {
                $stmt->bindParam(":id_local", $usuario['id'], PDO::PARAM_INT);
            }
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Usuario importado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al insertar usuario en base de datos'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlImportarUsuarioIndividual: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al importar usuario: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    IMPORTAR TODOS LOS USUARIOS DE SUCURSALES
    =============================================*/
    static public function mdlImportarUsuariosSucursales() {
        
        try {
            $conexion = ConexionCentral::conectar();
            $usuariosImportados = 0;
            $errores = [];
            
            // Obtener usuarios de todas las sucursales
            $sucursales = self::mdlConsultarUsuariosSucursales();
            
            foreach ($sucursales as $sucursal) {
                if ($sucursal['estado_conexion'] === 'conectado' && !empty($sucursal['usuarios'])) {
                    foreach ($sucursal['usuarios'] as $usuario) {
                        // Verificar si el usuario ya existe
                        $stmt = $conexion->prepare("
                            SELECT id FROM usuarios_central 
                            WHERE usuario = :usuario
                        ");
                        $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                        $stmt->execute();
                        
                        if (!$stmt->fetch()) {
                            // Insertar usuario
                            $stmt = $conexion->prepare("
                                INSERT INTO usuarios_central (
                                    nombre, usuario, password, perfil, foto, 
                                    telefono, direccion, activo, 
                                    sincronizado, fecha_creacion
                                ) VALUES (
                                    :nombre, :usuario, :password, :perfil, :foto,
                                    :telefono, :direccion, 1,
                                    0, NOW()
                                )
                            ");
                            
                            // Asegurar que todos los campos tengan valores por defecto
                            $password = !empty($usuario['password']) ? $usuario['password'] : 'password123';
                            $telefono = !empty($usuario['telefono']) ? $usuario['telefono'] : '';
                            $direccion = !empty($usuario['direccion']) ? $usuario['direccion'] : '';
                            $foto = !empty($usuario['foto']) ? $usuario['foto'] : 'vistas/img/usuarios/default/anonymous.png';
                            
                            $stmt->bindParam(":nombre", $usuario['nombre'], PDO::PARAM_STR);
                            $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                            $stmt->bindParam(":password", $password, PDO::PARAM_STR);
                            $stmt->bindParam(":perfil", $usuario['perfil'], PDO::PARAM_STR);
                            $stmt->bindParam(":foto", $foto, PDO::PARAM_STR);
                            $stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
                            $stmt->bindParam(":direccion", $direccion, PDO::PARAM_STR);
                            
                            if ($stmt->execute()) {
                                $usuariosImportados++;
                            } else {
                                $errores[] = "Error importando usuario: " . $usuario['usuario'];
                            }
                        }
                    }
                }
            }
            
            return [
                'success' => true,
                'message' => "Se importaron {$usuariosImportados} usuarios exitosamente",
                'usuarios_importados' => $usuariosImportados,
                'errores' => $errores
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlImportarUsuariosSucursales: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al importar usuarios: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    IMPORTAR USUARIOS DE SUCURSAL ESPECÍFICA
    =============================================*/
    static public function mdlImportarUsuariosSucursalEspecifica($sucursalId) {
        
        try {
            $conexion = ConexionCentral::conectar();
            $usuariosImportados = 0;
            $errores = [];
            
            // Obtener información de la sucursal específica
            $sucursales = self::mdlConsultarUsuariosSucursales($sucursalId);
            
            if (empty($sucursales)) {
                return [
                    'success' => false,
                    'error' => 'Sucursal no encontrada o sin usuarios'
                ];
            }
            
            $sucursal = $sucursales[0]; // Solo una sucursal específica
            
            if ($sucursal['estado_conexion'] === 'conectado' && !empty($sucursal['usuarios'])) {
                foreach ($sucursal['usuarios'] as $usuario) {
                    // Verificar si el usuario ya existe
                    $stmt = $conexion->prepare("
                        SELECT id FROM usuarios_central 
                        WHERE usuario = :usuario
                    ");
                    $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                    $stmt->execute();
                    
                    if (!$stmt->fetch()) {
                        // Insertar usuario con asignación a la sucursal
                        $stmt = $conexion->prepare("
                            INSERT INTO usuarios_central (
                                nombre, usuario, password, perfil, foto, 
                                telefono, direccion, activo, 
                                sucursales_asignadas, sincronizado, fecha_creacion
                            ) VALUES (
                                :nombre, :usuario, :password, :perfil, :foto,
                                :telefono, :direccion, 1,
                                :sucursales_asignadas, 0, NOW()
                            )
                        ");
                        
                        // Asegurar que todos los campos tengan valores por defecto
                        $password = !empty($usuario['password']) ? $usuario['password'] : 'password123';
                        $telefono = !empty($usuario['telefono']) ? $usuario['telefono'] : '';
                        $direccion = !empty($usuario['direccion']) ? $usuario['direccion'] : '';
                        $foto = !empty($usuario['foto']) ? $usuario['foto'] : 'vistas/img/usuarios/default/anonymous.png';
                        $sucursalesAsignadas = $sucursalId; // Asignar a la sucursal de origen
                        
                        $stmt->bindParam(":nombre", $usuario['nombre'], PDO::PARAM_STR);
                        $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                        $stmt->bindParam(":password", $password, PDO::PARAM_STR);
                        $stmt->bindParam(":perfil", $usuario['perfil'], PDO::PARAM_STR);
                        $stmt->bindParam(":foto", $foto, PDO::PARAM_STR);
                        $stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
                        $stmt->bindParam(":direccion", $direccion, PDO::PARAM_STR);
                        $stmt->bindParam(":sucursales_asignadas", $sucursalesAsignadas, PDO::PARAM_STR);
                        
                        if ($stmt->execute()) {
                            $usuariosImportados++;
                        } else {
                            $errores[] = "Error importando usuario: " . $usuario['usuario'];
                        }
                    } else {
                        // Usuario ya existe, actualizar sucursales asignadas
                        $stmt = $conexion->prepare("
                            UPDATE usuarios_central 
                            SET sucursales_asignadas = CONCAT(
                                IF(sucursales_asignadas IS NULL OR sucursales_asignadas = '', '', CONCAT(sucursales_asignadas, ',')),
                                :sucursal_id
                            )
                            WHERE usuario = :usuario 
                            AND (sucursales_asignadas IS NULL OR sucursales_asignadas = '' OR sucursales_asignadas NOT LIKE CONCAT('%', :sucursal_id, '%'))
                        ");
                        $stmt->bindParam(":sucursal_id", $sucursalId, PDO::PARAM_STR);
                        $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                        $stmt->execute();
                    }
                }
            } else {
                return [
                    'success' => false,
                    'error' => 'No se pudo conectar a la sucursal o no hay usuarios disponibles'
                ];
            }
            
            return [
                'success' => true,
                'message' => "Se importaron {$usuariosImportados} usuarios de la sucursal exitosamente",
                'usuarios_importados' => $usuariosImportados,
                'errores' => $errores,
                'sucursal' => $sucursal['sucursal']['nombre']
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlImportarUsuariosSucursalEspecifica: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al importar usuarios: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    ELIMINAR USUARIO CENTRAL
    =============================================*/
    static public function mdlEliminarUsuarioCentral($usuarioId) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            // Obtener información del usuario antes de eliminarlo
            $stmt = $conexion->prepare("
                SELECT usuario, sucursales_asignadas 
                FROM usuarios_central 
                WHERE id = ? AND activo = 1
            ");
            $stmt->execute([$usuarioId]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                return [
                    'success' => false,
                    'error' => 'Usuario no encontrado o ya eliminado'
                ];
            }
            
            // Eliminar usuario de todas las sucursales asignadas
            if (!empty($usuario['sucursales_asignadas'])) {
                $sucursalesIds = explode(',', $usuario['sucursales_asignadas']);
                
                foreach ($sucursalesIds as $sucursalId) {
                    $sucursalId = trim($sucursalId);
                    if ($sucursalId !== '') {
                        try {
                            // Obtener datos de conexión de la sucursal
                            $stmtSucursal = $conexion->prepare("
                                SELECT nombre, host_bd, usuario_bd, password_bd, nombre_bd, puerto_bd
                                FROM sucursales 
                                WHERE id = ? AND activo = 1
                            ");
                            $stmtSucursal->execute([$sucursalId]);
                            $sucursal = $stmtSucursal->fetch(PDO::FETCH_ASSOC);
                            
                            if ($sucursal) {
                                // Conectar a la sucursal
                                $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8";
                                $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
                                $pdoSucursal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                
                                // Eliminar usuario de la sucursal
                                $stmtEliminar = $pdoSucursal->prepare("
                                    DELETE FROM usuarios 
                                    WHERE usuario = :usuario OR id = :id_central
                                ");
                                $stmtEliminar->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                                $stmtEliminar->bindParam(":id_central", $usuarioId, PDO::PARAM_INT);
                                $stmtEliminar->execute();
                                
                                error_log("Usuario '{$usuario['usuario']}' eliminado de sucursal '{$sucursal['nombre']}'");
                            }
                        } catch (Exception $e) {
                            error_log("Error eliminando usuario de sucursal {$sucursalId}: " . $e->getMessage());
                            // Continuar con las demás sucursales aunque falle una
                        }
                    }
                }
            }
            
            // Eliminar usuario de la base de datos central
            $stmt = $conexion->prepare("
                UPDATE usuarios_central 
                SET activo = 0, fecha_actualizacion = NOW()
                WHERE id = ?
            ");
            
            if ($stmt->execute([$usuarioId])) {
                return [
                    'success' => true,
                    'message' => 'Usuario eliminado exitosamente de todas las sucursales y del sistema central'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error eliminando usuario del sistema central'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlEliminarUsuarioCentral: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ];
        }
    }

    /*=============================================
    ACTUALIZAR USUARIO CENTRAL
    =============================================*/
    static public function mdlActualizarUsuarioCentral($usuarioId, $datos) {
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                UPDATE usuarios_central 
                SET nombre = :nombre, 
                    perfil = :perfil, 
                    telefono = :telefono,
                    direccion = :direccion,
                    observaciones = :observaciones,
                    fecha_actualizacion = NOW()
                WHERE id = :id
            ");
            
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
            $stmt->bindParam(":id", $usuarioId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlActualizarUsuarioCentral: " . $e->getMessage());
            return "error";
        }
    }

    /*=============================================
    CONSULTAR USUARIOS DE SUCURSALES ESPECÍFICAS
    =============================================*/
    static public function mdlConsultarUsuariosSucursales($sucursalId = null) {
        
        try {
            $conexionCentral = ConexionCentral::conectar();
            $conexionLocal = Conexion::conectar();
            
            // Obtener información de la sucursal (incluyendo campos de conexión)
            // Excluir sucursales que apunten a la misma BD local
            $stmt = $conexionCentral->prepare("
                SELECT 
                    id, nombre, codigo_sucursal, url_api, activo,
                    usuario_bd, password_bd, nombre_bd, host_bd, puerto_bd
                FROM sucursales 
                WHERE activo = 1 
                " . ($sucursalId ? "AND id = ?" : "") . "
                ORDER BY nombre
            ");
            
            if($sucursalId) {
                $stmt->execute([$sucursalId]);
            } else {
                $stmt->execute();
            }
            
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $resultado = [];
            
            // Obtener información de la sucursal actual (local)
            $sucursalActual = self::mdlObtenerSucursalActual();
            
            foreach($sucursales as $sucursal) {
                // Intentar conectar directamente a la BD de la sucursal
                $usuariosSucursal = self::mdlConsultarUsuariosSucursalRemota($sucursal);
                
                // Determinar si es la sucursal actual
                $esActual = false;
                if ($sucursalActual && 
                    $sucursal['host_bd'] === $sucursalActual['host_bd'] && 
                    $sucursal['nombre_bd'] === $sucursalActual['nombre_bd']) {
                    $esActual = true;
                }
                
                $resultado[] = [
                    'sucursal' => $sucursal,
                    'usuarios' => $usuariosSucursal['usuarios'],
                    'estado_conexion' => $usuariosSucursal['estado'],
                    'total_usuarios' => $usuariosSucursal['total'],
                    'error' => $usuariosSucursal['error'],
                    'es_actual' => $esActual
                ];
            }
            
            return $resultado;
            
        } catch(Exception $e) {
            error_log("Error en mdlConsultarUsuariosSucursales: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    OBTENER SUCURSAL ACTUAL (LOCAL)
    =============================================*/
    static public function mdlObtenerSucursalActual() {
        
        try {
            // Obtener código de sucursal desde tabla local
            require_once __DIR__ . "/conexion.php";
            $conexionLocal = Conexion::conectar();
            
            $stmt = $conexionLocal->prepare("SELECT codigo_sucursal FROM sucursal_local LIMIT 1");
            $stmt->execute();
            $sucursalLocal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sucursalLocal || empty($sucursalLocal['codigo_sucursal'])) {
                error_log("No se encontró código de sucursal en sucursal_local");
                return null;
            }
            
            $codigoSucursal = $sucursalLocal['codigo_sucursal'];
            
            // Buscar la sucursal en la BD central usando el código
            $conexionCentral = ConexionCentral::conectar();
            $stmt = $conexionCentral->prepare("
                SELECT host_bd, nombre_bd, id, nombre 
                FROM sucursales 
                WHERE codigo_sucursal = ? AND activo = 1 
                LIMIT 1
            ");
            $stmt->execute([$codigoSucursal]);
            $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sucursal) {
                error_log("No se encontró sucursal en BD central con código: " . $codigoSucursal);
                return null;
            }
            
            return [
                'host_bd' => $sucursal['host_bd'],
                'nombre_bd' => $sucursal['nombre_bd'],
                'id' => $sucursal['id'],
                'nombre' => $sucursal['nombre'],
                'codigo_sucursal' => $codigoSucursal
            ];
            
        } catch(Exception $e) {
            error_log("Error en mdlObtenerSucursalActual: " . $e->getMessage());
            return null;
        }
    }

    /*=============================================
    CONSULTAR USUARIOS DE SUCURSAL REMOTA VIA BD
    =============================================*/
    static public function mdlConsultarUsuariosSucursalRemota($sucursal) {
        
        try {
            // Crear conexión directa a la BD de la sucursal
            $dsn = "mysql:host=" . $sucursal['host_bd'] . ";port=" . $sucursal['puerto_bd'] . ";dbname=" . $sucursal['nombre_bd'] . ";charset=utf8";
            
            $conexionRemota = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // Consultar usuarios de la sucursal remota
            $stmt = $conexionRemota->prepare("
                SELECT 
                    id, nombre, usuario, perfil, foto, estado, ultimo_login, fecha, empresa, telefono, direccion
                FROM usuarios 
                ORDER BY nombre
            ");
            
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear datos
            foreach($usuarios as &$usuario) {
                $usuario['fecha_creacion_formateada'] = date('d/m/Y H:i', strtotime($usuario['fecha']));
                $usuario['ultimo_login_formateado'] = $usuario['ultimo_login'] && $usuario['ultimo_login'] != '0000-00-00 00:00:00' 
                    ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) 
                    : 'Nunca';
                $usuario['estado_texto'] = $usuario['estado'] ? 'Activo' : 'Inactivo';
                $usuario['empresa_actual'] = $usuario['empresa'] ?: 'Sin empresa asignada';
            }
            
            return [
                'usuarios' => $usuarios,
                'total' => count($usuarios),
                'estado' => 'conectado',
                'error' => null
            ];
            
        } catch(Exception $e) {
            error_log("Error conectando a sucursal {$sucursal['nombre']}: " . $e->getMessage());
            return [
                'usuarios' => [],
                'total' => 0,
                'estado' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /*=============================================
    OBTENER USUARIOS DE LA SUCURSAL LOCAL
    =============================================*/
    static public function mdlObtenerUsuariosLocal() {
        
        try {
            $conexionLocal = Conexion::conectar();
            
            $stmt = $conexionLocal->prepare("
                SELECT id, nombre, usuario, perfil, foto, estado, ultimo_login, fecha, empresa, telefono, direccion
                FROM usuarios 
                ORDER BY nombre
            ");
            
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear datos
            foreach($usuarios as &$usuario) {
                $usuario['fecha_creacion_formateada'] = date('d/m/Y H:i', strtotime($usuario['fecha']));
                $usuario['ultimo_login_formateado'] = $usuario['ultimo_login'] && $usuario['ultimo_login'] != '0000-00-00 00:00:00' 
                    ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) 
                    : 'Nunca';
                $usuario['estado_texto'] = $usuario['estado'] ? 'Activo' : 'Inactivo';
                $usuario['empresa_actual'] = $usuario['empresa'] ?: 'Sin empresa asignada';
            }
            
            return $usuarios;
            
        } catch(Exception $e) {
            error_log("Error en mdlObtenerUsuariosLocal: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    EDITAR USUARIO CENTRAL
    =============================================*/
    static public function mdlEditarUsuarioCentral($datos) {
        try {
            $conexion = ConexionCentral::conectar();
            
            // Verificar si el usuario existe
            $stmt = $conexion->prepare("
                SELECT id FROM usuarios_central 
                WHERE id = :id
            ");
            $stmt->bindParam(":id", $datos['id'], PDO::PARAM_INT);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                return [
                    'success' => false,
                    'error' => 'El usuario no existe'
                ];
            }
            
            // Construir query dinámicamente según los campos proporcionados
            $campos = [];
            $valores = [];
            
            $campos[] = "nombre = :nombre";
            $valores[':nombre'] = $datos['nombre'];
            
            $campos[] = "usuario = :usuario";
            $valores[':usuario'] = $datos['usuario'];
            
            // Solo actualizar contraseña si se proporciona
            if (!empty($datos['password'])) {
                $campos[] = "password = :password";
                $valores[':password'] = $datos['password'];
            }
            
            $campos[] = "perfil = :perfil";
            $valores[':perfil'] = $datos['perfil'];
            
            $campos[] = "telefono = :telefono";
            $valores[':telefono'] = $datos['telefono'];
            
            $campos[] = "direccion = :direccion";
            $valores[':direccion'] = $datos['direccion'];
            
            // Manejar sucursales asignadas
            $sucursalesAsignadas = '';
            if (!empty($datos['sucursales_asignadas'])) {
                if (is_array($datos['sucursales_asignadas'])) {
                    $sucursalesAsignadas = implode(',', $datos['sucursales_asignadas']);
                } else {
                    $sucursalesAsignadas = $datos['sucursales_asignadas'];
                }
            }
            $campos[] = "sucursales_asignadas = :sucursales_asignadas";
            $valores[':sucursales_asignadas'] = $sucursalesAsignadas;
            
            $valores[':id'] = $datos['id'];
            
            $query = "UPDATE usuarios_central SET " . implode(', ', $campos) . " WHERE id = :id";
            $stmt = $conexion->prepare($query);
            
            // Bind de todos los parámetros
            foreach ($valores as $param => $value) {
                $stmt->bindValue($param, $value, PDO::PARAM_STR);
            }
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Usuario central actualizado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error actualizando usuario central'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlEditarUsuarioCentral: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno del servidor'
            ];
        }
    }
    
    /*=============================================
    SINCRONIZAR USUARIOS A SUCURSALES
    =============================================*/
    static public function mdlSincronizarUsuariosSucursales($sucursales) {
        try {
            $conexion = ConexionCentral::conectar();
            $usuariosCentrales = [];
            $resultados = [];
            
            // Obtener todos los usuarios centrales
            $stmt = $conexion->prepare("
                SELECT * FROM usuarios_central 
                WHERE activo = 1
            ");
            $stmt->execute();
            $usuariosCentrales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($sucursales as $sucursal) {
                $resultados[$sucursal['id']] = [
                    'sucursal' => $sucursal['nombre'],
                    'usuarios_creados' => 0,
                    'errores' => []
                ];
                
                // Conectar a la sucursal
                try {
                    $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8";
                    $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]);
                    
                    foreach ($usuariosCentrales as $usuario) {
                        // Verificar si el usuario debe estar en esta sucursal
                        $sucursalesAsignadas = explode(',', $usuario['sucursales_asignadas']);
                        if (!in_array($sucursal['id'], $sucursalesAsignadas)) {
                            continue;
                        }
                        
                        // Verificar si ya existe en la sucursal
                        $stmt = $pdoSucursal->prepare("
                            SELECT id FROM usuarios 
                            WHERE usuario = :usuario
                        ");
                        $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                        $stmt->execute();
                        
                        if ($stmt->fetch()) {
                            continue; // Ya existe, saltar
                        }
                        
                        // Crear usuario en la sucursal
                        $stmt = $pdoSucursal->prepare("
                            INSERT INTO usuarios (
                                nombre, usuario, password, perfil, foto, 
                                telefono, direccion, empresa, estado, fecha
                            ) VALUES (
                                :nombre, :usuario, :password, :perfil, :foto,
                                :telefono, :direccion, :empresa, 1, NOW()
                            )
                        ");
                        
                        $stmt->bindParam(":nombre", $usuario['nombre'], PDO::PARAM_STR);
                        $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                        $stmt->bindParam(":password", $usuario['password'], PDO::PARAM_STR);
                        $stmt->bindParam(":perfil", $usuario['perfil'], PDO::PARAM_STR);
                        $stmt->bindParam(":foto", $usuario['foto'], PDO::PARAM_STR);
                        $stmt->bindParam(":telefono", $usuario['telefono'], PDO::PARAM_STR);
                        $stmt->bindParam(":direccion", $usuario['direccion'], PDO::PARAM_STR);
                        $stmt->bindParam(":empresa", $sucursal['nombre'], PDO::PARAM_STR); // Campo empresa = nombre sucursal
                        
                        if ($stmt->execute()) {
                            $resultados[$sucursal['id']]['usuarios_creados']++;
                        } else {
                            $resultados[$sucursal['id']]['errores'][] = "Error creando usuario: " . $usuario['usuario'];
                        }
                    }
                    
                } catch (Exception $e) {
                    $resultados[$sucursal['id']]['errores'][] = "Error de conexión: " . $e->getMessage();
                }
            }
            
            return [
                'success' => true,
                'message' => 'Sincronización completada',
                'resultados' => $resultados
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlSincronizarUsuariosSucursales: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno del servidor'
            ];
        }
    }
    
    /*=============================================
    ASIGNAR SUCURSALES A USUARIO
    =============================================*/
    static public function mdlAsignarSucursalesUsuario($usuario_id, $sucursales) {
        try {
            // Deshabilitar output para evitar corrupción de JSON
            $old_output_handler = ob_get_level();
            if ($old_output_handler) {
                ob_end_clean();
            }
            
            error_log("Iniciando mdlAsignarSucursalesUsuario - Usuario ID: $usuario_id, Sucursales: " . json_encode($sucursales));
            
            $conexion = ConexionCentral::conectar();
            error_log("Conexión a BD central establecida correctamente");
            
            // Verificar si la columna sucursales_asignadas existe
            $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios_central LIKE 'sucursales_asignadas'");
            $stmt->execute();
            $columnaExiste = $stmt->fetch();
            
            if ($columnaExiste) {
                // Actualizar sucursales asignadas en usuarios_central
                $sucursalesAsignadas = implode(',', $sucursales);
                error_log("Sucursales asignadas (string): $sucursalesAsignadas");
                
                $stmt = $conexion->prepare("
                    UPDATE usuarios_central 
                    SET sucursales_asignadas = :sucursales_asignadas 
                    WHERE id = :id
                ");
                $stmt->bindParam(":sucursales_asignadas", $sucursalesAsignadas, PDO::PARAM_STR);
                $stmt->bindParam(":id", $usuario_id, PDO::PARAM_INT);
                
                error_log("Ejecutando UPDATE en usuarios_central...");
                if (!$stmt->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error ejecutando UPDATE: " . json_encode($errorInfo));
                    return [
                        'success' => false,
                        'error' => 'Error actualizando asignaciones de sucursales: ' . $errorInfo[2]
                    ];
                }
                
                error_log("UPDATE ejecutado correctamente");
            } else {
                error_log("Columna sucursales_asignadas no existe, creándola...");
                
                // Verificar si id_local existe para posicionar la columna correctamente
                $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios_central LIKE 'id_local'");
                $stmt->execute();
                $idLocalExiste = $stmt->fetch();
                
                if ($idLocalExiste) {
                    // Crear la columna después de id_local
                    $stmt = $conexion->prepare("ALTER TABLE usuarios_central ADD COLUMN sucursales_asignadas TEXT NULL AFTER id_local");
                } else {
                    // Crear la columna después de id
                    $stmt = $conexion->prepare("ALTER TABLE usuarios_central ADD COLUMN sucursales_asignadas TEXT NULL AFTER id");
                }
                $stmt->execute();
                
                // Ahora actualizar
                $sucursalesAsignadas = implode(',', $sucursales);
                $stmt = $conexion->prepare("
                    UPDATE usuarios_central 
                    SET sucursales_asignadas = :sucursales_asignadas 
                    WHERE id = :id
                ");
                $stmt->bindParam(":sucursales_asignadas", $sucursalesAsignadas, PDO::PARAM_STR);
                $stmt->bindParam(":id", $usuario_id, PDO::PARAM_INT);
                $stmt->execute();
                
                error_log("Columna creada y UPDATE ejecutado correctamente");
            }
            
            // Obtener datos del usuario
            $stmt = $conexion->prepare("
                SELECT * FROM usuarios_central WHERE id = :id
            ");
            $stmt->bindParam(":id", $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                return [
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ];
            }
            
            // Obtener sucursales seleccionadas
            $sucursalesData = [];
            if (!empty($sucursales)) {
                $placeholders = implode(',', array_fill(0, count($sucursales), '?'));
                $stmt = $conexion->prepare("
                    SELECT * FROM sucursales 
                    WHERE id IN ($placeholders)
                    AND activo = 1
                ");
                $stmt->execute($sucursales);
                $sucursalesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $resultados = [];
            
            // Obtener sucursales que NO están seleccionadas (para eliminar)
            $sucursalesNoSeleccionadas = [];
            if (!empty($sucursales)) {
                $placeholders = implode(',', array_fill(0, count($sucursales), '?'));
                $stmt = $conexion->prepare("
                    SELECT * FROM sucursales 
                    WHERE id NOT IN ($placeholders)
                    AND activo = 1
                ");
                $stmt->execute($sucursales);
                $sucursalesNoSeleccionadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Si no hay sucursales seleccionadas, obtener todas las sucursales activas para eliminar
                $stmt = $conexion->prepare("
                    SELECT * FROM sucursales 
                    WHERE activo = 1
                ");
                $stmt->execute();
                $sucursalesNoSeleccionadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Eliminar usuario de sucursales no seleccionadas
            foreach ($sucursalesNoSeleccionadas as $sucursal) {
                try {
                    $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8";
                    $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]);
                    
                    // Eliminar usuario de esta sucursal (buscar por usuario o id_local)
                    $stmt = $pdoSucursal->prepare("
                        DELETE FROM usuarios 
                        WHERE usuario = :usuario OR id = :id_local
                    ");
                    $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                    $stmt->bindParam(":id_local", $usuario['id'], PDO::PARAM_INT);
                    $stmt->execute();
                    
                    error_log("Usuario '{$usuario['usuario']}' eliminado de sucursal '{$sucursal['nombre']}'");
                    
                } catch (Exception $e) {
                    error_log("Error eliminando usuario de sucursal '{$sucursal['nombre']}': " . $e->getMessage());
                }
            }
            
            // Sincronizar a cada sucursal seleccionada
            foreach ($sucursalesData as $sucursal) {
                $resultados[$sucursal['id']] = [
                    'sucursal' => $sucursal['nombre'],
                    'usuario_creado' => false,
                    'error' => null
                ];
                
                error_log("Sincronizando usuario '{$usuario['usuario']}' a sucursal '{$sucursal['nombre']}' (ID: {$sucursal['id']})");
                
                try {
                    // Conectar a la sucursal
                    $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8";
                    $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]);
                    
                    // Verificar si ya existe en la sucursal (buscar por usuario o por id_local si existe)
                    $stmt = $pdoSucursal->prepare("
                        SELECT id FROM usuarios 
                        WHERE usuario = :usuario OR id = :id_local
                    ");
                    $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                    $stmt->bindParam(":id_local", $usuario['id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $usuarioExistente = $stmt->fetch();
                    
                    if ($usuarioExistente) {
                        // Actualizar usuario existente
                        error_log("Usuario '{$usuario['usuario']}' ya existe en sucursal '{$sucursal['nombre']}' (ID local: {$usuarioExistente['id']}), actualizando...");
                        $stmt = $pdoSucursal->prepare("
                            UPDATE usuarios SET 
                                nombre = :nombre, 
                                password = :password, 
                                perfil = :perfil, 
                                telefono = :telefono,
                                empresa = :empresa,
                                estado = 1
                            WHERE id = :id_local
                        ");
                        // Verificar si la contraseña ya está encriptada
                        if (strlen($usuario['password']) === 60 && strpos($usuario['password'], '$2a$') === 0) {
                            // Ya está encriptada, usar tal como está
                            $passwordEncriptada = $usuario['password'];
                        } else {
                            // No está encriptada, encriptarla
                            $passwordEncriptada = crypt($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                        }
                        
                        $stmt->bindParam(":nombre", $usuario['nombre'], PDO::PARAM_STR);
                        $stmt->bindParam(":password", $passwordEncriptada, PDO::PARAM_STR);
                        $stmt->bindParam(":perfil", $usuario['perfil'], PDO::PARAM_STR);
                        $stmt->bindParam(":telefono", $usuario['telefono'], PDO::PARAM_STR);
                        $stmt->bindParam(":empresa", $sucursal['nombre'], PDO::PARAM_STR);
                        $stmt->bindParam(":id_local", $usuarioExistente['id'], PDO::PARAM_INT);
                        $stmt->execute();
                        error_log("Usuario '{$usuario['usuario']}' actualizado en sucursal '{$sucursal['nombre']}' con empresa = '{$sucursal['nombre']}'");
                    } else {
                        // Crear nuevo usuario
                        error_log("Usuario '{$usuario['usuario']}' no existe en sucursal '{$sucursal['nombre']}', creando...");
                        
                        // Verificar si la contraseña ya está encriptada
                        if (strlen($usuario['password']) === 60 && strpos($usuario['password'], '$2a$') === 0) {
                            // Ya está encriptada, usar tal como está
                            $passwordEncriptada = $usuario['password'];
                        } else {
                            // No está encriptada, encriptarla
                            $passwordEncriptada = crypt($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                        }
                        
                        $stmt = $pdoSucursal->prepare("
                            INSERT INTO usuarios (
                                nombre, usuario, password, perfil, foto, 
                                telefono, empresa, estado, fecha, ultimo_login
                            ) VALUES (
                                :nombre, :usuario, :password, :perfil, :foto,
                                :telefono, :empresa, 1, NOW(), NOW()
                            )
                        ");
                        $stmt->bindParam(":nombre", $usuario['nombre'], PDO::PARAM_STR);
                        $stmt->bindParam(":usuario", $usuario['usuario'], PDO::PARAM_STR);
                        $stmt->bindParam(":password", $passwordEncriptada, PDO::PARAM_STR);
                        $stmt->bindParam(":perfil", $usuario['perfil'], PDO::PARAM_STR);
                        $stmt->bindParam(":foto", $usuario['foto'], PDO::PARAM_STR);
                        $stmt->bindParam(":telefono", $usuario['telefono'], PDO::PARAM_STR);
                        $stmt->bindParam(":empresa", $sucursal['nombre'], PDO::PARAM_STR);
                        $stmt->execute();
                        
                        // Obtener el ID del usuario creado para actualizar id_local en central
                        $nuevoIdLocal = $pdoSucursal->lastInsertId();
                        error_log("Usuario '{$usuario['usuario']}' creado en sucursal '{$sucursal['nombre']}' con ID local: $nuevoIdLocal");
                        
                        // Actualizar id_local en la BD central
                        $stmtCentral = $conexion->prepare("
                            UPDATE usuarios_central 
                            SET id_local = :id_local 
                            WHERE id = :id_central
                        ");
                        $stmtCentral->bindParam(":id_local", $nuevoIdLocal, PDO::PARAM_INT);
                        $stmtCentral->bindParam(":id_central", $usuario['id'], PDO::PARAM_INT);
                        $stmtCentral->execute();
                    }
                    
                    $resultados[$sucursal['id']]['usuario_creado'] = true;
                    
                } catch (Exception $e) {
                    $resultados[$sucursal['id']]['error'] = "Error de conexión: " . $e->getMessage();
                }
            }
            
            return [
                'success' => true,
                'message' => 'Sucursales asignadas y sincronizadas exitosamente',
                'resultados' => $resultados
            ];
            
        } catch (Exception $e) {
            error_log("Error en mdlAsignarSucursalesUsuario: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno del servidor'
            ];
        }
    }
    
    /*=============================================
    SINCRONIZAR TODOS LOS USUARIOS
    =============================================*/
    static public function mdlSincronizarTodosUsuarios() {
        try {
            // Deshabilitar output para evitar corrupción de JSON
            $old_output_handler = ob_get_level();
            if ($old_output_handler) {
                ob_end_clean();
            }
            
            // Deshabilitar error_log temporalmente
            $old_log_errors = ini_get('log_errors');
            $old_display_errors = ini_get('display_errors');
            ini_set('log_errors', 0);
            ini_set('display_errors', 0);
            
            $conexion = ConexionCentral::conectar();
            
            // Obtener todos los usuarios centrales con sucursales asignadas
            $stmt = $conexion->prepare("
                SELECT * FROM usuarios_central 
                WHERE activo = 1 
                AND sucursales_asignadas IS NOT NULL 
                AND sucursales_asignadas != ''
            ");
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Usuarios encontrados para sincronizar: " . count($usuarios));
            
            $resultados = [];
            $usuariosSincronizados = 0;
            $errores = 0;
            
            foreach ($usuarios as $usuario) {
                $sucursales = explode(',', $usuario['sucursales_asignadas']);
                error_log("Sincronizando usuario '{$usuario['usuario']}' a sucursales: " . json_encode($sucursales));
                
                $resultado = self::mdlAsignarSucursalesUsuario($usuario['id'], $sucursales);
                
                if ($resultado['success']) {
                    $usuariosSincronizados++;
                    $resultados[] = [
                        'usuario' => $usuario['usuario'],
                        'estado' => 'sincronizado',
                        'sucursales' => $sucursales
                    ];
                } else {
                    $errores++;
                    $resultados[] = [
                        'usuario' => $usuario['usuario'],
                        'estado' => 'error',
                        'error' => $resultado['error']
                    ];
                }
            }
            
            // Restaurar configuración
            ini_set('log_errors', $old_log_errors);
            ini_set('display_errors', $old_display_errors);
            
            return [
                'success' => true,
                'message' => "Sincronización completada. Usuarios sincronizados: $usuariosSincronizados, Errores: $errores",
                'usuarios_sincronizados' => $usuariosSincronizados,
                'errores' => $errores,
                'resultados' => $resultados
            ];
            
        } catch (Exception $e) {
            // Restaurar configuración
            ini_set('log_errors', $old_log_errors);
            ini_set('display_errors', $old_display_errors);
            
            return [
                'success' => false,
                'error' => 'Error interno del servidor'
            ];
        }
    }
    
}
?>
