<?php
/**
 * Sincronizar SQL y Git Pull en sucursales
 * Solo usuario admin con nombre admin
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

$esAdminAdmin = (isset($_SESSION["usuario"]) && strtolower($_SESSION["usuario"]) === "admin" && 
                 isset($_SESSION["nombre"]) && strtolower($_SESSION["nombre"]) === "admin");

if (!$esAdminAdmin) {
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

require_once __DIR__ . "/../modelos/sucursales.modelo.php";
require_once __DIR__ . "/../api-transferencias/conexion-central.php";
require_once __DIR__ . "/../instalacion/funciones-sql-migraciones.php";

$accion = $_POST['accion'] ?? '';

if ($accion === 'sync_sql_central') {
    // Sincronizar SQL en la BD CENTRAL (despachos, stock_transito, etc.)
    try {
        $conexion = ConexionCentral::conectar();
        $baseDir = dirname(__DIR__);
        $resultados = [];
        foreach ($GLOBALS['SQL_CENTRAL'] as $nombre => $ruta) {
            $rutaCompleta = $baseDir . '/' . $ruta;
            $res = ejecutarSQLConComparacion($conexion, $rutaCompleta);
            $msg = $res['ejecutadas'] > 0 ? "{$res['ejecutadas']} aplicadas" : "";
            if ($res['omitidas'] > 0) $msg .= ($msg ? ", " : "") . "{$res['omitidas']} omitidas (ya existían)";
            if (!empty($res['errores'])) {
                $resultados[] = ['script' => $nombre, 'estado' => 'error', 'mensaje' => implode('; ', array_slice($res['errores'], 0, 2))];
            } else {
                $resultados[] = ['script' => $nombre, 'estado' => 'ok', 'mensaje' => $msg ?: 'Sin cambios necesarios'];
            }
        }
        echo json_encode(['success' => true, 'resultados' => $resultados]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($accion === 'sync_sql') {
    $sucursalesNombres = json_decode($_POST['sucursales'] ?? '[]', true);
    $modo = $_POST['modo'] ?? 'seleccionados'; // 'seleccionados' o 'todos'

    if (!is_array($sucursalesNombres) || empty($sucursalesNombres)) {
        echo json_encode(['success' => false, 'error' => 'Selecciona al menos una sucursal']);
        exit;
    }

    $baseDir = dirname(__DIR__);
    $SQL_TODOS = $GLOBALS['SQL_LOCAL'];

    $scripts = ($modo === 'todos') ? array_keys($SQL_TODOS) : (json_decode($_POST['scripts'] ?? '[]', true) ?: []);
    if ($modo !== 'todos' && empty($scripts)) {
        echo json_encode(['success' => false, 'error' => 'Selecciona al menos un script o usa "Sincronizar todo"']);
        exit;
    }

    $resultados = [];

    foreach ($sucursalesNombres as $nombreSucursal) {
        $config = ModeloSucursales::mdlObtenerSucursalPorNombre($nombreSucursal);
        if (!$config) {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'error', 'mensaje' => 'Sucursal no encontrada en el central'];
            continue;
        }
        if (empty($config['host_bd']) || empty($config['nombre_bd'])) {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'error', 'mensaje' => 'Faltan credenciales BD (host_bd, nombre_bd). Edite la sucursal en Gestión de Sucursales.'];
            continue;
        }
        if (empty($config['usuario_bd'])) {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'error', 'mensaje' => 'Falta usuario_bd. Edite la sucursal en Gestión de Sucursales.'];
            continue;
        }

        try {
            $puerto = $config['puerto_bd'] ?? 3306;
            $dsn = "mysql:host={$config['host_bd']};dbname={$config['nombre_bd']};port=$puerto;charset=utf8mb4";
            $pdo = new PDO($dsn, $config['usuario_bd'], $config['password_bd'] ?? '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'error', 'mensaje' => 'Conexión BD: ' . $e->getMessage()];
            continue;
        }

        $ejecutadas = 0;
        $omitidas = 0;
        $erroresSucursal = [];

        foreach ($scripts as $nombreScript) {
            if (!isset($SQL_TODOS[$nombreScript])) continue;
            $ruta = $baseDir . '/' . $SQL_TODOS[$nombreScript];
            if (!file_exists($ruta)) {
                $erroresSucursal[] = "$nombreScript: no encontrado";
                continue;
            }

            $res = ejecutarSQLConComparacion($pdo, $ruta);
            $ejecutadas += $res['ejecutadas'];
            $omitidas += $res['omitidas'];
            if (!empty($res['errores'])) {
                $erroresSucursal = array_merge($erroresSucursal, $res['errores']);
            }
        }

        $msg = $ejecutadas > 0 ? "$ejecutadas aplicadas" : "";
        if ($omitidas > 0) $msg .= ($msg ? ", " : "") . "$omitidas omitidas (ya existían)";
        if (empty($erroresSucursal)) {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'ok', 'mensaje' => $msg ?: 'Sin cambios necesarios'];
        } else {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'error', 'mensaje' => implode('; ', array_slice($erroresSucursal, 0, 3))];
        }
    }

    echo json_encode(['success' => true, 'resultados' => $resultados]);
    exit;
}

if ($accion === 'git_pull') {
    $urls = json_decode($_POST['urls'] ?? '[]', true);
    if (!is_array($urls) || empty($urls)) {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $token = defined('GIT_PULL_TOKEN') ? GIT_PULL_TOKEN : (getenv('GIT_PULL_TOKEN') ?: 'adminv5_git_pull_2025');
    $resultados = [];

    foreach ($urls as $urlBase) {
        // urlBase puede ser url_base (raíz del sitio) o url_api (.../api-transferencias)
        $base = preg_replace('#/api-transferencias/?$#', '', rtrim($urlBase, '/'));
        $url = rtrim($base, '/') . '/ajax/git-pull-remoto.ajax.php';
        $nombreSucursal = parse_url($url, PHP_URL_HOST) ?: $base;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['token' => $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'error', 'mensaje' => $err];
            continue;
        }
        $data = json_decode($resp, true);
        if ($data && isset($data['success']) && $data['success']) {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'ok', 'mensaje' => $data['message'] ?? 'Git pull ejecutado'];
        } else {
            $msg = $data['error'] ?? null;
            if (!$msg && (strpos($resp, 'Ingresar al sistema') !== false || strpos($resp, 'login') !== false || strpos($resp, '<form') !== false)) {
                $msg = 'La URL devolvió página de login. Verifica que ajax/git-pull-remoto.ajax.php exista en la sucursal y que la URL sea correcta.';
            } elseif (!$msg) {
                $msg = substr(strip_tags($resp), 0, 150) ?: "HTTP $code";
            }
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'error', 'mensaje' => $msg];
        }
    }

    echo json_encode(['success' => true, 'resultados' => $resultados]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);
