<?php

/**
 * @const NOMBRE_SUCURSAL El nombre único de esta sucursal.
 * Cambia este valor en cada una de tus 4 copias del software.
 */
define('NOMBRE_SUCURSAL', 'Sucursal Principal'); // O 'Sucursal Norte', etc.

/**
 * @const API_URL La dirección web completa de la carpeta donde subiste tu API.
 * Asegúrate de que esta URL sea correcta y termine con una barra inclinada (/).
 */
define('API_URL', 'https://pruebas.acplasticos.com/api-transferencias/');

/**
 * Token para ejecutar git pull remotamente (herramientas-admin-sync)
 * Cambiar en producción por un valor secreto.
 */
if (!defined('GIT_PULL_TOKEN')) {
    define('GIT_PULL_TOKEN', 'adminv5_git_pull_2025');
}

?>