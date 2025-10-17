<?php

require_once "../controladores/catalogo-maestro.controlador.php";
require_once "../modelos/catalogo-maestro.modelo.php";

/*=============================================
CLASE AJAX CATALOGO MAESTRO
=============================================*/

class AjaxCatalogoMaestro {

    /*=============================================
    OBTENER SIGUIENTE CÓDIGO
    =============================================*/

    public $accion;

    public function ajaxObtenerSiguienteCodigo() {
        
        $respuesta = ModeloCatalogoMaestro::mdlObtenerSiguienteCodigo();
        echo $respuesta;
    }
    
    /*=============================================
    EDITAR PRODUCTO MAESTRO
    =============================================*/

    public $idProductoMaestro;

    public function ajaxEditarProductoMaestro() {
        
        $item = "id";
        $valor = $this->idProductoMaestro;

        $respuesta = ModeloCatalogoMaestro::mdlMostrarProductosMaestros($item, $valor);

        echo json_encode($respuesta);
    }

    /*=============================================
    VALIDAR CÓDIGO MAESTRO
    =============================================*/

    public $validarCodigo;

    public function ajaxValidarCodigoMaestro() {
        
        $item = "codigo";
        $valor = $this->validarCodigo;

        $respuesta = ModeloCatalogoMaestro::mdlMostrarProductosMaestros($item, $valor);

        echo json_encode($respuesta);
    }

    /*=============================================
    BUSCAR PRODUCTOS PARA HIJOS
    =============================================*/

    public $termino;

    public function ajaxBuscarProductosHijos() {
        
        $respuesta = ModeloCatalogoMaestro::mdlBuscarProductosHijos($this->termino);
        echo json_encode($respuesta);
    }
    
    /*=============================================
OBTENER DESCRIPCIÓN POR CÓDIGO
=============================================*/

public $codigo;

public function ajaxObtenerDescripcion() {
    
    $item = "codigo";
    $valor = $this->codigo;

    $respuesta = ModeloCatalogoMaestro::mdlMostrarProductosMaestros($item, $valor);

    if($respuesta) {
        echo json_encode(array(
            "codigo" => $respuesta["codigo"],
            "descripcion" => $respuesta["descripcion"],
            "precio_venta" => $respuesta["precio_venta"]
        ));
    } else {
        echo json_encode(array(
            "codigo" => $valor,
            "descripcion" => "Producto no encontrado",
            "precio_venta" => "0"
        ));
    }
}
}

/*=============================================
OBTENER SIGUIENTE CÓDIGO
=============================================*/

if(isset($_POST["accion"]) && $_POST["accion"] == "obtenerCodigo") {
    
    $obtenerCodigo = new AjaxCatalogoMaestro();
    $obtenerCodigo->ajaxObtenerSiguienteCodigo();
}

/*=============================================
EDITAR PRODUCTO MAESTRO
=============================================*/

if(isset($_POST["idProductoMaestro"])) {
    
    $editarProducto = new AjaxCatalogoMaestro();
    $editarProducto->idProductoMaestro = $_POST["idProductoMaestro"];
    $editarProducto->ajaxEditarProductoMaestro();
}

/*=============================================
VALIDAR CÓDIGO MAESTRO
=============================================*/

if(isset($_POST["validarCodigo"])) {
    
    $validarCodigo = new AjaxCatalogoMaestro();
    $validarCodigo->validarCodigo = $_POST["validarCodigo"];
    $validarCodigo->ajaxValidarCodigoMaestro();
}

/*=============================================
BUSCAR PRODUCTOS PARA HIJOS
=============================================*/

if(isset($_POST["accion"]) && $_POST["accion"] == "buscarProductos" && isset($_POST["termino"])) {
    
    $buscarProductos = new AjaxCatalogoMaestro();
    $buscarProductos->termino = $_POST["termino"];
    $buscarProductos->ajaxBuscarProductosHijos();
}
/*=============================================
OBTENER DESCRIPCIÓN POR CÓDIGO
=============================================*/

if(isset($_POST["accion"]) && $_POST["accion"] == "obtenerDescripcion" && isset($_POST["codigo"])) {
    
    $obtenerDescripcion = new AjaxCatalogoMaestro();
    $obtenerDescripcion->codigo = $_POST["codigo"];
    $obtenerDescripcion->ajaxObtenerDescripcion();
}

?>