<?php
// Script para verificar los datos reales en la tabla sucursal_local
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Verificar Datos en Tabla sucursal_local</h1>";

try {
    // Conexión a la BD local
    require_once "modelos/conexion.php";
    $pdo = Conexion::conectar();
    
    echo "<p>✅ Conexión exitosa a la BD local</p>";
    
    // Obtener datos de la tabla sucursal_local
    $stmt = $pdo->prepare("SELECT * FROM sucursal_local WHERE id = 1");
    $stmt->execute();
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($datos) {
        echo "<h2>📋 Datos encontrados en sucursal_local:</h2>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Valor</th></tr>";
        
        foreach ($datos as $campo => $valor) {
            $color = '';
            if (in_array($campo, ['usuario_bd', 'password_bd', 'nombre_bd', 'host_bd', 'puerto_bd'])) {
                $color = 'background: #e8f5e8;'; // Verde claro para campos de BD
            }
            echo "<tr style='$color'>";
            echo "<td><strong>$campo</strong></td>";
            echo "<td>" . htmlspecialchars($valor ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>🔍 Campos de BD específicos:</h3>";
        echo "<ul>";
        echo "<li><strong>usuario_bd:</strong> '" . ($datos['usuario_bd'] ?? 'NULL') . "'</li>";
        echo "<li><strong>password_bd:</strong> '" . ($datos['password_bd'] ?? 'NULL') . "'</li>";
        echo "<li><strong>nombre_bd:</strong> '" . ($datos['nombre_bd'] ?? 'NULL') . "'</li>";
        echo "<li><strong>host_bd:</strong> '" . ($datos['host_bd'] ?? 'NULL') . "'</li>";
        echo "<li><strong>puerto_bd:</strong> " . ($datos['puerto_bd'] ?? 'NULL') . "</li>";
        echo "</ul>";
        
        // Verificar si los campos están vacíos
        $camposVacios = [];
        if (empty($datos['usuario_bd'])) $camposVacios[] = 'usuario_bd';
        if (empty($datos['password_bd'])) $camposVacios[] = 'password_bd';
        if (empty($datos['nombre_bd'])) $camposVacios[] = 'nombre_bd';
        if (empty($datos['host_bd'])) $camposVacios[] = 'host_bd';
        if (empty($datos['puerto_bd'])) $camposVacios[] = 'puerto_bd';
        
        if (!empty($camposVacios)) {
            echo "<p style='color: green;'>✅ Todos los campos de BD tienen valores</p>";
        } else {
            echo "<p style='color: red;'>❌ Campos vacíos: " . implode(', ', $camposVacios) . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ No se encontraron datos en sucursal_local</p>";
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
