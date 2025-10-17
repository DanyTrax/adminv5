<?php

class ConexionCentral {
    static public function conectar(){

        // REEMPLAZA CON LOS DATOS DE TU BASE DE DATOS CENTRAL
        $servidor = "localhost";
        $nombreBD = "epicosie_central"; // O el nombre que le hayas puesto
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