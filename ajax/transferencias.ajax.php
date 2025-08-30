<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "../controladores/transferencias.controlador.php";
require_once "../modelos/productos.modelo.php";

class AjaxTransferencias{
    public $idTransferencia;
    public $idProducto;
    public $cantidad;

    public function ajaxDescontarStock(){
        $respuesta = ControladorTransferencias::ctrDespacharTransferencia($this->idTransferencia);
        echo $respuesta;
    }

    public function ajaxAgregarStock(){
        $respuesta = ControladorTransferencias::ctrAgregarStock($this->idProducto, $this->cantidad);
        echo $respuesta;
    }
}

// ROUTER DE PETICIONES
if(isset($_POST["accion"]) && $_POST["accion"] == "descontarStock"){
    $despachar = new AjaxTransferencias();
    $despachar->idTransferencia = $_POST["idTransferencia"];
    $despachar->ajaxDescontarStock();
}
else if(isset($_POST["accion"]) && $_POST["accion"] == "agregarStock"){
    $recepcion = new AjaxTransferencias();
    $recepcion->idProducto = $_POST["idProducto"];
    $recepcion->cantidad = $_POST["cantidad"];
    $recepcion->ajaxAgregarStock();
}