<?php

session_start();

require_once __DIR__ . "/../controladores/sucursales.controlador.php";
require_once __DIR__ . "/../controladores/productos.controlador.php";

// Limpiar cualquier salida previa si existe buffer
if (ob_get_level()) {
    ob_clean();
}

// Verificar sesión
if (!isset($_SESSION["perfil"])) {
    echo json_encode([
        'success' => false,
        'message' => 'Sesión no válida'
    ]);
    exit;
}

$accion = $_POST["accion"] ?? "";

switch ($accion) {
    
    case "consultar_stock_sucursales":
        try {
            $productos = json_decode($_POST["productos"] ?? "[]", true);
            
            if (empty($productos)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se proporcionaron productos para consultar'
                ]);
                exit;
            }
            
            // Obtener sucursales activas
            error_log("Consultando sucursales disponibles...");
            $sucursales = ControladorSucursales::ctrObtenerSucursalesDisponibles();
            
            error_log("Respuesta sucursales: " . json_encode($sucursales));
            
            if (!$sucursales || !$sucursales['success']) {
                error_log("Error obteniendo sucursales: " . ($sucursales['message'] ?? 'Error desconocido'));
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontraron sucursales activas: ' . ($sucursales['message'] ?? 'Error desconocido')
                ]);
                exit;
            }
            
            error_log("Sucursales encontradas: " . count($sucursales['data']));
            
            $stockData = [];
            
            // Para cada producto, consultar stock en todas las sucursales
            foreach ($productos as $producto) {
                $productoStock = [
                    'codigo' => $producto['codigo'] ?? '',
                    'descripcion' => $producto['descripcion'] ?? '',
                    'cantidad_solicitada' => $producto['cantidad'] ?? 0,
                    'sucursales' => []
                ];
                
                // Consultar stock en cada sucursal
                foreach ($sucursales['data'] as $sucursal) {
                    if ($sucursal['estado_conexion'] === 'conectado') {
                        $stockSucursal = consultarStockProductoEnSucursal($sucursal, $producto['codigo']);
                        
                        $productoStock['sucursales'][] = [
                            'id' => $sucursal['id'],
                            'nombre' => $sucursal['nombre'],
                            'stock_disponible' => $stockSucursal,
                            'puede_satisfacer' => $stockSucursal >= $producto['cantidad']
                        ];
                    }
                }
                
                $stockData[] = $productoStock;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $stockData
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al consultar stock: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
        break;
}

/*=============================================
CONSULTAR STOCK DE UN PRODUCTO EN UNA SUCURSAL
=============================================*/
function consultarStockProductoEnSucursal($sucursal, $codigoProducto) {
    try {
        // Conectar a la sucursal
        $dsn = "mysql:host={$sucursal['host_bd']};dbname={$sucursal['nombre_bd']};port={$sucursal['puerto_bd']}";
        $pdo = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Consultar stock del producto
        $stmt = $pdo->prepare("SELECT stock FROM productos WHERE codigo = :codigo");
        $stmt->bindParam(":codigo", $codigoProducto, PDO::PARAM_STR);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            return (int)$resultado['stock'];
        } else {
            return 0;
        }
        
    } catch (Exception $e) {
        error_log("Error consultando stock en sucursal {$sucursal['nombre']}: " . $e->getMessage());
        return 0;
    }
}

?>