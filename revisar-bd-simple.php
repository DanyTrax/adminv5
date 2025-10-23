<?php
// Script simple para revisar la estructura de la BD
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Revisión Simple de Base de Datos</h2>";

try {
    // Intentar conectar sin require config.php
    $host = 'localhost';
    $dbname = 'epicosie_pruebas'; // Cambiar por el nombre real de tu BD
    $username = 'epicosie_ricaurte'; // Cambiar por tu usuario
    $password = 'm5Wwg)~M{i~*kFr{'; // Cambiar por tu contraseña
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Conexión exitosa a la base de datos</p>";
    
    // Obtener todas las tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📋 Tablas encontradas (" . count($tablas) . "):</h3>";
    echo "<ol>";
    foreach ($tablas as $tabla) {
        echo "<li><strong>" . $tabla . "</strong></li>";
    }
    echo "</ol>";
    
    // Mostrar estructura de cada tabla
    foreach ($tablas as $tabla) {
        echo "<h4>📊 Tabla: <code>" . $tabla . "</code></h4>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p><strong>Registros:</strong> " . $total . "</p>";
        
        // Mostrar algunas columnas importantes
        $stmt = $pdo->query("DESCRIBE `$tabla`");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Columnas:</strong> ";
        $nombres_columnas = array_column($columnas, 'Field');
        echo implode(', ', $nombres_columnas);
        echo "</p>";
        
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error de conexión:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    
    echo "<h4>🔧 Información de depuración:</h4>";
    echo "<p>Verifica que los datos de conexión sean correctos:</p>";
    echo "<ul>";
    echo "<li><strong>Host:</strong> localhost</li>";
    echo "<li><strong>Base de datos:</strong> epicosie_pruebas</li>";
    echo "<li><strong>Usuario:</strong> epicosie_ricaurte</li>";
    echo "<li><strong>Contraseña:</strong> [oculta]</li>";
    echo "</ul>";
    
    echo "<h4>📝 Para corregir:</h4>";
    echo "<p>1. Verifica que la base de datos existe</p>";
    echo "<p>2. Verifica que el usuario tiene permisos</p>";
    echo "<p>3. Verifica que la contraseña es correcta</p>";
    echo "<p>4. Revisa los logs del servidor para más detalles</p>";
}
?>
