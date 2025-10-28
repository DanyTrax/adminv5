<?php
/*=============================================
CONTROLADOR PERSONALIZACIÓN SIMPLIFICADA
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
                'sidebar_text_color',
                'icono_pequeno',
                'logo_menu',
                'logo_login'
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
            
            $datos = [
                'nombre_configuracion' => $_POST['nombre_configuracion'],
                'login_gradient_start' => $_POST['login_gradient_start'],
                'login_gradient_end' => $_POST['login_gradient_end'],
                'navbar_color' => $_POST['navbar_color'],
                'navbar_hover_color' => $_POST['navbar_hover_color'],
                'sidebar_color' => $_POST['sidebar_color'],
                'sidebar_hover_color' => $_POST['sidebar_hover_color'],
                'sidebar_text_color' => $_POST['sidebar_text_color'],
                'icono_pequeno' => $_POST['icono_pequeno'],
                'logo_menu' => $_POST['logo_menu'],
                'logo_login' => $_POST['logo_login']
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
                            window.location = "personalizacion-colores";
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
                        window.location = "personalizacion-colores";
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
                        window.location = "personalizacion-colores";
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
