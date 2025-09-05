<?php
require_once 'config-instalacion.php';

// 🔒 VERIFICAR AUTENTICACIÓN AJAX
if (!verificarAutenticacion()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado', 'redirect' => 'index.php']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// Obtener datos JSON del request
$input = file_get_contents('php://input');
$datos = json_decode($input, true);

if (!$datos || !isset($datos['accion'])) {
    echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    exit;
}

try {
    
    $accion = $datos['accion'];
    $bd_origen = $datos['bd_origen'] ?? '';
    
    if (empty($bd_origen)) {
        throw new Exception('Base de datos origen no especificada');
    }
    
    // Conectar a la BD origen
    $pdo_origen = new PDO(
        "mysql:host=localhost;dbname={$bd_origen};charset=utf8mb4",
        "epicosie_ricaurte",
        "m5Wwg)~M{i~*kFr{",
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
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
?>