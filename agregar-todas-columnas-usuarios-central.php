<?php
require_once "api-transferencias/conexion-central.php";

try {
    echo "=== AGREGANDO TODAS LAS COLUMNAS FALTANTES A usuarios_central ===\n";
    
    $conexion = ConexionCentral::conectar();
    
    // Verificar estructura actual
    echo "\n=== ESTRUCTURA ACTUAL ===\n";
    $stmt = $conexion->prepare("DESCRIBE usuarios_central");
    $stmt->execute();
    $campos = $stmt->fetchAll();
    
    foreach ($campos as $campo) {
        echo "Campo: {$campo['Field']} | Tipo: {$campo['Type']}\n";
    }
    
    // Lista de columnas a agregar
    $columnas = [
        'id_local' => 'INT NULL AFTER id',
        'sucursales_asignadas' => 'TEXT NULL AFTER id_local'
    ];
    
    echo "\n=== AGREGANDO COLUMNAS FALTANTES ===\n";
    
    foreach ($columnas as $columna => $definicion) {
        // Verificar si la columna ya existe
        $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios_central LIKE '$columna'");
        $stmt->execute();
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "✅ Columna '$columna' ya existe.\n";
        } else {
            try {
                // Agregar la columna
                $sql = "ALTER TABLE usuarios_central ADD COLUMN $columna $definicion";
                echo "Ejecutando: $sql\n";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                echo "✅ Columna '$columna' agregada exitosamente.\n";
            } catch (Exception $e) {
                echo "❌ Error agregando columna '$columna': " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Verificar estructura final
    echo "\n=== ESTRUCTURA FINAL ===\n";
    $stmt = $conexion->prepare("DESCRIBE usuarios_central");
    $stmt->execute();
    $campos = $stmt->fetchAll();
    
    foreach ($campos as $campo) {
        echo "Campo: {$campo['Field']} | Tipo: {$campo['Type']} | Nulo: {$campo['Null']}\n";
    }
    
    echo "\n=== VERIFICACIÓN FINAL ===\n";
    
    // Verificar columnas específicas
    $columnasVerificar = ['id_local', 'sucursales_asignadas'];
    
    foreach ($columnasVerificar as $columna) {
        $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios_central LIKE '$columna'");
        $stmt->execute();
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "✅ Columna '$columna' existe y está lista.\n";
        } else {
            echo "❌ Columna '$columna' NO existe.\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
