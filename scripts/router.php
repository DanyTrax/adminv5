<?php
/**
 * Router para php -S (equivalente a .htaccess RewriteRule).
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . '/..' . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // servir archivo estático
}

// Rutas amigables → index.php?ruta=
$path = trim($uri, '/');
if ($path === '' || $path === 'index.php') {
    require __DIR__ . '/../index.php';
    return true;
}

// No reescribir ajax/, api-transferencias/, vistas/, etc. si existen como dirs
$first = explode('/', $path)[0];
if (in_array($first, ['ajax', 'api-transferencias', 'vistas', 'extensiones', 'instalacion', 'pdf', 'xml', 'scripts'], true)) {
    return false;
}

$_GET['ruta'] = $path;
require __DIR__ . '/../index.php';
return true;
