<?php
// Script para generar el SQL completo para el instalador
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Generador de SQL para Instalador</h1>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=epicosie_pruebas;charset=utf8", "epicosie_ricaurte", "m5Wwg)~M{i~*kFr{");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Conexión exitosa</p>";
    
    // Obtener todas las tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>📋 SQL Completo para Instalador</h2>";
    echo "<p>Este es el SQL que debe usar el instalador para crear todas las tablas:</p>";
    
    echo "<textarea style='width: 100%; height: 600px; font-family: monospace; font-size: 12px; background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px;' readonly>";
    
    echo "-- SQL para crear todas las tablas del sistema\n";
    echo "-- Generado automáticamente desde la BD local\n\n";
    
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SHOW CREATE TABLE `$tabla`");
        $create = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "-- ============================================\n";
        echo "-- Tabla: $tabla\n";
        echo "-- ============================================\n";
        echo $create['Create Table'] . ";\n\n";
    }
    
    echo "</textarea>";
    
    echo "<h3>📝 Instrucciones para usar en el instalador:</h3>";
    echo "<ol>";
    echo "<li>Copia todo el SQL del textarea anterior</li>";
    echo "<li>Reemplaza la función <code>crearTablasBD()</code> en el instalador</li>";
    echo "<li>Divide cada CREATE TABLE en un elemento del array <code>\$sql_tablas</code></li>";
    echo "<li>Verifica que no haya errores de sintaxis</li>";
    echo "</ol>";
    
    echo "<h3>🔍 Verificación:</h3>";
    echo "<p>Total de tablas: <strong>" . count($tablas) . "</strong></p>";
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li>$tabla</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
