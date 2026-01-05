<?php
/*=============================================
SISTEMA DE CSS DINÁMICO GLOBAL
=============================================*/

require_once "api-transferencias/conexion-central.php";
require_once "modelos/personalizacion-colores-simplificado.modelo.php";

try {
    // Obtener configuración activa de la sucursal actual (o global)
    $config = ModeloPersonalizacionColores::mdlObtenerConfiguracionActiva();
    
    if (!$config) {
        // Configuración por defecto si no existe
        $config = [
            'navbar_color' => '#3c8dbc',
            'navbar_hover_color' => '#2c3e50',
            'sidebar_color' => '#222d32',
            'sidebar_hover_color' => '#1a252f',
            'sidebar_text_color' => '#b8c7ce',
            'login_gradient_start' => '#3c8dbc',
            'login_gradient_end' => '#2c3e50',
            'icono_pequeno' => 'vistas/img/plantilla/icono-blanco.png',
            'logo_menu' => 'vistas/img/plantilla/logo-blanco-lineal.png',
            'logo_login' => 'vistas/img/plantilla/Infinito1.png'
        ];
    }
    
    // Generar CSS dinámico
    $css = "
    <style id='personalizacion-dinamica'>
        /* Barra Principal (Navbar + Logo) */
        .navbar.navbar-static-top,
        .main-header .navbar {
            background-color: {$config['navbar_color']} !important;
        }
        
        .main-header .logo {
            background-color: {$config['navbar_color']} !important;
        }
        
        .navbar .navbar-nav > li > a:hover,
        .navbar .navbar-nav > li > a:focus {
            background-color: {$config['navbar_hover_color']} !important;
        }
        
        .sidebar-toggle:hover,
        .sidebar-toggle:focus {
            background-color: {$config['navbar_hover_color']} !important;
        }
        
        /* Barra Lateral */
        .main-sidebar {
            background-color: {$config['sidebar_color']} !important;
        }
        
        .sidebar-menu > li > a {
            color: {$config['sidebar_text_color']} !important;
        }
        
        .sidebar-menu > li > a:hover,
        .sidebar-menu > li > a:focus {
            background-color: {$config['sidebar_hover_color']} !important;
        }
        
        .sidebar-menu > li.active > a {
            background-color: {$config['navbar_hover_color']} !important;
        }
        
        /* Imágenes dinámicas - Se actualizan con JavaScript */
        
        /* Login Page */
        .login-page {
            background: linear-gradient(135deg, {$config['login_gradient_start']} 0%, {$config['login_gradient_end']} 100%) !important;
        }
        
        /* Register Page */
        .register-page {
            background: linear-gradient(135deg, {$config['login_gradient_start']} 0%, {$config['login_gradient_end']} 100%) !important;
        }
        
        /* Botones principales */
        .btn-primary {
            background-color: {$config['navbar_color']} !important;
            border-color: {$config['navbar_color']} !important;
        }
        
        .btn-primary:hover,
        .btn-primary:focus {
            background-color: {$config['navbar_hover_color']} !important;
            border-color: {$config['navbar_hover_color']} !important;
        }
        
        /* Boxes principales */
        .box-primary .box-header {
            background-color: {$config['navbar_color']} !important;
        }
        
        /* Alertas */
        .alert-info {
            background-color: {$config['navbar_color']} !important;
            border-color: {$config['navbar_color']} !important;
        }
    </style>
    <script>
    // Actualizar imágenes dinámicamente cuando el DOM esté listo
    (function() {
        var configImagenes = {
            icono_pequeno: '{$config['icono_pequeno']}',
            logo_menu: '{$config['logo_menu']}',
            logo_login: '{$config['logo_login']}'
        };
        
        function actualizarImagenes() {
            // Actualizar logo mini
            var logoMini = document.querySelector('.logo-mini img');
            if (logoMini) {
                logoMini.src = configImagenes.icono_pequeno;
            }
            
            // Actualizar logo grande
            var logoLg = document.querySelector('.logo-lg img');
            if (logoLg) {
                logoLg.src = configImagenes.logo_menu;
            }
            
            // Actualizar logo login
            var loginLogo = document.querySelector('.login-logo img');
            if (loginLogo) {
                loginLogo.src = configImagenes.logo_login;
            }
        }
        
        // Ejecutar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', actualizarImagenes);
        } else {
            actualizarImagenes();
        }
        
        // También ejecutar después de un pequeño delay para asegurar que jQuery esté cargado
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                actualizarImagenes();
                
                // Actualizar también con jQuery por si acaso
                $('.logo-mini img').attr('src', configImagenes.icono_pequeno);
                $('.logo-lg img').attr('src', configImagenes.logo_menu);
                $('.login-logo img').attr('src', configImagenes.logo_login);
            });
        } else {
            // Si jQuery no está disponible, esperar un poco más
            setTimeout(actualizarImagenes, 500);
        }
    })();
    </script>
    ";
    
    echo $css;
    
} catch (Exception $e) {
    // CSS por defecto en caso de error
    echo "
    <style id='personalizacion-dinamica'>
        /* CSS por defecto en caso de error */
        .navbar.navbar-static-top { background-color: #3c8dbc !important; }
        .main-header .logo { background-color: #3c8dbc !important; }
        .main-sidebar { background-color: #222d32 !important; }
        .login-page { background: linear-gradient(135deg, #3c8dbc 0%, #2c3e50 100%) !important; }
    </style>
    ";
}
?>
