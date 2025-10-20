<?php

require_once __DIR__ . "/../api-transferencias/conexion-central.php";
require_once "conexion.php";

class ModeloUsuariosCentral {

    /*=============================================
    CREAR USUARIO EN BD CENTRAL
    =============================================*/
    static public function mdlCrearUsuarioCentral($tabla, $datos) {
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                INSERT INTO $tabla(
                    nombre, usuario, password, perfil, foto, 
                    sucursal_id, telefono, direccion, observaciones, activo
                ) VALUES (
                    :nombre, :usuario, :password, :perfil, :foto,
                    :sucursal_id, :telefono, :direccion, :observaciones, 1
                )
            ");
            
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
            $stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
            $stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);
            $stmt->bindParam(":sucursal_id", $datos["sucursal_id"], PDO::PARAM_INT);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlCrearUsuarioCentral: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    /*=============================================
    OBTENER USUARIOS CENTRALES
    =============================================*/
    static public function mdlObtenerUsuariosCentral($item = null, $valor = null) {
        
        try {
            $sql = "
                SELECT 
                    uc.*,
                    s.nombre as nombre_sucursal,
                    s.codigo_sucursal
                FROM usuarios_central uc
                LEFT JOIN sucursales s ON uc.sucursal_id = s.id
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
                SELECT id, nombre, codigo_sucursal, activa
                FROM sucursales 
                WHERE activa = 1 
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
                WHERE activa = 1 AND id != ?
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
    ELIMINAR USUARIO CENTRAL
    =============================================*/
    static public function mdlEliminarUsuarioCentral($usuarioId) {
        
        try {
            $stmt = ConexionCentral::conectar()->prepare("
                UPDATE usuarios_central 
                SET activo = 0, fecha_actualizacion = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([$usuarioId]);
            return "ok";
            
        } catch (Exception $e) {
            error_log("Error en mdlEliminarUsuarioCentral: " . $e->getMessage());
            return "error";
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
                    sucursal_id = :sucursal_id,
                    telefono = :telefono,
                    direccion = :direccion,
                    observaciones = :observaciones,
                    fecha_actualizacion = NOW()
                WHERE id = :id
            ");
            
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
            $stmt->bindParam(":sucursal_id", $datos["sucursal_id"], PDO::PARAM_INT);
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
}
?>
