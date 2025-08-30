<?php

class Conexion{

	static public function conectar(){

		$link = new PDO("mysql:host=localhost;dbname=epicosie_pruebas",
			            "epicosie_ricaurte",
			            "m5Wwg)~M{i~*kFr{");

		$link->exec("set names utf8");

		return $link;

	}

}