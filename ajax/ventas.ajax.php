<?php
session_start();
require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";
require_once "../modelos/productos.modelo.php";
require_once "../modelos/clientes.modelo.php";

class AjaxVentas{

	/*=============================================
	TRAER VENTA PARA MODAL DE ABONO
	=============================================*/	
	public $idVenta;

	public function ajaxTraerVenta(){

		// Llama al controlador para buscar los datos de una venta por su ID
		$respuesta = ControladorVentas::ctrMostrarVentas("id", $this->idVenta);

		// Devuelve los datos en formato JSON para que JavaScript los lea
		echo json_encode($respuesta);

	}

	/*=============================================
	ELIMINAR VENTA
	=============================================*/	
	public $idVentaBorrar;

	public function ajaxEliminarVenta(){

		// Llama al controlador para ejecutar el borrado
		ControladorVentas::ctrEliminarVentaAjax($this->idVentaBorrar);

	}

}

/*=============================================
MANEJADOR DE PETICIONES AJAX
=============================================*/
// Si la petición es para el modal de Abono (envía 'idVenta')
if(isset($_POST["idVenta"])){

	$traerVenta = new AjaxVentas();
	$traerVenta -> idVenta = $_POST["idVenta"];
	$traerVenta -> ajaxTraerVenta();

}

// Si la petición es para Borrar una Venta (envía 'idVentaBorrar')
if(isset($_POST["idVentaBorrar"])){

	$eliminarVenta = new AjaxVentas();
	$eliminarVenta -> idVentaBorrar = $_POST["idVentaBorrar"];
	$eliminarVenta -> ajaxEliminarVenta();

}
if(isset($_POST["editarVenta"])){
    $respuesta = ControladorVentas::ctrEditarVenta();
    echo $respuesta;
}