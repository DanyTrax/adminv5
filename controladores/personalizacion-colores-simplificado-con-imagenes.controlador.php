<?php
/*=============================================
CONTROLADOR PERSONALIZACIÓN SIMPLIFICADA CON SUBIDA DE IMÁGENES
=============================================*/

require_once __DIR__ . "/../modelos/personalizacion-colores-simplificado.modelo.php";

class ControladorPersonalizacionColores {
    
    /*=============================================
    MOSTRAR CONFIGURACIÓN ACTIVA
    =============================================*/
    static public function ctrMostrarConfiguracionActiva() {
        
        return ModeloPersonalizacionColores::mdlObtenerConfiguracionActiva();
    }
    
    /*=============================================
    ACTUALIZAR CONFIGURACIÓN
    =============================================*/
    static public function ctrActualizarConfiguracion() {
        
        if (isset($_POST["actualizarConfiguracion"])) {
            
            // Validar campos requeridos
            $camposRequeridos = [
                'nombre_configuracion',
                'login_gradient_start',
                'login_gradient_end',
                'navbar_color',
                'navbar_hover_color',
                'sidebar_color',
                'sidebar_hover_color',
                'sidebar_text_color'
            ];
            
            foreach ($camposRequeridos as $campo) {
                if (empty($_POST[$campo])) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Campos incompletos",
                            text: "Todos los campos son obligatorios",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
            }
            
            // Validar formato de colores hexadecimales
            $patronColor = '/^#[0-9A-Fa-f]{6}$/';
            $camposColor = [
                'login_gradient_start',
                'login_gradient_end',
                'navbar_color',
                'navbar_hover_color',
                'sidebar_color',
                'sidebar_hover_color',
                'sidebar_text_color'
            ];
            
            foreach ($camposColor as $campo) {
                if (!preg_match($patronColor, $_POST[$campo])) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Color inválido",
                            text: "El campo ' . $campo . ' debe tener un formato de color hexadecimal válido (#RRGGBB)",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }
            }
            
            // Procesar imágenes
            $imagenes = [
                'icono_pequeno' => 'vistas/img/plantilla/icono-blanco.png',
                'logo_menu' => 'vistas/img/plantilla/logo-blanco-lineal.png',
                'logo_login' => 'vistas/img/plantilla/Infinito1.png'
            ];
            
            // Si se subieron imágenes, procesarlas
            if (!empty($_FILES)) {
                foreach ($_FILES as $campo => $archivo) {
                    if ($archivo['error'] == 0) {
                        $rutaDestino = self::procesarImagen($archivo, $campo);
                        if ($rutaDestino) {
                            $imagenes[str_replace('_file', '', $campo)] = $rutaDestino;
                        }
                    }
                }
            }
            
            $datos = [
                'nombre_configuracion' => $_POST['nombre_configuracion'],
                'login_gradient_start' => $_POST['login_gradient_start'],
                'login_gradient_end' => $_POST['login_gradient_end'],
                'navbar_color' => $_POST['navbar_color'],
                'navbar_hover_color' => $_POST['navbar_hover_color'],
                'sidebar_color' => $_POST['sidebar_color'],
                'sidebar_hover_color' => $_POST['sidebar_hover_color'],
                'sidebar_text_color' => $_POST['sidebar_text_color'],
                'icono_pequeno' => $imagenes['icono_pequeno'],
                'logo_menu' => $imagenes['logo_menu'],
                'logo_login' => $imagenes['logo_login']
            ];
            
            $resultado = ModeloPersonalizacionColores::mdlActualizarConfiguracion($datos);
            
            if ($resultado['success']) {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Configuración actualizada!",
                        text: "' . $resultado['message'] . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result) {
                        if (result.value) {
                            window.location = "personalizacion-colores-simplificado";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "' . $resultado['error'] . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }
    
    /*=============================================
    PROCESAR IMAGEN SUBIDA
    =============================================*/
    static private function procesarImagen($archivo, $tipo) {
        
        // Crear directorio si no existe
        $directorio = "vistas/img/personalizacion/";
        if (!file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        // Validar tipo de archivo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($archivo['type'], $tiposPermitidos)) {
            return false;
        }
        
        // Generar nombre único
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = $tipo . '_' . time() . '.' . $extension;
        $rutaCompleta = $directorio . $nombreArchivo;
        
        // Redimensionar y guardar imagen
        if (self::redimensionarImagen($archivo['tmp_name'], $rutaCompleta, $tipo)) {
            return $rutaCompleta;
        }
        
        return false;
    }
    
    /*=============================================
    REDIMENSIONAR IMAGEN
    =============================================*/
    static private function redimensionarImagen($archivoOrigen, $archivoDestino, $tipo) {
        
        // Obtener dimensiones de la imagen original
        $infoImagen = getimagesize($archivoOrigen);
        if (!$infoImagen) {
            return false;
        }
        
        $anchoOriginal = $infoImagen[0];
        $altoOriginal = $infoImagen[1];
        $tipoImagen = $infoImagen[2];
        
        // Definir dimensiones según el tipo
        $dimensiones = [
            'icono_pequeno_file' => [50, 50],
            'logo_menu_file' => [200, 50],
            'logo_login_file' => [200, 100]
        ];
        
        if (!isset($dimensiones[$tipo])) {
            return false;
        }
        
        list($anchoDestino, $altoDestino) = $dimensiones[$tipo];
        
        // Crear imagen desde archivo
        switch ($tipoImagen) {
            case IMAGETYPE_JPEG:
                $imagenOriginal = imagecreatefromjpeg($archivoOrigen);
                break;
            case IMAGETYPE_PNG:
                $imagenOriginal = imagecreatefrompng($archivoOrigen);
                break;
            case IMAGETYPE_GIF:
                $imagenOriginal = imagecreatefromgif($archivoOrigen);
                break;
            case IMAGETYPE_WEBP:
                $imagenOriginal = imagecreatefromwebp($archivoOrigen);
                break;
            default:
                return false;
        }
        
        if (!$imagenOriginal) {
            return false;
        }
        
        // Crear imagen redimensionada
        $imagenRedimensionada = imagecreatetruecolor($anchoDestino, $altoDestino);
        
        // Preservar transparencia para PNG
        if ($tipoImagen == IMAGETYPE_PNG) {
            imagealphablending($imagenRedimensionada, false);
            imagesavealpha($imagenRedimensionada, true);
            $transparente = imagecolorallocatealpha($imagenRedimensionada, 255, 255, 255, 127);
            imagefill($imagenRedimensionada, 0, 0, $transparente);
        }
        
        // Redimensionar
        imagecopyresampled(
            $imagenRedimensionada, $imagenOriginal,
            0, 0, 0, 0,
            $anchoDestino, $altoDestino,
            $anchoOriginal, $altoOriginal
        );
        
        // Guardar imagen
        $resultado = false;
        switch ($tipoImagen) {
            case IMAGETYPE_JPEG:
                $resultado = imagejpeg($imagenRedimensionada, $archivoDestino, 90);
                break;
            case IMAGETYPE_PNG:
                $resultado = imagepng($imagenRedimensionada, $archivoDestino, 9);
                break;
            case IMAGETYPE_GIF:
                $resultado = imagegif($imagenRedimensionada, $archivoDestino);
                break;
            case IMAGETYPE_WEBP:
                $resultado = imagewebp($imagenRedimensionada, $archivoDestino, 90);
                break;
        }
        
        // Liberar memoria
        imagedestroy($imagenOriginal);
        imagedestroy($imagenRedimensionada);
        
        return $resultado;
    }
    
    /*=============================================
    OBTENER TODAS LAS CONFIGURACIONES
    =============================================*/
    static public function ctrObtenerTodasConfiguraciones() {
        
        return ModeloPersonalizacionColores::mdlObtenerTodasConfiguraciones();
    }
    
    /*=============================================
    ACTIVAR CONFIGURACIÓN
    =============================================*/
    static public function ctrActivarConfiguracion($id) {
        
        $resultado = ModeloPersonalizacionColores::mdlActivarConfiguracion($id);
        
        if ($resultado['success']) {
            echo '<script>
                swal({
                    type: "success",
                    title: "¡Configuración activada!",
                    text: "' . $resultado['message'] . '",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result) {
                    if (result.value) {
                        window.location = "personalizacion-colores-simplificado";
                    }
                });
            </script>';
        } else {
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "' . $resultado['error'] . '",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
    }
    
    /*=============================================
    ELIMINAR CONFIGURACIÓN
    =============================================*/
    static public function ctrEliminarConfiguracion($id) {
        
        $resultado = ModeloPersonalizacionColores::mdlEliminarConfiguracion($id);
        
        if ($resultado['success']) {
            echo '<script>
                swal({
                    type: "success",
                    title: "¡Configuración eliminada!",
                    text: "' . $resultado['message'] . '",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result) {
                    if (result.value) {
                        window.location = "personalizacion-colores-simplificado";
                    }
                });
            </script>';
        } else {
            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "' . $resultado['error'] . '",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
    }
    
    /*=============================================
    APLICAR CONFIGURACIÓN A ESTILOS
    =============================================*/
    static public function ctrAplicarConfiguracionEstilos() {
        
        $configuracion = self::ctrMostrarConfiguracionActiva();
        
        $css = "
        <style>
            /* Barra Principal (Navbar + Logo) */
            .navbar.navbar-static-top {
                background-color: {$configuracion['navbar_color']} !important;
            }
            
            .main-header .logo {
                background-color: {$configuracion['navbar_color']} !important;
            }
            
            .navbar .navbar-nav > li > a:hover {
                background-color: {$configuracion['navbar_hover_color']} !important;
            }
            
            .sidebar-toggle:hover {
                background-color: {$configuracion['navbar_hover_color']} !important;
            }
            
            /* Barra Lateral */
            .main-sidebar {
                background-color: {$configuracion['sidebar_color']} !important;
            }
            
            .sidebar-menu > li > a {
                color: {$configuracion['sidebar_text_color']} !important;
            }
            
            .sidebar-menu > li > a:hover {
                background-color: {$configuracion['sidebar_hover_color']} !important;
            }
            
            /* Imágenes */
            .logo-mini img {
                content: url('{$configuracion['icono_pequeno']}') !important;
            }
            
            .logo-lg img {
                content: url('{$configuracion['logo_menu']}') !important;
            }
            
            .login-logo img {
                content: url('{$configuracion['logo_login']}') !important;
            }
            
            /* Login Page */
            .login-page {
                background: linear-gradient(135deg, {$configuracion['login_gradient_start']} 0%, {$configuracion['login_gradient_end']} 100%) !important;
            }
            
            /* Register Page */
            .register-page {
                background: linear-gradient(135deg, {$configuracion['login_gradient_start']} 0%, {$configuracion['login_gradient_end']} 100%) !important;
            }
        </style>
        ";
        
        return $css;
    }
}
?>
