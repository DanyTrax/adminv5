<?php
require_once "api-transferencias/conexion-central.php";

try {
    echo "=== VERIFICANDO CONEXIÓN A BD CENTRAL ===\n";
    
    $conexion = ConexionCentral::conectar();
    
    // Verificar la BD actual
    $stmt = $conexion->prepare("SELECT DATABASE() as db_name");
    $stmt->execute();
    $db = $stmt->fetch();
    echo "Base de datos conectada: " . $db['db_name'] . "\n";
    
    // Verificar si la tabla usuarios_central existe
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'usuarios_central'");
    $stmt->execute();
    $tabla = $stmt->fetch();
    
    if ($tabla) {
        echo "Tabla 'usuarios_central' existe.\n";
        
        // Verificar estructura
        $stmt = $conexion->prepare("DESCRIBE usuarios_central");
        $stmt->execute();
        $campos = $stmt->fetchAll();
        
        echo "\nCampos de la tabla:\n";
        foreach ($campos as $campo) {
            echo "- {$campo['Field']} ({$campo['Type']})\n";
        }
        
        // Verificar si sucursales_asignadas existe
        $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios_central LIKE 'sucursales_asignadas'");
        $stmt->execute();
        $columna = $stmt->fetch();
        
        if ($columna) {
            echo "\n✅ Columna 'sucursales_asignadas' existe.\n";
        } else {
            echo "\n❌ Columna 'sucursales_asignadas' NO existe.\n";
        }
        
    } else {
        echo "❌ Tabla 'usuarios_central' NO existe.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
