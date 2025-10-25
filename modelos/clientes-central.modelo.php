<?php

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

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
	EDITAR CLIENTE CENTRAL CON SINCRONIZACIÓN
	=============================================*/

	static public function mdlEditarClienteCentralConSincronizacion($datos)
	{
		try {
			// Primero editar en central
			$resultado = self::mdlEditarClienteCentral($datos);
			
			if (!$resultado['success']) {
				return $resultado;
			}
			
			// Si hay sucursales asignadas, sincronizar
			if (!empty($datos['sucursales_asignadas'])) {
				$sucursalesArray = explode(',', $datos['sucursales_asignadas']);
				$sucursalesArray = array_filter(array_map('trim', $sucursalesArray));
				
				if (!empty($sucursalesArray)) {
					$resultadoSincronizacion = self::mdlSincronizarClienteSucursales($datos['id_central'], $sucursalesArray);
					
					if ($resultadoSincronizacion['success']) {
						$resultado['sincronizacion'] = $resultadoSincronizacion;
					}
				}
			}
			
			return $resultado;
		} catch (Exception $e) {
			error_log("Error en mdlEditarClienteCentralConSincronizacion: " . $e->getMessage());
			return [
				'success' => false,
				'error' => 'Error sincronizando cliente: ' . $e->getMessage()
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
	SINCRONIZAR CLIENTE A SUCURSALES LOCALES
	=============================================*/

	static public function mdlSincronizarClienteSucursales($id_cliente_central, $sucursales)
	{
		try {
			$conexion = ConexionCentral::conectar();
			
			// Obtener datos del cliente central
			$stmt = $conexion->prepare("SELECT * FROM clientes_central WHERE id_central = :id_central");
			$stmt->bindParam(":id_central", $id_cliente_central, PDO::PARAM_INT);
			$stmt->execute();
			$clienteCentral = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if (!$clienteCentral) {
				return [
					'success' => false,
					'error' => 'Cliente central no encontrado'
				];
			}
			
			// Obtener sucursales desde central
			require_once __DIR__ . "/sucursales.modelo.php";
			$todasSucursales = ModeloSucursales::mdlObtenerSucursales();
			
			if (!$todasSucursales || !isset($todasSucursales['data'])) {
				return [
					'success' => false,
					'error' => 'No se pudieron obtener las sucursales'
				];
			}
			
			$resultados = [];
			
			// Sincronizar a cada sucursal asignada
			foreach ($todasSucursales['data'] as $sucursal) {
				// Verificar si esta sucursal está asignada
				if (!in_array($sucursal['id'], $sucursales)) {
					continue;
				}
				
				$resultados[$sucursal['id']] = [
					'sucursal' => $sucursal['nombre'],
					'estado' => 'iniciado'
				];
				
				try {
					// Conectar a la sucursal
					$dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8";
					$pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd'], [
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
					]);
					
					// Verificar si el cliente ya existe en la sucursal
					$stmt = $pdoSucursal->prepare("SELECT id FROM clientes WHERE documento = :documento");
					$stmt->bindParam(":documento", $clienteCentral['documento'], PDO::PARAM_STR);
					$stmt->execute();
					$clienteExistente = $stmt->fetch();
					
					if ($clienteExistente) {
						// Actualizar cliente existente
						$stmt = $pdoSucursal->prepare("
							UPDATE clientes SET
								nombre = :nombre,
								email = :email,
								telefono = :telefono,
								direccion = :direccion,
								fecha_nacimiento = :fecha_nacimiento
							WHERE documento = :documento
						");
						
						$stmt->bindParam(":nombre", $clienteCentral['nombre'], PDO::PARAM_STR);
						$stmt->bindParam(":email", $clienteCentral['email'], PDO::PARAM_STR);
						$stmt->bindParam(":telefono", $clienteCentral['telefono'], PDO::PARAM_STR);
						$stmt->bindParam(":direccion", $clienteCentral['direccion'], PDO::PARAM_STR);
						$stmt->bindParam(":fecha_nacimiento", $clienteCentral['fecha_nacimiento'], PDO::PARAM_STR);
						$stmt->bindParam(":documento", $clienteCentral['documento'], PDO::PARAM_STR);
						
						if ($stmt->execute()) {
							$resultados[$sucursal['id']]['estado'] = 'actualizado';
						} else {
							$resultados[$sucursal['id']]['estado'] = 'error';
							$resultados[$sucursal['id']]['error'] = 'Error actualizando cliente';
						}
					} else {
						// Crear nuevo cliente en la sucursal
						$stmt = $pdoSucursal->prepare("
							INSERT INTO clientes (
								nombre, documento, email, telefono, direccion, 
								fecha_nacimiento, compras, ultima_compra
							) VALUES (
								:nombre, :documento, :email, :telefono, :direccion,
								:fecha_nacimiento, 0, NOW()
							)
						");
						
						$stmt->bindParam(":nombre", $clienteCentral['nombre'], PDO::PARAM_STR);
						$stmt->bindParam(":documento", $clienteCentral['documento'], PDO::PARAM_STR);
						$stmt->bindParam(":email", $clienteCentral['email'], PDO::PARAM_STR);
						$stmt->bindParam(":telefono", $clienteCentral['telefono'], PDO::PARAM_STR);
						$stmt->bindParam(":direccion", $clienteCentral['direccion'], PDO::PARAM_STR);
						$stmt->bindParam(":fecha_nacimiento", $clienteCentral['fecha_nacimiento'], PDO::PARAM_STR);
						
						if ($stmt->execute()) {
							$resultados[$sucursal['id']]['estado'] = 'creado';
						} else {
							$resultados[$sucursal['id']]['estado'] = 'error';
							$resultados[$sucursal['id']]['error'] = 'Error creando cliente';
						}
					}
					
				} catch (Exception $e) {
					error_log("Error sincronizando cliente a sucursal {$sucursal['id']}: " . $e->getMessage());
					$resultados[$sucursal['id']]['estado'] = 'error';
					$resultados[$sucursal['id']]['error'] = $e->getMessage();
				}
			}
			
			return [
				'success' => true,
				'message' => 'Cliente sincronizado a sucursales',
				'resultados' => $resultados
			];
			
		} catch (Exception $e) {
			error_log("Error en mdlSincronizarClienteSucursales: " . $e->getMessage());
			return [
				'success' => false,
				'error' => 'Error interno del servidor'
			];
		}
	}

	/*=============================================
	IMPORTAR CLIENTES DESDE SUCURSALES LOCALES
	=============================================*/

	static public function mdlImportarClientesDesdeSucursales()
	{
		try {
			$conexion = ConexionCentral::conectar();
			
			// Obtener sucursales activas
			require_once __DIR__ . "/sucursales.modelo.php";
			$todasSucursales = ModeloSucursales::mdlObtenerSucursales();
			
			if (!$todasSucursales || !isset($todasSucursales['data'])) {
				return [
					'success' => false,
					'error' => 'No se pudieron obtener las sucursales'
				];
			}
			
			$clientesImportados = 0;
			$clientesDuplicados = 0;
			$errores = [];
			$resultados = [];
			
			// Importar clientes de cada sucursal
			foreach ($todasSucursales['data'] as $sucursal) {
				if ($sucursal['activo'] != 1) {
					continue;
				}
				
				$resultados[$sucursal['id']] = [
					'sucursal' => $sucursal['nombre'],
					'clientes_importados' => 0,
					'clientes_duplicados' => 0,
					'errores' => []
				];
				
				try {
					// Conectar a la sucursal
					$dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8";
					$pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd'], [
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
					]);
					
					// Obtener clientes de la sucursal
					$stmt = $pdoSucursal->prepare("SELECT * FROM clientes ORDER BY nombre");
					$stmt->execute();
					$clientesLocales = $stmt->fetchAll();
					
					foreach ($clientesLocales as $clienteLocal) {
						// Verificar si ya existe en central por documento
						$stmtCheck = $conexion->prepare("SELECT id_central FROM clientes_central WHERE documento = :documento");
						$stmtCheck->bindParam(":documento", $clienteLocal['documento'], PDO::PARAM_STR);
						$stmtCheck->execute();
						$clienteExistente = $stmtCheck->fetch();
						
						if ($clienteExistente) {
							// Cliente duplicado
							$clientesDuplicados++;
							$resultados[$sucursal['id']]['clientes_duplicados']++;
							continue;
						}
						
						// Crear cliente en central
						$datosCliente = [
							'documento' => $clienteLocal['documento'],
							'email' => $clienteLocal['email'] ?? '',
							'nombre' => $clienteLocal['nombre'],
							'telefono' => $clienteLocal['telefono'] ?? '',
							'direccion' => $clienteLocal['direccion'] ?? '',
							'fecha_nacimiento' => $clienteLocal['fecha_nacimiento'] ?? null,
							'sucursales_asignadas' => $sucursal['id'],
							'id_local_principal' => $clienteLocal['id'],
							'sucursal_origen' => $sucursal['nombre']
						];
						
						$resultadoCrear = self::mdlCrearClienteCentral($datosCliente);
						
						if ($resultadoCrear['success']) {
							$clientesImportados++;
							$resultados[$sucursal['id']]['clientes_importados']++;
						} else {
							$errores[] = "Error importando cliente {$clienteLocal['documento']}: " . $resultadoCrear['error'];
							$resultados[$sucursal['id']]['errores'][] = "Cliente {$clienteLocal['documento']}: " . $resultadoCrear['error'];
						}
					}
					
				} catch (Exception $e) {
					error_log("Error importando clientes de sucursal {$sucursal['id']}: " . $e->getMessage());
					$errores[] = "Error en sucursal {$sucursal['nombre']}: " . $e->getMessage();
					$resultados[$sucursal['id']]['errores'][] = $e->getMessage();
				}
			}
			
			return [
				'success' => true,
				'message' => "Importación completada. Clientes importados: {$clientesImportados}, Duplicados: {$clientesDuplicados}",
				'clientes_importados' => $clientesImportados,
				'clientes_duplicados' => $clientesDuplicados,
				'errores' => $errores,
				'resultados' => $resultados
			];
			
		} catch (Exception $e) {
			error_log("Error en mdlImportarClientesDesdeSucursales: " . $e->getMessage());
			return [
				'success' => false,
				'error' => 'Error interno del servidor: ' . $e->getMessage()
			];
		}
	}

	/*=============================================
	OBTENER SUCURSALES DISPONIBLES
	=============================================*/

	static public function mdlObtenerSucursalesDisponibles()
	{
		try {
			require_once __DIR__ . "/sucursales.modelo.php";
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

	/*=============================================
	OBTENER SUCURSALES PARA SINCRONIZACIÓN BIDIRECCIONAL
	=============================================*/

	static public function mdlObtenerSucursalesBidireccional()
	{
		try {
			$conexion = ConexionCentral::conectar();

			$stmt = $conexion->prepare("
				SELECT 
					s.id,
					s.nombre,
					s.direccion,
					0 as sincronizada
				FROM sucursales s
				WHERE s.activo = 1
				ORDER BY s.nombre
			");

			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

		} catch (Exception $e) {
			error_log("Error en mdlObtenerSucursalesBidireccional: " . $e->getMessage());
			return false;
		}
	}

	/*=============================================
	OBTENER SUCURSALES DESTINO PARA COPIAR
	=============================================*/

	static public function mdlObtenerSucursalesDestino()
	{
		try {
			$conexion = ConexionCentral::conectar();

			$stmt = $conexion->prepare("
				SELECT id, nombre, direccion
				FROM sucursales
				WHERE activo = 1
				ORDER BY nombre
			");

			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

		} catch (Exception $e) {
			error_log("Error en mdlObtenerSucursalesDestino: " . $e->getMessage());
			return false;
		}
	}

	/*=============================================
	OBTENER SUCURSALES PARA BORRAR
	=============================================*/

	static public function mdlObtenerSucursalesParaBorrar()
	{
		try {
			$conexion = ConexionCentral::conectar();

			$stmt = $conexion->prepare("
				SELECT id, nombre, direccion
				FROM sucursales
				WHERE activo = 1
				ORDER BY nombre
			");

			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);

		} catch (Exception $e) {
			error_log("Error en mdlObtenerSucursalesParaBorrar: " . $e->getMessage());
			return false;
		}
	}

	/*=============================================
	GUARDAR SINCRONIZACIÓN BIDIRECCIONAL
	=============================================*/

	static public function mdlGuardarSincronizacionBidireccional($sucursales)
	{
		try {
			// Por ahora, solo retornamos éxito
			// En el futuro se puede implementar una tabla de configuración específica
			// para almacenar las sucursales seleccionadas para sincronización bidireccional
			
			error_log("Sincronización bidireccional configurada para sucursales: " . json_encode($sucursales));
			return true;

		} catch (Exception $e) {
			error_log("Error en mdlGuardarSincronizacionBidireccional: " . $e->getMessage());
			return false;
		}
	}

	/*=============================================
	COPIAR CLIENTES A SUCURSAL
	=============================================*/

	static public function mdlCopiarClientesASucursal($sucursalId)
	{
		try {
			$conexionCentral = ConexionCentral::conectar();
			$conexionCentral->beginTransaction();

			// Obtener clientes centrales
			$stmt = $conexionCentral->prepare("SELECT * FROM clientes_central WHERE activo = 1");
			$stmt->execute();
			$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// Obtener datos de conexión de la sucursal
			$stmt = $conexionCentral->prepare("
				SELECT nombre, host_bd, usuario_bd, password_bd, nombre_bd, puerto_bd 
				FROM sucursales 
				WHERE id = :id AND activo = 1
			");
			$stmt->bindParam(":id", $sucursalId, PDO::PARAM_INT);
			$stmt->execute();
			$sucursal = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$sucursal) {
				throw new Exception("Sucursal no encontrada");
			}

			// Conectar a la base de datos local de la sucursal
			$dsn = "mysql:host={$sucursal['host_bd']};dbname={$sucursal['nombre_bd']};charset=utf8mb4";
			$conexionLocal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
			$conexionLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$clientesCopiados = 0;
			$clientesDuplicados = 0;

			foreach ($clientes as $cliente) {
				// Verificar si el cliente ya existe en la sucursal
				$stmt = $conexionLocal->prepare("SELECT id FROM clientes WHERE documento = :documento");
				$stmt->bindParam(":documento", $cliente['documento'], PDO::PARAM_STR);
				$stmt->execute();
				
				if ($stmt->fetch()) {
					$clientesDuplicados++;
					continue; // Cliente ya existe, saltar
				}

				// Insertar cliente en la sucursal local
				$stmt = $conexionLocal->prepare("
					INSERT INTO clientes (documento, nombre, email, telefono, direccion, fecha_nacimiento, compras, ultima_compra)
					VALUES (:documento, :nombre, :email, :telefono, :direccion, :fecha_nacimiento, 0, NULL)
				");
				
				$stmt->bindParam(":documento", $cliente['documento'], PDO::PARAM_STR);
				$stmt->bindParam(":nombre", $cliente['nombre'], PDO::PARAM_STR);
				$stmt->bindParam(":email", $cliente['email'], PDO::PARAM_STR);
				$stmt->bindParam(":telefono", $cliente['telefono'], PDO::PARAM_STR);
				$stmt->bindParam(":direccion", $cliente['direccion'], PDO::PARAM_STR);
				$stmt->bindParam(":fecha_nacimiento", $cliente['fecha_nacimiento'], PDO::PARAM_STR);
				
				if ($stmt->execute()) {
					$clientesCopiados++;
				}
			}

			$conexionCentral->commit();
			
			error_log("Clientes copiados a sucursal '{$sucursal['nombre']}': {$clientesCopiados} nuevos, {$clientesDuplicados} duplicados");
			return [
				'success' => true,
				'copiados' => $clientesCopiados,
				'duplicados' => $clientesDuplicados,
				'total' => count($clientes)
			];

		} catch (Exception $e) {
			$conexionCentral->rollback();
			error_log("Error en mdlCopiarClientesASucursal: " . $e->getMessage());
			return false;
		}
	}

	/*=============================================
	BORRAR CLIENTES
	=============================================*/

	static public function mdlBorrarClientes($origen, $sucursalId = null)
	{
		try {
			$conexionCentral = ConexionCentral::conectar();
			$conexionCentral->beginTransaction();

			if ($origen === "central") {
				// Borrar todos los clientes centrales
				$stmt = $conexionCentral->prepare("DELETE FROM clientes_central");
				$stmt->execute();
				
				error_log("Todos los clientes centrales eliminados");
				$conexionCentral->commit();
				return true;

			} elseif ($origen === "sucursal" && $sucursalId) {
				// Obtener datos de conexión de la sucursal
				$stmt = $conexionCentral->prepare("
					SELECT nombre, host_bd, usuario_bd, password_bd, nombre_bd, puerto_bd 
					FROM sucursales 
					WHERE id = :id AND activo = 1
				");
				$stmt->bindParam(":id", $sucursalId, PDO::PARAM_INT);
				$stmt->execute();
				$sucursal = $stmt->fetch(PDO::FETCH_ASSOC);

				if (!$sucursal) {
					throw new Exception("Sucursal no encontrada");
				}

				// Conectar a la base de datos local de la sucursal
				$dsn = "mysql:host={$sucursal['host_bd']};dbname={$sucursal['nombre_bd']};charset=utf8mb4";
				$conexionLocal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
				$conexionLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				// Contar clientes antes de borrar
				$stmt = $conexionLocal->prepare("SELECT COUNT(*) as total FROM clientes");
				$stmt->execute();
				$totalClientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

				// Borrar todos los clientes de la sucursal
				$stmt = $conexionLocal->prepare("DELETE FROM clientes");
				$stmt->execute();

				$conexionCentral->commit();
				
				error_log("Clientes eliminados de sucursal '{$sucursal['nombre']}': {$totalClientes} clientes borrados");
				return [
					'success' => true,
					'eliminados' => $totalClientes,
					'sucursal' => $sucursal['nombre']
				];
			}

			$conexionCentral->commit();
			return true;

		} catch (Exception $e) {
			$conexionCentral->rollback();
			error_log("Error en mdlBorrarClientes: " . $e->getMessage());
			return false;
		}
	}
}
