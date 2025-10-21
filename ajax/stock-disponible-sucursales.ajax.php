<?php

require_once "../controladores/sucursales.controlador.php";
require_once "../modelos/sucursales.modelo.php";

class AjaxStockDisponibleSucursales {

    /*=============================================
    CONSULTAR STOCK EN TODAS LAS SUCURSALES
    =============================================*/
    public $accion;
    public $productos;

    public function ajaxConsultarStockSucursales() {
        
        try {
            $productos = json_decode($this->productos, true);
            
            if(!$productos || !is_array($productos)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudieron procesar los productos'
                ]);
                return;
            }
            
            // Obtener todas las sucursales activas
            $sucursales = ControladorSucursales::ctrMostrarSucursales(null, null);
            
            if(!$sucursales) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontraron sucursales activas'
                ]);
                return;
            }
            
            $stockPorSucursal = [];
            $stockLocal = [];
            
            // Obtener códigos de productos
            $codigos = array_column($productos, 'codigo');
            
            // Consultar stock local
            $stockLocal = $this->consultarStockLocal($codigos);
            
            // Consultar stock en otras sucursales
            foreach($sucursales as $sucursal) {
                if($sucursal['activo'] == 1) {
                    $stockSucursal = $this->consultarStockSucursal($sucursal, $codigos);
                    if($stockSucursal) {
                        $stockPorSucursal[$sucursal['codigo_sucursal']] = [
                            'nombre' => $sucursal['nombre'],
                            'stock' => $stockSucursal
                        ];
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'local' => $stockLocal,
                    'sucursales' => $stockPorSucursal,
                    'productos_solicitud' => $productos
                ]
            ]);
            
        } catch(Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /*=============================================
    CONSULTAR STOCK LOCAL
    =============================================*/
    private function consultarStockLocal($codigos) {
        
        try {
            $tabla = "productos";
            $where = "codigo IN ('" . implode("','", $codigos) . "')";
            
            $respuesta = ModeloProductos::mdlMostrarProductos($tabla, $where);
            
            $stock = [];
            if($respuesta) {
                foreach($respuesta as $producto) {
                    $stock[$producto['codigo']] = (int)$producto['stock'];
                }
            }
            
            return $stock;
            
        } catch(Exception $e) {
            error_log("Error consultando stock local: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    CONSULTAR STOCK EN SUCURSAL REMOTA
    =============================================*/
    private function consultarStockSucursal($sucursal, $codigos) {
        
        try {
            // Verificar si la sucursal tiene conexión directa a BD
            if(!empty($sucursal['usuario_bd']) && !empty($sucursal['password_bd']) && 
               !empty($sucursal['nombre_bd']) && !empty($sucursal['host_bd'])) {
                
                return $this->consultarStockDirecto($sucursal, $codigos);
            } else {
                // Usar API HTTP
                return $this->consultarStockAPI($sucursal, $codigos);
            }
            
        } catch(Exception $e) {
            error_log("Error consultando stock sucursal {$sucursal['codigo_sucursal']}: " . $e->getMessage());
            return null;
        }
    }
    
    /*=============================================
    CONSULTAR STOCK DIRECTO A BD
    =============================================*/
    private function consultarStockDirecto($sucursal, $codigos) {
        
        try {
            $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']}";
            $pdo = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $where = "codigo IN ('" . implode("','", $codigos) . "')";
            $sql = "SELECT codigo, stock FROM productos WHERE $where";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stock = [];
            foreach($resultados as $producto) {
                $stock[$producto['codigo']] = (int)$producto['stock'];
            }
            
            return $stock;
            
        } catch(Exception $e) {
            error_log("Error conexión directa sucursal {$sucursal['codigo_sucursal']}: " . $e->getMessage());
            return null;
        }
    }
    
    /*=============================================
    CONSULTAR STOCK VIA API
    =============================================*/
    private function consultarStockAPI($sucursal, $codigos) {
        
        try {
            $url = rtrim($sucursal['url_api'], '/') . '/api-transferencias/obtener_stock_productos.php';
            
            $data = [
                'codigos' => $codigos
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if($httpCode == 200 && $response) {
                $data = json_decode($response, true);
                if($data && isset($data['success']) && $data['success']) {
                    return $data['stock'] ?? [];
                }
            }
            
            return null;
            
        } catch(Exception $e) {
            error_log("Error API sucursal {$sucursal['codigo_sucursal']}: " . $e->getMessage());
            return null;
        }
    }
}

/*=============================================
EJECUTAR AJAX
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "consultar_stock_sucursales") {
    
    $ajax = new AjaxStockDisponibleSucursales();
    $ajax->accion = $_POST["accion"];
    $ajax->productos = $_POST["productos"];
    $ajax->ajaxConsultarStockSucursales();
}
