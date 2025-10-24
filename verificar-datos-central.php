<?php
// Script para verificar los datos en la BD central
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Verificar Datos en BD Central</h1>";

try {
    // Conexión a la BD central
    require_once "api-transferencias/conexion-central.php";
    $pdo = ConexionCentral::conectar();
    
    echo "<p>✅ Conexión exitosa a la BD central</p>";
    
    // Obtener datos de la tabla sucursales
    $stmt = $pdo->prepare("SELECT * FROM sucursales WHERE codigo_sucursal = 'SUC002'");
    $stmt->execute();
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($datos) {
        echo "<h2>📋 Datos encontrados en BD central para SUC002:</h2>";
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
        
        echo "<h3>🔍 Campos de BD específicos en BD central:</h3>";
        echo "<ul>";
        echo "<li><strong>usuario_bd:</strong> '" . ($datos['usuario_bd'] ?? 'NULL') . "'</li>";
        echo "<li><strong>password_bd:</strong> '" . ($datos['password_bd'] ?? 'NULL') . "'</li>";
        echo "<li><strong>nombre_bd:</strong> '" . ($datos['nombre_bd'] ?? 'NULL') . "'</li>";
        echo "<li><strong>host_bd:</strong> '" . ($datos['host_bd'] ?? 'NULL') . "'</li>";
        echo "<li><strong>puerto_bd:</strong> " . ($datos['puerto_bd'] ?? 'NULL') . "</li>";
        echo "</ul>";
        
        // Verificar si los campos tienen datos reales o genéricos
        $datosReales = true;
        if ($datos['usuario_bd'] === 'usuario_bd' || empty($datos['usuario_bd'])) {
            $datosReales = false;
            echo "<p style='color: red;'>❌ usuario_bd tiene valor genérico o vacío</p>";
        }
        if ($datos['nombre_bd'] === 'nombre_bd' || empty($datos['nombre_bd'])) {
            $datosReales = false;
            echo "<p style='color: red;'>❌ nombre_bd tiene valor genérico o vacío</p>";
        }
        
        if ($datosReales) {
            echo "<p style='color: green;'>✅ Los datos de BD son reales</p>";
        } else {
            echo "<p style='color: red;'>❌ Los datos de BD son genéricos o vacíos</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ No se encontraron datos para SUC002 en BD central</p>";
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
