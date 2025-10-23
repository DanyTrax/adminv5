<?php
// Script ultra simple para listar tablas
echo "<h2>📋 Lista de Tablas</h2>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=epicosie_pruebas;charset=utf8", "epicosie_ricaurte", "m5Wwg)~M{i~*kFr{");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>✅ Conexión exitosa</p>";
    echo "<p><strong>Total de tablas:</strong> " . count($tablas) . "</p>";
    
    echo "<h3>Lista de tablas:</h3>";
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li>" . $tabla . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
