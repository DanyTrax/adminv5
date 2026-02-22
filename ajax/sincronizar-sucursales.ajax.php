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

$accion = $_POST['accion'] ?? '';

if ($accion === 'sync_sql') {
    $sucursalesNombres = json_decode($_POST['sucursales'] ?? '[]', true);
    $scripts = json_decode($_POST['scripts'] ?? '[]', true);

    if (!is_array($sucursalesNombres) || !is_array($scripts) || empty($sucursalesNombres) || empty($scripts)) {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $SQL_LOCAL = [
        'crear-abonos-historial' => 'instalacion/sql/crear-abonos-historial.sql',
        'agregar-columnas-bd' => 'instalacion/sql/agregar-columnas-bd.sql',
        'crear-tablas-trazabilidad' => 'instalacion/sql/crear-tablas-trazabilidad.sql'
    ];

    $baseDir = dirname(__DIR__);
    $resultados = [];

    foreach ($sucursalesNombres as $nombreSucursal) {
        $config = ModeloSucursales::mdlObtenerSucursalPorNombre($nombreSucursal);
        if (!$config || empty($config['host_bd']) || empty($config['nombre_bd'])) {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'error', 'mensaje' => 'No se pudo conectar a la sucursal'];
            continue;
        }

        try {
            $puerto = $config['puerto_bd'] ?? 3306;
            $dsn = "mysql:host={$config['host_bd']};dbname={$config['nombre_bd']};port=$puerto;charset=utf8mb4";
            $pdo = new PDO($dsn, $config['usuario_bd'], $config['password_bd']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'error', 'mensaje' => 'Conexión: ' . $e->getMessage()];
            continue;
        }

        $erroresSucursal = [];
        $ejecutadas = 0;

        foreach ($scripts as $nombreScript) {
            if (!isset($SQL_LOCAL[$nombreScript])) continue;
            $ruta = $baseDir . '/' . $SQL_LOCAL[$nombreScript];
            if (!file_exists($ruta)) {
                $erroresSucursal[] = "$nombreScript: archivo no encontrado";
                continue;
            }

            $sql = file_get_contents($ruta);
            $sentencias = array_filter(
                array_map(function($s) {
                    $s = trim($s);
                    $s = preg_replace('/^(\s*--[^\n]*\n?)+/', '', $s);
                    return trim($s);
                }, explode(';', $sql)),
                function($s) { 
                    return strlen($s) > 10 && !preg_match('/^--/', $s) && !preg_match('/^(DESCRIBE|SELECT \*)/i', $s);
                }
            );

            foreach ($sentencias as $sentencia) {
                $sentencia = trim($sentencia);
                if (empty($sentencia) || substr($sentencia, 0, 2) === '--') continue;
                if (preg_match('/^(DESCRIBE|SELECT \*)/i', $sentencia)) continue;
                try {
                    $pdo->exec($sentencia);
                    $ejecutadas++;
                } catch (PDOException $e) {
                    $msg = $e->getMessage();
                    if (strpos($msg, 'Duplicate column') !== false || strpos($msg, 'already exists') !== false || strpos($msg, 'Duplicate key') !== false) {
                        $ejecutadas++;
                    } else {
                        $erroresSucursal[] = substr($msg, 0, 100);
                    }
                }
            }
        }

        if (empty($erroresSucursal)) {
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'ok', 'mensaje' => "$ejecutadas sentencias ejecutadas"];
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
        $url = rtrim($urlBase, '/') . '/ajax/git-pull-remoto.ajax.php';
        $nombreSucursal = parse_url($urlBase, PHP_URL_HOST) ?: $urlBase;

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
            $resultados[] = ['sucursal' => $nombreSucursal, 'estado' => 'error', 'mensaje' => $data['error'] ?? ($resp ?: "HTTP $code")];
        }
    }

    echo json_encode(['success' => true, 'resultados' => $resultados]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);
