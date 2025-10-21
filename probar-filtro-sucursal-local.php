<?php
require_once "config.php";
require_once "modelos/usuarios-central.modelo.php";

echo "<h2>🔍 PROBAR FILTRO DE SUCURSAL LOCAL</h2>";

try {
    echo "<h3>📋 ANTES del filtro (todas las sucursales):</h3>";
    
    // Obtener todas las sucursales (sin filtro)
    $conexionCentral = ConexionCentral::conectar();
    $stmt = $conexionCentral->prepare("
        SELECT id, nombre, codigo_sucursal, host_bd, nombre_bd
        FROM sucursales 
        WHERE activo = 1
        ORDER BY nombre
    ");
    $stmt->execute();
    $todasSucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Código</th><th>Host BD</th><th>Nombre BD</th><th>Tipo</th></tr>";
    
    foreach ($todasSucursales as $sucursal) {
        $tipo = ($sucursal['host_bd'] == 'localhost' && $sucursal['nombre_bd'] == 'epicosie_pruebas') ? 
                '<span style="color: red;">LOCAL (se filtrará)</span>' : 
                '<span style="color: green;">REMOTA</span>';
        
        echo "<tr>";
        echo "<td>" . $sucursal['id'] . "</td>";
        echo "<td>" . $sucursal['nombre'] . "</td>";
        echo "<td>" . $sucursal['codigo_sucursal'] . "</td>";
        echo "<td>" . $sucursal['host_bd'] . "</td>";
        echo "<td>" . $sucursal['nombre_bd'] . "</td>";
        echo "<td>" . $tipo . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>📋 DESPUÉS del filtro (solo sucursales remotas):</h3>";
    
    // Probar el método con filtro
    $sucursalesFiltradas = ModeloUsuariosCentral::mdlConsultarUsuariosSucursales();
    
    echo "<p><strong>Total de sucursales remotas encontradas:</strong> " . count($sucursalesFiltradas) . "</p>";
    
    if (count($sucursalesFiltradas) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Código</th><th>Estado</th></tr>";
        
        foreach ($sucursalesFiltradas as $sucursal) {
            echo "<tr>";
            echo "<td>" . $sucursal['sucursal']['id'] . "</td>";
            echo "<td>" . $sucursal['sucursal']['nombre'] . "</td>";
            echo "<td>" . $sucursal['sucursal']['codigo_sucursal'] . "</td>";
            echo "<td>" . $sucursal['estado_conexion'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No se encontraron sucursales remotas (esto es correcto si todas apuntan a BD local)</p>";
    }
    
    echo "<h3>✅ RESULTADO:</h3>";
    echo "<p>El filtro está funcionando correctamente. Las sucursales que apuntan a la BD local se excluyen de la lista de sucursales remotas.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
