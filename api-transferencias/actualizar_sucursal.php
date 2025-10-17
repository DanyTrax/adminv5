<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion-central.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    if (empty($input['id']) || empty($input['codigo_sucursal']) || empty($input['nombre']) || empty($input['url_base']) || empty($input['api_url'])) {
        echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
        exit;
    }
    
    $dbCentral = ConexionCentral::conectar();
    
    // Verificar si existe la sucursal
    $stmtExiste = $dbCentral->prepare("SELECT id FROM sucursales WHERE id = ?");
    $stmtExiste->execute([$input['id']]);
    
    if (!$stmtExiste->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Sucursal no encontrada']);
        exit;
    }
    
    // Verificar si el código de sucursal ya existe en otra sucursal
    $stmtCodigo = $dbCentral->prepare("SELECT id FROM sucursales WHERE codigo_sucursal = ? AND id != ?");
    $stmtCodigo->execute([$input['codigo_sucursal'], $input['id']]);
    
    if ($stmtCodigo->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El código de sucursal ya existe en otra sucursal']);
        exit;
    }
    
    // Actualizar sucursal
    $stmt = $dbCentral->prepare("
        UPDATE sucursales SET 
        codigo_sucursal = ?, nombre = ?, direccion = ?, telefono = ?, email = ?, 
        logo = ?, url_base = ?, api_url = ?, activa = ?, es_principal = ?, observaciones = ?
        WHERE id = ?
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
        $input['observaciones'] ?? '',
        $input['id']
    ]);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Sucursal actualizada correctamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la sucursal']);
    }
    
} catch (Exception $e) {
    
    error_log("Error en actualizar_sucursal.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>