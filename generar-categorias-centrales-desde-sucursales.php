<?php
/**
 * Script para generar categorías centrales basándose en las categorías existentes en las sucursales
 * Este script analiza todas las sucursales y crea las categorías centrales que faltan
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Generador de Categorías Centrales desde Sucursales</h1>";

// Incluir conexión central
require_once __DIR__ . "/api-transferencias/conexion-central.php";

try {
    // Conectar a base de datos central
    $pdoCentral = ConexionCentral::conectar();
    echo "<p>✅ Conexión a base de datos central establecida</p>";
    
    // Obtener sucursales activas
    $stmt = $pdoCentral->prepare("SELECT * FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>📊 Total de sucursales activas: " . count($sucursales) . "</p>";
    
    $todasLasCategorias = [];
    $categoriasConProductos = [];
    
    // Analizar cada sucursal
    foreach ($sucursales as $sucursal) {
        echo "<h3>🔍 Analizando sucursal: " . $sucursal['nombre'] . "</h3>";
        
        try {
            // Conectar a sucursal
            $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']}";
            $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
            $pdoSucursal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Obtener categorías de la sucursal
            $stmt = $pdoSucursal->prepare("SELECT DISTINCT categoria FROM categorias WHERE categoria IS NOT NULL AND categoria != ''");
            $stmt->execute();
            $categoriasSucursal = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p>📋 Categorías encontradas: " . count($categoriasSucursal) . "</p>";
            
            // Obtener categorías de productos
            $stmt = $pdoSucursal->prepare("SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria != ''");
            $stmt->execute();
            $categoriasProductos = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p>🛍️ Categorías en productos: " . count($categoriasProductos) . "</p>";
            
            // Combinar todas las categorías
            $categoriasCombinadas = array_unique(array_merge($categoriasSucursal, $categoriasProductos));
            
            foreach ($categoriasCombinadas as $categoria) {
                if (!empty($categoria)) {
                    $todasLasCategorias[] = $categoria;
                    
                    // Contar productos por categoría
                    $stmt = $pdoSucursal->prepare("SELECT COUNT(*) FROM productos WHERE categoria = ?");
                    $stmt->execute([$categoria]);
                    $cantidadProductos = $stmt->fetchColumn();
                    
                    if ($cantidadProductos > 0) {
                        if (!isset($categoriasConProductos[$categoria])) {
                            $categoriasConProductos[$categoria] = 0;
                        }
                        $categoriasConProductos[$categoria] += $cantidadProductos;
                    }
                }
            }
            
        } catch (Exception $e) {
            echo "<p>❌ Error conectando a sucursal {$sucursal['nombre']}: " . $e->getMessage() . "</p>";
        }
    }
    
    // Eliminar duplicados y ordenar
    $todasLasCategorias = array_unique($todasLasCategorias);
    sort($todasLasCategorias);
    
    echo "<h3>📊 Resumen de Categorías Encontradas</h3>";
    echo "<p>Total de categorías únicas: " . count($todasLasCategorias) . "</p>";
    echo "<p>Categorías con productos: " . count($categoriasConProductos) . "</p>";
    
    // Verificar qué categorías ya existen en central
    $stmt = $pdoCentral->prepare("SELECT categoria FROM categorias_central");
    $stmt->execute();
    $categoriasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $categoriasNuevas = array_diff($todasLasCategorias, $categoriasExistentes);
    
    echo "<h3>🆕 Categorías Nuevas a Crear</h3>";
    echo "<p>Total de categorías nuevas: " . count($categoriasNuevas) . "</p>";
    
    if (count($categoriasNuevas) > 0) {
        echo "<h4>Lista de categorías a crear:</h4>";
        echo "<ul>";
        foreach ($categoriasNuevas as $categoria) {
            $productos = isset($categoriasConProductos[$categoria]) ? $categoriasConProductos[$categoria] : 0;
            echo "<li><strong>$categoria</strong> (Productos: $productos)</li>";
        }
        echo "</ul>";
        
        // Crear las categorías
        echo "<h3>🔧 Creando Categorías Centrales</h3>";
        
        $stmt = $pdoCentral->prepare("
            INSERT INTO categorias_central (categoria, descripcion, activo, sincronizado, fecha_creacion) 
            VALUES (?, '', 1, 0, NOW())
        ");
        
        $categoriasCreadas = 0;
        foreach ($categoriasNuevas as $categoria) {
            try {
                $stmt->execute([$categoria]);
                $categoriasCreadas++;
                echo "<p>✅ Categoría creada: $categoria</p>";
            } catch (Exception $e) {
                echo "<p>❌ Error creando categoría $categoria: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<h3>✅ Proceso Completado</h3>";
        echo "<p>Total de categorías creadas: $categoriasCreadas</p>";
        
    } else {
        echo "<p>ℹ️ No hay categorías nuevas para crear. Todas las categorías ya existen en central.</p>";
    }
    
    // Generar reporte final
    echo "<h3>📋 Reporte Final</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT COUNT(*) FROM categorias_central");
    $stmt->execute();
    $totalCategoriasCentral = $stmt->fetchColumn();
    
    echo "<p>📊 Total de categorías en central: $totalCategoriasCentral</p>";
    echo "<p>🆕 Categorías nuevas agregadas: " . count($categoriasNuevas) . "</p>";
    
    // Mostrar todas las categorías centrales
    $stmt = $pdoCentral->prepare("SELECT categoria, activo, sincronizado FROM categorias_central ORDER BY categoria");
    $stmt->execute();
    $todasCategoriasCentral = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>📋 Lista completa de categorías centrales:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Categoría</th><th>Estado</th><th>Sincronizada</th></tr>";
    foreach ($todasCategoriasCentral as $cat) {
        $estado = $cat['activo'] ? 'Activa' : 'Inactiva';
        $sincronizada = $cat['sincronizado'] ? 'Sí' : 'No';
        echo "<tr><td>{$cat['categoria']}</td><td>$estado</td><td>$sincronizada</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ Error general: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>🎯 Próximos Pasos</h3>";
echo "<ol>";
echo "<li>Revisar las categorías creadas en la tabla categorias_central</li>";
echo "<li>Verificar que todas las categorías necesarias estén incluidas</li>";
echo "<li>Ejecutar la sincronización desde el panel de administración</li>";
echo "<li>Verificar que los productos mantengan sus categorías</li>";
echo "</ol>";
?>
