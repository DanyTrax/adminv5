<?php
// Script para revisar la estructura completa de la base de datos
require_once "config.php";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Estructura Completa de la Base de Datos</h2>";
    echo "<p><strong>Base de datos:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>Host:</strong> " . DB_HOST . ":" . DB_PORT . "</p>";
    
    // Obtener todas las tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📋 Tablas encontradas (" . count($tablas) . "):</h3>";
    echo "<ol>";
    foreach ($tablas as $tabla) {
        echo "<li><strong>" . $tabla . "</strong></li>";
    }
    echo "</ol>";
    
    // Para cada tabla, mostrar estructura
    foreach ($tablas as $tabla) {
        echo "<h4>📊 Tabla: <code>" . $tabla . "</code></h4>";
        
        // Obtener estructura
        $stmt = $pdo->query("DESCRIBE `$tabla`");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th>";
        echo "</tr>";
        
        foreach ($columnas as $columna) {
            echo "<tr>";
            echo "<td><strong>" . $columna['Field'] . "</strong></td>";
            echo "<td>" . $columna['Type'] . "</td>";
            echo "<td>" . $columna['Null'] . "</td>";
            echo "<td>" . $columna['Key'] . "</td>";
            echo "<td>" . ($columna['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $columna['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p><strong>Registros:</strong> " . $total . "</p>";
        
        echo "<hr>";
    }
    
    // Generar SQL para crear todas las tablas
    echo "<h3>🔧 SQL para crear todas las tablas:</h3>";
    echo "<textarea style='width: 100%; height: 300px; font-family: monospace; font-size: 12px;'>";
    
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SHOW CREATE TABLE `$tabla`");
        $create = $stmt->fetch(PDO::FETCH_ASSOC);
        echo $create['Create Table'] . ";\n\n";
    }
    
    echo "</textarea>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
