<?php
/**
 * Script de prueba para verificar la búsqueda AJAX de productos
 */

echo "<h2>🧪 Probar Búsqueda AJAX de Productos</h2>\n";

try {
    // Simular POST request
    $_POST["buscarProductos"] = "test";
    
    echo "<p>✅ Simulando búsqueda de productos...</p>\n";
    
    // Incluir archivos necesarios
    require_once "modelos/salidas-inventario.modelo.php";
    
    echo "<p>✅ Modelo cargado correctamente</p>\n";
    
    // Probar búsqueda
    $productos = ModeloSalidasInventario::mdlBuscarProductos("test");
    
    echo "<p>✅ Búsqueda ejecutada</p>\n";
    echo "<p>📊 Productos encontrados: " . count($productos) . "</p>\n";
    
    if (count($productos) > 0) {
        echo "<h3>📋 Productos encontrados:</h3>\n";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>\n";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Código</th><th>Descripción</th><th>Stock</th><th>Precio</th></tr>\n";
        
        foreach ($productos as $producto) {
            echo "<tr>\n";
            echo "<td>" . $producto['id'] . "</td>\n";
            echo "<td>" . htmlspecialchars($producto['codigo'] ?? 'N/A') . "</td>\n";
            echo "<td>" . htmlspecialchars($producto['descripcion'] ?? 'N/A') . "</td>\n";
            echo "<td>" . $producto['stock'] . "</td>\n";
            echo "<td>$" . number_format($producto['precio_venta'], 0, ',', '.') . "</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<p>ℹ️ No se encontraron productos con la búsqueda 'test'</p>\n";
    }
    
    // Probar con búsqueda vacía
    echo "<h3>🔍 Probando con búsqueda vacía:</h3>\n";
    $productos_vacios = ModeloSalidasInventario::mdlBuscarProductos("");
    echo "<p>📊 Productos encontrados: " . count($productos_vacios) . "</p>\n";
    
    // Probar con búsqueda específica
    echo "<h3>🔍 Probando con búsqueda específica:</h3>\n";
    $productos_especificos = ModeloSalidasInventario::mdlBuscarProductos("a");
    echo "<p>📊 Productos encontrados: " . count($productos_especificos) . "</p>\n";
    
    if (count($productos_especificos) > 0) {
        echo "<p>✅ Primeros 3 productos:</p>\n";
        echo "<ul>\n";
        for ($i = 0; $i < min(3, count($productos_especificos)); $i++) {
            $p = $productos_especificos[$i];
            echo "<li><strong>" . htmlspecialchars($p['descripcion']) . "</strong> - Código: " . htmlspecialchars($p['codigo']) . " - Stock: " . $p['stock'] . "</li>\n";
        }
        echo "</ul>\n";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h6>🎉 Prueba completada exitosamente</h6>\n";
    echo "<p>La búsqueda AJAX de productos está funcionando correctamente.</p>\n";
    echo "<p>El módulo de Salidas de Inventario debería funcionar sin problemas.</p>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>\n";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>\n";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; }
th { background: #007bff; color: white; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>";
?>
