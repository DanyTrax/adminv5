<?php
/**
 * Script para generar categorías centrales basándose en las categorías
 * que realmente se usan en los productos de las sucursales
 * 
 * Arquitectura:
 * - BD Central: categorias_central
 * - BD Local (sucursales): productos con campo categoria
 * - API: sincronización entre central y sucursales
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Generador de Categorías Centrales desde Sucursales</h1>";

try {
    // Conectar a BD Central
    require_once __DIR__ . "/api-transferencias/conexion-central.php";
    $pdoCentral = ConexionCentral::conectar();
    echo "<p>✅ Conexión a BD Central establecida</p>";
    
    // 1. Obtener sucursales activas
    $stmt = $pdoCentral->prepare("SELECT * FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>🏢 Total de sucursales activas: " . count($sucursales) . "</p>";
    
    $todasLasCategorias = [];
    $categoriasConProductos = [];
    $sucursalesAnalizadas = 0;
    
    // 2. Analizar cada sucursal
    foreach ($sucursales as $sucursal) {
        echo "<h3>🔍 Analizando sucursal: {$sucursal['nombre']}</h3>";
        
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
            
            echo "<p>📦 Categorías encontradas: " . count($categoriasSucursal) . "</p>";
            
            if (count($categoriasSucursal) > 0) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>Categoría</th><th>Productos</th></tr>";
                foreach ($categoriasSucursal as $cat) {
                    echo "<tr><td>{$cat['categoria']}</td><td>{$cat['total_productos']}</td></tr>";
                    
                    // Acumular para análisis general
                    $todasLasCategorias[] = $cat['categoria'];
                    if (!isset($categoriasConProductos[$cat['categoria']])) {
                        $categoriasConProductos[$cat['categoria']] = 0;
                    }
                    $categoriasConProductos[$cat['categoria']] += $cat['total_productos'];
                }
                echo "</table>";
            }
            
            $sucursalesAnalizadas++;
            
        } catch (Exception $e) {
            echo "<p>❌ Error conectando a sucursal {$sucursal['nombre']}: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p>✅ Sucursales analizadas: $sucursalesAnalizadas</p>";
    
    // 3. Procesar categorías encontradas
    $todasLasCategorias = array_unique($todasLasCategorias);
    sort($todasLasCategorias);
    
    echo "<h3>📊 Resumen de Categorías Encontradas</h3>";
    echo "<p>Total de categorías únicas: " . count($todasLasCategorias) . "</p>";
    
    if (count($todasLasCategorias) > 0) {
        echo "<h4>🔍 Todas las categorías encontradas:</h4>";
        echo "<ul>";
        foreach ($todasLasCategorias as $categoria) {
            $productos = isset($categoriasConProductos[$categoria]) ? $categoriasConProductos[$categoria] : 0;
            echo "<li><strong>$categoria</strong> ($productos productos)</li>";
        }
        echo "</ul>";
    }
    
    // 4. Verificar qué categorías ya existen en central
    $stmt = $pdoCentral->prepare("SELECT categoria FROM categorias_central");
    $stmt->execute();
    $categoriasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $categoriasNuevas = array_diff($todasLasCategorias, $categoriasExistentes);
    
    echo "<h3>🆕 Categorías Nuevas a Crear</h3>";
    echo "<p>Total de categorías nuevas: " . count($categoriasNuevas) . "</p>";
    
    if (count($categoriasNuevas) > 0) {
        echo "<h4>📝 Lista de categorías a crear:</h4>";
        echo "<ul>";
        foreach ($categoriasNuevas as $categoria) {
            $productos = isset($categoriasConProductos[$categoria]) ? $categoriasConProductos[$categoria] : 0;
            echo "<li><strong>$categoria</strong> (Productos: $productos)</li>";
        }
        echo "</ul>";
        
        // 5. Crear las categorías en central
        echo "<h3>🔧 Creando Categorías Centrales</h3>";
        
        $stmt = $pdoCentral->prepare("
            INSERT INTO categorias_central (categoria, descripcion, activo, sincronizado, fecha_creacion) 
            VALUES (?, '', 1, 0, NOW())
        ");
        
        $categoriasCreadas = 0;
        $errores = 0;
        
        foreach ($categoriasNuevas as $categoria) {
            try {
                $stmt->execute([$categoria]);
                $categoriasCreadas++;
                echo "<p>✅ Categoría creada: $categoria</p>";
            } catch (Exception $e) {
                $errores++;
                echo "<p>❌ Error creando categoría $categoria: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<p>📊 Resultado: $categoriasCreadas creadas, $errores errores</p>";
        
    } else {
        echo "<p>ℹ️ No hay categorías nuevas para crear. Todas las categorías ya existen en central.</p>";
    }
    
    // 6. Activar y marcar como no sincronizadas las categorías que se usan
    echo "<h3>🔄 Activando Categorías para Sincronización</h3>";
    
    $placeholders = str_repeat('?,', count($todasLasCategorias) - 1) . '?';
    $stmt = $pdoCentral->prepare("
        UPDATE categorias_central 
        SET activo = 1, sincronizado = 0, fecha_actualizacion = NOW()
        WHERE categoria IN ($placeholders)
    ");
    $stmt->execute($todasLasCategorias);
    
    $categoriasActualizadas = $stmt->rowCount();
    echo "<p>✅ Categorías actualizadas: $categoriasActualizadas</p>";
    
    // 7. Verificación final
    echo "<h3>✅ Verificación Final</h3>";
    
    $stmt = $pdoCentral->prepare("
        SELECT 
            categoria,
            activo,
            sincronizado,
            fecha_creacion
        FROM categorias_central 
        WHERE categoria IN ($placeholders)
        ORDER BY categoria
    ");
    $stmt->execute($todasLasCategorias);
    $categoriasVerificacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Categoría</th><th>Activa</th><th>Sincronizada</th><th>Fecha Creación</th></tr>";
    foreach ($categoriasVerificacion as $cat) {
        $activa = $cat['activo'] ? 'Sí' : 'No';
        $sincronizada = $cat['sincronizado'] ? 'Sí' : 'No';
        echo "<tr>";
        echo "<td>{$cat['categoria']}</td>";
        echo "<td>$activa</td>";
        echo "<td>$sincronizada</td>";
        echo "<td>{$cat['fecha_creacion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 8. Estadísticas finales
    $stmt = $pdoCentral->prepare("SELECT COUNT(*) FROM categorias_central");
    $stmt->execute();
    $totalCategoriasCentral = $stmt->fetchColumn();
    
    echo "<h3>📈 Estadísticas Finales</h3>";
    echo "<ul>";
    echo "<li>📊 Total de categorías en central: $totalCategoriasCentral</li>";
    echo "<li>🆕 Categorías nuevas agregadas: " . count($categoriasNuevas) . "</li>";
    echo "<li>🔄 Categorías actualizadas: $categoriasActualizadas</li>";
    echo "<li>🏢 Sucursales analizadas: $sucursalesAnalizadas</li>";
    echo "</ul>";
    
    if (count($categoriasNuevas) > 0 || $categoriasActualizadas > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>🎉 ¡Proceso Completado!</h3>";
        echo "<p>Las categorías centrales han sido actualizadas correctamente.</p>";
        echo "<p>Ahora puedes ejecutar la sincronización desde el panel de administración.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>ℹ️ Sin Cambios</h3>";
        echo "<p>Todas las categorías ya estaban en central y actualizadas.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>🎯 Próximos Pasos</h3>";
echo "<ol>";
echo "<li>Verificar que todas las categorías estén correctamente creadas en central</li>";
echo "<li>Ejecutar la sincronización desde el panel de administración</li>";
echo "<li>Verificar que los productos en las sucursales mantengan sus categorías</li>";
echo "<li>Si hay problemas, revisar los logs de sincronización</li>";
echo "</ol>";
?>
