<?php
/*=============================================
CONFIGURACIÓN DE SEGURIDAD PARA INSTALACIÓN
danytrax/adminv5 - Sistema Seguro
=============================================*/

// Iniciar sesión inmediatamente
if (session_status() == PHP_SESSION_NONE) {
    session_name('INSTALACION_SESSION');
    session_start();
}

// 🔒 CONTRASEÑA MAESTRA PARA INSTALACIÓN
define('INSTALACION_PASSWORD', 'InstalarAdmin2024!');

// 🔒 TIEMPO DE SESIÓN (en minutos)
define('SESION_DURACION', 60);

// 🔒 INTENTOS MÁXIMOS DE LOGIN
define('MAX_INTENTOS_LOGIN', 3);

/*=============================================
FUNCIONES DE SEGURIDAD
=============================================*/

function iniciarSesionInstalacion() {
    if (session_status() == PHP_SESSION_NONE) {
        session_name('INSTALACION_SESSION');
        session_start();
    }
    return true;
}

function verificarAutenticacion() {
    iniciarSesionInstalacion();
    
    // Verificar si está autenticado y no ha expirado
    if (!isset($_SESSION['instalacion_autenticado']) || 
        !isset($_SESSION['instalacion_tiempo']) ||
        (time() - $_SESSION['instalacion_tiempo']) > (SESION_DURACION * 60)) {
        
        // Limpiar sesión si expiró
        if (isset($_SESSION['instalacion_autenticado'])) {
            session_destroy();
            iniciarSesionInstalacion();
        }
        return false;
    }
    
    // Actualizar tiempo de actividad
    $_SESSION['instalacion_tiempo'] = time();
    return true;
}

function autenticarUsuario($password) {
    iniciarSesionInstalacion();
    
    // Debug log
    error_log("INSTALACION AUTH: Intentando autenticar con password de longitud: " . strlen($password));
    
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
    
    // Comparar contraseñas
    $password_correcta = INSTALACION_PASSWORD;
    error_log("INSTALACION AUTH: Comparando '" . substr($password, 0, 5) . "...' con '" . substr($password_correcta, 0, 5) . "...'");
    
    if ($password === $password_correcta) {
        // Autenticación exitosa
        $_SESSION['instalacion_autenticado'] = true;
        $_SESSION['instalacion_tiempo'] = time();
        $_SESSION['instalacion_usuario'] = 'Administrador';
        $_SESSION['intentos_login'] = 0;
        
        error_log("INSTALACION AUTH: Autenticación exitosa");
        
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
        
        error_log("INSTALACION AUTH: Contraseña incorrecta, intentos: " . $_SESSION['intentos_login']);
        
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

function logInstalacion($mensaje, $tipo = 'INFO') {
    $log_dir = __DIR__ . '/logs/';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . 'instalacion-' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $log_entry = "[{$timestamp}] [{$tipo}] IP: {$ip} | {$mensaje}" . PHP_EOL;
    
    error_log($log_entry, 3, $log_file);
}

// Log de carga del archivo
error_log("INSTALACION CONFIG: Archivo de configuración cargado correctamente");
?>