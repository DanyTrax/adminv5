<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion-central.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    if (empty($input['codigo_sucursal']) || empty($input['nombre']) || empty($input['url_base']) || empty($input['api_url'])) {
        echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
        exit;
    }
    
    $dbCentral = ConexionCentral::conectar();
    
    // Verificar si ya existe el código de sucursal
    $stmtExiste = $dbCentral->prepare("SELECT id FROM sucursales WHERE codigo_sucursal = ?");
    $stmtExiste->execute([$input['codigo_sucursal']]);
    
    if ($stmtExiste->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El código de sucursal ya existe']);
        exit;
    }
    
    // Insertar nueva sucursal
    $stmt = $dbCentral->prepare("
        INSERT INTO sucursales 
        (codigo_sucursal, nombre, direccion, telefono, email, logo, url_base, api_url, activa, es_principal, observaciones) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $resultado = $stmt->execute([
        $input['codigo_sucursal'],
        $input['nombre'],
        $input['direccion'] ?? '',
        $input['telefono'] ?? '',
        $input['email'] ?? '',
        $input['logo'] ?? '',
        $input['url_base'],
        $input['api_url'],
        $input['activa'] ?? 1,
        $input['es_principal'] ?? 0,
        $input['observaciones'] ?? ''
    ]);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Sucursal creada correctamente',
            'id' => $dbCentral->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la sucursal']);
    }
    
} catch (Exception $e) {
    
    error_log("Error en crear_sucursal.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>