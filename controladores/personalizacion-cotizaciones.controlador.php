<?php

require_once __DIR__ . "/../modelos/personalizacion-cotizaciones.modelo.php";

class ControladorPersonalizacionCotizaciones {
    
    /*=============================================
    MOSTRAR CONFIGURACIÓN ACTIVA
    =============================================*/
    static public function ctrMostrarConfiguracionActiva($idSucursal = null) {
        
        return ModeloPersonalizacionCotizaciones::mdlObtenerConfiguracionActiva($idSucursal);
    }
    
    /*=============================================
    OBTENER TODAS LAS CONFIGURACIONES
    =============================================*/
    static public function ctrObtenerTodasConfiguraciones($idSucursal = null) {
        
        return ModeloPersonalizacionCotizaciones::mdlObtenerTodasConfiguraciones($idSucursal);
    }
    
    /*=============================================
    CREAR CONFIGURACIÓN
    =============================================*/
    static public function ctrCrearConfiguracion() {
        
        if (isset($_POST["nuevoHeaderNombreEmpresa"])) {
            
            $datos = [
                "id_sucursal" => !empty($_POST["nuevoIdSucursal"]) ? (int)$_POST["nuevoIdSucursal"] : null,
                "header_logo" => $_POST["nuevoHeaderLogo"] ?? 'vistas/img/cotizacion/Infinito1.png',
                "logo_width" => !empty($_POST["nuevoLogoWidth"]) ? (int)$_POST["nuevoLogoWidth"] : 80,
                "logo_align_vertical" => $_POST["nuevoLogoAlignVertical"] ?? 'center',
                "logo_align_horizontal" => $_POST["nuevoLogoAlignHorizontal"] ?? 'center',
                "header_nombre_empresa" => $_POST["nuevoHeaderNombreEmpresa"] ?? '',
                "header_nit" => $_POST["nuevoHeaderNit"] ?? '',
                "header_regimen" => $_POST["nuevoHeaderRegimen"] ?? '',
                "header_servicios" => $_POST["nuevoHeaderServicios"] ?? '',
                "header_color_fondo" => $_POST["nuevoHeaderColorFondo"] ?? '#873173',
                "header_color_texto" => $_POST["nuevoHeaderColorTexto"] ?? '#FFFFFF',
                "header_font_size" => !empty($_POST["nuevoHeaderFontSize"]) ? (int)$_POST["nuevoHeaderFontSize"] : 14,
                "body_font_size" => !empty($_POST["nuevoBodyFontSize"]) ? (int)$_POST["nuevoBodyFontSize"] : 13,
                "footer_direccion" => $_POST["nuevoFooterDireccion"] ?? '',
                "footer_telefono" => $_POST["nuevoFooterTelefono"] ?? '',
                "footer_movil" => $_POST["nuevoFooterMovil"] ?? '',
                "footer_correo" => $_POST["nuevoFooterCorreo"] ?? '',
                "footer_color_fondo" => $_POST["nuevoFooterColorFondo"] ?? '#873173',
                "footer_color_texto" => $_POST["nuevoFooterColorTexto"] ?? '#FFFFFF',
                "footer_font_size" => !empty($_POST["nuevoFooterFontSize"]) ? (int)$_POST["nuevoFooterFontSize"] : 16,
                "usuario_creador" => $_SESSION["id"] ?? 1
            ];
            
            try {
                $respuesta = ModeloPersonalizacionCotizaciones::mdlCrearConfiguracion($datos);
                
                if ($respuesta && $respuesta > 0) {
                    return [
                        "success" => true,
                        "mensaje" => "Configuración creada exitosamente",
                        "id" => $respuesta
                    ];
                } else {
                    return [
                        "success" => false,
                        "mensaje" => "Error al crear la configuración. La operación no retornó un ID válido."
                    ];
                }
            } catch (PDOException $e) {
                error_log("Error PDO en ctrCrearConfiguracion: " . $e->getMessage());
                $mensaje = "Error de base de datos: " . $e->getMessage();
                // Simplificar mensaje para el usuario
                if (strpos($e->getMessage(), 'Table') !== false) {
                    $mensaje = "La tabla 'personalizacion_cotizaciones' no existe. Ejecute primero el script de creación.";
                }
                return [
                    "success" => false,
                    "mensaje" => $mensaje
                ];
            } catch (Exception $e) {
                error_log("Error en ctrCrearConfiguracion: " . $e->getMessage());
                return [
                    "success" => false,
                    "mensaje" => "Error: " . $e->getMessage()
                ];
            }
        }
        
        return [
            "success" => false,
            "mensaje" => "Datos incompletos"
        ];
    }
    
