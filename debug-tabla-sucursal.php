<?php
require_once "modelos/conexion.php";

try {
    echo "<h2>🔍 Verificando tabla sucursal_local en BD local</h2>";
    
    // Verificar estructura
    $stmt = Conexion::conectar()->prepare("DESCRIBE sucursal_local");
    $stmt->execute();
    $columnas = $stmt->fetchAll();
    
    echo "<h3>Estructura de la tabla:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Por defecto</th></tr>";
    
    foreach($columnas as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar datos
    $stmt2 = Conexion::conectar()->prepare("SELECT * FROM sucursal_local ORDER BY id ASC");
    $stmt2->execute();
    $sucursales = $stmt2->fetchAll();
    
    echo "<h3>Datos en la tabla:</h3>";
    if(count($sucursales) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        $primeraSucursal = $sucursales[0];
        echo "<tr>";
        foreach(array_keys($primeraSucursal) as $campo) {
            if(!is_numeric($campo)) { // Solo mostrar campos con nombre
                echo "<th>$campo</th>";
            }
        }
        echo "</tr>";
        
        foreach($sucursales as $sucursal) {
            echo "<tr>";
            foreach($sucursal as $key => $value) {
                if(!is_numeric($key)) { // Solo mostrar campos con nombre
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No hay datos en la tabla sucursal_local</p>";
    }
    
    // Verificar sesión actual
    session_start();
    echo "<h3>Variables de sesión relacionadas:</h3>";
    echo "<ul>";
    echo "<li><strong>id_sucursal:</strong> " . ($_SESSION["id_sucursal"] ?? 'No definida') . "</li>";
    echo "<li><strong>nombre_sucursal:</strong> " . ($_SESSION["nombre_sucursal"] ?? 'No definida') . "</li>";
    echo "<li><strong>codigo_sucursal:</strong> " . ($_SESSION["codigo_sucursal"] ?? 'No definida') . "</li>";
    echo "</ul>";
    
} catch(Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>