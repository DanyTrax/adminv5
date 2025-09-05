<?php
// Verificación de autenticación
session_start();

if (!isset($_SESSION['instalacion_logueado']) || $_SESSION['instalacion_logueado'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Configuración de errores
ini_set('display_errors', 0);
error_reporting(0);

// Headers JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    // Obtener datos JSON del request
    $input = file_get_contents('php://input');
    if (empty($input)) {
        throw new Exception('No se recibieron datos');
    }
    
    $datos = json_decode($input, true);
    if ($datos === null) {
        throw new Exception('JSON inválido: ' . json_last_error_msg());
    }
    
    if (!isset($datos['accion'])) {
        throw new Exception('Acción no especificada');
    }
    
    $accion = $datos['accion'];
    $bd_origen = $datos['bd_origen'] ?? '';
    
    if (empty($bd_origen)) {
        throw new Exception('Base de datos origen no especificada');
    }
    
    // Configuración de conexión
    $host = 'localhost';
    $usuario = 'epicosie_ricaurte';
    $password = 'm5Wwg)~M{i~*kFr{';
    
    // Conectar a BD origen
    try {
        $dsn = "mysql:host={$host};dbname={$bd_origen};charset=utf8mb4";
        $pdo = new PDO($dsn, $usuario, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);
    } catch (PDOException $e) {
        throw new Exception('Error de conexión: ' . $e->getMessage());
    }
    
    switch ($accion) {
        
case 'obtener_clientes':
    
    // Verificar que existe la tabla
    $stmt_check = $pdo->prepare("SHOW TABLES LIKE 'clientes'");
    $stmt_check->execute();
    if ($stmt_check->rowCount() === 0) {
        throw new Exception('Tabla clientes no existe');
    }
    
    // ✅ OBTENER SOLO EL CONTEO TOTAL DE CLIENTES
    $stmt_total = $pdo->prepare("
        SELECT COUNT(*) as total_clientes 
        FROM clientes 
        WHERE LENGTH(TRIM(nombre)) > 0
    ");
    $stmt_total->execute();
    $resultado_total = $stmt_total->fetch();
    $total_clientes = (int)$resultado_total['total_clientes'];
    
    // ✅ OBTENER ALGUNOS DATOS ESTADÍSTICOS ADICIONALES
    $stmt_stats = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN LENGTH(TRIM(email)) > 0 AND email LIKE '%@%' THEN 1 END) as con_email,
            COUNT(CASE WHEN LENGTH(TRIM(telefono)) > 0 THEN 1 END) as con_telefono,
            COUNT(CASE WHEN LENGTH(TRIM(direccion)) > 0 THEN 1 END) as con_direccion,
            COUNT(CASE WHEN compras > 0 THEN 1 END) as con_compras,
            MIN(fecha) as primer_cliente,
            MAX(fecha) as ultimo_cliente
        FROM clientes 
        WHERE LENGTH(TRIM(nombre)) > 0
    ");
    $stmt_stats->execute();
    $stats = $stmt_stats->fetch();
    
    // ✅ OBTENER LISTA DE IDs PARA IMPORTACIÓN MASIVA
    $stmt_ids = $pdo->prepare("
        SELECT id, nombre 
        FROM clientes 
        WHERE LENGTH(TRIM(nombre)) > 0
        ORDER BY nombre ASC
    ");
    $stmt_ids->execute();
    $clientes_ids = $stmt_ids->fetchAll();
    
    // Procesar IDs para importación
    $ids_importacion = [];
    foreach ($clientes_ids as $cliente) {
        $ids_importacion[] = [
            'id' => (int)$cliente['id'],
            'nombre' => trim($cliente['nombre'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'tipo_respuesta' => 'resumen_clientes',
        'total_clientes' => $total_clientes,
        'estadisticas' => [
            'con_email' => (int)$stats['con_email'],
            'con_telefono' => (int)$stats['con_telefono'], 
            'con_direccion' => (int)$stats['con_direccion'],
            'con_compras' => (int)$stats['con_compras'],
            'primer_cliente' => $stats['primer_cliente'] ?: 'No disponible',
            'ultimo_cliente' => $stats['ultimo_cliente'] ?: 'No disponible'
        ],
        'clientes_para_importar' => $ids_importacion,
        'bd_origen' => $bd_origen,
        'mensaje' => "Se encontraron {$total_clientes} clientes en la base de datos"
    ], JSON_UNESCAPED_UNICODE);
    
    break;
            
        case 'obtener_usuarios':
            
            // Verificar que existe la tabla
            $stmt_check = $pdo->prepare("SHOW TABLES LIKE 'usuarios'");
            $stmt_check->execute();
            if ($stmt_check->rowCount() === 0) {
                throw new Exception('Tabla usuarios no existe');
            }
            
            // Obtener usuarios (datos completos)
            $stmt = $pdo->prepare("
                SELECT id, nombre, usuario, perfil, estado, ultimo_login, 
                       empresa, telefono, direccion, foto, fecha
                FROM usuarios 
                WHERE estado = 1 
                AND LENGTH(TRIM(nombre)) > 0
                ORDER BY perfil DESC, nombre ASC
                LIMIT 20
            ");
            $stmt->execute();
            $usuarios = $stmt->fetchAll();
            
            // Procesar usuarios (formato completo)
            $usuarios_procesados = [];
            foreach ($usuarios as $usuario) {
                $usuarios_procesados[] = [
                    'id' => (int)$usuario['id'],
                    'nombre' => trim($usuario['nombre']) ?: 'Sin nombre',
                    'usuario' => trim($usuario['usuario']) ?: 'Sin usuario',
                    'perfil' => trim($usuario['perfil']) ?: 'Usuario',
                    'estado' => (int)$usuario['estado'],
                    'ultimo_login' => $usuario['ultimo_login'] ?: 'Nunca',
                    'empresa' => trim($usuario['empresa']) ?: 'Sin empresa',
                    'telefono' => trim($usuario['telefono']) ?: 'Sin teléfono',
                    'direccion' => trim($usuario['direccion']) ?: 'Sin dirección',
                    'foto' => $usuario['foto'] ?: 'vistas/img/usuarios/default/anonymous.png',
                    'fecha_registro' => $usuario['fecha'] ?: 'No disponible'
                ];
            }
            
            echo json_encode([
                'success' => true,
                'usuarios' => $usuarios_procesados,
                'total' => count($usuarios_procesados),
                'bd_origen' => $bd_origen
            ], JSON_UNESCAPED_UNICODE);
            
            break;
            
        default:
            throw new Exception('Acción no reconocida: ' . $accion);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?>