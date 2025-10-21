<?php
/**
 * PROBAR CONEXIÓN A BASE DE DATOS DE SUCURSAL
 * Script para probar la nueva funcionalidad de conexión directa a BD
 */

require_once "config.php";
require_once "modelos/conexion.php";
require_once "modelos/sucursales.modelo.php";

echo "<h2>🔍 PROBAR CONEXIÓN A BASE DE DATOS DE SUCURSAL</h2>";

// Obtener sucursales
try {
    $stmt = Conexion::conectar()->prepare("
        SELECT id, nombre, usuario_bd, password_bd, nombre_bd, host_bd, puerto_bd 
        FROM sucursales 
        WHERE activo = 1 
        ORDER BY id
    ");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Sucursales Activas:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Host BD</th><th>Base de Datos</th><th>Usuario BD</th><th>Prueba</th></tr>";
    
    foreach ($sucursales as $sucursal) {
        echo "<tr>";
        echo "<td>" . $sucursal['id'] . "</td>";
        echo "<td>" . $sucursal['nombre'] . "</td>";
        echo "<td>" . $sucursal['host_bd'] . "</td>";
        echo "<td>" . $sucursal['nombre_bd'] . "</td>";
        echo "<td>" . $sucursal['usuario_bd'] . "</td>";
        
        // Probar conexión
        echo "<td>";
        
        try {
            $resultado = ModeloSucursales::mdlProbarConexionBDSucursal($sucursal['id']);
            
            if ($resultado['success']) {
                echo "<span style='color: green;'>✅ OK (" . $resultado['tiempo_respuesta'] . ")</span>";
                echo "<br><small>" . $resultado['message'] . "</small>";
            } else {
                echo "<span style='color: red;'>❌ FALLO</span>";
                echo "<br><small>" . $resultado['message'] . "</small>";
            }
            
        } catch (Exception $e) {
            echo "<span style='color: red;'>❌ ERROR</span>";
            echo "<br><small>" . $e->getMessage() . "</small>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error al obtener sucursales: " . $e->getMessage() . "</p>";
}

echo "<h3>💡 INFORMACIÓN:</h3>";
echo "<p>Este script prueba la conexión directa a las bases de datos de cada sucursal usando los datos de configuración almacenados en la tabla 'sucursales'.</p>";
echo "<p>La conexión se realiza usando PDO con los parámetros: host, puerto, nombre de BD, usuario y contraseña.</p>";

?>
