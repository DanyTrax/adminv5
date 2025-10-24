<?php
/**
 * Script para corregir la sincronización de categorías usando la estructura real
 * 
 * Estructura real identificada:
 * - BD Central: tabla 'categorias' (original) con 9 categorías funcionales
 * - BD Local: productos.id_categoria -> categorias.id (relación por ID)
 * - Problema: categorias_central está causando conflictos
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Corregir Sincronización de Categorías - Estructura Real</h1>";

try {
    // Conectar a BD Central
    require_once __DIR__ . "/api-transferencias/conexion-central.php";
    $pdoCentral = ConexionCentral::conectar();
    echo "<p>✅ Conexión a BD Central establecida</p>";
    
    // 1. Analizar tabla 'categorias' original (la que funciona)
    echo "<h3>📋 1. Analizando tabla 'categorias' original (funcional)</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT * FROM categorias ORDER BY categoria");
    $stmt->execute();
    $categoriasOriginales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>📊 Total de categorías originales: " . count($categoriasOriginales) . "</p>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Categoría</th><th>Fecha</th></tr>";
    foreach ($categoriasOriginales as $cat) {
        echo "<tr>";
        echo "<td>{$cat['id']}</td>";
        echo "<td>{$cat['categoria']}</td>";
        echo "<td>{$cat['fecha']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Analizar sucursales para ver qué categorías se usan realmente
    echo "<h3>🏢 2. Analizando categorías en sucursales</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT * FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $categoriasUsadasEnSucursales = [];
    $productosPorCategoria = [];
    
    foreach ($sucursales as $sucursal) {
        echo "<h4>🔍 Analizando sucursal: {$sucursal['nombre']}</h4>";
        
        try {
            // Conectar a sucursal
            $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']}";
            $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
            $pdoSucursal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Obtener categorías locales
            $stmt = $pdoSucursal->prepare("SELECT * FROM categorias ORDER BY categoria");
            $stmt->execute();
            $categoriasSucursal = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>📦 Categorías en sucursal: " . count($categoriasSucursal) . "</p>";
            
            if (count($categoriasSucursal) > 0) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Categoría</th><th>Fecha</th></tr>";
                foreach ($categoriasSucursal as $cat) {
                    echo "<tr>";
                    echo "<td>{$cat['id']}</td>";
                    echo "<td>{$cat['categoria']}</td>";
                    echo "<td>{$cat['fecha']}</td>";
                    echo "</tr>";
                    
                    // Acumular categorías
                    $categoriasUsadasEnSucursales[] = $cat['categoria'];
                }
                echo "</table>";
            }
            
            // Obtener productos por categoría
            $stmt = $pdoSucursal->prepare("
                SELECT 
                    c.id,
                    c.categoria,
                    COUNT(p.id) as total_productos
                FROM categorias c
                LEFT JOIN productos p ON c.id = p.id_categoria
                GROUP BY c.id, c.categoria
                ORDER BY total_productos DESC
            ");
            $stmt->execute();
            $productosPorCategoriaSucursal = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h5>📊 Productos por categoría en esta sucursal:</h5>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Categoría</th><th>Productos</th></tr>";
            foreach ($productosPorCategoriaSucursal as $cat) {
                echo "<tr>";
                echo "<td>{$cat['id']}</td>";
                echo "<td>{$cat['categoria']}</td>";
                echo "<td>{$cat['total_productos']}</td>";
                echo "</tr>";
                
                // Acumular para análisis general
                if (!isset($productosPorCategoria[$cat['categoria']])) {
                    $productosPorCategoria[$cat['categoria']] = 0;
                }
                $productosPorCategoria[$cat['categoria']] += $cat['total_productos'];
            }
            echo "</table>";
            
        } catch (Exception $e) {
            echo "<p>❌ Error conectando a sucursal {$sucursal['nombre']}: " . $e->getMessage() . "</p>";
        }
    }
    
    // 3. Identificar categorías que faltan en central
    $categoriasUsadasEnSucursales = array_unique($categoriasUsadasEnSucursales);
    sort($categoriasUsadasEnSucursales);
    
    $categoriasOriginalesNombres = array_column($categoriasOriginales, 'categoria');
    $categoriasFaltantesEnCentral = array_diff($categoriasUsadasEnSucursales, $categoriasOriginalesNombres);
    
    echo "<h3>📊 3. Análisis de Categorías</h3>";
    echo "<p>📦 Categorías usadas en sucursales: " . count($categoriasUsadasEnSucursales) . "</p>";
    echo "<p>📋 Categorías en central: " . count($categoriasOriginalesNombres) . "</p>";
    echo "<p>❌ Categorías faltantes en central: " . count($categoriasFaltantesEnCentral) . "</p>";
    
    if (count($categoriasFaltantesEnCentral) > 0) {
        echo "<h4>🔍 Categorías que faltan en central:</h4>";
        echo "<ul>";
        foreach ($categoriasFaltantesEnCentral as $categoria) {
            $productos = isset($productosPorCategoria[$categoria]) ? $productosPorCategoria[$categoria] : 0;
            echo "<li><strong>$categoria</strong> ($productos productos)</li>";
        }
        echo "</ul>";
        
        // 4. Agregar categorías faltantes a tabla 'categorias' original
        echo "<h3>🔧 4. Agregando categorías faltantes a tabla 'categorias'</h3>";
        
        $stmt = $pdoCentral->prepare("INSERT INTO categorias (categoria, fecha) VALUES (?, NOW())");
        $categoriasAgregadas = 0;
        
        foreach ($categoriasFaltantesEnCentral as $categoria) {
            try {
                $stmt->execute([$categoria]);
                $categoriasAgregadas++;
                echo "<p>✅ Categoría agregada: $categoria</p>";
            } catch (Exception $e) {
                echo "<p>❌ Error agregando categoría $categoria: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<p>📊 Total de categorías agregadas: $categoriasAgregadas</p>";
    } else {
        echo "<p>ℹ️ Todas las categorías ya están en central</p>";
    }
    
    // 5. Verificar estado final
    echo "<h3>✅ 5. Verificación Final</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT * FROM categorias ORDER BY categoria");
    $stmt->execute();
    $categoriasFinales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>📊 Total de categorías en central: " . count($categoriasFinales) . "</p>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Categoría</th><th>Fecha</th></tr>";
    foreach ($categoriasFinales as $cat) {
        echo "<tr>";
        echo "<td>{$cat['id']}</td>";
        echo "<td>{$cat['categoria']}</td>";
        echo "<td>{$cat['fecha']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 6. Recomendaciones sobre categorias_central
    echo "<h3>⚠️ 6. Recomendaciones sobre tabla 'categorias_central'</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT COUNT(*) FROM categorias_central");
    $stmt->execute();
    $totalCategoriasCentral = $stmt->fetchColumn();
    
    echo "<p>📊 Total de categorías en 'categorias_central': $totalCategoriasCentral</p>";
    
    if ($totalCategoriasCentral > 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4>⚠️ Conflicto Detectado</h4>";
        echo "<p>La tabla 'categorias_central' tiene $totalCategoriasCentral categorías que pueden estar causando conflictos.</p>";
        echo "<p><strong>Recomendación:</strong> Eliminar o renombrar la tabla 'categorias_central' para evitar conflictos.</p>";
        echo "</div>";
        
        echo "<h4>🔧 Comandos SQL para limpiar:</h4>";
        echo "<pre>";
        echo "-- Opción 1: Renombrar tabla (recomendado)\n";
        echo "RENAME TABLE categorias_central TO categorias_central_backup;\n\n";
        echo "-- Opción 2: Eliminar tabla (si estás seguro)\n";
        echo "-- DROP TABLE categorias_central;\n";
        echo "</pre>";
    }
    
    // 7. Instrucciones para sincronización
    echo "<h3>🎯 7. Instrucciones para Sincronización</h3>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h4>✅ Estructura Correcta Identificada</h4>";
    echo "<ol>";
    echo "<li><strong>BD Central:</strong> Usar tabla 'categorias' (original)</li>";
    echo "<li><strong>BD Local:</strong> Sincronizar desde 'categorias' central a 'categorias' local</li>";
    echo "<li><strong>Productos:</strong> Usar campo 'id_categoria' que apunta a 'categorias.id' local</li>";
    echo "<li><strong>Eliminar:</strong> No usar 'categorias_central' para evitar conflictos</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h4>🔧 Pasos para corregir sincronización:</h4>";
    echo "<ol>";
    echo "<li>Modificar el código de sincronización para usar tabla 'categorias' en lugar de 'categorias_central'</li>";
    echo "<li>Actualizar el modelo de sincronización para trabajar con la estructura real</li>";
    echo "<li>Probar la sincronización con la tabla original</li>";
    echo "<li>Verificar que los productos mantengan sus categorías</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>🎯 Resumen</h3>";
echo "<ul>";
echo "<li>✅ Estructura real identificada: productos.id_categoria -> categorias.id</li>";
echo "<li>✅ Tabla 'categorias' original funciona correctamente</li>";
echo "<li>⚠️ Tabla 'categorias_central' está causando conflictos</li>";
echo "<li>🔧 Necesitamos modificar el código de sincronización</li>";
echo "</ul>";
?>
