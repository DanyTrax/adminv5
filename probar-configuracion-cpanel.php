<?php
/**
 * Script para probar directamente en el servidor con configuración de cPanel
 */

require_once "api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();
    
    echo "<h2>🔍 Probando configuración SQL del cPanel</h2>";
    
    // Verificar configuración SQL
    echo "<h3>📊 Configuración SQL:</h3>";
    $stmt = $pdo->query("SELECT @@sql_mode");
    $sqlMode = $stmt->fetch();
    echo "<p><strong>SQL Mode:</strong> " . $sqlMode['@@sql_mode'] . "</p>";
    
    $stmt = $pdo->query("SELECT @@version");
    $version = $stmt->fetch();
    echo "<p><strong>MySQL Version:</strong> " . $version['@@version'] . "</p>";
    
    $stmt = $pdo->query("SELECT @@character_set_database");
    $charset = $stmt->fetch();
    echo "<p><strong>Character Set:</strong> " . $charset['@@character_set_database'] . "</p>";
    
    // Verificar estructura actual de la tabla
    echo "<h3>📋 Estructura actual de usuarios_central:</h3>";
    $stmt = $pdo->query("SHOW CREATE TABLE usuarios_central");
    $createTable = $stmt->fetch();
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($createTable['Create Table']);
    echo "</pre>";
    
    // Probar inserción con diferentes enfoques
    echo "<h3>🧪 Probando diferentes enfoques de inserción:</h3>";
    
    // Enfoque 1: INSERT directo
    echo "<h4>Enfoque 1: INSERT directo</h4>";
    try {
        $sql = "INSERT INTO usuarios_central (nombre, usuario, password, perfil, foto, telefono, direccion, activo, sincronizado, fecha_creacion) VALUES ('test1', 'test1_" . time() . "', 'password123', 'Especial', 'default.png', '123456789', 'test', 1, 0, NOW())";
        $pdo->exec($sql);
        echo "✅ INSERT directo exitoso<br>";
        
        // Limpiar
        $pdo->exec("DELETE FROM usuarios_central WHERE usuario LIKE 'test1_%'");
        
    } catch (Exception $e) {
        echo "❌ INSERT directo falló: " . $e->getMessage() . "<br>";
    }
    
    // Enfoque 2: Prepared statement con bindValue
    echo "<h4>Enfoque 2: Prepared statement con bindValue</h4>";
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios_central (nombre, usuario, password, perfil, foto, telefono, direccion, activo, sincronizado, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->bindValue(1, 'test2', PDO::PARAM_STR);
        $stmt->bindValue(2, 'test2_' . time(), PDO::PARAM_STR);
        $stmt->bindValue(3, 'password123', PDO::PARAM_STR);
        $stmt->bindValue(4, 'Especial', PDO::PARAM_STR);
        $stmt->bindValue(5, 'default.png', PDO::PARAM_STR);
        $stmt->bindValue(6, '123456789', PDO::PARAM_STR);
        $stmt->bindValue(7, 'test', PDO::PARAM_STR);
        $stmt->bindValue(8, 1, PDO::PARAM_INT);
        $stmt->bindValue(9, 0, PDO::PARAM_INT);
        
        $stmt->execute();
        echo "✅ Prepared statement con bindValue exitoso<br>";
        
        // Limpiar
        $pdo->exec("DELETE FROM usuarios_central WHERE usuario LIKE 'test2_%'");
        
    } catch (Exception $e) {
        echo "❌ Prepared statement con bindValue falló: " . $e->getMessage() . "<br>";
    }
    
    // Enfoque 3: Con nombres de parámetros
    echo "<h4>Enfoque 3: Con nombres de parámetros</h4>";
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios_central (nombre, usuario, password, perfil, foto, telefono, direccion, activo, sincronizado, fecha_creacion) VALUES (:nombre, :usuario, :password, :perfil, :foto, :telefono, :direccion, :activo, :sincronizado, NOW())");
        
        $stmt->bindValue(':nombre', 'test3', PDO::PARAM_STR);
        $stmt->bindValue(':usuario', 'test3_' . time(), PDO::PARAM_STR);
        $stmt->bindValue(':password', 'password123', PDO::PARAM_STR);
        $stmt->bindValue(':perfil', 'Especial', PDO::PARAM_STR);
        $stmt->bindValue(':foto', 'default.png', PDO::PARAM_STR);
        $stmt->bindValue(':telefono', '123456789', PDO::PARAM_STR);
        $stmt->bindValue(':direccion', 'test', PDO::PARAM_STR);
        $stmt->bindValue(':activo', 1, PDO::PARAM_INT);
        $stmt->bindValue(':sincronizado', 0, PDO::PARAM_INT);
        
        $stmt->execute();
        echo "✅ Con nombres de parámetros exitoso<br>";
        
        // Limpiar
        $pdo->exec("DELETE FROM usuarios_central WHERE usuario LIKE 'test3_%'");
        
    } catch (Exception $e) {
        echo "❌ Con nombres de parámetros falló: " . $e->getMessage() . "<br>";
    }
    
    // Verificar si hay triggers o constraints
    echo "<h3>🔍 Verificando triggers y constraints:</h3>";
    $stmt = $pdo->query("SHOW TRIGGERS LIKE 'usuarios_central'");
    $triggers = $stmt->fetchAll();
    
    if (empty($triggers)) {
        echo "✅ No hay triggers en la tabla<br>";
    } else {
        echo "⚠️ Triggers encontrados:<br>";
        foreach ($triggers as $trigger) {
            echo "- " . $trigger['Trigger'] . "<br>";
        }
    }
    
    // Verificar foreign keys
    $stmt = $pdo->query("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'usuarios_central' AND CONSTRAINT_SCHEMA = DATABASE()");
    $foreignKeys = $stmt->fetchAll();
    
    if (empty($foreignKeys)) {
        echo "✅ No hay foreign keys en la tabla<br>";
    } else {
        echo "⚠️ Foreign keys encontrados:<br>";
        foreach ($foreignKeys as $fk) {
            echo "- " . $fk['COLUMN_NAME'] . " -> " . $fk['REFERENCED_TABLE_NAME'] . "." . $fk['REFERENCED_COLUMN_NAME'] . "<br>";
        }
    }
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h6>💡 Posibles causas en cPanel:</h6>";
    echo "<ul>";
    echo "<li><strong>SQL Mode:</strong> Puede estar en modo estricto que rechaza valores ENUM</li>";
    echo "<li><strong>Character Set:</strong> Problemas de codificación de caracteres</li>";
    echo "<li><strong>Triggers:</strong> Triggers que modifican datos durante inserción</li>";
    echo "<li><strong>Foreign Keys:</strong> Constraints que causan problemas</li>";
    echo "<li><strong>Versión MySQL:</strong> Versión muy antigua o muy nueva</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3, h4 { color: #333; }
pre { font-size: 12px; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>";
?>
