<?php

class ConexionCentral {
    static public function conectar(){

        $configPath = dirname(__DIR__) . "/config.database.php";
        if (file_exists($configPath)) {
            require_once $configPath;
        } else {
            throw new RuntimeException("Falta config.database.php. Copie config.database.php.example y complete las credenciales.");
        }

        $servidor = DB_CENTRAL_HOST;
        $nombreBD = DB_CENTRAL_NAME;
        $usuario = DB_CENTRAL_USER;
        $password = DB_CENTRAL_PASS;

        try {
            $link = new PDO(
                "mysql:host=$servidor;dbname=$nombreBD;charset=utf8mb4",
                $usuario,
                $password
            );

            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $link;

        } catch (PDOException $e) {
            throw $e;
        }
    }
}
