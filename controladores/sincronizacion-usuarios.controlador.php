<?php

require_once __DIR__ . "/../modelos/sincronizacion-usuarios.modelo.php";
require_once __DIR__ . "/../modelos/usuarios-central.modelo.php";

class ControladorSincronizacionUsuarios {

    /*=============================================
    SINCRONIZAR USUARIO CENTRAL → TODAS LAS SUCURSALES
    =============================================*/
    static public function ctrSincronizarUsuarioCentral($usuarioCentralId) {
        
        try {
            $conexionCentral = ConexionCentral::conectar();
            
            // Obtener datos del usuario central
            $stmt = $conexionCentral->prepare("
                SELECT uc.*, s.nombre as sucursal_nombre, s.url_api
                FROM usuarios_central uc
                INNER JOIN sucursales s ON uc.sucursal_id = s.id
                WHERE uc.id = ?
            ");
            $stmt->execute([$usuarioCentralId]);
            $usuarioCentral = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$usuarioCentral) {
                return ['success' => false, 'error' => 'Usuario central no encontrado'];
            }
            
            // Sincronizar con la sucursal local (si estamos en la sucursal correcta)
            $resultado = ModeloSincronizacionUsuarios::mdlSincronizarUsuarioCentralALocal(
                $usuarioCentral, 
                $usuarioCentral['sucursal_id']
            );
            
            // Marcar como sincronizado en central
            if($resultado['success']) {
                $stmt = $conexionCentral->prepare("
                    UPDATE usuarios_central 
                    SET sincronizado = 1, fecha_sincronizacion = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$usuarioCentralId]);
            }
            
            return $resultado;
            
        } catch(Exception $e) {
            error_log("Error en ctrSincronizarUsuarioCentral: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /*=============================================
    IMPORTAR USUARIOS DE SUCURSAL LOCAL → CENTRAL
    =============================================*/
    static public function ctrImportarUsuariosSucursal($sucursalId) {
        
        try {
            $conexionCentral = ConexionCentral::conectar();
            
            // Obtener información de la sucursal
            $stmt = $conexionCentral->prepare("
                SELECT id, nombre, codigo_sucursal 
                FROM sucursales 
                WHERE id = ? AND activo = 1
            ");
            $stmt->execute([$sucursalId]);
            $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$sucursal) {
                return ['success' => false, 'error' => 'Sucursal no encontrada'];
            }
            
            // Obtener usuarios de la sucursal local
            $usuariosLocal = ModeloSincronizacionUsuarios::mdlConsultarUsuariosSucursalLocal(
                $sucursalId, 
                $sucursal['nombre']
            );
            
            $resultados = [];
            $exitosos = 0;
            $errores = 0;
            
            foreach($usuariosLocal as $usuario) {
                $resultado = ModeloSincronizacionUsuarios::mdlSincronizarUsuarioLocalACentral(
                    $usuario, 
                    $sucursalId
                );
                
                $resultados[] = [
                    'usuario' => $usuario['usuario'],
                    'nombre' => $usuario['nombre'],
                    'resultado' => $resultado
                ];
                
                if($resultado['success']) {
                    $exitosos++;
                } else {
                    $errores++;
                }
            }
            
            return [
                'success' => true,
                'total_procesados' => count($usuariosLocal),
                'exitosos' => $exitosos,
                'errores' => $errores,
                'resultados' => $resultados
            ];
            
        } catch(Exception $e) {
            error_log("Error en ctrImportarUsuariosSucursal: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /*=============================================
    SINCRONIZAR TODOS LOS USUARIOS CENTRALES
    =============================================*/
    static public function ctrSincronizarTodosUsuariosCentrales() {
        
        try {
            $conexionCentral = ConexionCentral::conectar();
            
            // Obtener todos los usuarios centrales
            $stmt = $conexionCentral->prepare("
                SELECT uc.*, s.nombre as sucursal_nombre, s.url_api
                FROM usuarios_central uc
                INNER JOIN sucursales s ON uc.sucursal_id = s.id
                WHERE s.activo = 1
            ");
            $stmt->execute();
            $usuariosCentrales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $resultados = [];
            $exitosos = 0;
            $errores = 0;
            
            foreach($usuariosCentrales as $usuario) {
                $resultado = self::ctrSincronizarUsuarioCentral($usuario['id']);
                
                $resultados[] = [
                    'usuario' => $usuario['usuario'],
                    'sucursal' => $usuario['sucursal_nombre'],
                    'resultado' => $resultado
                ];
                
                if($resultado['success']) {
                    $exitosos++;
                } else {
                    $errores++;
                }
            }
            
            return [
                'success' => true,
                'total_procesados' => count($usuariosCentrales),
                'exitosos' => $exitosos,
                'errores' => $errores,
                'resultados' => $resultados
            ];
            
        } catch(Exception $e) {
            error_log("Error en ctrSincronizarTodosUsuariosCentrales: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /*=============================================
    OBTENER ESTADO DE SINCRONIZACIÓN
    =============================================*/
    static public function ctrObtenerEstadoSincronizacion() {
        return ModeloSincronizacionUsuarios::mdlObtenerEstadoSincronizacion();
    }

    /*=============================================
    CONSULTAR USUARIOS DE TODAS LAS SUCURSALES
    =============================================*/
    static public function ctrConsultarUsuariosTodasSucursales() {
        
        try {
            $conexionCentral = ConexionCentral::conectar();
            
            // Obtener sucursales activas
            $stmt = $conexionCentral->prepare("
                SELECT id, nombre, codigo_sucursal, url_api 
                FROM sucursales 
                WHERE activo = 1
                ORDER BY nombre
            ");
            $stmt->execute();
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $resultado = [];
            
            foreach($sucursales as $sucursal) {
                try {
                    $usuarios = ModeloSincronizacionUsuarios::mdlConsultarUsuariosSucursalLocal(
                        $sucursal['id'], 
                        $sucursal['nombre']
                    );
                    
                    $resultado[] = [
                        'sucursal' => $sucursal,
                        'usuarios' => $usuarios,
                        'total_usuarios' => count($usuarios),
                        'estado_conexion' => 'conectado'
                    ];
                    
                } catch(Exception $e) {
                    $resultado[] = [
                        'sucursal' => $sucursal,
                        'usuarios' => [],
                        'total_usuarios' => 0,
                        'estado_conexion' => 'error',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return $resultado;
            
        } catch(Exception $e) {
            error_log("Error en ctrConsultarUsuariosTodasSucursales: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    ELIMINAR USUARIO BIDIRECCIONAL
    =============================================*/
    static public function ctrEliminarUsuarioBidireccional($usuarioId, $tipo, $sucursalId = null) {
        return ModeloSincronizacionUsuarios::mdlEliminarUsuarioBidireccional($usuarioId, $tipo, $sucursalId);
    }
}
?>
