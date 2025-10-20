<?php
/**
 * SCRIPT PARA CREAR TABLA DE USUARIOS EN BD CENTRAL
 * 
 * Este script crea la tabla de usuarios en la base de datos central
 * para gestionar usuarios que se sincronizarán con las sucursales locales.
 */

require_once "api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();
    
    echo "<h2>🔧 Creando Tabla de Usuarios en BD Central</h2>";
    
    // Crear tabla de usuarios en BD central
    $createTable = "
    CREATE TABLE IF NOT EXISTS usuarios_central (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        perfil ENUM('Administrador', 'Vendedor', 'Contador', 'Transportador', 'Limitado') NOT NULL,
        foto VARCHAR(255) DEFAULT 'vistas/img/usuarios/default/anonymous.png',
        sucursal_id INT NOT NULL,
        telefono VARCHAR(20),
        direccion TEXT,
        activo BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        sincronizado BOOLEAN DEFAULT FALSE,
        fecha_sincronizacion TIMESTAMP NULL,
        observaciones TEXT,
        INDEX idx_sucursal (sucursal_id),
        INDEX idx_perfil (perfil),
        INDEX idx_activo (activo),
        INDEX idx_sincronizado (sincronizado),
        FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
    ";
    
    $pdo->exec($createTable);
    echo "✅ Tabla 'usuarios_central' creada exitosamente<br>";
    
    // Crear tabla de sincronización
    $createSyncTable = "
    CREATE TABLE IF NOT EXISTS sincronizacion_usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_central_id INT NOT NULL,
        sucursal_destino_id INT NOT NULL,
        usuario_local_id INT,
        estado ENUM('pendiente', 'sincronizado', 'error') DEFAULT 'pendiente',
        fecha_sincronizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        error_mensaje TEXT,
        FOREIGN KEY (usuario_central_id) REFERENCES usuarios_central(id),
        FOREIGN KEY (sucursal_destino_id) REFERENCES sucursales(id),
        UNIQUE KEY unique_usuario_sucursal (usuario_central_id, sucursal_destino_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
    ";
    
    $pdo->exec($createSyncTable);
    echo "✅ Tabla 'sincronizacion_usuarios' creada exitosamente<br>";
    
    echo "<h3>🎉 ¡Tablas creadas exitosamente!</h3>";
    echo "<p><strong>Próximos pasos:</strong></p>";
    echo "<ul>";
    echo "<li>Crear controlador para gestión de usuarios centrales</li>";
    echo "<li>Crear vista en barra superior</li>";
    echo "<li>Implementar sincronización con BD local</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Error en la creación:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
