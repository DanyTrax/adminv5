<?php

require_once "conexion-central.php";

class ModeloClientesCentral
{

	/*=============================================
	CREAR CLIENTE CENTRAL
	=============================================*/

	static public function mdlCrearClienteCentral($datos)
	{
		try {
			$conexion = ConexionCentral::conectar();

			// Verificar si existe por documento
			$stmt = $conexion->prepare("SELECT id_central FROM clientes_central WHERE documento = :documento");
			$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
			$stmt->execute();
			$resultado = $stmt->fetch();

			if ($resultado) {
				return [
					'success' => false,
					'error' => 'El documento ya existe en el sistema central'
				];
			}

			// Verificar si existe por email (si el email no está vacío)
			if (!empty($datos["email"])) {
				$stmt = $conexion->prepare("SELECT id_central FROM clientes_central WHERE email = :email");
				$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
				$stmt->execute();
				$resultado = $stmt->fetch();

				if ($resultado) {
					return [
						'success' => false,
						'error' => 'El email ya existe en el sistema central'
					];
				}
			}

			// Crear cliente en central
			$stmt = $conexion->prepare("INSERT INTO clientes_central(
				documento, email, nombre, telefono, direccion, fecha_nacimiento,
				sucursales_asignadas, id_local_principal, sucursal_origen, activo
			) VALUES (
				:documento, :email, :nombre, :telefono, :direccion, :fecha_nacimiento,
				:sucursales_asignadas, :id_local_principal, :sucursal_origen, 1
			)");

			$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
			$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
			$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
			$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
			$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
			$stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
			$stmt->bindParam(":sucursales_asignadas", $datos["sucursales_asignadas"], PDO::PARAM_STR);
			$stmt->bindParam(":id_local_principal", $datos["id_local_principal"], PDO::PARAM_INT);
			$stmt->bindParam(":sucursal_origen", $datos["sucursal_origen"], PDO::PARAM_STR);

			if ($stmt->execute()) {
				return [
					'success' => true,
					'id_central' => $conexion->lastInsertId()
				];
			} else {
				return [
					'success' => false,
					'error' => 'Error al crear cliente en sistema central'
				];
			}
		} catch (Exception $e) {
			error_log("Error en mdlCrearClienteCentral: " . $e->getMessage());
			return [
				'success' => false,
				'error' => 'Error de base de datos: ' . $e->getMessage()
			];
		}
	}

	/*=============================================
	EDITAR CLIENTE CENTRAL
	=============================================*/

