<?php
/*=============================================
SISTEMA DE CSS DINÁMICO GLOBAL
=============================================*/

require_once "api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    // Obtener configuración activa
    $stmt = $conexion->prepare("SELECT * FROM personalizacion_colores WHERE activo = 1 LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
        
        /* Imágenes dinámicas */
        .logo-mini img,
        .logo-mini .img-responsive {
            content: url('{$config['icono_pequeno']}') !important;
        }
        
        .logo-lg img,
        .logo-lg .img-responsive {
            content: url('{$config['logo_menu']}') !important;
        }
        
        .login-logo img,
        .login-logo .img-responsive {
            content: url('{$config['logo_login']}') !important;
        }
        
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
