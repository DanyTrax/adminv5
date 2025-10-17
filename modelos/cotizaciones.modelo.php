<?php

require_once "conexion.php";

class ModeloCotizaciones
{
	private const TABLA = 'cotizaciones';
	public static function all()
	{
		$stmt = Conexion::conectar()->prepare("SELECT * FROM cotizaciones ORDER BY id DESC");
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public static function save($datos)
	{
		$columns = '';
		$values = '';
		foreach ($datos as $key => $value) {
			$columns .= $key . ',';
			$values .= "'" . $value . "',";
		}
		$columns = substr($columns, 0, -1);
		$values = substr($values, 0, -1);
		$sql = "INSERT INTO " . self::TABLA . " ($columns) VALUES ($values)";

		$stmt = Conexion::conectar()->prepare($sql);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
	}

	public static function update($id, $datos)
	{
		$columns = '';
		foreach ($datos as $key => $value) {
			$columns .= $key . "='" . $value . "',";
		}
		$columns = substr($columns, 0, -1);
		$sql = "UPDATE " . self::TABLA . " SET $columns WHERE id = :id";

		$stmt = Conexion::conectar()->prepare($sql);
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
	}

	public static function findById($id)
	{
		$stmt = Conexion::conectar()->prepare("SELECT * FROM cotizaciones WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch();
	}

	public static function deleteById($id)
	{
		$stmt = Conexion::conectar()->prepare("DELETE FROM cotizaciones WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
	}
}
