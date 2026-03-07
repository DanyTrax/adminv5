<?php

session_start();

require_once __DIR__ . "/../controladores/sucursales.controlador.php";
require_once __DIR__ . "/../modelos/sucursales.modelo.php";
require_once __DIR__ . "/../modelos/productos.modelo.php";
require_once __DIR__ . "/../modelos/categorias.modelo.php";

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

            // 1) Obtener sucursales activas: mismo origen que la pantalla Sucursales (BD central).
            //    Cada sucursal tiene host_bd, nombre_bd, usuario_bd, password_bd para conectar a su propia BD y leer tabla productos (codigo, stock).
            $sucursales = ControladorSucursales::ctrObtenerSucursalesDisponibles();
            $dataSuc = (isset($sucursales['success']) && $sucursales['success'] && isset($sucursales['data']) && is_array($sucursales['data']))
                ? $sucursales['data']
                : [];

            $sucursalesConectadas = [];
            if (!empty($dataSuc)) {
                $sucursalesConectadas = array_values(array_filter($dataSuc, function ($s) {
                    return isset($s['estado_conexion']) && $s['estado_conexion'] === 'conectado';
                }));
            }

            // 2) Si el central no devolvió sucursales o ninguna está conectada: usar sucursal local (esta sucursal).
            //    Los datos de conexión están en sucursal_local (misma idea que en Sucursales: una BD por sucursal).
            if (empty($sucursalesConectadas)) {
                $local = ModeloSucursales::mdlObtenerConfiguracionLocal();
                if ($local && !empty($local['host_bd']) && !empty($local['nombre_bd']) && !empty($local['usuario_bd'])) {
                    $sucursalesConectadas = [[
                        'id' => 0,
                        'nombre' => isset($local['nombre']) ? $local['nombre'] : (isset($local['codigo_sucursal']) ? $local['codigo_sucursal'] : 'Esta sucursal'),
                        'codigo_sucursal' => isset($local['codigo_sucursal']) ? $local['codigo_sucursal'] : '',
                        'host_bd' => $local['host_bd'],
                        'nombre_bd' => $local['nombre_bd'],
                        'usuario_bd' => $local['usuario_bd'],
                        'password_bd' => isset($local['password_bd']) ? $local['password_bd'] : '',
                        'puerto_bd' => isset($local['puerto_bd']) ? (int)$local['puerto_bd'] : 3306,
                        'estado_conexion' => 'conectado'
                    ]];
                }
            }

            if (empty($sucursalesConectadas)) {
                $msg = 'No hay sucursales con conexión disponible. ';
                if (empty($dataSuc)) {
                    $msg .= 'No se pudo obtener la lista del central; verifique conexión a la BD central. ';
                }
                $msg .= 'En Sucursales debe haber activas con host_bd, nombre_bd, usuario_bd y password_bd. Para ver al menos esta sucursal, configure sucursal_local.';
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }

            $listaSucursales = array_map(function ($s) {
                $id = isset($s['id']) ? (int)$s['id'] : 0;
                $nombre = isset($s['nombre']) ? $s['nombre'] : (isset($s['codigo_sucursal']) ? $s['codigo_sucursal'] : 'Sucursal');
                return ['id' => $id, 'nombre' => $nombre];
            }, $sucursalesConectadas);

            $productos = $idCategoria
                ? ModeloProductos::mdlMostrarProductos("productos", "id_categoria", $idCategoria, "codigo")
                : ModeloProductos::mdlMostrarProductos("productos", null, null, "id");
            if (!is_array($productos)) {
                $productos = [];
            }

            // Mapa id_categoria -> nombre para orden y visualización (igual que tabla productos)
            $mapaCategorias = [];
            $todasCategorias = ModeloCategorias::mdlMostrarCategorias("categorias", null, null);
            if (!is_array($todasCategorias)) {
                $todasCategorias = [];
            }
            foreach ($todasCategorias as $cat) {
                $mapaCategorias[(int)$cat['id']] = isset($cat['categoria']) ? $cat['categoria'] : '';
            }

            $codigos = array_values(array_unique(array_filter(array_map(function ($p) {
                return isset($p['codigo']) ? trim($p['codigo']) : '';
            }, $productos))));

            $stockPorSucursal = [];
            foreach ($sucursalesConectadas as $suc) {
                $sid = isset($suc['id']) ? (string)$suc['id'] : '';
                $stockPorSucursal[$sid] = consultarStockTodosProductosEnSucursal($suc, $codigos);
            }

            $resultadoProductos = [];
            foreach ($productos as $prod) {
                $codigo = isset($prod['codigo']) ? $prod['codigo'] : '';
                $descripcion = isset($prod['descripcion']) ? $prod['descripcion'] : '';
                $idCategoriaProd = isset($prod['id_categoria']) ? (int)$prod['id_categoria'] : 0;
                $nombreCategoria = isset($mapaCategorias[$idCategoriaProd]) ? $mapaCategorias[$idCategoriaProd] : '';
                $stocks = [];
                $total = 0;
                foreach ($sucursalesConectadas as $suc) {
                    $sid = isset($suc['id']) ? (string)$suc['id'] : '';
                    $cant = isset($stockPorSucursal[$sid][$codigo]) ? (int)$stockPorSucursal[$sid][$codigo] : 0;
                    $stocks[$sid] = $cant;
                    $total += $cant;
                }
                $resultadoProductos[] = [
                    'codigo' => $codigo,
                    'descripcion' => $descripcion,
                    'id_categoria' => $idCategoriaProd,
                    'categoria' => $nombreCategoria,
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
CONSULTAR STOCK DE TODOS LOS PRODUCTOS EN UNA SUCURSAL (una sola query)
=============================================*/
function consultarStockTodosProductosEnSucursal($sucursal, $codigos) {
    $out = [];
    if (empty($codigos)) {
        return $out;
    }
    $codigos = array_values(array_unique($codigos));
    try {
        $host = isset($sucursal['host_bd']) ? $sucursal['host_bd'] : '';
        $nombreBd = isset($sucursal['nombre_bd']) ? $sucursal['nombre_bd'] : '';
        $puerto = isset($sucursal['puerto_bd']) ? $sucursal['puerto_bd'] : 3306;
        $usuario = isset($sucursal['usuario_bd']) ? $sucursal['usuario_bd'] : '';
        $password = isset($sucursal['password_bd']) ? $sucursal['password_bd'] : '';
        if (empty($host) || empty($nombreBd) || empty($usuario)) {
            return $out;
        }
        $dsn = "mysql:host=" . $host . ";dbname=" . $nombreBd . ";port=" . $puerto . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $chunkSize = 400;
        $chunks = array_chunk($codigos, $chunkSize);
        foreach ($chunks as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $stmt = $pdo->prepare("SELECT codigo, stock FROM productos WHERE codigo IN ($placeholders)");
            $stmt->execute(array_values($chunk));
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $out[$row['codigo']] = (int)$row['stock'];
            }
        }
        return $out;
    } catch (Exception $e) {
        $nombreSuc = isset($sucursal['nombre']) ? $sucursal['nombre'] : 'sucursal';
        error_log("Error consultando stock en sucursal {$nombreSuc}: " . $e->getMessage());
        return $out;
    }
}

/*=============================================
CONSULTAR STOCK DE UN PRODUCTO EN UNA SUCURSAL
=============================================*/
function consultarStockProductoEnSucursal($sucursal, $codigoProducto) {
    $codigos = array($codigoProducto);
    $res = consultarStockTodosProductosEnSucursal($sucursal, $codigos);
    return isset($res[$codigoProducto]) ? $res[$codigoProducto] : 0;
}

?>