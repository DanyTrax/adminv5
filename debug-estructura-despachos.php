<?php
require_once "api-transferencias/conexion-central.php";

try {
    echo "<h2>🔍 Estructura completa de tabla despachos</h2>";
    
    $stmt = ConexionCentral::conectar()->prepare("DESCRIBE despachos");
    $stmt->execute();
    $columnas = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th></tr>";
    
    foreach($columnas as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar el despacho específico después de aceptar
    echo "<h3>🔍 Estado actual del despacho ID 7:</h3>";
    $stmt2 = ConexionCentral::conectar()->prepare("SELECT * FROM despachos WHERE id = 7");
    $stmt2->execute();
    $despacho = $stmt2->fetch();
    
    if($despacho) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        foreach($despacho as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
    }
    
} catch(Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>