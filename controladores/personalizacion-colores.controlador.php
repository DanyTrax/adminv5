<?php
/*=============================================
CONTROLADOR PERSONALIZACIÓN DE COLORES
=============================================*/

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
                'navbar_color',
                'navbar_text_color',
                'sidebar_color',
                'sidebar_text_color',
                'logo_mini_color',
                'logo_lg_color',
                'icon_color',
                'login_gradient_start',
                'login_gradient_end',
                'login_logo_color',
                'login_text_color'
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
            foreach ($camposRequeridos as $campo) {
                if ($campo !== 'nombre_configuracion' && !preg_match($patronColor, $_POST[$campo])) {
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
                'navbar_color' => $_POST['navbar_color'],
                'navbar_text_color' => $_POST['navbar_text_color'],
                'sidebar_color' => $_POST['sidebar_color'],
                'sidebar_text_color' => $_POST['sidebar_text_color'],
                'logo_mini_color' => $_POST['logo_mini_color'],
                'logo_lg_color' => $_POST['logo_lg_color'],
                'icon_color' => $_POST['icon_color'],
                'login_gradient_start' => $_POST['login_gradient_start'],
                'login_gradient_end' => $_POST['login_gradient_end'],
                'login_logo_color' => $_POST['login_logo_color'],
                'login_text_color' => $_POST['login_text_color']
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
            /* Navbar */
            .navbar.navbar-static-top {
                background-color: {$configuracion['navbar_color']} !important;
                color: {$configuracion['navbar_text_color']} !important;
            }
            
            .navbar .navbar-nav > li > a {
                color: {$configuracion['navbar_text_color']} !important;
            }
            
            .navbar .navbar-nav > li > a:hover {
                background-color: rgba(255,255,255,0.1) !important;
            }
            
            /* Sidebar */
            .main-sidebar {
                background-color: {$configuracion['sidebar_color']} !important;
            }
            
            .sidebar-menu > li > a {
                color: {$configuracion['sidebar_text_color']} !important;
            }
            
            .sidebar-menu > li > a:hover {
                background-color: rgba(255,255,255,0.1) !important;
            }
            
            /* Logo */
            .logo-mini {
                color: {$configuracion['logo_mini_color']} !important;
            }
            
            .logo-lg {
                color: {$configuracion['logo_lg_color']} !important;
            }
            
            /* Iconos */
            .fa, .icon {
                color: {$configuracion['icon_color']} !important;
            }
            
            /* Login Page */
            .login-page {
                background: linear-gradient(135deg, {$configuracion['login_gradient_start']} 0%, {$configuracion['login_gradient_end']} 100%) !important;
            }
            
            .login-logo {
                color: {$configuracion['login_logo_color']} !important;
            }
            
            .login-box-body {
                color: {$configuracion['login_text_color']} !important;
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
