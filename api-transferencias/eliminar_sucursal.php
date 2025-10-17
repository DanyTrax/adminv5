<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion-central.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar ID requerido
    if (empty($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de sucursal requerido']);
        exit;
    }
    
    $dbCentral = ConexionCentral::conectar();
    
    // Verificar si existe la sucursal y obtener información
    $stmtExiste = $dbCentral->prepare("SELECT id, nombre, logo, es_principal FROM sucursales WHERE id = ?");
    $stmtExiste->execute([$input['id']]);
    $sucursal = $stmtExiste->fetch(PDO::FETCH_ASSOC);
    
    if (!$sucursal) {
        echo json_encode(['success' => false, 'message' => 'Sucursal no encontrada']);
        exit;
    }
    
    // No permitir eliminar sucursal principal
    if ($sucursal['es_principal'] == 1) {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar la sucursal principal']);
        exit;
    }
    
    // Eliminar sucursal
    $stmt = $dbCentral->prepare("DELETE FROM sucursales WHERE id = ?");
    $resultado = $stmt->execute([$input['id']]);
    
    if ($resultado) {
        
        // Intentar eliminar logo si existe
        if (!empty($sucursal['logo'])) {
            $rutaLogo = __DIR__ . '/../vistas/img/sucursales/' . $sucursal['logo'];
            if (file_exists($rutaLogo)) {
                @unlink($rutaLogo);
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Sucursal eliminada correctamente'
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la sucursal']);
    }
    
} catch (Exception $e) {
    
    error_log("Error en eliminar_sucursal.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>