	static public function mdlEditarClienteCentral($datos)
	{
		try {
			$conexion = ConexionCentral::conectar();

			// Verificar si existe otro cliente con el mismo documento
			$stmt = $conexion->prepare("SELECT id_central FROM clientes_central WHERE documento = :documento AND id_central <> :id_central");
			$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
			$stmt->bindParam(":id_central", $datos["id_central"], PDO::PARAM_INT);
			$stmt->execute();
			$resultado = $stmt->fetch();

			if ($resultado) {
				return [
					'success' => false,
					'error' => 'El documento ya existe en otro cliente del sistema central'
				];
			}

			// Verificar si existe otro cliente con el mismo email (si el email no está vacío)
			if (!empty($datos["email"])) {
				$stmt = $conexion->prepare("SELECT id_central FROM clientes_central WHERE email = :email AND id_central <> :id_central");
				$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
				$stmt->bindParam(":id_central", $datos["id_central"], PDO::PARAM_INT);
				$stmt->execute();
				$resultado = $stmt->fetch();

				if ($resultado) {
					return [
						'success' => false,
						'error' => 'El email ya existe en otro cliente del sistema central'
					];
				}
			}

			// Actualizar cliente en central
			$stmt = $conexion->prepare("UPDATE clientes_central SET
				documento = :documento,
				email = :email,
				nombre = :nombre,
				telefono = :telefono,
				direccion = :direccion,
				fecha_nacimiento = :fecha_nacimiento,
				sucursales_asignadas = :sucursales_asignadas
			WHERE id_central = :id_central");

			$stmt->bindParam(":id_central", $datos["id_central"], PDO::PARAM_INT);
			$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
			$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
			$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
			$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
			$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
			$stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
			$stmt->bindParam(":sucursales_asignadas", $datos["sucursales_asignadas"], PDO::PARAM_STR);

			if ($stmt->execute()) {
				return [
					'success' => true,
					'message' => 'Cliente actualizado exitosamente'
				];
			} else {
				return [
					'success' => false,
					'error' => 'Error al actualizar cliente en sistema central'
				];
			}
		} catch (Exception $e) {
			error_log("Error en mdlEditarClienteCentral: " . $e->getMessage());
			return [
				'success' => false,
				'error' => 'Error de base de datos: ' . $e->getMessage()
			];
		}
	}

	/*=============================================
	ELIMINAR CLIENTE CENTRAL
	=============================================*/

	static public function mdlEliminarClienteCentral($id_central)
	{
		try {
			$conexion = ConexionCentral::conectar();

			// Marcar como inactivo en lugar de eliminar
			$stmt = $conexion->prepare("UPDATE clientes_central SET activo = 0 WHERE id_central = :id_central");
			$stmt->bindParam(":id_central", $id_central, PDO::PARAM_INT);

			if ($stmt->execute()) {
				return [
					'success' => true,
					'message' => 'Cliente eliminado exitosamente'
				];
			} else {
				return [
					'success' => false,
					'error' => 'Error al eliminar cliente en sistema central'
				];
			}
		} catch (Exception $e) {
			error_log("Error en mdlEliminarClienteCentral: " . $e->getMessage());
			return [
				'success' => false,
				'error' => 'Error de base de datos: ' . $e->getMessage()
			];
		}
	}

	/*=============================================
	OBTENER CLIENTES CENTRALES
	=============================================*/

	static public function mdlObtenerClientesCentral($item = null, $valor = null)
	{
		try {
			$conexion = ConexionCentral::conectar();

			if ($item != null) {
				$stmt = $conexion->prepare("SELECT * FROM clientes_central WHERE $item = :$item AND activo = 1");
				$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
				$stmt->execute();
				return $stmt->fetch(PDO::FETCH_ASSOC);
			} else {
				$stmt = $conexion->prepare("SELECT * FROM clientes_central WHERE activo = 1 ORDER BY nombre");
				$stmt->execute();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (Exception $e) {
			error_log("Error en mdlObtenerClientesCentral: " . $e->getMessage());
			return false;
		}
	}

	/*=============================================
	VERIFICAR DUPLICADO CLIENTE
	=============================================*/

	static public function mdlVerificarDuplicadoCliente($documento, $email = null)
	{
		try {
			$conexion = ConexionCentral::conectar();

			// Verificar por documento
			$stmt = $conexion->prepare("SELECT id_central, nombre, sucursal_origen FROM clientes_central WHERE documento = :documento AND activo = 1");
			$stmt->bindParam(":documento", $documento, PDO::PARAM_STR);
			$stmt->execute();
			$clientePorDocumento = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($clientePorDocumento) {
				return [
					'existe' => true,
					'campo' => 'documento',
					'cliente' => $clientePorDocumento
				];
			}

			// Verificar por email (si no está vacío)
			if (!empty($email)) {
				$stmt = $conexion->prepare("SELECT id_central, nombre, sucursal_origen FROM clientes_central WHERE email = :email AND activo = 1");
				$stmt->bindParam(":email", $email, PDO::PARAM_STR);
				$stmt->execute();
				$clientePorEmail = $stmt->fetch(PDO::FETCH_ASSOC);

				if ($clientePorEmail) {
					return [
						'existe' => true,
						'campo' => 'email',
						'cliente' => $clientePorEmail
					];
				}
			}

			return [
				'existe' => false
			];
		} catch (Exception $e) {
			error_log("Error en mdlVerificarDuplicadoCliente: " . $e->getMessage());
			return [
				'existe' => false,
				'error' => $e->getMessage()
			];
		}
	}

	/*=============================================
	OBTENER SUCURSALES DISPONIBLES
	=============================================*/

	static public function mdlObtenerSucursalesDisponibles()
	{
		try {
			require_once "sucursales.modelo.php";
			$sucursales = ModeloSucursales::mdlObtenerSucursales();
			
			if ($sucursales && isset($sucursales['data'])) {
				return array_filter($sucursales['data'], function($sucursal) {
					return $sucursal['activo'] == 1;
				});
			}
			
			return [];
		} catch (Exception $e) {
			error_log("Error en mdlObtenerSucursalesDisponibles: " . $e->getMessage());
			return [];
		}
	}
}
