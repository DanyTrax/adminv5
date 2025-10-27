<?php

require_once __DIR__ . "/../controladores/salidas-inventario.controlador.php";
require_once __DIR__ . "/../modelos/salidas-inventario.modelo.php";

/*=============================================
BUSCAR PRODUCTOS PARA AJAX
=============================================*/

if(isset($_POST["buscarProductos"])){

	$busqueda = $_POST["buscarProductos"];

	$productos = ModeloSalidasInventario::mdlBuscarProductos($busqueda);

	echo json_encode($productos);

}
