<?php
require_once "api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    echo "<h2>📋 Estructura de la tabla 'despachos':</h2>";
    
    $stmt = $conexion->prepare("DESCRIBE despachos");
    $stmt->execute();
    $columnas = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
    
    foreach($columnas as $columna) {
        echo "<tr>";
        echo "<td><strong>" . $columna['Field'] . "</strong></td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "<td>" . ($columna['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $columna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h3>🔍 Verificar registros existentes:</h3>";
    $stmt2 = $conexion->prepare("SELECT COUNT(*) as total FROM despachos");
    $stmt2->execute();
    $total = $stmt2->fetch();
    echo "<p>Total de despachos en BD: <strong>" . $total['total'] . "</strong></p>";
    
    if($total['total'] > 0) {
        echo "<h4>📝 Últimos 3 registros:</h4>";
        $stmt3 = $conexion->prepare("SELECT * FROM despachos ORDER BY id DESC LIMIT 3");
        $stmt3->execute();
        $registros = $stmt3->fetchAll();
        
        echo "<pre>";
        print_r($registros);
        echo "</pre>";
    }
    
} catch(Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>