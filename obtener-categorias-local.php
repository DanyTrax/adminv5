<?php
// Script para obtener las categorías de la BD local
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>📂 Obtener Categorías de BD Local</h1>";

try {
    // Conexión a la BD local
    require_once "config.php";
    require_once "modelos/conexion.php";
    $pdo = Conexion::conectar();
    
    echo "<p>✅ Conexión exitosa a la BD local</p>";
    
    // Obtener todas las categorías
    $stmt = $pdo->prepare("SELECT * FROM categorias ORDER BY id");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($categorias) {
        echo "<h2>📋 Categorías encontradas (" . count($categorias) . "):</h2>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Categoría</th><th>Fecha</th></tr>";
        
        foreach ($categorias as $cat) {
            echo "<tr>";
            echo "<td>" . $cat['id'] . "</td>";
            echo "<td>" . htmlspecialchars($cat['categoria']) . "</td>";
            echo "<td>" . $cat['fecha'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>🔧 SQL para insertar categorías:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
        echo "-- Insertar categorías iniciales\n";
        foreach ($categorias as $cat) {
            $categoria_escaped = addslashes($cat['categoria']);
            echo "INSERT INTO categorias (categoria) VALUES ('$categoria_escaped');\n";
        }
        echo "</pre>";
        
    } else {
        echo "<p style='color: red;'>❌ No se encontraron categorías en la BD local</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; }
th { background: #007bff; color: white; }
pre { font-family: 'Courier New', monospace; }
</style>";
?>
