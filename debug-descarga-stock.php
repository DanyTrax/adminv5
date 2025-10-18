<?php
// Debug para descarga de stock
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug - Descarga de Stock</h2>";

try {
    require_once "api-transferencias/conexion-central.php";
    
    $conexionCentral = ConexionCentral::conectar();
    echo "✅ Conexión a base central exitosa<br><br>";
    
    // 1. Verificar estructura de tabla stock_transito
    echo "<h3>📋 Estructura de tabla 'stock_transito'</h3>";
    $stmt = $conexionCentral->prepare("DESCRIBE stock_transito");
    $stmt->execute();
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach($campos as $campo) {
        echo "<tr>";
        echo "<td>" . $campo['Field'] . "</td>";
        echo "<td>" . $campo['Type'] . "</td>";
        echo "<td>" . $campo['Null'] . "</td>";
        echo "<td>" . $campo['Key'] . "</td>";
        echo "<td>" . $campo['Default'] . "</td>";
        echo "<td>" . $campo['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // 2. Ver datos de stock_transito
    echo "<h3>📊 Datos de stock_transito</h3>";
    $stmt = $conexionCentral->prepare("SELECT * FROM stock_transito LIMIT 3");
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($stocks) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach(array_keys($stocks[0]) as $campo) {
            echo "<th>$campo</th>";
        }
        echo "</tr>";
        
        foreach($stocks as $stock) {
            echo "<tr>";
            foreach($stock as $valor) {
                echo "<td>" . htmlspecialchars($valor ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "❌ No hay datos en stock_transito<br><br>";
    }
    
    // 3. Simular validación de cantidad
    if(isset($_GET['id']) && isset($_GET['cantidad'])) {
        $idStock = intval($_GET['id']);
        $cantidad = intval($_GET['cantidad']);
        
        echo "<h3>🧪 Simulación de Validación</h3>";
        echo "ID Stock: $idStock<br>";
        echo "Cantidad solicitada: $cantidad<br><br>";
        
        $stmt = $conexionCentral->prepare("SELECT * FROM stock_transito WHERE id = ?");
        $stmt->execute([$idStock]);
        $stock = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($stock) {
            echo "✅ Stock encontrado:<br>";
            echo "Cantidad disponible: " . ($stock['cantidad_disponible'] ?? 'NULL') . "<br>";
            echo "Código producto: " . ($stock['codigo_producto'] ?? 'NULL') . "<br>";
            echo "Descripción: " . ($stock['descripcion_producto'] ?? 'NULL') . "<br><br>";
            
            // Validaciones
            echo "<h4>🔍 Validaciones:</h4>";
            
            if($cantidad <= 0) {
                echo "❌ Cantidad debe ser mayor a 0<br>";
            } else {
                echo "✅ Cantidad mayor a 0<br>";
            }
            
            if($cantidad > ($stock['cantidad_disponible'] ?? 0)) {
                echo "❌ Cantidad excede la disponible (" . ($stock['cantidad_disponible'] ?? 0) . ")<br>";
            } else {
                echo "✅ Cantidad dentro del rango disponible<br>";
            }
            
        } else {
            echo "❌ Stock no encontrado con ID: $idStock<br>";
        }
    }
    
    echo "<br><h3>🧪 Para probar validación:</h3>";
    echo "Agrega ?id=X&cantidad=Y a la URL<br>";
    echo "Ejemplo: ?id=1&cantidad=5<br>";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
