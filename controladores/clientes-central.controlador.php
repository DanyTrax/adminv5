<?php

require_once __DIR__ . "/../modelos/clientes-central.modelo.php";

class ControladorClientesCentral
{

	/*=============================================
	CREAR CLIENTE CENTRAL
	=============================================*/

	static public function ctrCrearClienteCentral($datos)
	{
		return ModeloClientesCentral::mdlCrearClienteCentral($datos);
	}

	/*=============================================
	EDITAR CLIENTE CENTRAL
	=============================================*/

	static public function ctrEditarClienteCentral($datos)
	{
		return ModeloClientesCentral::mdlEditarClienteCentralConSincronizacion($datos);
	}

	/*=============================================
	ELIMINAR CLIENTE CENTRAL
	=============================================*/

	static public function ctrEliminarClienteCentral($id_central)
	{
		return ModeloClientesCentral::mdlEliminarClienteCentral($id_central);
	}

	/*=============================================
	MOSTRAR CLIENTES CENTRALES
	=============================================*/

	static public function ctrMostrarClientesCentral($item = null, $valor = null)
	{
		return ModeloClientesCentral::mdlObtenerClientesCentral($item, $valor);
	}

	/*=============================================
	VERIFICAR DUPLICADO CLIENTE
	=============================================*/

	static public function ctrVerificarDuplicadoCliente($documento, $email = null)
	{
		return ModeloClientesCentral::mdlVerificarDuplicadoCliente($documento, $email);
	}

	/*=============================================
	IMPORTAR CLIENTES DESDE SUCURSALES
	=============================================*/

	static public function ctrImportarClientesDesdeSucursales()
	{
		return ModeloClientesCentral::mdlImportarClientesDesdeSucursales();
	}

	/*=============================================
	OBTENER SUCURSALES DISPONIBLES
	=============================================*/

	static public function ctrObtenerSucursalesDisponibles()
	{
		return ModeloClientesCentral::mdlObtenerSucursalesDisponibles();
	}
}
