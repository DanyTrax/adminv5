<?php
/*=============================================
AJAX PARA OBTENER SUCURSAL LOCAL
=============================================*/

header('Content-Type: application/json');

try {
    // Incluir conexión
    require_once __DIR__ . "/../modelos/conexion.php";
    
    // Obtener sucursal desde BD local
    $stmt = Conexion::conectar()->prepare("SELECT * FROM sucursal_local LIMIT 1");
    $stmt->execute();
    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($sucursal) {
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $sucursal['id'],
                'nombre' => $sucursal['nombre'],
                'direccion' => $sucursal['direccion'] ?? '',
                'telefono' => $sucursal['telefono'] ?? '',
                'email' => $sucursal['email'] ?? ''
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No se encontró sucursal en BD local'
        ]);
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener sucursal: ' . $e->getMessage()
    ]);
}
?>
