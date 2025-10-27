<?php
/**
 * Script para corregir el campo perfil en usuarios_central
 * Agregar 'Especial' a los valores permitidos del ENUM
 */

require_once "api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();
    
    echo "<h2>🔧 Corrigiendo campo perfil en usuarios_central</h2>";
    
    // Modificar el campo perfil para incluir 'Especial'
    $sql = "ALTER TABLE usuarios_central 
            MODIFY COLUMN perfil ENUM('Administrador', 'Especial', 'Vendedor', 'Contador', 'Transportador', 'Limitado') NOT NULL";
    
    $pdo->exec($sql);
    echo "✅ Campo 'perfil' actualizado exitosamente<br>";
    echo "📋 Valores permitidos: Administrador, Especial, Vendedor, Contador, Transportador, Limitado<br>";
    
    // Verificar la estructura actual
    $stmt = $pdo->query("DESCRIBE usuarios_central");
    $columnas = $stmt->fetchAll();
    
    echo "<h3>📊 Estructura actual de la tabla usuarios_central:</h3>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
    
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>" . $columna['Field'] . "</td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "<td>" . $columna['Default'] . "</td>";
        echo "<td>" . $columna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h6>🎉 Corrección completada exitosamente</h6>";
    echo "<p>El campo 'perfil' ahora acepta el valor 'Especial' y la creación de usuarios centrales debería funcionar correctamente.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; }
th { background: #007bff; color: white; }
</style>";
?>
