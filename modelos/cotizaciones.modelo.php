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
		if (!is_array($datos) || empty($datos)) {
			return "error";
		}
		$cols = [];
		$placeholders = [];
		foreach (array_keys($datos) as $key) {
			if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
				return "error";
			}
			$cols[] = "`$key`";
			$placeholders[] = ":$key";
		}
		$sql = "INSERT INTO " . self::TABLA . " (" . implode(", ", $cols) . ") VALUES (" . implode(", ", $placeholders) . ")";

		$stmt = Conexion::conectar()->prepare($sql);
		foreach ($datos as $key => $value) {
			$stmt->bindValue(":$key", $value);
		}

		if ($stmt->execute()) {
			return "ok";
		} else {
			return "error";
		}
	}

	public static function update($id, $datos)
	{
		if (!is_array($datos) || empty($datos)) {
			return "error";
		}
		$sets = [];
		foreach (array_keys($datos) as $key) {
			if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
				return "error";
			}
			$sets[] = "`$key` = :$key";
		}
		$sql = "UPDATE " . self::TABLA . " SET " . implode(", ", $sets) . " WHERE id = :id";

		$stmt = Conexion::conectar()->prepare($sql);
		foreach ($datos as $key => $value) {
			$stmt->bindValue(":$key", $value);
		}
		$stmt->bindValue(":id", $id, PDO::PARAM_INT);

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
