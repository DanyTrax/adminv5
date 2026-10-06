<?php
require_once __DIR__ . "/../src/AjaxAuth.php";
AjaxAuth::requireProfiles(["Administrador"]);

header('Content-Type: application/json; charset=utf-8');

$configPath = dirname(__DIR__) . "/config.database.php";
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Falta config.database.php']);
    exit;
}
require_once $configPath;

$input = file_get_contents('php://input');
$datos = json_decode($input, true);

if (!$datos || !isset($datos['accion'])) {
    echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    exit;
}

try {
    $accion = $datos['accion'];
    $bd_origen = $datos['bd_origen'] ?? '';

    if (empty($bd_origen) || !preg_match('/^[a-zA-Z0-9_]+$/', $bd_origen)) {
        throw new Exception('Base de datos origen no válida');
    }

    $pdo_origen = new PDO(
        "mysql:host=" . DB_LOCAL_HOST . ";dbname={$bd_origen};charset=utf8mb4",
        DB_LOCAL_USER,
        DB_LOCAL_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    switch ($accion) {
        case 'obtener_clientes':
            $stmt = $pdo_origen->prepare("
                SELECT id, nombre, documento, email, telefono, direccion, 
                       compras, ultima_compra, fecha_nacimiento
                FROM clientes 
                ORDER BY nombre ASC 
                LIMIT 100
            ");
            $stmt->execute();
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'clientes' => $clientes,
                'total' => count($clientes)
            ]);
            break;

        case 'obtener_usuarios':
            $stmt = $pdo_origen->prepare("
                SELECT id, nombre, usuario, perfil, estado, ultimo_login, 
                       empresa, telefono, direccion, foto
                FROM usuarios 
                WHERE estado = 1
                ORDER BY nombre ASC
            ");
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'usuarios' => $usuarios,
                'total' => count($usuarios)
            ]);
            break;

        default:
            throw new Exception('Acción no reconocida');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
