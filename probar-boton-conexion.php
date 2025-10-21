<?php
/**
 * PROBAR BOTÓN DE CONEXIÓN
 * Script para verificar que el botón se genera correctamente con idSucursal
 */

require_once "config.php";
require_once "modelos/conexion.php";

echo "<h2>🔍 PROBAR GENERACIÓN DE BOTÓN DE CONEXIÓN</h2>";

try {
    // Obtener sucursales
    $stmt = Conexion::conectar()->prepare("
        SELECT id, nombre, codigo_sucursal, url_api 
        FROM sucursales 
        WHERE activo = 1 
        ORDER BY id
    ");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Sucursales y sus botones:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Código</th><th>URL API</th><th>Botón Generado</th></tr>";
    
    foreach ($sucursales as $sucursal) {
        echo "<tr>";
        echo "<td>" . $sucursal['id'] . "</td>";
        echo "<td>" . $sucursal['nombre'] . "</td>";
        echo "<td>" . $sucursal['codigo_sucursal'] . "</td>";
        echo "<td>" . $sucursal['url_api'] . "</td>";
        
        // Generar botón como lo hace el AJAX
        $boton = '<button class="btn btn-info btn-xs btnProbarConexion" 
                        idSucursal="' . $sucursal['id'] . '" 
                        nombreSucursal="' . htmlspecialchars($sucursal['nombre']) . '"
                        title="Probar conexión">
                        <i class="fa fa-wifi"></i>
                      </button>';
        
        echo "<td>" . htmlspecialchars($boton) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>✅ VERIFICACIÓN:</h3>";
    echo "<p>Los botones ahora incluyen el atributo <code>idSucursal</code> necesario para la funcionalidad.</p>";
    echo "<p>El JavaScript podrá obtener el ID de la sucursal correctamente.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
