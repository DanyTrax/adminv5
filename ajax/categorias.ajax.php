<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";
require_once "../modelos/productos.modelo.php";

class AjaxCategorias{

	/* EDITAR CATEGORÍA */
	public $idCategoria;
	public function ajaxEditarCategoria(){
		$item = "id";
		$valor = $this->idCategoria;
		$respuesta = ControladorCategorias::ctrMostrarCategorias($item, $valor);
		echo json_encode($respuesta);
	}

	/* BORRAR CATEGORIA */
	public $idCategoriaBorrar;
	public function ajaxBorrarCategoria(){
		$respuesta = ControladorCategorias::ctrBorrarCategoria($this->idCategoriaBorrar);
		echo $respuesta;
	}
}

/* Objeto para Editar */
if(isset($_POST["idCategoria"])){
	$categoria = new AjaxCategorias();
	$categoria -> idCategoria = $_POST["idCategoria"];
	$categoria -> ajaxEditarCategoria();
}

/* Objeto para Borrar */
if(isset($_POST["idCategoriaBorrar"])){
	$borrar = new AjaxCategorias();
	$borrar -> idCategoriaBorrar = $_POST["idCategoriaBorrar"];
	$borrar -> ajaxBorrarCategoria();
}