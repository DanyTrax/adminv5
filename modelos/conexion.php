<?php

class Conexion{

	static public function conectar(){

		$configPath = dirname(__DIR__) . "/config.database.php";
		if (file_exists($configPath)) {
			require_once $configPath;
		} else {
			throw new RuntimeException("Falta config.database.php. Copie config.database.php.example y complete las credenciales.");
		}

		$link = new PDO(
			"mysql:host=" . DB_LOCAL_HOST . ";dbname=" . DB_LOCAL_NAME,
			DB_LOCAL_USER,
			DB_LOCAL_PASS
		);

		$link->exec("set names utf8");

		return $link;

	}

}
