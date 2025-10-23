<?php
// Configuración del nuevo instalador simplificado
define('INSTALADOR_VERSION', '2.0');
define('INSTALADOR_NOMBRE', 'Instalador de Sucursal Simplificado');

// Configuración de la instalación
define('INSTALACION_PASSWORD', 'InstalarAdmin2024!');
define('MAX_INTENTOS', 3);
define('TIEMPO_BLOQUEO', 300); // 5 minutos

// Configuración de la base de datos por defecto
define('DB_HOST_DEFAULT', 'localhost');
define('DB_PORT_DEFAULT', '3306');

// Configuración del sistema central
define('CENTRAL_URL_DEFAULT', 'https://central.empresa.com/');

// Mensajes del instalador
$MENSAJES = [
    'exito' => 'Instalación completada exitosamente.',
    'error_bd' => 'Error conectando a la base de datos.',
    'error_crear_bd' => 'Error creando la base de datos.',
    'error_crear_tablas' => 'Error creando las tablas.',
    'error_insertar_datos' => 'Error insertando datos iniciales.',
    'error_config' => 'Error creando archivo de configuración.',
    'error_central' => 'Error registrando sucursal en sistema central.'
];

// Validaciones
function validarCodigoSucursal($codigo) {
    return preg_match('/^[A-Z0-9]{3,10}$/', $codigo);
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validarURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

function validarUsuario($usuario) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $usuario);
}

function validarPassword($password) {
    return strlen($password) >= 6;
}
?>
