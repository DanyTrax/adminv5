<?php
/**
 * Script para ejecutar automáticamente la generación de categorías centrales
 * basándose en las categorías existentes en los productos
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚀 Ejecutor de Generación de Categorías Centrales</h1>";

try {
    // Incluir conexión central
    require_once __DIR__ . "/api-transferencias/conexion-central.php";
    
    // Conectar a base de datos central
    $pdoCentral = ConexionCentral::conectar();
    echo "<p>✅ Conexión a base de datos central establecida</p>";
    
    // Paso 1: Obtener todas las categorías únicas de productos
    echo "<h3>📊 Paso 1: Analizando categorías en productos</h3>";
    
    $stmt = $pdoCentral->prepare("
        SELECT DISTINCT categoria, COUNT(*) as total_productos
        FROM productos 
        WHERE categoria IS NOT NULL 
          AND categoria != ''
          AND categoria != 'NULL'
        GROUP BY categoria
        ORDER BY total_productos DESC
    ");
    $stmt->execute();
    $categoriasProductos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>📦 Categorías encontradas en productos: " . count($categoriasProductos) . "</p>";
    
    if (count($categoriasProductos) > 0) {
        echo "<h4>🔍 Categorías más usadas:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Categoría</th><th>Productos</th></tr>";
        for ($i = 0; $i < min(10, count($categoriasProductos)); $i++) {
            $cat = $categoriasProductos[$i];
            echo "<tr><td>{$cat['categoria']}</td><td>{$cat['total_productos']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Paso 2: Verificar qué categorías ya existen en central
    echo "<h3>🔍 Paso 2: Verificando categorías existentes en central</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT categoria FROM categorias_central");
    $stmt->execute();
    $categoriasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>📋 Categorías existentes en central: " . count($categoriasExistentes) . "</p>";
    
    // Paso 3: Identificar categorías faltantes
    $categoriasFaltantes = [];
    foreach ($categoriasProductos as $cat) {
        if (!in_array($cat['categoria'], $categoriasExistentes)) {
            $categoriasFaltantes[] = $cat;
        }
    }
    
    echo "<p>🆕 Categorías faltantes en central: " . count($categoriasFaltantes) . "</p>";
    
    if (count($categoriasFaltantes) > 0) {
        echo "<h4>📝 Categorías a crear:</h4>";
        echo "<ul>";
        foreach ($categoriasFaltantes as $cat) {
            echo "<li><strong>{$cat['categoria']}</strong> ({$cat['total_productos']} productos)</li>";
        }
        echo "</ul>";
        
        // Paso 4: Crear categorías faltantes
        echo "<h3>🔧 Paso 3: Creando categorías faltantes</h3>";
        
        $stmt = $pdoCentral->prepare("
            INSERT INTO categorias_central (categoria, descripcion, activo, sincronizado, fecha_creacion) 
            VALUES (?, '', 1, 0, NOW())
        ");
        
        $categoriasCreadas = 0;
        $errores = 0;
        
        foreach ($categoriasFaltantes as $cat) {
            try {
                $stmt->execute([$cat['categoria']]);
                $categoriasCreadas++;
                echo "<p>✅ Categoría creada: {$cat['categoria']}</p>";
            } catch (Exception $e) {
                $errores++;
                echo "<p>❌ Error creando categoría {$cat['categoria']}: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<p>📊 Resultado: $categoriasCreadas creadas, $errores errores</p>";
        
    } else {
        echo "<p>ℹ️ Todas las categorías ya existen en central</p>";
    }
    
    // Paso 5: Activar y marcar como no sincronizadas
    echo "<h3>🔄 Paso 4: Activando categorías para sincronización</h3>";
    
    $categoriasProductosNombres = array_column($categoriasProductos, 'categoria');
    $placeholders = str_repeat('?,', count($categoriasProductosNombres) - 1) . '?';
    
    $stmt = $pdoCentral->prepare("
        UPDATE categorias_central 
        SET activo = 1, sincronizado = 0, fecha_actualizacion = NOW()
        WHERE categoria IN ($placeholders)
    ");
    $stmt->execute($categoriasProductosNombres);
    
    $categoriasActualizadas = $stmt->rowCount();
    echo "<p>✅ Categorías actualizadas: $categoriasActualizadas</p>";
    
    // Paso 6: Verificación final
    echo "<h3>✅ Paso 5: Verificación final</h3>";
    
    $stmt = $pdoCentral->prepare("
        SELECT 
            p.categoria,
            COUNT(p.id) as productos,
            CASE 
                WHEN cc.categoria IS NOT NULL THEN 'EN CENTRAL'
                ELSE 'FALTA EN CENTRAL'
            END as estado
        FROM productos p
        LEFT JOIN categorias_central cc ON p.categoria = cc.categoria
        WHERE p.categoria IS NOT NULL 
          AND p.categoria != ''
          AND p.categoria != 'NULL'
        GROUP BY p.categoria, cc.categoria
        ORDER BY productos DESC
    ");
    $stmt->execute();
    $verificacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $enCentral = 0;
    $faltantes = 0;
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Categoría</th><th>Productos</th><th>Estado</th></tr>";
    foreach ($verificacion as $ver) {
        echo "<tr>";
        echo "<td>{$ver['categoria']}</td>";
        echo "<td>{$ver['productos']}</td>";
        echo "<td>{$ver['estado']}</td>";
        echo "</tr>";
        
        if ($ver['estado'] == 'EN CENTRAL') {
            $enCentral++;
        } else {
            $faltantes++;
        }
    }
    echo "</table>";
    
    echo "<h3>📊 Resumen Final</h3>";
    echo "<p>✅ Categorías en central: $enCentral</p>";
    echo "<p>❌ Categorías faltantes: $faltantes</p>";
    
    // Estadísticas generales
    $stmt = $pdoCentral->prepare("SELECT COUNT(*) FROM categorias_central");
    $stmt->execute();
    $totalCategoriasCentral = $stmt->fetchColumn();
    
    $stmt = $pdoCentral->prepare("SELECT COUNT(*) FROM productos WHERE categoria IS NOT NULL AND categoria != '' AND categoria != 'NULL'");
    $stmt->execute();
    $productosConCategoria = $stmt->fetchColumn();
    
    $stmt = $pdoCentral->prepare("SELECT COUNT(*) FROM productos");
    $stmt->execute();
    $totalProductos = $stmt->fetchColumn();
    
    echo "<h3>📈 Estadísticas Generales</h3>";
    echo "<ul>";
    echo "<li>Total de categorías en central: $totalCategoriasCentral</li>";
    echo "<li>Total de productos: $totalProductos</li>";
    echo "<li>Productos con categoría: $productosConCategoria</li>";
    echo "<li>Productos sin categoría: " . ($totalProductos - $productosConCategoria) . "</li>";
    echo "</ul>";
    
    if ($faltantes == 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>🎉 ¡Proceso Completado Exitosamente!</h3>";
        echo "<p>Todas las categorías de productos están ahora en la tabla central.</p>";
        echo "<p>Puedes proceder con la sincronización desde el panel de administración.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>⚠️ Atención</h3>";
        echo "<p>Hay $faltantes categorías que aún no están en central. Revisa los resultados arriba.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>🎯 Próximos Pasos</h3>";
echo "<ol>";
echo "<li>Verificar que todas las categorías estén correctamente creadas</li>";
echo "<li>Ejecutar la sincronización desde el panel de administración</li>";
echo "<li>Verificar que los productos mantengan sus categorías después de la sincronización</li>";
echo "<li>Si hay problemas, usar el script de restauración desde backup</li>";
echo "</ol>";
?>
