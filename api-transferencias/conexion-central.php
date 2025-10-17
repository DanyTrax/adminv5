<?php

class ConexionCentral {
    static public function conectar(){

        // Usar la misma base de datos que la conexión principal
        $servidor = "localhost";
        $nombreBD = "epicosie_pruebas";
        $usuario = "epicosie_ricaurte";
        $password = "m5Wwg)~M{i~*kFr{";

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