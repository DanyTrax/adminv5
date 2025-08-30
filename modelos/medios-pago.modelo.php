<?php

require_once "conexion.php";


class ModeloMediosPago {

    // MOSTRAR
    static public function mdlMostrarMediosPago($tabla){
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY nombre ASC");
        $stmt -> execute();
        return $stmt -> fetchAll();
    }

    // CREAR
    static public function mdlCrearMedioPago($tabla, $datos){
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre) VALUES (:nombre)");
        $stmt->bindParam(":nombre", $datos, PDO::PARAM_STR);
        if($stmt->execute()){ return "ok"; }else{ return "error"; }
    }

    // EDITAR
    static public function mdlEditarMedioPago($tabla, $datos){
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre WHERE id = :id");
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
        if($stmt->execute()){ return "ok"; }else{ return "error"; }
    }

    // BORRAR
    static public function mdlBorrarMedioPago($tabla, $datos){
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $datos, PDO::PARAM_INT);
        if($stmt->execute()){ return "ok"; }else{ return "error"; }
    }
}