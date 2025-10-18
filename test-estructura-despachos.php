<?php
// Test para verificar la estructura de la tabla despachos

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test Estructura Tabla Despachos\n";
echo "==================================\n\n";

require_once "api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    echo "✅ Conexión exitosa\n\n";
    
    // Obtener estructura de la tabla
    $stmt = $conexion->prepare("DESCRIBE despachos");
    $stmt->execute();
    $columnas = $stmt->fetchAll();
    
    echo "📋 Estructura de la tabla 'despachos':\n";
    echo "-------------------------------------\n";
    foreach($columnas as $columna) {
        echo "- " . $columna['Field'] . " (" . $columna['Type'] . ")\n";
    }
    
    echo "\n🔍 Verificar despacho ID 21:\n";
    echo "----------------------------\n";
    
    $stmt = $conexion->prepare("SELECT * FROM despachos WHERE id = 21");
    $stmt->execute();
    $despacho = $stmt->fetch();
    
    if($despacho) {
        echo "✅ Despacho encontrado:\n";
        foreach($despacho as $key => $value) {
            if(!is_numeric($key)) {
                echo "- $key: $value\n";
            }
        }
    } else {
        echo "❌ Despacho no encontrado\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🏁 Test completado\n";
?>
