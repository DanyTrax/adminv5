<?php
/**
 * Script para revisar la estructura real de las tablas
 * y entender cómo funciona el sistema de categorías existente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Revisión de Estructura Real de Tablas</h1>";

try {
    // Conectar a BD Central
    require_once __DIR__ . "/api-transferencias/conexion-central.php";
    $pdoCentral = ConexionCentral::conectar();
    echo "<p>✅ Conexión a BD Central establecida</p>";
    
    // 1. Revisar estructura de BD Central
    echo "<h3>📋 1. Estructura de BD Central</h3>";
    
    // Ver todas las tablas en BD Central
    $stmt = $pdoCentral->prepare("SHOW TABLES");
    $stmt->execute();
    $tablasCentral = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>📊 Tablas en BD Central: " . count($tablasCentral) . "</p>";
    echo "<ul>";
    foreach ($tablasCentral as $tabla) {
        echo "<li>$tabla</li>";
    }
    echo "</ul>";
    
    // Verificar si existe tabla categorias (original)
    if (in_array('categorias', $tablasCentral)) {
        echo "<h4>🔍 Tabla 'categorias' (original) encontrada en BD Central</h4>";
        
        // Ver estructura de tabla categorias
        $stmt = $pdoCentral->prepare("DESCRIBE categorias");
        $stmt->execute();
        $estructuraCategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
        foreach ($estructuraCategorias as $campo) {
            echo "<tr>";
            echo "<td>{$campo['Field']}</td>";
            echo "<td>{$campo['Type']}</td>";
            echo "<td>{$campo['Null']}</td>";
            echo "<td>{$campo['Key']}</td>";
            echo "<td>{$campo['Default']}</td>";
            echo "<td>{$campo['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Ver datos de tabla categorias
        $stmt = $pdoCentral->prepare("SELECT * FROM categorias ORDER BY categoria");
        $stmt->execute();
        $categoriasOriginales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>📦 Datos en tabla 'categorias' (original):</h4>";
        echo "<p>Total de categorías: " . count($categoriasOriginales) . "</p>";
        
        if (count($categoriasOriginales) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Categoría</th><th>Activa</th><th>Fecha</th></tr>";
            foreach ($categoriasOriginales as $cat) {
                $activa = isset($cat['activo']) && $cat['activo'] ? 'Sí' : 'No';
                $fecha = isset($cat['fecha_creacion']) ? $cat['fecha_creacion'] : (isset($cat['fecha']) ? $cat['fecha'] : 'N/A');
                echo "<tr>";
                echo "<td>{$cat['id']}</td>";
                echo "<td>{$cat['categoria']}</td>";
                echo "<td>$activa</td>";
                echo "<td>$fecha</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Verificar tabla categorias_central
    if (in_array('categorias_central', $tablasCentral)) {
        echo "<h4>🔍 Tabla 'categorias_central' (nueva) encontrada en BD Central</h4>";
        
        $stmt = $pdoCentral->prepare("SELECT COUNT(*) FROM categorias_central");
        $stmt->execute();
        $totalCategoriasCentral = $stmt->fetchColumn();
        echo "<p>Total de categorías en categorias_central: $totalCategoriasCentral</p>";
    }
    
    // 2. Revisar sucursales
    echo "<h3>🏢 2. Revisión de Sucursales</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT * FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Total de sucursales activas: " . count($sucursales) . "</p>";
    
    foreach ($sucursales as $sucursal) {
        echo "<h4>🔍 Analizando sucursal: {$sucursal['nombre']}</h4>";
        
        try {
            // Conectar a sucursal
            $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']}";
            $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
            $pdoSucursal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Ver todas las tablas en la sucursal
            $stmt = $pdoSucursal->prepare("SHOW TABLES");
            $stmt->execute();
            $tablasSucursal = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p>📊 Tablas en sucursal: " . count($tablasSucursal) . "</p>";
            echo "<ul>";
            foreach ($tablasSucursal as $tabla) {
                echo "<li>$tabla</li>";
            }
            echo "</ul>";
            
            // Verificar tabla productos
            if (in_array('productos', $tablasSucursal)) {
                echo "<h5>🔍 Estructura de tabla 'productos':</h5>";
                
                $stmt = $pdoSucursal->prepare("DESCRIBE productos");
                $stmt->execute();
                $estructuraProductos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
                foreach ($estructuraProductos as $campo) {
                    echo "<tr>";
                    echo "<td>{$campo['Field']}</td>";
                    echo "<td>{$campo['Type']}</td>";
                    echo "<td>{$campo['Null']}</td>";
                    echo "<td>{$campo['Key']}</td>";
                    echo "<td>{$campo['Default']}</td>";
                    echo "<td>{$campo['Extra']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // Ver algunos productos de ejemplo
                $stmt = $pdoSucursal->prepare("SELECT * FROM productos LIMIT 5");
                $stmt->execute();
                $productosEjemplo = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($productosEjemplo) > 0) {
                    echo "<h5>📦 Ejemplo de productos:</h5>";
                    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                    echo "<tr>";
                    foreach (array_keys($productosEjemplo[0]) as $campo) {
                        echo "<th>$campo</th>";
                    }
                    echo "</tr>";
                    foreach ($productosEjemplo as $producto) {
                        echo "<tr>";
                        foreach ($producto as $valor) {
                            echo "<td>" . (is_null($valor) ? 'NULL' : $valor) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }
            
            // Verificar tabla categorias en sucursal
            if (in_array('categorias', $tablasSucursal)) {
                echo "<h5>🔍 Tabla 'categorias' en sucursal:</h5>";
                
                $stmt = $pdoSucursal->prepare("SELECT COUNT(*) FROM categorias");
                $stmt->execute();
                $totalCategoriasSucursal = $stmt->fetchColumn();
                echo "<p>Total de categorías en sucursal: $totalCategoriasSucursal</p>";
                
                if ($totalCategoriasSucursal > 0) {
                    $stmt = $pdoSucursal->prepare("SELECT * FROM categorias ORDER BY categoria LIMIT 10");
                    $stmt->execute();
                    $categoriasSucursal = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                    echo "<tr>";
                    foreach (array_keys($categoriasSucursal[0]) as $campo) {
                        echo "<th>$campo</th>";
                    }
                    echo "</tr>";
                    foreach ($categoriasSucursal as $cat) {
                        echo "<tr>";
                        foreach ($cat as $valor) {
                            echo "<td>" . (is_null($valor) ? 'NULL' : $valor) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }
            
        } catch (Exception $e) {
            echo "<p>❌ Error conectando a sucursal {$sucursal['nombre']}: " . $e->getMessage() . "</p>";
        }
    }
    
    // 3. Análisis de la relación entre tablas
    echo "<h3>🔗 3. Análisis de Relaciones</h3>";
    
    echo "<h4>📋 Posibles estructuras de categorías:</h4>";
    echo "<ol>";
    echo "<li><strong>Tabla 'categorias' en BD Central:</strong> Categorías maestras</li>";
    echo "<li><strong>Tabla 'categorias' en BD Local:</strong> Categorías locales (sincronizadas desde central)</li>";
    echo "<li><strong>Tabla 'productos' en BD Local:</strong> Productos con referencia a categorías locales</li>";
    echo "</ol>";
    
    echo "<h4>🔍 Posibles campos de relación:</h4>";
    echo "<ul>";
    echo "<li><strong>productos.categoria_id:</strong> ID de la categoría en tabla local</li>";
    echo "<li><strong>productos.id_categoria:</strong> ID de la categoría en tabla local</li>";
    echo "<li><strong>productos.categoria:</strong> Nombre directo de la categoría</li>";
    echo "<li><strong>Otra estructura:</strong> Necesitamos identificar el campo correcto</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>🎯 Próximos Pasos</h3>";
echo "<ol>";
echo "<li>Revisar la estructura mostrada arriba</li>";
echo "<li>Identificar cómo se relacionan productos con categorías</li>";
echo "<li>Verificar si existe tabla 'categorias' en BD Central (original)</li>";
echo "<li>Entender el flujo de sincronización actual</li>";
echo "<li>Adaptar los scripts a la estructura real</li>";
echo "</ol>";
?>
