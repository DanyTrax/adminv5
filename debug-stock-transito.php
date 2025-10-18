<?php
/**
 * Script para verificar la estructura de la tabla stock_transito
 */

echo "<h2>🔍 Debug - Estructura de Tabla stock_transito</h2>\n";
echo "<hr>\n";

try {
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    echo "✅ <strong>Conexión a base central exitosa</strong><br>\n";
    
    echo "<h3>📋 Estructura de la tabla 'stock_transito'</h3>\n";
    
    $stmt = $conexion->query("DESCRIBE stock_transito");
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    foreach($estructura as $campo) {
        echo "<tr>";
        echo "<td><strong>{$campo['Field']}</strong></td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>{$campo['Default']}</td>";
        echo "<td>{$campo['Extra']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<h3>📊 Conteo de registros</h3>\n";
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM stock_transito");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "📊 <strong>Total de registros en stock_transito:</strong> $total<br>\n";
    
    if($total > 0) {
        echo "<h3>📋 Primeros 3 registros (para verificar estructura)</h3>\n";
        $stmt = $conexion->query("SELECT * FROM stock_transito ORDER BY id DESC LIMIT 3");
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        if(!empty($registros)) {
            // Mostrar encabezados
            echo "<tr>";
            foreach(array_keys($registros[0]) as $campo) {
                echo "<th>$campo</th>";
            }
            echo "</tr>\n";
            
            // Mostrar datos
            foreach($registros as $registro) {
                echo "<tr>";
                foreach($registro as $valor) {
                    $valorMostrar = is_null($valor) ? 'NULL' : (strlen($valor) > 30 ? substr($valor, 0, 30) . '...' : $valor);
                    echo "<td>$valorMostrar</td>";
                }
                echo "</tr>\n";
            }
        }
        echo "</table>\n";
    }
    
} catch(Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<hr>\n";
echo "<p><strong>🎯 Análisis completado.</strong></p>\n";
?>
