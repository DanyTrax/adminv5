<?php

class ConexionCentral {
    static public function conectar(){

        // Base de datos CENTRAL para despachos y transferencias
        $servidor = "localhost";
        $nombreBD = "epicosie_central";
        $usuario = "epicosie_central";
        $password = "=Nf?M#6A'QU&.6c";

        try {
            // CORRECCIÓN: Añadimos charset=utf8mb4 directamente a la línea de conexión.
            $link = new PDO(
                "mysql:host=$servidor;dbname=$nombreBD;charset=utf8mb4",
                $usuario,
                $password
            );

            // Habilitamos los errores de PDO para ver problemas
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $link;

        } catch (PDOException $e) {
            // Manejar el error de conexión
            die("Error de conexión: " . $e->getMessage());
        }
    }
}