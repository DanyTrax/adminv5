<?php
require_once "api-transferencias/conexion-central.php";

try {
    $stmt = ConexionCentral::conectar()->prepare("DESCRIBE despachos");
    $stmt->execute();
    $columnas = $stmt->fetchAll();
    
    echo "<h2>Estructura de la tabla 'despachos':</h2>";
    echo "<table border='1'>";
    foreach($columnas as $columna) {
        echo "<tr><td>" . $columna['Field'] . "</td><td>" . $columna['Type'] . "</td></tr>";
    }
    echo "</table>";
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>