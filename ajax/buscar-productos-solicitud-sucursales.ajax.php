<?php

session_start();

require_once __DIR__ . "/../controladores/despachos.controlador.php";

if(isset($_POST["buscarProductosSolicitudSucursales"])) {
    
    $idSolicitud = $_POST["idSolicitud"];
    
    $resultado = ControladorDespachos::ctrBuscarProductosSolicitudEnSucursales($idSolicitud);
    
    echo json_encode($resultado);
}
