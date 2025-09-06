<?php

/**
 * @const NOMBRE_SUCURSAL El nombre único de esta sucursal.
 * Cambia este valor en cada una de tus 4 copias del software.
 */
define('NOMBRE_SUCURSAL', 'Sucursal Principal'); // O 'Sucursal Norte', etc.

/**
 * Función para obtener la URL del API dinámicamente
 */
function obtenerApiUrl() {
    try {
        // Intentar obtener desde configuración local primero
        require_once __DIR__ . "/modelos/conexion.php";
        $stmt = Conexion::conectar()->prepare("SELECT url_api FROM sucursal_local WHERE id = 1 LIMIT 1");
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado && !empty($resultado['url_api'])) {
            return rtrim($resultado['url_api'], '/') . '/';
        }
    } catch (Exception $e) {
        error_log("Error obteniendo API URL local: " . $e->getMessage());
    }
    
    try {
        // Si no hay configuración local, intentar desde BD central
        require_once __DIR__ . "/api-transferencias/conexion-central.php";
        $pdo = ConexionCentral::conectar();
        $stmt = $pdo->prepare("SELECT url_api FROM sucursales WHERE es_principal = 1 LIMIT 1");
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado && !empty($resultado['url_api'])) {
            return rtrim($resultado['url_api'], '/') . '/';
        }
    } catch (Exception $e) {
        error_log("Error obteniendo API URL central: " . $e->getMessage());
    }
    
    // URL por defecto como fallback
    return 'https://pruebas2.acplasticos.com/api-transferencias/';
}

/**
 * @const API_URL La dirección web completa de la carpeta donde subiste tu API.
 * Ahora se obtiene dinámicamente desde la base de datos.
 */
define('API_URL', obtenerApiUrl());

?>