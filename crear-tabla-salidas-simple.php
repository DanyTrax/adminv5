<?php
/**
 * Script para crear tabla salidas_inventario simplificada
 */

echo "<h2>📦 Crear Tabla Salidas de Inventario Simplificada</h2>\n";

try {
    // Conexión a la BD
    require_once "modelos/conexion.php";
    $pdo = Conexion::conectar();
    
    echo "<p>✅ Conexión exitosa a la BD</p>\n";
    
    // Crear tabla salidas_inventario simplificada
    $sql = "CREATE TABLE IF NOT EXISTS salidas_inventario (
        id INT(11) NOT NULL AUTO_INCREMENT,
        id_usuario INT(11) NOT NULL,
        id_producto INT(11) NOT NULL,
        cantidad INT(11) NOT NULL,
        descripcion TEXT DEFAULT NULL,
        numero_remision VARCHAR(50) DEFAULT NULL,
        fecha_salida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_usuario (id_usuario),
        KEY idx_producto (id_producto),
        KEY idx_fecha (fecha_salida),
        CONSTRAINT fk_salidas_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE,
        CONSTRAINT fk_salidas_producto FOREIGN KEY (id_producto) REFERENCES productos (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci";
    
    $pdo->exec($sql);
    echo "<p>✅ Tabla 'salidas_inventario' creada exitosamente</p>\n";
    
    // Mostrar estructura de la tabla
    echo "<h3>🔧 Estructura de la tabla:</h3>\n";
    $stmt = $pdo->prepare("DESCRIBE salidas_inventario");
    $stmt->execute();
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>\n";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>\n";
    
    foreach ($campos as $campo) {
        echo "<tr>\n";
        echo "<td><strong>" . $campo['Field'] . "</strong></td>\n";
        echo "<td>" . $campo['Type'] . "</td>\n";
        echo "<td>" . $campo['Null'] . "</td>\n";
        echo "<td>" . $campo['Key'] . "</td>\n";
        echo "<td>" . ($campo['Default'] ?? 'NULL') . "</td>\n";
        echo "<td>" . $campo['Extra'] . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Verificar si hay datos existentes
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM salidas_inventario");
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>ℹ️ La tabla contiene " . $resultado['total'] . " registros</p>\n";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h6>🎉 Tabla simplificada creada exitosamente</h6>\n";
    echo "<p>La tabla <strong>salidas_inventario</strong> está lista para el módulo simplificado.</p>\n";
    echo "<p><strong>Características:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ Sin estadísticas</li>\n";
    echo "<li>✅ Sin estados</li>\n";
    echo "<li>✅ Solo registro básico</li>\n";
    echo "<li>✅ Descuento de stock</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; }
th { background: #007bff; color: white; }
</style>";
?>
