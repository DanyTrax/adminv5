<?php

// Requerimos los archivos necesarios al principio
require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

// También incluimos las categorías, ya que la clase las puede necesitar
require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class AjaxProductos{

	// --- Propiedades para editar y crear ---
	public $idCategoria;
	public $idProducto;
	public $traerProductos;
	public $nombreProducto;

	// --- Propiedades para dividir ---
	public $idProductoDividir;
	public $tipoDivision;

	// --- Métodos ---
	public function ajaxCrearCodigoProducto(){
		$item = "id_categoria";
		$valor = $this->idCategoria;
		$orden = "id";
		$respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
		echo json_encode($respuesta);
	}

	public function ajaxEditarProducto(){
		if($this->traerProductos == "ok"){
			$item = null;
			$valor = null;
			$orden = "id";
		}else if($this->nombreProducto != ""){
			$item = "descripcion";
			$valor = $this->nombreProducto;
			$orden = "id";
		}else{
			$item = "id";
			$valor = $this->idProducto;
			$orden = "id";
		}
		$respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
		echo json_encode($respuesta);
	}

	// MÉTODO PARA DIVIDIR
	public function ajaxDividirProducto(){
		// Llama a la función del controlador que ya corregimos
		ControladorProductos::ctrDividirProductoAjax($this->idProductoDividir, $this->tipoDivision);
	}
}

// =============================================
// MANEJADOR DE PETICIONES (ROUTER)
// =============================================
// Si la petición es para dividir un producto
if(isset($_POST["idProductoDividir"])){

	$divisor = new AjaxProductos();
	$divisor -> idProductoDividir = $_POST["idProductoDividir"];
	$divisor -> tipoDivision = $_POST["tipoDivision"];
	$divisor -> ajaxDividirProducto();

} 
// Si la petición es para editar o traer un producto
else if(isset($_POST["idProducto"]) || isset($_POST["traerProductos"]) || isset($_POST["nombreProducto"])){

	$editarProducto = new AjaxProductos();
	$editarProducto -> idProducto = $_POST["idProducto"] ?? null;
	$editarProducto -> traerProductos = $_POST["traerProductos"] ?? null;
	$editarProducto -> nombreProducto = $_POST["nombreProducto"] ?? null;
	$editarProducto -> ajaxEditarProducto();

} 
// Si la petición es para crear un código de producto
if(isset($_POST["idCategoria"])){
    $item = "id_categoria";
    $valor = $_POST["idCategoria"];
    $respuesta = ControladorProductos::ctrObtenerUltimoCodigo($item, $valor);
    echo json_encode($respuesta);
}