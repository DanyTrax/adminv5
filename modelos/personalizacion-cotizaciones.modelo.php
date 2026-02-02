<?php

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

class ModeloPersonalizacionCotizaciones {
    
    /*=============================================
    OBTENER ID SUCURSAL ACTUAL
    =============================================*/
    static public function mdlObtenerIdSucursalActual() {
        
        try {
            require_once __DIR__ . "/conexion.php";
            $conexionLocal = Conexion::conectar();
            
            $stmt = $conexionLocal->prepare("SELECT codigo_sucursal FROM sucursal_local LIMIT 1");
            $stmt->execute();
            $sucursalLocal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sucursalLocal || empty($sucursalLocal['codigo_sucursal'])) {
                return null;
            }
            
            $codigoSucursal = $sucursalLocal['codigo_sucursal'];
            
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
    OBTENER CONFIGURACIÓN ACTIVA
    =============================================*/
    static public function mdlObtenerConfiguracionActiva($idSucursal = null) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            // Si no se especifica sucursal, obtener la actual
            if ($idSucursal === null) {
                $idSucursal = self::mdlObtenerIdSucursalActual();
            }
            
            // Buscar configuración específica de la sucursal
            if ($idSucursal !== null) {
                $stmt = $conexion->prepare("
                    SELECT pc.*, s.nombre as nombre_sucursal
                    FROM personalizacion_cotizaciones pc
                    LEFT JOIN sucursales s ON pc.id_sucursal = s.id
                    WHERE pc.activo = 1 AND pc.id_sucursal = ?
                    LIMIT 1
                ");
                $stmt->execute([$idSucursal]);
                $config = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($config) {
                    return $config;
                }
            }
            
            // Si no hay configuración específica, buscar la global
            $stmt = $conexion->prepare("
                SELECT * FROM personalizacion_cotizaciones
                WHERE activo = 1 AND id_sucursal IS NULL
                LIMIT 1
            ");
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($config) {
                $config['nombre_sucursal'] = 'Global';
                return $config;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerConfiguracionActiva: " . $e->getMessage());
            return null;
        }
    }
    
    /*=============================================
    OBTENER TODAS LAS CONFIGURACIONES
    =============================================*/
    static public function mdlObtenerTodasConfiguraciones($idSucursal = null) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            if ($idSucursal !== null) {
                $stmt = $conexion->prepare("
                    SELECT pc.*, s.nombre as nombre_sucursal
                    FROM personalizacion_cotizaciones pc
                    LEFT JOIN sucursales s ON pc.id_sucursal = s.id
                    WHERE pc.id_sucursal = ? OR pc.id_sucursal IS NULL
                    ORDER BY pc.id_sucursal IS NULL, pc.fecha_actualizacion DESC
                ");
                $stmt->execute([$idSucursal]);
            } else {
                $stmt = $conexion->prepare("
                    SELECT pc.*, s.nombre as nombre_sucursal
                    FROM personalizacion_cotizaciones pc
                    LEFT JOIN sucursales s ON pc.id_sucursal = s.id
                    ORDER BY pc.id_sucursal IS NULL, pc.fecha_actualizacion DESC
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
    CREAR CONFIGURACIÓN
    =============================================*/
    static public function mdlCrearConfiguracion($datos) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            // Verificar que la tabla existe
            $stmt = $conexion->query("SHOW TABLES LIKE 'personalizacion_cotizaciones'");
            if ($stmt->rowCount() == 0) {
                throw new Exception("La tabla 'personalizacion_cotizaciones' no existe. Ejecute primero el script de creación en: crear-tabla-personalizacion-cotizaciones");
            }
            
            // Desactivar otras configuraciones de la misma sucursal
            if (isset($datos['id_sucursal']) && $datos['id_sucursal'] !== null) {
                $stmt = $conexion->prepare("UPDATE personalizacion_cotizaciones SET activo = 0 WHERE id_sucursal = ?");
                $stmt->execute([$datos['id_sucursal']]);
            } else {
                $stmt = $conexion->prepare("UPDATE personalizacion_cotizaciones SET activo = 0 WHERE id_sucursal IS NULL");
                $stmt->execute();
            }
            
            // Obtener nombre de sucursal si existe
            $nombreSucursal = 'Global';
            if (isset($datos['id_sucursal']) && $datos['id_sucursal'] !== null) {
                $stmt = $conexion->prepare("SELECT nombre FROM sucursales WHERE id = ?");
                $stmt->execute([$datos['id_sucursal']]);
                $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($sucursal) {
                    $nombreSucursal = $sucursal['nombre'];
                }
            }
            
            $stmt = $conexion->prepare("
                INSERT INTO personalizacion_cotizaciones (
                    id_sucursal,
                    nombre_sucursal,
                    header_logo,
                    logo_width,
                    logo_align_vertical,
                    logo_align_horizontal,
                    header_nombre_empresa,
                    header_nit,
                    header_regimen,
                    header_servicios,
                    header_color_fondo,
                    header_color_texto,
                    header_font_size,
                    body_font_size,
                    footer_direccion,
                    footer_telefono,
                    footer_movil,
                    footer_correo,
                    footer_color_fondo,
                    footer_color_texto,
                    footer_font_size,
                    activo,
                    usuario_creador
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
            ");
            
            $resultado = $stmt->execute([
                $datos['id_sucursal'] ?? null,
                $nombreSucursal,
                $datos['header_logo'] ?? 'vistas/img/cotizacion/Infinito1.png',
                $datos['logo_width'] ?? 80,
                $datos['logo_align_vertical'] ?? 'center',
                $datos['logo_align_horizontal'] ?? 'center',
                $datos['header_nombre_empresa'] ?? 'ACPLASTICOS',
                $datos['header_nit'] ?? '',
                $datos['header_regimen'] ?? '',
                $datos['header_servicios'] ?? '',
                $datos['header_color_fondo'] ?? '#873173',
                $datos['header_color_texto'] ?? '#FFFFFF',
                $datos['header_font_size'] ?? 14,
                $datos['body_font_size'] ?? 13,
                $datos['footer_direccion'] ?? '',
                $datos['footer_telefono'] ?? '',
                $datos['footer_movil'] ?? '',
                $datos['footer_correo'] ?? '',
                $datos['footer_color_fondo'] ?? '#873173',
                $datos['footer_color_texto'] ?? '#FFFFFF',
                $datos['footer_font_size'] ?? 16,
                $datos['usuario_creador'] ?? 1
            ]);
            
            if ($resultado) {
                return $conexion->lastInsertId();
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error en execute: " . print_r($errorInfo, true));
                throw new Exception("Error al ejecutar INSERT: " . ($errorInfo[2] ?? 'Error desconocido'));
            }
            
        } catch (PDOException $e) {
            error_log("Error PDO en mdlCrearConfiguracion: " . $e->getMessage());
            throw new Exception("Error de base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Error en mdlCrearConfiguracion: " . $e->getMessage());
            throw $e;
        }
    }
    
    /*=============================================
    ACTUALIZAR CONFIGURACIÓN
    =============================================*/
    static public function mdlActualizarConfiguracion($datos) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            // Obtener nombre de sucursal si existe
            $nombreSucursal = 'Global';
            if (isset($datos['id_sucursal']) && $datos['id_sucursal'] !== null) {
                $stmt = $conexion->prepare("SELECT nombre FROM sucursales WHERE id = ?");
                $stmt->execute([$datos['id_sucursal']]);
                $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($sucursal) {
                    $nombreSucursal = $sucursal['nombre'];
                }
            }
            
            $stmt = $conexion->prepare("
                UPDATE personalizacion_cotizaciones SET
                    id_sucursal = ?,
                    nombre_sucursal = ?,
                    header_logo = ?,
                    logo_width = ?,
                    logo_align_vertical = ?,
                    logo_align_horizontal = ?,
                    header_nombre_empresa = ?,
                    header_nit = ?,
                    header_regimen = ?,
                    header_servicios = ?,
                    header_color_fondo = ?,
                    header_color_texto = ?,
                    header_font_size = ?,
                    body_font_size = ?,
                    footer_direccion = ?,
                    footer_telefono = ?,
                    footer_movil = ?,
                    footer_correo = ?,
                    footer_color_fondo = ?,
                    footer_color_texto = ?,
                    footer_font_size = ?
                WHERE id = ?
            ");
            
            $resultado = $stmt->execute([
                $datos['id_sucursal'] ?? null,
                $nombreSucursal,
                $datos['header_logo'] ?? 'vistas/img/cotizacion/Infinito1.png',
                $datos['logo_width'] ?? 80,
                $datos['logo_align_vertical'] ?? 'center',
                $datos['logo_align_horizontal'] ?? 'center',
                $datos['header_nombre_empresa'] ?? 'ACPLASTICOS',
                $datos['header_nit'] ?? '',
                $datos['header_regimen'] ?? '',
                $datos['header_servicios'] ?? '',
                $datos['header_color_fondo'] ?? '#873173',
                $datos['header_color_texto'] ?? '#FFFFFF',
                $datos['header_font_size'] ?? 14,
                $datos['body_font_size'] ?? 13,
                $datos['footer_direccion'] ?? '',
                $datos['footer_telefono'] ?? '',
                $datos['footer_movil'] ?? '',
                $datos['footer_correo'] ?? '',
                $datos['footer_color_fondo'] ?? '#873173',
                $datos['footer_color_texto'] ?? '#FFFFFF',
                $datos['footer_font_size'] ?? 16,
                $datos['id']
            ]);
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error en mdlActualizarConfiguracion: " . $e->getMessage());
            return false;
        }
    }
    
    /*=============================================
    ACTIVAR CONFIGURACIÓN
    =============================================*/
    static public function mdlActivarConfiguracion($id, $idSucursal = null) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            // Obtener la configuración a activar
            $stmt = $conexion->prepare("SELECT id_sucursal FROM personalizacion_cotizaciones WHERE id = ?");
            $stmt->execute([$id]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$config) {
                return false;
            }
            
            // Desactivar otras configuraciones de la misma sucursal
            if ($config['id_sucursal'] !== null) {
                $stmt = $conexion->prepare("UPDATE personalizacion_cotizaciones SET activo = 0 WHERE id_sucursal = ? AND id != ?");
                $stmt->execute([$config['id_sucursal'], $id]);
            } else {
                $stmt = $conexion->prepare("UPDATE personalizacion_cotizaciones SET activo = 0 WHERE id_sucursal IS NULL AND id != ?");
                $stmt->execute([$id]);
            }
            
            // Activar la configuración seleccionada
            $stmt = $conexion->prepare("UPDATE personalizacion_cotizaciones SET activo = 1 WHERE id = ?");
            return $stmt->execute([$id]);
            
        } catch (Exception $e) {
            error_log("Error en mdlActivarConfiguracion: " . $e->getMessage());
            return false;
        }
    }
    
    /*=============================================
    ELIMINAR CONFIGURACIÓN
    =============================================*/
    static public function mdlEliminarConfiguracion($id) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            $stmt = $conexion->prepare("DELETE FROM personalizacion_cotizaciones WHERE id = ?");
            return $stmt->execute([$id]);
            
        } catch (Exception $e) {
            error_log("Error en mdlEliminarConfiguracion: " . $e->getMessage());
            return false;
        }
    }
    
    /*=============================================
    OBTENER CONFIGURACIÓN POR ID
    =============================================*/
    static public function mdlObtenerConfiguracion($id) {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            $stmt = $conexion->prepare("
                SELECT pc.*, s.nombre as nombre_sucursal
                FROM personalizacion_cotizaciones pc
                LEFT JOIN sucursales s ON pc.id_sucursal = s.id
                WHERE pc.id = ?
            ");
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerConfiguracion: " . $e->getMessage());
            return null;
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES ACTIVAS
    =============================================*/
    static public function mdlObtenerSucursalesActivas() {
        
        try {
            $conexion = ConexionCentral::conectar();
            
            $stmt = $conexion->prepare("SELECT id, nombre FROM sucursales WHERE activo = 1 ORDER BY nombre");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en mdlObtenerSucursalesActivas: " . $e->getMessage());
            return [];
        }
    }
}

