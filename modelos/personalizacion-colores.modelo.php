<?php
/*=============================================
MODELO PERSONALIZACIÓN DE COLORES
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
            'navbar_color' => '#3c8dbc',
            'navbar_text_color' => '#ffffff',
            'navbar_hover_color' => '#2c3e50',
            'sidebar_color' => '#222d32',
            'sidebar_text_color' => '#b8c7ce',
            'sidebar_hover_color' => '#1a252f',
            'logo_mini_color' => '#ffffff',
            'logo_lg_color' => '#ffffff',
            'logo_background_color' => '#3c8dbc',
            'icon_color' => '#3c8dbc',
            'sidebar_toggle_hover_color' => '#2c3e50',
            'dropdown_hover_color' => '#f5f5f5',
            'button_primary_color' => '#3c8dbc',
            'button_primary_hover_color' => '#2c3e50',
            'link_hover_color' => '#2c3e50',
            'active_menu_color' => '#1a252f',
            'active_menu_text_color' => '#ffffff',
            'login_gradient_start' => '#3c8dbc',
            'login_gradient_end' => '#2c3e50',
            'login_logo_color' => '#ffffff',
            'login_text_color' => '#ffffff'
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
                    navbar_color,
                    navbar_text_color,
                    navbar_hover_color,
                    sidebar_color,
                    sidebar_text_color,
                    sidebar_hover_color,
                    logo_mini_color,
                    logo_lg_color,
                    logo_background_color,
                    icon_color,
                    sidebar_toggle_hover_color,
                    dropdown_hover_color,
                    button_primary_color,
                    button_primary_hover_color,
                    link_hover_color,
                    active_menu_color,
                    active_menu_text_color,
                    login_gradient_start,
                    login_gradient_end,
                    login_logo_color,
                    login_text_color,
                    activo,
                    usuario_creador
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
            ");
            
            $resultado = $stmt->execute([
                $datos['nombre_configuracion'],
                $datos['navbar_color'],
                $datos['navbar_text_color'],
                $datos['navbar_hover_color'],
                $datos['sidebar_color'],
                $datos['sidebar_text_color'],
                $datos['sidebar_hover_color'],
                $datos['logo_mini_color'],
                $datos['logo_lg_color'],
                $datos['logo_background_color'],
                $datos['icon_color'],
                $datos['sidebar_toggle_hover_color'],
                $datos['dropdown_hover_color'],
                $datos['button_primary_color'],
                $datos['button_primary_hover_color'],
                $datos['link_hover_color'],
                $datos['active_menu_color'],
                $datos['active_menu_text_color'],
                $datos['login_gradient_start'],
                $datos['login_gradient_end'],
                $datos['login_logo_color'],
                $datos['login_text_color'],
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
