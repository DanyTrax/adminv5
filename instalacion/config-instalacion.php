<?php
/*=============================================
CONFIGURACIÓN DE SEGURIDAD PARA INSTALACIÓN
danytrax/adminv5 - Sistema Seguro
=============================================*/

// 🔒 CONTRASEÑA MAESTRA PARA INSTALACIÓN
define('INSTALACION_PASSWORD', 'InstalarAdmin2024!');

// 🔒 TIEMPO DE SESIÓN (en minutos)
define('SESION_DURACION', 60);

// 🔒 INTENTOS MÁXIMOS DE LOGIN
define('MAX_INTENTOS_LOGIN', 3);

// 🔒 CONFIGURACIÓN DE BASE DE DATOS PERMITIDA
$configuraciones_bd_permitidas = [
    'epicosie_pruebas',
    'epicosie_pruebas2',
    'epicosie_sucursal',
    // Agregar otras BDs según sea necesario
];

// 🔒 USUARIOS DE BD PERMITIDOS
$usuarios_bd_permitidos = [
    'epicosie_ricaurte'
];

/*=============================================
FUNCIONES DE SEGURIDAD
=============================================*/

function iniciarSesionInstalacion() {
    if (session_status() == PHP_SESSION_NONE) {
        session_name('INSTALACION_SESSION');
        session_start();
    }
}

function verificarAutenticacion() {
    iniciarSesionInstalacion();
    
    // Verificar si está autenticado y no ha expirado
    if (!isset($_SESSION['instalacion_autenticado']) || 
        !isset($_SESSION['instalacion_tiempo']) ||
        (time() - $_SESSION['instalacion_tiempo']) > (SESION_DURACION * 60)) {
        
        // Limpiar sesión
        session_destroy();
        return false;
    }
    
    // Actualizar tiempo de actividad
    $_SESSION['instalacion_tiempo'] = time();
    return true;
}

function autenticarUsuario($password) {
    iniciarSesionInstalacion();
    
    // Verificar intentos previos
    $intentos = $_SESSION['intentos_login'] ?? 0;
    if ($intentos >= MAX_INTENTOS_LOGIN) {
        // Bloqueo temporal
        if (!isset($_SESSION['tiempo_bloqueo']) || 
            (time() - $_SESSION['tiempo_bloqueo']) < 300) { // 5 minutos
            return [
                'success' => false,
                'message' => 'Demasiados intentos. Espere 5 minutos antes de intentar nuevamente.',
                'bloqueado' => true
            ];
        } else {
            // Resetear intentos después del bloqueo
            $_SESSION['intentos_login'] = 0;
            unset($_SESSION['tiempo_bloqueo']);
        }
    }
    
    if ($password === INSTALACION_PASSWORD) {
        // Autenticación exitosa
        $_SESSION['instalacion_autenticado'] = true;
        $_SESSION['instalacion_tiempo'] = time();
        $_SESSION['instalacion_usuario'] = 'Administrador';
        $_SESSION['intentos_login'] = 0;
        
        return [
            'success' => true,
            'message' => 'Acceso concedido al sistema de instalación'
        ];
    } else {
        // Contraseña incorrecta
        $_SESSION['intentos_login'] = ($intentos + 1);
        
        if ($_SESSION['intentos_login'] >= MAX_INTENTOS_LOGIN) {
            $_SESSION['tiempo_bloqueo'] = time();
        }
        
        return [
            'success' => false,
            'message' => 'Contraseña incorrecta. Intento ' . $_SESSION['intentos_login'] . ' de ' . MAX_INTENTOS_LOGIN,
            'intentos_restantes' => MAX_INTENTOS_LOGIN - $_SESSION['intentos_login']
        ];
    }
}

function cerrarSesionInstalacion() {
    iniciarSesionInstalacion();
    session_destroy();
}

function validarConfiguracionBD($bd_nombre, $bd_usuario) {
    global $configuraciones_bd_permitidas, $usuarios_bd_permitidos;
    
    $errores = [];
    
    // Validar nombre de BD
    $bd_permitida = false;
    foreach ($configuraciones_bd_permitidas as $bd_permitida_pattern) {
        if (strpos($bd_nombre, $bd_permitida_pattern) === 0) {
            $bd_permitida = true;
            break;
        }
    }
    
    if (!$bd_permitida) {
        $errores[] = "Base de datos no permitida: {$bd_nombre}";
    }
    
    // Validar usuario de BD
    if (!in_array($bd_usuario, $usuarios_bd_permitidos)) {
        $errores[] = "Usuario de BD no permitido: {$bd_usuario}";
    }
    
    return $errores;
}

function logInstalacion($mensaje, $tipo = 'INFO') {
    $log_dir = __DIR__ . '/logs/';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . 'instalacion-' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $log_entry = "[{$timestamp}] [{$tipo}] IP: {$ip} | {$mensaje} | UA: {$user_agent}" . PHP_EOL;
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Log de acceso al archivo de configuración
logInstalacion("Archivo de configuración cargado");
?>