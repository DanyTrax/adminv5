<?php
// Script para crear la tabla categorias_central en la BD central
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>📂 Crear Tabla Categorías Central</h1>";

try {
    // Conexión a la BD central
    require_once "api-transferencias/conexion-central.php";
    $pdo = ConexionCentral::conectar();
    
    echo "<p>✅ Conexión exitosa a la BD central</p>";
    
    // Crear tabla categorias_central
    $sql = "CREATE TABLE IF NOT EXISTS categorias_central (
        id INT(11) NOT NULL AUTO_INCREMENT,
        categoria VARCHAR(255) NOT NULL,
        descripcion TEXT DEFAULT NULL,
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        sincronizado TINYINT(1) DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY unique_categoria (categoria),
        KEY idx_activo (activo),
        KEY idx_sincronizado (sincronizado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci";
    
    $pdo->exec($sql);
    echo "<p>✅ Tabla 'categorias_central' creada exitosamente</p>";
    
    // Insertar categorías básicas si la tabla está vacía
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categorias_central");
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['total'] == 0) {
        echo "<p>📝 Insertando categorías básicas...</p>";
        
        $categorias_basicas = [
            ['categoria' => 'General', 'descripcion' => 'Categoría general para productos diversos'],
            ['categoria' => 'Electrónicos', 'descripcion' => 'Dispositivos electrónicos y tecnología'],
            ['categoria' => 'Ropa', 'descripcion' => 'Vestimenta y accesorios de moda'],
            ['categoria' => 'Hogar', 'descripcion' => 'Artículos para el hogar y decoración'],
            ['categoria' => 'Deportes', 'descripcion' => 'Equipos y accesorios deportivos'],
            ['categoria' => 'Libros', 'descripcion' => 'Libros y material educativo'],
            ['categoria' => 'Juguetes', 'descripcion' => 'Juguetes y entretenimiento'],
            ['categoria' => 'Alimentación', 'descripcion' => 'Productos alimenticios y bebidas'],
            ['categoria' => 'Belleza', 'descripcion' => 'Productos de belleza y cuidado personal'],
            ['categoria' => 'Automotriz', 'descripcion' => 'Repuestos y accesorios automotrices']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO categorias_central (categoria, descripcion, activo, sincronizado) VALUES (?, ?, 1, 0)");
        
        foreach ($categorias_basicas as $cat) {
            $stmt->execute([$cat['categoria'], $cat['descripcion']]);
        }
        
        echo "<p>✅ " . count($categorias_basicas) . " categorías básicas insertadas</p>";
    } else {
        echo "<p>ℹ️ La tabla ya contiene " . $resultado['total'] . " categorías</p>";
    }
    
    // Mostrar estructura de la tabla
    echo "<h2>🔧 Estructura de la tabla:</h2>";
    $stmt = $pdo->prepare("DESCRIBE categorias_central");
    $stmt->execute();
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
    
    foreach ($campos as $campo) {
        echo "<tr>";
        echo "<td><strong>" . $campo['Field'] . "</strong></td>";
        echo "<td>" . $campo['Type'] . "</td>";
        echo "<td>" . $campo['Null'] . "</td>";
        echo "<td>" . $campo['Key'] . "</td>";
        echo "<td>" . ($campo['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $campo['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar categorías existentes
    $stmt = $pdo->prepare("SELECT * FROM categorias_central ORDER BY categoria");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($categorias) {
        echo "<h2>📋 Categorías en BD central:</h2>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Categoría</th><th>Descripción</th><th>Activo</th><th>Sincronizado</th><th>Fecha Creación</th></tr>";
        
        foreach ($categorias as $cat) {
            $activo = $cat['activo'] ? '✅ Sí' : '❌ No';
            $sincronizado = $cat['sincronizado'] ? '✅ Sí' : '❌ No';
            echo "<tr>";
            echo "<td>" . $cat['id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($cat['categoria']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($cat['descripcion'] ?? '') . "</td>";
            echo "<td>" . $activo . "</td>";
            echo "<td>" . $sincronizado . "</td>";
            echo "<td>" . $cat['fecha_creacion'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
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
</style>";
?>
