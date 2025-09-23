<?php

require_once "../controladores/solicitudes-stock.controlador.php";
require_once "../modelos/solicitudes-stock.modelo.php";

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";

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
    BUSCAR VENTAS PARA REMISIÓN
    =============================================*/
    public $busquedaVenta;

    public function ajaxBuscarVentas(){
        
        $respuesta = ControladorSolicitudesStock::ctrBuscarVentasRemision($this->busquedaVenta);
        
        if($respuesta){
            echo json_encode($respuesta);
        } else {
            echo json_encode([]);
        }
    }

    /*=============================================
    OBTENER PRODUCTOS DE UNA VENTA
    =============================================*/
    public $codigoVenta;

    public function ajaxObtenerProductosVenta(){
        
        $respuesta = ControladorSolicitudesStock::ctrObtenerProductosVenta($this->codigoVenta);
        
        if($respuesta){
            echo json_encode($respuesta);
        } else {
            echo json_encode([]);
        }
    }

    /*=============================================
    OBTENER PRODUCTO INDIVIDUAL
    =============================================*/
    public $idProducto;

    public function ajaxObtenerProducto(){
        
        $item = "id";
        $valor = $this->idProducto;
        
        $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor);
        
        echo json_encode($respuesta);
    }

    /*=============================================
    VALIDAR STOCK PRODUCTO
    =============================================*/
    public $validarStock;
    public $cantidadSolicitada;

    public function ajaxValidarStock(){
        
        $producto = ControladorProductos::ctrMostrarProductos("id", $this->validarStock);
        
        $resultado = array(
            "producto_id" => $this->validarStock,
            "stock_disponible" => $producto["stock"],
            "cantidad_solicitada" => $this->cantidadSolicitada,
            "stock_suficiente" => ($producto["stock"] >= $this->cantidadSolicitada),
            "stock_restante" => ($producto["stock"] - $this->cantidadSolicitada)
        );
        
        echo json_encode($resultado);
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
BUSCAR VENTAS
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

/*=============================================
VALIDAR STOCK
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "validar_stock"){
    
    $validarStock = new AjaxSolicitudesStock();
    $validarStock->validarStock = $_POST["producto_id"];
    $validarStock->cantidadSolicitada = $_POST["cantidad"];
    $validarStock->ajaxValidarStock();
}