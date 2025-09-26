<?php
require_once "api-transferencias/conexion-central.php";

try {
    echo "<h2>🔍 Verificando tabla stock_transito en BD central</h2>";
    
    // Verificar estructura
    $stmt = ConexionCentral::conectar()->prepare("DESCRIBE stock_transito");
    $stmt->execute();
    $columnas = $stmt->fetchAll();
    
    echo "<h3>Estructura de la tabla stock_transito:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Por defecto</th></tr>";
    
    foreach($columnas as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar algunos registros
    $stmt2 = ConexionCentral::conectar()->prepare("SELECT * FROM stock_transito LIMIT 3");
    $stmt2->execute();
    $registros = $stmt2->fetchAll();
    
    echo "<h3>Primeros registros:</h3><pre>";
    print_r($registros);
    echo "</pre>";
    
} catch(Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>