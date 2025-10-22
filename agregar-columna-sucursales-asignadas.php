<?php
require_once "api-transferencias/conexion-central.php";

try {
    echo "=== AGREGANDO COLUMNA sucursales_asignadas A usuarios_central ===\n";
    
    $conexion = ConexionCentral::conectar();
    
    // Verificar si la columna ya existe
    $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios_central LIKE 'sucursales_asignadas'");
    $stmt->execute();
    $existe = $stmt->fetch();
    
    if ($existe) {
        echo "La columna 'sucursales_asignadas' ya existe.\n";
    } else {
        // Agregar la columna
        $stmt = $conexion->prepare("ALTER TABLE usuarios_central ADD COLUMN sucursales_asignadas TEXT NULL AFTER id_local");
        $stmt->execute();
        echo "Columna 'sucursales_asignadas' agregada exitosamente.\n";
    }
    
    // Verificar la estructura actualizada
    echo "\n=== ESTRUCTURA ACTUALIZADA ===\n";
    $stmt = $conexion->prepare("DESCRIBE usuarios_central");
    $stmt->execute();
    $campos = $stmt->fetchAll();
    
    foreach ($campos as $campo) {
        echo "Campo: {$campo['Field']} | Tipo: {$campo['Type']} | Nulo: {$campo['Null']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
