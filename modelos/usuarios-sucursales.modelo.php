<?php

require_once "conexion.php";
require_once __DIR__ . "/../api-transferencias/conexion-central.php";

class ModeloUsuariosSucursales {

    /*=============================================
    CREAR USUARIO CON SUCURSAL
    =============================================*/
    static public function mdlCrearUsuarioConSucursal($tabla, $datos) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO $tabla(
                    nombre, usuario, password, perfil, foto, 
                    sucursal_id, es_transportador, sucursales_permitidas,
                    telefono, direccion, estado, ultimo_login
                ) VALUES (
                    :nombre, :usuario, :password, :perfil, :foto,
                    :sucursal_id, :es_transportador, :sucursales_permitidas,
                    :telefono, :direccion, 1, NOW()
                )
            ");
            
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
            $stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
            $stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);
            $stmt->bindParam(":sucursal_id", $datos["sucursal_id"], PDO::PARAM_INT);
            $stmt->bindParam(":es_transportador", $datos["es_transportador"], PDO::PARAM_BOOL);
            $stmt->bindParam(":sucursales_permitidas", $datos["sucursales_permitidas"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlCrearUsuarioConSucursal: " . $e->getMessage());
            return "error";
        }
    }
    
    /*=============================================
    ASIGNAR SUCURSAL A USUARIO
    =============================================*/
    static public function mdlAsignarSucursalUsuario($usuario, $sucursalId) {
        
        try {
            // Obtener ID del usuario
            $stmt = Conexion::conectar()->prepare("SELECT id FROM usuarios WHERE usuario = ?");
            $stmt->execute([$usuario]);
            $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuarioData) {
                return "error";
            }
            
            $usuarioId = $usuarioData['id'];
            
            // Insertar relación en tabla usuario_sucursal
            $stmt = Conexion::conectar()->prepare("
                INSERT IGNORE INTO usuario_sucursal (usuario_id, sucursal_id, activo)
                VALUES (?, ?, 1)
            ");
            
            $stmt->execute([$usuarioId, $sucursalId]);
            
            return "ok";
            
        } catch (Exception $e) {
            error_log("Error en mdlAsignarSucursalUsuario: " . $e->getMessage());
            return "error";
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES DE USUARIO
    =============================================*/
    static public function mdlObtenerSucursalesUsuario($usuarioId) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    us.*,
                    cs.nombre_sucursal,
                    cs.codigo_sucursal,
                    cs.direccion,
                    cs.telefono,
                    cs.email
                FROM usuario_sucursal us
                LEFT JOIN configuracion_sucursal cs ON us.sucursal_id = cs.sucursal_id
                WHERE us.usuario_id = ? AND us.activo = 1
                ORDER BY cs.nombre_sucursal
            ");
            
            $stmt->execute([$usuarioId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSucursalesUsuario: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    OBTENER USUARIOS CON SUCURSALES
    =============================================*/
    static public function mdlObtenerUsuariosConSucursales($filtroSucursal = null) {
        
        try {
            $sql = "
                SELECT 
                    u.*,
                    cs.nombre_sucursal,
                    cs.codigo_sucursal,
                    GROUP_CONCAT(us2.sucursal_id) as sucursales_adicionales
                FROM usuarios u
                LEFT JOIN configuracion_sucursal cs ON u.sucursal_id = cs.sucursal_id
                LEFT JOIN usuario_sucursal us2 ON u.id = us2.usuario_id AND us2.activo = 1
            ";
            
            $params = [];
            
            if ($filtroSucursal) {
                $sql .= " WHERE u.sucursal_id = ?";
                $params[] = $filtroSucursal;
            }
            
            $sql .= " GROUP BY u.id ORDER BY u.nombre";
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerUsuariosConSucursales: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    ACTUALIZAR SUCURSALES DE USUARIO
    =============================================*/
    static public function mdlActualizarSucursalesUsuario($usuarioId, $sucursalesPermitidas) {
        
        try {
            $pdo = Conexion::conectar();
            $pdo->beginTransaction();
            
            // Eliminar relaciones existentes
            $stmt = $pdo->prepare("DELETE FROM usuario_sucursal WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);
            
            // Insertar nuevas relaciones
            if (!empty($sucursalesPermitidas)) {
                $stmt = $pdo->prepare("
                    INSERT INTO usuario_sucursal (usuario_id, sucursal_id, activo)
                    VALUES (?, ?, 1)
                ");
                
                foreach ($sucursalesPermitidas as $sucursalId) {
                    $stmt->execute([$usuarioId, $sucursalId]);
                }
            }
            
            // Actualizar campo sucursales_permitidas en usuarios
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET sucursales_permitidas = ?
                WHERE id = ?
            ");
            
            $sucursalesJson = json_encode($sucursalesPermitidas);
            $stmt->execute([$sucursalesJson, $usuarioId]);
            
            $pdo->commit();
            return "ok";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error en mdlActualizarSucursalesUsuario: " . $e->getMessage());
            return "error";
        }
    }
    
    /*=============================================
    OBTENER ESTADÍSTICAS DE USUARIOS POR SUCURSAL
    =============================================*/
    static public function mdlObtenerEstadisticasUsuariosSucursal() {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    cs.nombre_sucursal,
                    cs.codigo_sucursal,
                    COUNT(u.id) as total_usuarios,
                    SUM(CASE WHEN u.es_transportador = 1 THEN 1 ELSE 0 END) as transportadores,
                    SUM(CASE WHEN u.perfil = 'Administrador' THEN 1 ELSE 0 END) as administradores,
                    SUM(CASE WHEN u.perfil = 'Vendedor' THEN 1 ELSE 0 END) as vendedores,
                    SUM(CASE WHEN u.perfil = 'Contador' THEN 1 ELSE 0 END) as contadores
                FROM configuracion_sucursal cs
                LEFT JOIN usuarios u ON cs.sucursal_id = u.sucursal_id
                WHERE cs.activa = 1
                GROUP BY cs.id, cs.nombre_sucursal, cs.codigo_sucursal
                ORDER BY cs.nombre_sucursal
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerEstadisticasUsuariosSucursal: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    VALIDAR USUARIO EN SUCURSAL
    =============================================*/
    static public function mdlValidarUsuarioEnSucursal($usuarioId, $sucursalId) {
        
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    u.id,
                    u.es_transportador,
                    u.sucursales_permitidas,
                    us.sucursal_id
                FROM usuarios u
                LEFT JOIN usuario_sucursal us ON u.id = us.usuario_id AND us.sucursal_id = ? AND us.activo = 1
                WHERE u.id = ?
            ");
            
            $stmt->execute([$sucursalId, $usuarioId]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultado) {
                return false;
            }
            
            // Si es transportador, verificar sucursales permitidas
            if ($resultado['es_transportador']) {
                $sucursalesPermitidas = json_decode($resultado['sucursales_permitidas'], true);
                return in_array($sucursalId, $sucursalesPermitidas ?: []);
            }
            
            // Si no es transportador, verificar su sucursal principal
            return $resultado['sucursal_id'] == $sucursalId;
            
        } catch (Exception $e) {
            error_log("Error en mdlValidarUsuarioEnSucursal: " . $e->getMessage());
            return false;
        }
    }
}
?>
