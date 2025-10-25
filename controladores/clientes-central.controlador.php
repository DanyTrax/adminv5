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

	/*=============================================
	OBTENER SUCURSALES PARA SINCRONIZACIÓN BIDIRECCIONAL
	=============================================*/

	static public function ctrObtenerSucursalesBidireccional()
	{
		return ModeloClientesCentral::mdlObtenerSucursalesBidireccional();
	}

	/*=============================================
	OBTENER SUCURSALES DESTINO PARA COPIAR
	=============================================*/

	static public function ctrObtenerSucursalesDestino()
	{
		return ModeloClientesCentral::mdlObtenerSucursalesDestino();
	}

	/*=============================================
	OBTENER SUCURSALES PARA BORRAR
	=============================================*/

	static public function ctrObtenerSucursalesParaBorrar()
	{
		return ModeloClientesCentral::mdlObtenerSucursalesParaBorrar();
	}

	/*=============================================
	GUARDAR SINCRONIZACIÓN BIDIRECCIONAL
	=============================================*/

	static public function ctrGuardarSincronizacionBidireccional($sucursales)
	{
		return ModeloClientesCentral::mdlGuardarSincronizacionBidireccional($sucursales);
	}

	/*=============================================
	COPIAR CLIENTES A SUCURSAL
	=============================================*/

	static public function ctrCopiarClientesASucursal($direccion, $sucursalId)
	{
		return ModeloClientesCentral::mdlCopiarClientesASucursal($direccion, $sucursalId);
	}

	/*=============================================
	BORRAR CLIENTES
	=============================================*/

	static public function ctrBorrarClientes($origen, $sucursalId = null)
	{
		return ModeloClientesCentral::mdlBorrarClientes($origen, $sucursalId);
	}
}
