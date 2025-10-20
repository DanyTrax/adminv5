<?php

require_once __DIR__ . "/../api-transferencias/conexion-central.php";
require_once "conexion.php";

class ModeloSincronizacionUsuarios {

    /*=============================================
    SINCRONIZAR USUARIO CENTRAL → LOCAL
    =============================================*/
    static public function mdlSincronizarUsuarioCentralALocal($usuarioCentral, $sucursalId) {
        
        try {
            $conexionLocal = Conexion::conectar();
            
            // Verificar si el usuario ya existe en la sucursal local
            $stmt = $conexionLocal->prepare("
                SELECT id FROM usuarios 
                WHERE usuario = ? AND empresa = ?
            ");
            $stmt->execute([$usuarioCentral['usuario'], $usuarioCentral['sucursal_nombre']]);
            $usuarioExistente = $stmt->fetch();
            
            if($usuarioExistente) {
                // Actualizar usuario existente
                $stmt = $conexionLocal->prepare("
                    UPDATE usuarios SET 
                        nombre = ?,
                        password = ?,
                        perfil = ?,
                        foto = ?,
                        estado = ?,
                        telefono = ?,
                        direccion = ?,
                        fecha = NOW()
                    WHERE id = ?
                ");
                
                $resultado = $stmt->execute([
                    $usuarioCentral['nombre'],
                    $usuarioCentral['password'],
                    $usuarioCentral['perfil'],
                    $usuarioCentral['foto'],
                    $usuarioCentral['activo'] ? 1 : 0,
                    $usuarioCentral['telefono'] ?? '',
                    $usuarioCentral['direccion'] ?? '',
                    $usuarioExistente['id']
                ]);
                
                return [
                    'accion' => 'actualizado',
                    'id_local' => $usuarioExistente['id'],
                    'success' => $resultado
                ];
                
            } else {
                // Crear nuevo usuario
                $stmt = $conexionLocal->prepare("
                    INSERT INTO usuarios (
                        nombre, usuario, password, perfil, foto, estado, 
                        ultimo_login, empresa, telefono, direccion, fecha
                    ) VALUES (?, ?, ?, ?, ?, ?, '0000-00-00 00:00:00', ?, ?, ?, NOW())
                ");
                
                $resultado = $stmt->execute([
                    $usuarioCentral['nombre'],
                    $usuarioCentral['usuario'],
                    $usuarioCentral['password'],
                    $usuarioCentral['perfil'],
                    $usuarioCentral['foto'],
                    $usuarioCentral['activo'] ? 1 : 0,
                    $usuarioCentral['sucursal_nombre'],
                    $usuarioCentral['telefono'] ?? '',
                    $usuarioCentral['direccion'] ?? ''
                ]);
                
                return [
                    'accion' => 'creado',
                    'id_local' => $conexionLocal->lastInsertId(),
                    'success' => $resultado
                ];
            }
            
        } catch(Exception $e) {
            error_log("Error en mdlSincronizarUsuarioCentralALocal: " . $e->getMessage());
            return [
                'accion' => 'error',
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /*=============================================
    SINCRONIZAR USUARIO LOCAL → CENTRAL
    =============================================*/
    static public function mdlSincronizarUsuarioLocalACentral($usuarioLocal, $sucursalId) {
        
        try {
            $conexionCentral = ConexionCentral::conectar();
            
            // Verificar si el usuario ya existe en central
            $stmt = $conexionCentral->prepare("
                SELECT id FROM usuarios_central 
                WHERE usuario = ? AND sucursal_id = ?
            ");
            $stmt->execute([$usuarioLocal['usuario'], $sucursalId]);
            $usuarioExistente = $stmt->fetch();
            
            if($usuarioExistente) {
                // Actualizar usuario existente en central
                $stmt = $conexionCentral->prepare("
                    UPDATE usuarios_central SET 
                        nombre = ?,
                        password = ?,
                        perfil = ?,
                        foto = ?,
                        telefono = ?,
                        direccion = ?,
                        activo = ?,
                        fecha_actualizacion = NOW(),
                        sincronizado = 1,
                        fecha_sincronizacion = NOW()
                    WHERE id = ?
                ");
                
                $resultado = $stmt->execute([
                    $usuarioLocal['nombre'],
                    $usuarioLocal['password'],
                    $usuarioLocal['perfil'],
                    $usuarioLocal['foto'],
                    $usuarioLocal['telefono'] ?? '',
                    $usuarioLocal['direccion'] ?? '',
                    $usuarioLocal['estado'] ? 1 : 0,
                    $usuarioExistente['id']
                ]);
                
                return [
                    'accion' => 'actualizado',
                    'id_central' => $usuarioExistente['id'],
                    'success' => $resultado
                ];
                
            } else {
                // Crear nuevo usuario en central
                $stmt = $conexionCentral->prepare("
                    INSERT INTO usuarios_central (
                        nombre, usuario, password, perfil, foto, sucursal_id,
                        telefono, direccion, activo, sincronizado, fecha_sincronizacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
                ");
                
                $resultado = $stmt->execute([
                    $usuarioLocal['nombre'],
                    $usuarioLocal['usuario'],
                    $usuarioLocal['password'],
                    $usuarioLocal['perfil'],
                    $usuarioLocal['foto'],
                    $sucursalId,
                    $usuarioLocal['telefono'] ?? '',
                    $usuarioLocal['direccion'] ?? '',
                    $usuarioLocal['estado'] ? 1 : 0
                ]);
                
                return [
                    'accion' => 'creado',
                    'id_central' => $conexionCentral->lastInsertId(),
                    'success' => $resultado
                ];
            }
            
        } catch(Exception $e) {
            error_log("Error en mdlSincronizarUsuarioLocalACentral: " . $e->getMessage());
            return [
                'accion' => 'error',
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /*=============================================
    CONSULTAR USUARIOS DE SUCURSAL LOCAL
    =============================================*/
    static public function mdlConsultarUsuariosSucursalLocal($sucursalId, $sucursalNombre) {
        
        try {
            $conexionLocal = Conexion::conectar();
            
            $stmt = $conexionLocal->prepare("
                SELECT 
                    id, nombre, usuario, password, perfil, foto, estado,
                    ultimo_login, empresa, telefono, direccion, fecha
                FROM usuarios 
                WHERE empresa = ?
                ORDER BY nombre
            ");
            
            $stmt->execute([$sucursalNombre]);
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar información de sucursal
            foreach($usuarios as &$usuario) {
                $usuario['sucursal_id'] = $sucursalId;
                $usuario['sucursal_nombre'] = $sucursalNombre;
                $usuario['fecha_creacion_formateada'] = date('d/m/Y H:i', strtotime($usuario['fecha']));
                $usuario['ultimo_login_formateado'] = $usuario['ultimo_login'] && $usuario['ultimo_login'] != '0000-00-00 00:00:00' 
                    ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) 
                    : 'Nunca';
                $usuario['estado_texto'] = $usuario['estado'] ? 'Activo' : 'Inactivo';
            }
            
            return $usuarios;
            
        } catch(Exception $e) {
            error_log("Error en mdlConsultarUsuariosSucursalLocal: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    ELIMINAR USUARIO BIDIRECCIONAL
    =============================================*/
    static public function mdlEliminarUsuarioBidireccional($usuarioId, $tipo, $sucursalId = null) {
        
        try {
            $conexionCentral = ConexionCentral::conectar();
            $conexionLocal = Conexion::conectar();
            
            if($tipo === 'central') {
                // Eliminar de central y luego de todas las sucursales
                $stmt = $conexionCentral->prepare("DELETE FROM usuarios_central WHERE id = ?");
                $stmt->execute([$usuarioId]);
                
                // Aquí podrías implementar la eliminación en todas las sucursales
                // via API o conexión directa
                
                return ['success' => true, 'accion' => 'eliminado_central'];
                
            } elseif($tipo === 'local') {
                // Eliminar de local y luego de central
                $stmt = $conexionLocal->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$usuarioId]);
                
                if($sucursalId) {
                    $stmt = $conexionCentral->prepare("DELETE FROM usuarios_central WHERE usuario = ? AND sucursal_id = ?");
                    $stmt->execute([$usuarioId, $sucursalId]);
                }
                
                return ['success' => true, 'accion' => 'eliminado_local'];
            }
            
        } catch(Exception $e) {
            error_log("Error en mdlEliminarUsuarioBidireccional: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /*=============================================
    OBTENER ESTADO DE SINCRONIZACIÓN
    =============================================*/
    static public function mdlObtenerEstadoSincronizacion() {
        
        try {
            $conexionCentral = ConexionCentral::conectar();
            
            // Obtener sucursales activas
            $stmt = $conexionCentral->prepare("
                SELECT id, nombre, codigo_sucursal, url_api 
                FROM sucursales 
                WHERE activo = 1
            ");
            $stmt->execute();
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $estado = [];
            
            foreach($sucursales as $sucursal) {
                // Contar usuarios en central para esta sucursal
                $stmt = $conexionCentral->prepare("
                    SELECT COUNT(*) as total FROM usuarios_central 
                    WHERE sucursal_id = ?
                ");
                $stmt->execute([$sucursal['id']]);
                $usuariosCentral = $stmt->fetch()['total'];
                
                // Intentar consultar usuarios locales
                try {
                    $usuariosLocal = self::mdlConsultarUsuariosSucursalLocal($sucursal['id'], $sucursal['nombre']);
                    $totalLocal = count($usuariosLocal);
                    $estadoConexion = 'conectado';
                } catch(Exception $e) {
                    $totalLocal = 0;
                    $estadoConexion = 'error';
                }
                
                $estado[] = [
                    'sucursal' => $sucursal,
                    'usuarios_central' => $usuariosCentral,
                    'usuarios_local' => $totalLocal,
                    'estado_conexion' => $estadoConexion,
                    'sincronizado' => $usuariosCentral == $totalLocal
                ];
            }
            
            return $estado;
            
        } catch(Exception $e) {
            error_log("Error en mdlObtenerEstadoSincronizacion: " . $e->getMessage());
            return [];
        }
    }
}
?>
