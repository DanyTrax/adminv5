<?php
/**
 * Verificar estructura de stock_transito en cPanel
 */

echo "<h2>🔍 Verificar - Tabla stock_transito</h2>";
echo "<hr>";

try {
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    echo "✅ <strong>Conexión exitosa</strong><br><br>";
    
    echo "<h3>📋 Estructura de la tabla 'stock_transito'</h3>";
    
    $stmt = $conexion->query("DESCRIBE stock_transito");
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach($estructura as $campo) {
        echo "<tr>";
        echo "<td><strong>{$campo['Field']}</strong></td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>{$campo['Default']}</td>";
        echo "<td>{$campo['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    echo "<h3>📊 Conteo de registros</h3>";
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM stock_transito");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "📊 <strong>Total de registros:</strong> $total<br><br>";
    
    if($total > 0) {
        echo "<h3>📋 Primeros 3 registros</h3>";
        $stmt = $conexion->query("SELECT * FROM stock_transito LIMIT 3");
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        if(!empty($registros)) {
            // Headers
            echo "<tr>";
            foreach(array_keys($registros[0]) as $header) {
                echo "<th><strong>$header</strong></th>";
            }
            echo "</tr>";
            
            // Data
            foreach($registros as $registro) {
                echo "<tr>";
                foreach($registro as $valor) {
                    echo "<td>$valor</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
    }
    
} catch(Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>🎯 Verificación completada.</strong></p>";
?>
