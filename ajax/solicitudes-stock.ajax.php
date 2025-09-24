<?php

session_start();


require_once "../modelos/conexion.php";
require_once "../api-transferencias/conexion-central.php";
require_once "../controladores/solicitudes-stock.controlador.php";
require_once "../modelos/solicitudes-stock.modelo.php";

class AjaxSolicitudesStock {

    /*=============================================
    MOSTRAR SOLICITUD INDIVIDUAL
    =============================================*/
    public $idSolicitud;

    public function ajaxMostrarSolicitud(){
        
        $item = "id";
        $valor = $this->idSolicitud;
        
        $respuesta = ControladorSolicitudesStock::ctrMostrarSolicitudes($item, $valor);
        
        echo json_encode($respuesta);
    }

    /*=============================================
    BUSCAR VENTAS PARA REMISIÓN - EN BASE LOCAL
    =============================================*/
    public $busquedaVenta;

    public function ajaxBuscarVentas(){
        
        try {
            // ✅ USAR CONEXIÓN LOCAL PARA BUSCAR VENTAS
            $stmt = Conexion::conectar()->prepare("SELECT 
                v.id,
                v.codigo,
                v.fecha,
                v.total,
                c.nombre as nombre_cliente,
                c.documento as documento_cliente
                FROM ventas v 
                LEFT JOIN clientes c ON v.id_cliente = c.id 
                WHERE v.codigo LIKE :busqueda 
                OR c.nombre LIKE :busqueda 
                OR c.documento LIKE :busqueda
                ORDER BY v.fecha DESC 
                LIMIT 10");
            
            $busqueda = "%" . $this->busquedaVenta . "%";
            $stmt->bindParam(":busqueda", $busqueda, PDO::PARAM_STR);
            $stmt->execute();
            
            $ventas = $stmt->fetchAll();
            
            echo json_encode([
                "success" => true,
                "data" => $ventas
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error buscando remisiones: " . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    OBTENER PRODUCTOS DE UNA VENTA - EN BASE LOCAL
    =============================================*/
    public $codigoVenta;

    public function ajaxObtenerProductosVenta(){
        
        try {
            // ✅ USAR CONEXIÓN LOCAL PARA OBTENER PRODUCTOS DE LA VENTA
            $stmt = Conexion::conectar()->prepare("SELECT 
                productos
                FROM ventas 
                WHERE codigo = :codigo");
            
            $stmt->bindParam(":codigo", $this->codigoVenta, PDO::PARAM_STR);
            $stmt->execute();
            
            $venta = $stmt->fetch();
            
            if($venta) {
                $productos = json_decode($venta["productos"], true);
                
                echo json_encode([
                    "success" => true,
                    "productos" => $productos
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Venta no encontrada"
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error obteniendo productos de venta: " . $e->getMessage()
            ]);
        }
    }

    /*=============================================
    OBTENER PRODUCTO INDIVIDUAL - BASE LOCAL
    =============================================*/
    public $idProducto;

    public function ajaxObtenerProducto(){
        
        try {
            // ✅ USAR CONEXIÓN LOCAL PARA PRODUCTOS
            $stmt = Conexion::conectar()->prepare("SELECT 
                id, codigo, descripcion, stock
                FROM productos 
                WHERE id = :id");
            
            $stmt->bindParam(":id", $this->idProducto, PDO::PARAM_INT);
            $stmt->execute();
            
            $producto = $stmt->fetch();
            
            if($producto) {
                echo json_encode([
                    "success" => true,
                    "data" => $producto
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Producto no encontrado"
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error obteniendo producto: " . $e->getMessage()
            ]);
        }
    }
}

/*=============================================
MOSTRAR SOLICITUD
=============================================*/
if(isset($_POST["idSolicitud"])){
    
    $solicitud = new AjaxSolicitudesStock();
    $solicitud->idSolicitud = $_POST["idSolicitud"];
    $solicitud->ajaxMostrarSolicitud();
}

/*=============================================
BUSCAR VENTAS (REMISIONES)
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "buscar_ventas"){
    
    $buscarVentas = new AjaxSolicitudesStock();
    $buscarVentas->busquedaVenta = $_POST["busqueda"];
    $buscarVentas->ajaxBuscarVentas();
}

/*=============================================
OBTENER PRODUCTOS DE VENTA
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "productos_venta"){
    
    $productosVenta = new AjaxSolicitudesStock();
    $productosVenta->codigoVenta = $_POST["codigo_venta"];
    $productosVenta->ajaxObtenerProductosVenta();
}

/*=============================================
OBTENER PRODUCTO
=============================================*/
if(isset($_POST["idProducto"])){
    
    $producto = new AjaxSolicitudesStock();
    $producto->idProducto = $_POST["idProducto"];
    $producto->ajaxObtenerProducto();
}