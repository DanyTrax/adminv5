<?php

require_once "conexion.php";

class ModeloClientes
{

	/*=============================================
	CREAR CLIENTE
	=============================================*/

	static public function mdlIngresarCliente($tabla, $datos)
	{
		$conexion = Conexion::conectar();
		
		// ✅ VERIFICAR SI EL DOCUMENTO YA EXISTE
		$stmt = $conexion->prepare("SELECT * FROM $tabla WHERE documento = :documento");
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->execute();
		$resultadoDocumento = $stmt->fetch();

		if ($resultadoDocumento) {
			return 'duplicado_documento';
		}

		// ✅ VERIFICAR SI EL NOMBRE YA EXISTE (comparación sin distinguir mayúsculas/minúsculas)
		$stmt = $conexion->prepare("SELECT * FROM $tabla WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:nombre))");
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->execute();
		$resultadoNombre = $stmt->fetch();

		if ($resultadoNombre) {
			return 'duplicado_nombre';
		}

		// ✅ SI NO HAY DUPLICADOS, PROCEDER CON LA INSERCIÓN
		$stmt = $conexion->prepare("INSERT INTO $tabla(nombre, documento, email, telefono, direccion, fecha_nacimiento, compras, ultima_compra) VALUES (:nombre, :documento, :email, :telefono, :direccion, now(), 0, now())");

		// CAMBIO: Usar PDO::PARAM_STR para soportar documentos grandes (BIGINT o VARCHAR)
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);


		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}
	}

	/*=============================================
	MOSTRAR CLIENTES
	=============================================*/

	static public function mdlMostrarClientes($tabla, $item, $valor)
	{

		if ($item != null) {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();
		} else {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY nombre");

			$stmt->execute();

			return $stmt->fetchAll();
		}

		$stmt->close();

		$stmt = null;
	}

	/*=============================================
	EDITAR CLIENTE
	=============================================*/

	static public function mdlEditarCliente($tabla, $datos)
	{
		$conexion = Conexion::conectar();
		
		// ✅ VERIFICAR SI EL DOCUMENTO YA EXISTE (excluyendo el cliente actual)
		$stmt = $conexion->prepare("SELECT * FROM $tabla WHERE documento = :documento AND id <> :id");
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->execute();
		$resultadoDocumento = $stmt->fetch();

		if ($resultadoDocumento) {
			return 'duplicado_documento';
		}

		// ✅ VERIFICAR SI EL NOMBRE YA EXISTE (excluyendo el cliente actual, comparación sin distinguir mayúsculas/minúsculas)
		$stmt = $conexion->prepare("SELECT * FROM $tabla WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:nombre)) AND id <> :id");
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->execute();
		$resultadoNombre = $stmt->fetch();

		if ($resultadoNombre) {
			return 'duplicado_nombre';
		}

		// ✅ SI NO HAY DUPLICADOS, PROCEDER CON LA ACTUALIZACIÓN
		$stmt = $conexion->prepare("UPDATE $tabla SET nombre = :nombre, documento = :documento, email = :email, telefono = :telefono, direccion = :direccion WHERE id = :id");

		// CAMBIO: Usar PDO::PARAM_STR para soportar documentos grandes (BIGINT o VARCHAR)
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);


		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	ELIMINAR CLIENTE
	=============================================*/

	static public function mdlEliminarCliente($tabla, $datos)
	{

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt->bindParam(":id", $datos, PDO::PARAM_INT);

		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}

		$stmt->close();

		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR CLIENTE
	=============================================*/

	static public function mdlActualizarCliente($tabla, $item1, $valor1, $valor)
	{

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");

		$stmt->bindParam(":" . $item1, $valor1, PDO::PARAM_STR);
		$stmt->bindParam(":id", $valor, PDO::PARAM_STR);

		if ($stmt->execute()) {

			return "ok";
		} else {

			return "error";
		}

		$stmt->close();

		$stmt = null;
	}
}