    /*=============================================
    ACTUALIZAR CONFIGURACIÓN
    =============================================*/
    static public function ctrActualizarConfiguracion() {
        
        if (isset($_POST["editarId"])) {
            
            $datos = [
                "id" => (int)$_POST["editarId"],
                "id_sucursal" => !empty($_POST["editarIdSucursal"]) ? (int)$_POST["editarIdSucursal"] : null,
                "header_logo" => $_POST["editarHeaderLogo"] ?? 'vistas/img/cotizacion/Infinito1.png',
                "header_nombre_empresa" => $_POST["editarHeaderNombreEmpresa"] ?? '',
                "header_nit" => $_POST["editarHeaderNit"] ?? '',
                "header_regimen" => $_POST["editarHeaderRegimen"] ?? '',
                "header_servicios" => $_POST["editarHeaderServicios"] ?? '',
                "header_color_fondo" => $_POST["editarHeaderColorFondo"] ?? '#873173',
                "header_color_texto" => $_POST["editarHeaderColorTexto"] ?? '#FFFFFF',
                "header_font_size" => !empty($_POST["editarHeaderFontSize"]) ? (int)$_POST["editarHeaderFontSize"] : 14,
                "body_font_size" => !empty($_POST["editarBodyFontSize"]) ? (int)$_POST["editarBodyFontSize"] : 13,
                "footer_direccion" => $_POST["editarFooterDireccion"] ?? '',
                "footer_telefono" => $_POST["editarFooterTelefono"] ?? '',
                "footer_movil" => $_POST["editarFooterMovil"] ?? '',
                "footer_correo" => $_POST["editarFooterCorreo"] ?? '',
                "footer_color_fondo" => $_POST["editarFooterColorFondo"] ?? '#873173',
                "footer_color_texto" => $_POST["editarFooterColorTexto"] ?? '#FFFFFF',
                "footer_font_size" => !empty($_POST["editarFooterFontSize"]) ? (int)$_POST["editarFooterFontSize"] : 16
            ];
            
            $respuesta = ModeloPersonalizacionCotizaciones::mdlActualizarConfiguracion($datos);
            
            if ($respuesta) {
                return [
                    "success" => true,
                    "mensaje" => "Configuración actualizada exitosamente"
                ];
            } else {
                return [
                    "success" => false,
                    "mensaje" => "Error al actualizar la configuración"
                ];
            }
        }
        
        return [
            "success" => false,
            "mensaje" => "Datos incompletos"
        ];
    }
    
    /*=============================================
    ACTIVAR CONFIGURACIÓN
    =============================================*/
    static public function ctrActivarConfiguracion($id, $idSucursal = null) {
        
        return ModeloPersonalizacionCotizaciones::mdlActivarConfiguracion($id, $idSucursal);
    }
    
    /*=============================================
    ELIMINAR CONFIGURACIÓN
    =============================================*/
    static public function ctrEliminarConfiguracion($id) {
        
        return ModeloPersonalizacionCotizaciones::mdlEliminarConfiguracion($id);
    }
    
    /*=============================================
    OBTENER CONFIGURACIÓN POR ID
    =============================================*/
    static public function ctrObtenerConfiguracion($id) {
        
        return ModeloPersonalizacionCotizaciones::mdlObtenerConfiguracion($id);
    }
    
    /*=============================================
    OBTENER SUCURSALES ACTIVAS
    =============================================*/
    static public function ctrObtenerSucursales() {
        
        return ModeloPersonalizacionCotizaciones::mdlObtenerSucursalesActivas();
    }
}

