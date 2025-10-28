<?php
/*=============================================
CONTROLADOR PERSONALIZACIÓN DE COLORES
=============================================*/

require_once __DIR__ . "/../modelos/personalizacion-colores.modelo.php";

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
                'navbar_hover_color',
                'sidebar_color',
                'sidebar_text_color',
                'sidebar_hover_color',
                'logo_mini_color',
                'logo_lg_color',
                'logo_background_color',
                'logo_mini_imagen',
                'logo_lg_imagen',
                'login_logo_imagen',
                'favicon_imagen',
                'icon_color',
                'sidebar_toggle_hover_color',
                'dropdown_hover_color',
                'button_primary_color',
                'button_primary_hover_color',
                'link_hover_color',
                'active_menu_color',
                'active_menu_text_color',
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
                'navbar_hover_color' => $_POST['navbar_hover_color'],
                'sidebar_color' => $_POST['sidebar_color'],
                'sidebar_text_color' => $_POST['sidebar_text_color'],
                'sidebar_hover_color' => $_POST['sidebar_hover_color'],
                'logo_mini_color' => $_POST['logo_mini_color'],
                'logo_lg_color' => $_POST['logo_lg_color'],
                'logo_background_color' => $_POST['logo_background_color'],
                'logo_mini_imagen' => $_POST['logo_mini_imagen'],
                'logo_lg_imagen' => $_POST['logo_lg_imagen'],
                'login_logo_imagen' => $_POST['login_logo_imagen'],
                'favicon_imagen' => $_POST['favicon_imagen'],
                'icon_color' => $_POST['icon_color'],
                'sidebar_toggle_hover_color' => $_POST['sidebar_toggle_hover_color'],
                'dropdown_hover_color' => $_POST['dropdown_hover_color'],
                'button_primary_color' => $_POST['button_primary_color'],
                'button_primary_hover_color' => $_POST['button_primary_hover_color'],
                'link_hover_color' => $_POST['link_hover_color'],
                'active_menu_color' => $_POST['active_menu_color'],
                'active_menu_text_color' => $_POST['active_menu_text_color'],
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
                background-color: {$configuracion['navbar_hover_color']} !important;
            }
            
            /* Sidebar */
            .main-sidebar {
                background-color: {$configuracion['sidebar_color']} !important;
            }
            
            .sidebar-menu > li > a {
                color: {$configuracion['sidebar_text_color']} !important;
            }
            
            .sidebar-menu > li > a:hover {
                background-color: {$configuracion['sidebar_hover_color']} !important;
            }
            
            .sidebar-menu > li.active > a {
                background-color: {$configuracion['active_menu_color']} !important;
                color: {$configuracion['active_menu_text_color']} !important;
            }
            
            /* Logo */
            .main-header .logo {
                background-color: {$configuracion['logo_background_color']} !important;
            }
            
            .logo-mini img {
                content: url('{$configuracion['logo_mini_imagen']}') !important;
            }
            
            .logo-lg img {
                content: url('{$configuracion['logo_lg_imagen']}') !important;
            }
            
            .logo-mini {
                color: {$configuracion['logo_mini_color']} !important;
            }
            
            .logo-lg {
                color: {$configuracion['logo_lg_color']} !important;
            }
            
            /* Sidebar Toggle */
            .sidebar-toggle:hover {
                background-color: {$configuracion['sidebar_toggle_hover_color']} !important;
            }
            
            /* Iconos */
            .fa, .icon {
                color: {$configuracion['icon_color']} !important;
            }
            
            /* Dropdowns */
            .dropdown-menu > li > a:hover {
                background-color: {$configuracion['dropdown_hover_color']} !important;
            }
            
            /* Botones Primarios */
            .btn-primary {
                background-color: {$configuracion['button_primary_color']} !important;
                border-color: {$configuracion['button_primary_color']} !important;
            }
            
            .btn-primary:hover {
                background-color: {$configuracion['button_primary_hover_color']} !important;
                border-color: {$configuracion['button_primary_hover_color']} !important;
            }
            
            /* Enlaces */
            a:hover {
                color: {$configuracion['link_hover_color']} !important;
            }
            
            /* Login Page */
            .login-page {
                background: linear-gradient(135deg, {$configuracion['login_gradient_start']} 0%, {$configuracion['login_gradient_end']} 100%) !important;
            }
            
            .login-logo img {
                content: url('{$configuracion['login_logo_imagen']}') !important;
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
            
            /* Favicon */
            link[rel=\"icon\"] {
                href: '{$configuracion['favicon_imagen']}' !important;
            }
        </style>
        ";
        
        return $css;
    }
}
?>
