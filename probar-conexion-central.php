<?php
/**
 * PROBAR CONEXIÓN CENTRAL
 * Script para verificar que la conexión central funciona correctamente
 */

require_once "config.php";
require_once "api-transferencias/conexion-central.php";

echo "<h2>🔍 PROBAR CONEXIÓN CENTRAL</h2>";

try {
    // Probar conexión central
    $pdo = ConexionCentral::conectar();
    echo "<p style='color: green;'>✅ Conexión central establecida correctamente</p>";
    
    // Verificar tabla sucursales
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'sucursales'");
    $stmt->execute();
    $tabla = $stmt->fetch();
    
    if ($tabla) {
        echo "<p style='color: green;'>✅ Tabla 'sucursales' existe en BD central</p>";
        
        // Obtener sucursales
        $stmt = $pdo->prepare("SELECT id, nombre, usuario_bd, password_bd, nombre_bd, host_bd, puerto_bd FROM sucursales WHERE activo = 1");
        $stmt->execute();
        $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Sucursales en BD Central:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Host BD</th><th>Base de Datos</th><th>Usuario BD</th></tr>";
        
        foreach ($sucursales as $sucursal) {
            echo "<tr>";
            echo "<td>" . $sucursal['id'] . "</td>";
            echo "<td>" . $sucursal['nombre'] . "</td>";
            echo "<td>" . $sucursal['host_bd'] . "</td>";
            echo "<td>" . $sucursal['nombre_bd'] . "</td>";
            echo "<td>" . $sucursal['usuario_bd'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>❌ Tabla 'sucursales' no existe en BD central</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>💡 INFORMACIÓN:</h3>";
echo "<p>La conexión central se usa para obtener los datos de configuración de las sucursales.</p>";
echo "<p>Luego se conecta directamente a la BD de cada sucursal usando esos datos.</p>";
?>
