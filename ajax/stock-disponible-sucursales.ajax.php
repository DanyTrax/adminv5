<?php

session_start();

require_once __DIR__ . "/../controladores/sucursales.controlador.php";
require_once __DIR__ . "/../modelos/catalogo-maestro.modelo.php";

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

    case "obtener_stock_todas_sucursales":
        try {
            if (ob_get_level()) {
                ob_clean();
            }
            header('Content-Type: application/json; charset=utf-8');

            $idCategoria = isset($_POST["id_categoria"]) && $_POST["id_categoria"] !== '' ? (int)$_POST["id_categoria"] : null;

            $sucursales = ControladorSucursales::ctrObtenerSucursalesDisponibles();
            if (!$sucursales || !$sucursales['success']) {
                echo json_encode([
                    'success' => false,
                    'message' => $sucursales['message'] ?? 'No se encontraron sucursales activas'
                ]);
                exit;
            }
            $dataSuc = isset($sucursales['data']) && is_array($sucursales['data']) ? $sucursales['data'] : [];
            if (empty($dataSuc)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontraron sucursales activas'
                ]);
                exit;
            }

            $sucursalesConectadas = array_filter($dataSuc, function ($s) {
                return isset($s['estado_conexion']) && $s['estado_conexion'] === 'conectado';
            });
            $sucursalesConectadas = array_values($sucursalesConectadas);

            if (empty($sucursalesConectadas)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No hay sucursales con conexión disponible. Verifique que las sucursales tengan host_bd, nombre_bd, usuario_bd y password_bd configurados en la BD central.'
                ]);
                exit;
            }

            $listaSucursales = array_map(function ($s) {
                $id = isset($s['id']) ? (int)$s['id'] : 0;
                $nombre = isset($s['nombre']) ? $s['nombre'] : (isset($s['codigo_sucursal']) ? $s['codigo_sucursal'] : 'Sucursal');
                return ['id' => $id, 'nombre' => $nombre];
            }, $sucursalesConectadas);

            $productos = $idCategoria
                ? ModeloCatalogoMaestro::mdlMostrarCatalogoMaestro("id_categoria", $idCategoria)
                : ModeloCatalogoMaestro::mdlMostrarCatalogoMaestro(null, null);
            if (!is_array($productos)) {
                $productos = [];
            }

            $resultadoProductos = [];
            foreach ($productos as $prod) {
                $codigo = isset($prod['codigo']) ? $prod['codigo'] : '';
                $descripcion = isset($prod['descripcion']) ? $prod['descripcion'] : '';
                $stocks = [];
                $total = 0;
                foreach ($sucursalesConectadas as $suc) {
                    $cant = consultarStockProductoEnSucursal($suc, $codigo);
                    $sid = isset($suc['id']) ? (string)$suc['id'] : '';
                    $stocks[$sid] = $cant;
                    $total += $cant;
                }
                $resultadoProductos[] = [
                    'codigo' => $codigo,
                    'descripcion' => $descripcion,
                    'stocks' => $stocks,
                    'total' => $total
                ];
            }

            echo json_encode([
                'success' => true,
                'sucursales' => $listaSucursales,
                'productos' => $resultadoProductos
            ]);
        } catch (Throwable $e) {
            if (ob_get_level()) {
                ob_clean();
            }
            header('Content-Type: application/json; charset=utf-8');
            error_log("obtener_stock_todas_sucursales: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    
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
        $host = isset($sucursal['host_bd']) ? $sucursal['host_bd'] : '';
        $nombreBd = isset($sucursal['nombre_bd']) ? $sucursal['nombre_bd'] : '';
        $puerto = isset($sucursal['puerto_bd']) ? $sucursal['puerto_bd'] : 3306;
        $usuario = isset($sucursal['usuario_bd']) ? $sucursal['usuario_bd'] : '';
        $password = isset($sucursal['password_bd']) ? $sucursal['password_bd'] : '';
        if (empty($host) || empty($nombreBd) || empty($usuario)) {
            return 0;
        }
        $dsn = "mysql:host=" . $host . ";dbname=" . $nombreBd . ";port=" . $puerto;
        $pdo = new PDO($dsn, $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT stock FROM productos WHERE codigo = :codigo");
        $stmt->bindParam(":codigo", $codigoProducto, PDO::PARAM_STR);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($resultado && isset($resultado['stock'])) ? (int)$resultado['stock'] : 0;
    } catch (Exception $e) {
        $nombreSuc = isset($sucursal['nombre']) ? $sucursal['nombre'] : 'sucursal';
        error_log("Error consultando stock en sucursal {$nombreSuc}: " . $e->getMessage());
        return 0;
    }
}

?>