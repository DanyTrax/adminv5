<?php
/**
 * Script para diagnosticar categorías con la arquitectura correcta:
 * - Categorías centrales: BD Central
 * - Productos: BD Local de cada sucursal
 * - API: Intersección entre sucursales
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico de Categorías - Arquitectura Correcta</h1>";

try {
    // Conectar a BD Central
    require_once __DIR__ . "/api-transferencias/conexion-central.php";
    $pdoCentral = ConexionCentral::conectar();
    echo "<p>✅ Conexión a BD Central establecida</p>";
    
    // 1. Verificar categorías centrales
    echo "<h3>📋 1. Categorías Centrales (BD Central)</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT COUNT(*) FROM categorias_central");
    $stmt->execute();
    $totalCategoriasCentral = $stmt->fetchColumn();
    echo "<p>Total de categorías centrales: $totalCategoriasCentral</p>";
    
    $stmt = $pdoCentral->prepare("SELECT * FROM categorias_central ORDER BY categoria");
    $stmt->execute();
    $categoriasCentrales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Categoría</th><th>Activa</th><th>Sincronizada</th><th>Fecha Creación</th></tr>";
    foreach ($categoriasCentrales as $cat) {
        $activa = $cat['activo'] ? 'Sí' : 'No';
        $sincronizada = $cat['sincronizado'] ? 'Sí' : 'No';
        echo "<tr>";
        echo "<td>{$cat['id']}</td>";
        echo "<td>{$cat['categoria']}</td>";
        echo "<td>$activa</td>";
        echo "<td>$sincronizada</td>";
        echo "<td>{$cat['fecha_creacion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Obtener sucursales activas
    echo "<h3>🏢 2. Sucursales Activas</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT * FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Total de sucursales activas: " . count($sucursales) . "</p>";
    
    $todasLasCategoriasEnSucursales = [];
    $productosPorCategoria = [];
    
    // 3. Analizar cada sucursal
    foreach ($sucursales as $sucursal) {
        echo "<h4>🔍 Analizando sucursal: {$sucursal['nombre']}</h4>";
        
        try {
            // Conectar a sucursal
            $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']}";
            $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
            $pdoSucursal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Verificar si existe tabla productos
            $stmt = $pdoSucursal->prepare("SHOW TABLES LIKE 'productos'");
            $stmt->execute();
            if (!$stmt->fetch()) {
                echo "<p>⚠️ No existe tabla productos en esta sucursal</p>";
                continue;
            }
            
            // Obtener categorías de productos en esta sucursal
            $stmt = $pdoSucursal->prepare("
                SELECT DISTINCT categoria, COUNT(*) as total_productos
                FROM productos 
                WHERE categoria IS NOT NULL 
                  AND categoria != ''
                  AND categoria != 'NULL'
                GROUP BY categoria
                ORDER BY total_productos DESC
            ");
            $stmt->execute();
            $categoriasSucursal = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>📦 Categorías en productos: " . count($categoriasSucursal) . "</p>";
            
            if (count($categoriasSucursal) > 0) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>Categoría</th><th>Productos</th></tr>";
                foreach ($categoriasSucursal as $cat) {
                    echo "<tr><td>{$cat['categoria']}</td><td>{$cat['total_productos']}</td></tr>";
                    
                    // Acumular para análisis general
                    $todasLasCategoriasEnSucursales[] = $cat['categoria'];
                    if (!isset($productosPorCategoria[$cat['categoria']])) {
                        $productosPorCategoria[$cat['categoria']] = 0;
                    }
                    $productosPorCategoria[$cat['categoria']] += $cat['total_productos'];
                }
                echo "</table>";
            }
            
            // Verificar productos sin categoría
            $stmt = $pdoSucursal->prepare("
                SELECT COUNT(*) as productos_sin_categoria
                FROM productos 
                WHERE categoria IS NULL 
                   OR categoria = ''
                   OR categoria = 'NULL'
            ");
            $stmt->execute();
            $productosSinCategoria = $stmt->fetchColumn();
            echo "<p>❌ Productos sin categoría: $productosSinCategoria</p>";
            
        } catch (Exception $e) {
            echo "<p>❌ Error conectando a sucursal {$sucursal['nombre']}: " . $e->getMessage() . "</p>";
        }
    }
    
    // 4. Análisis general
    echo "<h3>📊 3. Análisis General</h3>";
    
    $todasLasCategoriasEnSucursales = array_unique($todasLasCategoriasEnSucursales);
    sort($todasLasCategoriasEnSucursales);
    
    echo "<p>Total de categorías únicas en sucursales: " . count($todasLasCategoriasEnSucursales) . "</p>";
    
    // Categorías que están en sucursales pero no en central
    $categoriasFaltantesEnCentral = [];
    foreach ($todasLasCategoriasEnSucursales as $categoria) {
        $existeEnCentral = false;
        foreach ($categoriasCentrales as $catCentral) {
            if ($catCentral['categoria'] == $categoria) {
                $existeEnCentral = true;
                break;
            }
        }
        if (!$existeEnCentral) {
            $categoriasFaltantesEnCentral[] = $categoria;
        }
    }
    
    echo "<p>❌ Categorías en sucursales que NO están en central: " . count($categoriasFaltantesEnCentral) . "</p>";
    
    if (count($categoriasFaltantesEnCentral) > 0) {
        echo "<h4>🔍 Categorías faltantes en central:</h4>";
        echo "<ul>";
        foreach ($categoriasFaltantesEnCentral as $categoria) {
            $productos = isset($productosPorCategoria[$categoria]) ? $productosPorCategoria[$categoria] : 0;
            echo "<li><strong>$categoria</strong> ($productos productos)</li>";
        }
        echo "</ul>";
    }
    
    // Categorías que están en central pero no en sucursales
    $categoriasEnCentralNoEnSucursales = [];
    foreach ($categoriasCentrales as $catCentral) {
        if (!in_array($catCentral['categoria'], $todasLasCategoriasEnSucursales)) {
            $categoriasEnCentralNoEnSucursales[] = $catCentral['categoria'];
        }
    }
    
    echo "<p>⚠️ Categorías en central que NO están en sucursales: " . count($categoriasEnCentralNoEnSucursales) . "</p>";
    
    if (count($categoriasEnCentralNoEnSucursales) > 0) {
        echo "<h4>🔍 Categorías en central no usadas:</h4>";
        echo "<ul>";
        foreach ($categoriasEnCentralNoEnSucursales as $categoria) {
            echo "<li><strong>$categoria</strong></li>";
        }
        echo "</ul>";
    }
    
    // 5. Generar comandos SQL para corregir
    echo "<h3>🔧 4. Comandos SQL para Corregir</h3>";
    
    if (count($categoriasFaltantesEnCentral) > 0) {
        echo "<h4>📝 INSERT para categorías faltantes:</h4>";
        echo "<pre>";
        foreach ($categoriasFaltantesEnCentral as $categoria) {
            echo "INSERT INTO categorias_central (categoria, descripcion, activo, sincronizado, fecha_creacion) VALUES ('$categoria', '', 1, 0, NOW());\n";
        }
        echo "</pre>";
    }
    
    // 6. Resumen final
    echo "<h3>📋 5. Resumen Final</h3>";
    echo "<ul>";
    echo "<li>✅ Categorías en central: $totalCategoriasCentral</li>";
    echo "<li>📦 Categorías en sucursales: " . count($todasLasCategoriasEnSucursales) . "</li>";
    echo "<li>❌ Categorías faltantes en central: " . count($categoriasFaltantesEnCentral) . "</li>";
    echo "<li>⚠️ Categorías no usadas en central: " . count($categoriasEnCentralNoEnSucursales) . "</li>";
    echo "</ul>";
    
    if (count($categoriasFaltantesEnCentral) == 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>🎉 ¡Perfecto!</h3>";
        echo "<p>Todas las categorías de las sucursales están en central. El problema puede estar en la sincronización.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>⚠️ Acción Requerida</h3>";
        echo "<p>Hay " . count($categoriasFaltantesEnCentral) . " categorías que necesitan ser agregadas a central.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>🎯 Próximos Pasos</h3>";
echo "<ol>";
echo "<li>Si hay categorías faltantes, ejecutar los comandos INSERT mostrados arriba</li>";
echo "<li>Verificar que todas las categorías estén activas en central</li>";
echo "<li>Ejecutar sincronización desde el panel de administración</li>";
echo "<li>Verificar que los productos mantengan sus categorías después de sincronizar</li>";
echo "</ol>";
?>
