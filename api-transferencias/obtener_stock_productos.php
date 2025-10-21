<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once "../config.php";

class ApiStockProductos {
    
    public function obtenerStockProductos() {
        
        try {
            // Verificar que se recibieron códigos
            if(!isset($_POST['codigos']) || !is_array($_POST['codigos'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se proporcionaron códigos de productos'
                ]);
                return;
            }
            
            $codigos = $_POST['codigos'];
            
            // Conectar a la base de datos local
            $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Consultar stock de productos
            $placeholders = str_repeat('?,', count($codigos) - 1) . '?';
            $sql = "SELECT codigo, stock FROM productos WHERE codigo IN ($placeholders)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($codigos);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stock = [];
            foreach($resultados as $producto) {
                $stock[$producto['codigo']] = (int)$producto['stock'];
            }
            
            echo json_encode([
                'success' => true,
                'stock' => $stock,
                'sucursal' => 'Local',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch(Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}

// Ejecutar API
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $api = new ApiStockProductos();
    $api->obtenerStockProductos();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
