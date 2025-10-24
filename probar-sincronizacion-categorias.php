<?php
// Script para probar la sincronización de categorías
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Probar Sincronización de Categorías</h1>";

try {
    // Incluir el modelo
    require_once "modelos/categorias-central.modelo.php";
    
    echo "<h2>📋 Paso 1: Verificar categorías centrales</h2>";
    $categorias = ModeloCategoriasCentral::mdlObtenerCategoriasCentral();
    
    if ($categorias['success']) {
        echo "<p>✅ Categorías centrales obtenidas: " . count($categorias['data']) . "</p>";
        echo "<ul>";
        foreach ($categorias['data'] as $cat) {
            echo "<li>" . $cat['categoria'] . " (Activo: " . ($cat['activo'] ? 'Sí' : 'No') . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ Error al obtener categorías: " . $categorias['message'] . "</p>";
        exit;
    }
    
    echo "<h2>🔄 Paso 2: Ejecutar sincronización</h2>";
    $resultado = ModeloCategoriasCentral::mdlSincronizarCategoriasSucursales();
    
    if ($resultado['success']) {
        echo "<p>✅ Sincronización exitosa</p>";
        echo "<p><strong>Mensaje:</strong> " . $resultado['message'] . "</p>";
        echo "<p><strong>Sucursales sincronizadas:</strong> " . $resultado['sucursales_sincronizadas'] . "/" . $resultado['total_sucursales'] . "</p>";
        
        if (!empty($resultado['errores'])) {
            echo "<h3>⚠️ Errores encontrados:</h3>";
            echo "<ul>";
            foreach ($resultado['errores'] as $error) {
                echo "<li style='color: red;'>" . $error . "</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p>❌ Error en sincronización: " . $resultado['message'] . "</p>";
    }
    
    echo "<h2>📊 Paso 3: Verificar estado de sincronización</h2>";
    $categorias_despues = ModeloCategoriasCentral::mdlObtenerCategoriasCentral();
    
    if ($categorias_despues['success']) {
        $sincronizadas = 0;
        foreach ($categorias_despues['data'] as $cat) {
            if ($cat['sincronizado']) {
                $sincronizadas++;
            }
        }
        echo "<p>✅ Categorías marcadas como sincronizadas: $sincronizadas/" . count($categorias_despues['data']) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error general:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>";
?>
