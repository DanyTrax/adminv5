<?php
require_once "config.php";
require_once "api-transferencias/conexion-central.php";

echo "<h2>🔍 VERIFICAR SUCURSALES EN BD CENTRAL</h2>";

try {
    $pdo = ConexionCentral::conectar();
    
    $stmt = $pdo->prepare("SELECT * FROM sucursales ORDER BY id");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Sucursales en BD Central:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    if (count($sucursales) > 0) {
        $columnas = array_keys($sucursales[0]);
        echo "<tr>";
        foreach ($columnas as $columna) {
            echo "<th>" . $columna . "</th>";
        }
        echo "</tr>";
        
        foreach ($sucursales as $sucursal) {
            echo "<tr>";
            foreach ($columnas as $columna) {
                echo "<td>" . htmlspecialchars($sucursal[$columna]) . "</td>";
            }
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No hay sucursales</td></tr>";
    }
    
    echo "</table>";
    
    echo "<h3>💡 ESTRATEGIA PARA IDENTIFICAR SUCURSAL LOCAL:</h3>";
    echo "<p>Como no hay columna 'es_principal', podemos identificar la sucursal local por:</p>";
    echo "<ul>";
    echo "<li><strong>Nombre de BD:</strong> Si usa 'epicosie_pruebas' es la sucursal local</li>";
    echo "<li><strong>Host:</strong> Si es 'localhost' y coincide con la BD actual</li>";
    echo "<li><strong>Usuario BD:</strong> Si coincide con el usuario actual</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
