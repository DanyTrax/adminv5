<?php
/**
 * Script para agregar campo id_sucursal a la tabla personalizacion_colores
 * Ejecutar una sola vez desde el navegador o línea de comandos
 */

require_once __DIR__ . "/api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();
    
    // Verificar si el campo ya existe
    $stmt = $pdo->prepare("SHOW COLUMNS FROM personalizacion_colores LIKE 'id_sucursal'");
    $stmt->execute();
    $existe = $stmt->fetch();
    
    if ($existe) {
        echo "✅ El campo 'id_sucursal' ya existe en la tabla personalizacion_colores.\n";
        exit;
    }
    
    // Agregar campo id_sucursal (NULL para permitir configuraciones globales)
    $stmt = $pdo->prepare("
        ALTER TABLE personalizacion_colores 
        ADD COLUMN id_sucursal INT(11) NULL DEFAULT NULL AFTER activo,
        ADD INDEX idx_id_sucursal (id_sucursal)
    ");
    
    $stmt->execute();
    
    echo "✅ Campo 'id_sucursal' agregado exitosamente a la tabla personalizacion_colores.\n";
    echo "✅ Índice creado para mejorar el rendimiento de consultas.\n";
    
    // Actualizar configuraciones existentes para que sean globales (NULL)
    $stmt = $pdo->prepare("UPDATE personalizacion_colores SET id_sucursal = NULL WHERE id_sucursal IS NULL");
    $stmt->execute();
    
    echo "✅ Configuraciones existentes actualizadas (marcadas como globales).\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    error_log("Error en agregar-campo-sucursal-personalizacion: " . $e->getMessage());
}

