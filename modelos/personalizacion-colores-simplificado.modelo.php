<?php
/*=============================================
MODELO PERSONALIZACIÓN SIMPLIFICADA
=============================================*/

require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/../api-transferencias/conexion-central.php";

class ModeloPersonalizacionColores {
    
    /*=============================================
    OBTENER CONFIGURACIÓN ACTIVA
    =============================================*/
    static public function mdlObtenerConfiguracionActiva() {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            $stmt = $conexion->prepare("
                SELECT * FROM personalizacion_colores 
                WHERE activo = 1 
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
            
            // Desactivar todas las configuraciones existentes
            $stmt = $conexion->prepare("UPDATE personalizacion_colores SET activo = 0");
            $stmt->execute();
            
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
                    usuario_creador
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
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
    OBTENER TODAS LAS CONFIGURACIONES
    =============================================*/
    static public function mdlObtenerTodasConfiguraciones() {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            $stmt = $conexion->prepare("
                SELECT * FROM personalizacion_colores 
                ORDER BY fecha_actualizacion DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerTodasConfiguraciones: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    ACTIVAR CONFIGURACIÓN ESPECÍFICA
    =============================================*/
    static public function mdlActivarConfiguracion($id) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            $conexion->beginTransaction();
            
            // Desactivar todas las configuraciones
            $stmt = $conexion->prepare("UPDATE personalizacion_colores SET activo = 0");
            $stmt->execute();
            
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
}
?>
