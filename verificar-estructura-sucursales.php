<?php
require_once "config.php";
require_once "modelos/conexion.php";

echo "<h2>🔍 VERIFICAR ESTRUCTURA DE SUCURSALES</h2>";

try {
    $stmt = Conexion::conectar()->prepare("DESCRIBE sucursales");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Estructura de la tabla 'sucursales':</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>" . $columna['Field'] . "</td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "<td>" . $columna['Default'] . "</td>";
        echo "<td>" . $columna['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Obtener datos de sucursales
    echo "<h3>📊 Datos de sucursales:</h3>";
    $stmt = Conexion::conectar()->prepare("SELECT * FROM sucursales LIMIT 3");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($sucursales) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach (array_keys($sucursales[0]) as $campo) {
            echo "<th>" . $campo . "</th>";
        }
        echo "</tr>";
        
        foreach ($sucursales as $sucursal) {
            echo "<tr>";
            foreach ($sucursal as $valor) {
                echo "<td>" . htmlspecialchars($valor) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay sucursales registradas.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
