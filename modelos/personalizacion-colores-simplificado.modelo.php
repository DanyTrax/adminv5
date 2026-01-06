<?php
/*=============================================
MODELO PERSONALIZACIÓN SIMPLIFICADA
=============================================*/

require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/../api-transferencias/conexion-central.php";

class ModeloPersonalizacionColores {
    
    /*=============================================
    OBTENER ID SUCURSAL ACTUAL
    =============================================*/
    static public function mdlObtenerIdSucursalActual() {
        
        try {
            // Obtener código de sucursal desde BD local
            require_once __DIR__ . "/conexion.php";
            $conexionLocal = Conexion::conectar();
            
            $stmt = $conexionLocal->prepare("SELECT codigo_sucursal FROM sucursal_local LIMIT 1");
            $stmt->execute();
            $sucursalLocal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sucursalLocal || empty($sucursalLocal['codigo_sucursal'])) {
                return null;
            }
            
            $codigoSucursal = $sucursalLocal['codigo_sucursal'];
            
            // Obtener ID de sucursal desde BD central
            $conexionCentral = ConexionCentral::conectar();
            $stmt = $conexionCentral->prepare("SELECT id FROM sucursales WHERE codigo_sucursal = ? AND activo = 1 LIMIT 1");
            $stmt->execute([$codigoSucursal]);
            $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $sucursal ? (int)$sucursal['id'] : null;
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerIdSucursalActual: " . $e->getMessage());
            return null;
        }
    }
    
    /*=============================================
    OBTENER CONFIGURACIÓN ACTIVA (POR SUCURSAL O GLOBAL)
    =============================================*/
    static public function mdlObtenerConfiguracionActiva($idSucursal = null) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            // Si no se proporciona ID de sucursal, obtener el de la sucursal actual
            if ($idSucursal === null) {
                $idSucursal = self::mdlObtenerIdSucursalActual();
            }
            
            // Primero buscar configuración específica de la sucursal
            if ($idSucursal !== null) {
                $stmt = $conexion->prepare("
                    SELECT * FROM personalizacion_colores 
                    WHERE activo = 1 AND id_sucursal = ?
                    ORDER BY fecha_actualizacion DESC 
                    LIMIT 1
                ");
                $stmt->execute([$idSucursal]);
                $configuracion = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($configuracion) {
                    return $configuracion;
                }
            }
            
            // Si no hay configuración específica, buscar configuración global (id_sucursal IS NULL)
            $stmt = $conexion->prepare("
                SELECT * FROM personalizacion_colores 
                WHERE activo = 1 AND id_sucursal IS NULL
                ORDER BY fecha_actualizacion DESC 
                LIMIT 1
            ");
            $stmt->execute();
            $configuracion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$configuracion) {
                // Si no hay configuración activa, crear una por defecto
                return self::mdlCrearConfiguracionPorDefecto();
            }
            
            return $configuracion;
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerConfiguracionActiva: " . $e->getMessage());
            return self::mdlCrearConfiguracionPorDefecto();
        }
    }
    
    /*=============================================
    CREAR CONFIGURACIÓN POR DEFECTO
    =============================================*/
    static public function mdlCrearConfiguracionPorDefecto() {
        
        return [
            'id' => 0,
            'nombre_configuracion' => 'Configuración Principal',
            'login_gradient_start' => '#3c8dbc',
            'login_gradient_end' => '#2c3e50',
            'navbar_color' => '#3c8dbc',
            'navbar_hover_color' => '#2c3e50',
            'sidebar_color' => '#222d32',
            'sidebar_hover_color' => '#1a252f',
            'sidebar_text_color' => '#b8c7ce',
            'icono_pequeno' => 'vistas/img/plantilla/icono-blanco.png',
            'logo_menu' => 'vistas/img/plantilla/logo-blanco-lineal.png',
            'logo_login' => 'vistas/img/plantilla/Infinito1.png'
        ];
    }
    
    /*=============================================
    ACTUALIZAR CONFIGURACIÓN
    =============================================*/
    static public function mdlActualizarConfiguracion($datos) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            $idSucursal = isset($datos['id_sucursal']) ? $datos['id_sucursal'] : null;
            
            // Desactivar todas las configuraciones de la misma sucursal (o globales si id_sucursal es null)
            if ($idSucursal !== null) {
                $stmt = $conexion->prepare("UPDATE personalizacion_colores SET activo = 0 WHERE id_sucursal = ?");
                $stmt->execute([$idSucursal]);
            } else {
                $stmt = $conexion->prepare("UPDATE personalizacion_colores SET activo = 0 WHERE id_sucursal IS NULL");
                $stmt->execute();
            }
            
            // Insertar nueva configuración activa
            $stmt = $conexion->prepare("
                INSERT INTO personalizacion_colores (
                    nombre_configuracion,
                    login_gradient_start,
                    login_gradient_end,
                    navbar_color,
                    navbar_hover_color,
                    sidebar_color,
                    sidebar_hover_color,
                    sidebar_text_color,
                    icono_pequeno,
                    logo_menu,
                    logo_login,
                    activo,
                    id_sucursal,
                    usuario_creador
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
            ");
            
            $resultado = $stmt->execute([
                $datos['nombre_configuracion'],
                $datos['login_gradient_start'],
                $datos['login_gradient_end'],
                $datos['navbar_color'],
                $datos['navbar_hover_color'],
                $datos['sidebar_color'],
                $datos['sidebar_hover_color'],
                $datos['sidebar_text_color'],
                $datos['icono_pequeno'],
                $datos['logo_menu'],
                $datos['logo_login'],
                $idSucursal,
                $_SESSION['id'] ?? 1
            ]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Configuración actualizada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al actualizar la configuración'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlActualizarConfiguracion: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /*=============================================
    OBTENER TODAS LAS CONFIGURACIONES (POR SUCURSAL O TODAS)
    =============================================*/
    static public function mdlObtenerTodasConfiguraciones($idSucursal = null) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            if ($idSucursal !== null) {
                // Obtener configuraciones de una sucursal específica
                $stmt = $conexion->prepare("
                    SELECT pc.*, s.nombre as nombre_sucursal, s.codigo_sucursal
                    FROM personalizacion_colores pc
                    LEFT JOIN sucursales s ON pc.id_sucursal = s.id
                    WHERE pc.id_sucursal = ? OR pc.id_sucursal IS NULL
                    ORDER BY pc.fecha_actualizacion DESC
                ");
                $stmt->execute([$idSucursal]);
            } else {
                // Obtener todas las configuraciones con información de sucursal
                $stmt = $conexion->prepare("
                    SELECT pc.*, s.nombre as nombre_sucursal, s.codigo_sucursal
                    FROM personalizacion_colores pc
                    LEFT JOIN sucursales s ON pc.id_sucursal = s.id
                    ORDER BY pc.fecha_actualizacion DESC
                ");
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerTodasConfiguraciones: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    ACTIVAR CONFIGURACIÓN ESPECÍFICA
    =============================================*/
    static public function mdlActivarConfiguracion($id, $idSucursalDestino = null) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            // Obtener información de la configuración
            $stmt = $conexion->prepare("SELECT id_sucursal FROM personalizacion_colores WHERE id = ?");
            $stmt->execute([$id]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$config) {
                return [
                    'success' => false,
                    'error' => 'Configuración no encontrada'
                ];
            }
            
            // Si se especifica una sucursal destino, usar esa; si no, usar la de la configuración
            $idSucursal = $idSucursalDestino !== null ? $idSucursalDestino : $config['id_sucursal'];
            
            // Si se está aplicando a una sucursal específica, obtener el nombre de la sucursal
            $nombreSucursal = 'Global';
            if ($idSucursal !== null) {
                $stmt = $conexion->prepare("SELECT nombre FROM sucursales WHERE id = ?");
                $stmt->execute([$idSucursal]);
                $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($sucursal) {
                    $nombreSucursal = $sucursal['nombre'];
                }
            }
            
            $conexion->beginTransaction();
            
            // Desactivar todas las configuraciones de la misma sucursal (o globales si id_sucursal es null)
            if ($idSucursal !== null) {
                $stmt = $conexion->prepare("UPDATE personalizacion_colores SET activo = 0 WHERE id_sucursal = ?");
                $stmt->execute([$idSucursal]);
            } else {
                $stmt = $conexion->prepare("UPDATE personalizacion_colores SET activo = 0 WHERE id_sucursal IS NULL");
                $stmt->execute();
            }
            
            // Activar la configuración específica
            $stmt = $conexion->prepare("
                UPDATE personalizacion_colores 
                SET activo = 1, fecha_actualizacion = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            $resultado = $stmt->execute([$id]);
            
            if ($resultado) {
                $conexion->commit();
                return [
                    'success' => true,
                    'message' => 'Configuración activada exitosamente'
                ];
            } else {
                $conexion->rollBack();
                return [
                    'success' => false,
                    'error' => 'Error al activar la configuración'
                ];
            }
            
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("Error en mdlActivarConfiguracion: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /*=============================================
    ELIMINAR CONFIGURACIÓN
    =============================================*/
    static public function mdlEliminarConfiguracion($id) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            // Verificar que no sea la única configuración
            $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM personalizacion_colores");
            $stmt->execute();
            $total = $stmt->fetch()['total'];
            
            if ($total <= 1) {
                return [
                    'success' => false,
                    'error' => 'No se puede eliminar la única configuración existente'
                ];
            }
            
            // Eliminar configuración
            $stmt = $conexion->prepare("DELETE FROM personalizacion_colores WHERE id = ?");
            $resultado = $stmt->execute([$id]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Configuración eliminada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al eliminar la configuración'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlEliminarConfiguracion: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /*=============================================
    OBTENER CONFIGURACIÓN POR ID
    =============================================*/
    static public function mdlObtenerConfiguracion($id) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            $stmt = $conexion->prepare("
                SELECT * FROM personalizacion_colores 
                WHERE id = ?
            ");
            
            $stmt->execute([$id]);
            $configuracion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($configuracion) {
                return [
                    'success' => true,
                    'configuracion' => $configuracion
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Configuración no encontrada'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerConfiguracion: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /*=============================================
    EDITAR CONFIGURACIÓN
    =============================================*/
    static public function mdlEditarConfiguracion($datos) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            $idSucursal = isset($datos['id_sucursal']) ? $datos['id_sucursal'] : null;
            
            $stmt = $conexion->prepare("
                UPDATE personalizacion_colores SET 
                    nombre_configuracion = ?,
                    login_gradient_start = ?,
                    login_gradient_end = ?,
                    navbar_color = ?,
                    navbar_hover_color = ?,
                    sidebar_color = ?,
                    sidebar_hover_color = ?,
                    sidebar_text_color = ?,
                    icono_pequeno = ?,
                    logo_menu = ?,
                    logo_login = ?,
                    id_sucursal = ?,
                    fecha_actualizacion = NOW()
                WHERE id = ?
            ");
            
            $resultado = $stmt->execute([
                $datos['nombre_configuracion'],
                $datos['login_gradient_start'],
                $datos['login_gradient_end'],
                $datos['navbar_color'],
                $datos['navbar_hover_color'],
                $datos['sidebar_color'],
                $datos['sidebar_hover_color'],
                $datos['sidebar_text_color'],
                $datos['icono_pequeno'],
                $datos['logo_menu'],
                $datos['logo_login'],
                $idSucursal,
                $datos['id']
            ]);
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Configuración actualizada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al actualizar la configuración'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlEditarConfiguracion: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
}
?>
