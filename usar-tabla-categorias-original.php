<?php
/**
 * Script para usar la tabla 'categorias' original en lugar de 'categorias_central'
 * Este script respeta la estructura existente que funciona bien
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Usar Tabla Categorías Original</h1>";

try {
    // Conectar a BD Central
    require_once __DIR__ . "/api-transferencias/conexion-central.php";
    $pdoCentral = ConexionCentral::conectar();
    echo "<p>✅ Conexión a BD Central establecida</p>";
    
    // 1. Verificar si existe tabla 'categorias' original
    $stmt = $pdoCentral->prepare("SHOW TABLES LIKE 'categorias'");
    $stmt->execute();
    $existeTablaCategorias = $stmt->fetch();
    
    if (!$existeTablaCategorias) {
        echo "<p>❌ No existe tabla 'categorias' en BD Central</p>";
        echo "<p>💡 Necesitamos crear la tabla 'categorias' original</p>";
        
        // Crear tabla categorias original
        $sqlCrearTabla = "
            CREATE TABLE IF NOT EXISTS categorias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                categoria VARCHAR(255) NOT NULL UNIQUE,
                descripcion TEXT,
                activo TINYINT(1) DEFAULT 1,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdoCentral->exec($sqlCrearTabla);
        echo "<p>✅ Tabla 'categorias' creada en BD Central</p>";
    } else {
        echo "<p>✅ Tabla 'categorias' existe en BD Central</p>";
    }
    
    // 2. Ver estructura de tabla categorias
    echo "<h3>📋 Estructura de tabla 'categorias':</h3>";
    
    $stmt = $pdoCentral->prepare("DESCRIBE categorias");
    $stmt->execute();
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
    foreach ($estructura as $campo) {
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
    
    // 3. Ver datos actuales en tabla categorias
    $stmt = $pdoCentral->prepare("SELECT * FROM categorias ORDER BY categoria");
    $stmt->execute();
    $categoriasExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📦 Categorías existentes en tabla 'categorias':</h3>";
    echo "<p>Total: " . count($categoriasExistentes) . "</p>";
    
    if (count($categoriasExistentes) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Categoría</th><th>Descripción</th><th>Activa</th><th>Fecha Creación</th></tr>";
        foreach ($categoriasExistentes as $cat) {
            $activa = $cat['activo'] ? 'Sí' : 'No';
            echo "<tr>";
            echo "<td>{$cat['id']}</td>";
            echo "<td>{$cat['categoria']}</td>";
            echo "<td>{$cat['descripcion']}</td>";
            echo "<td>$activa</td>";
            echo "<td>{$cat['fecha_creacion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Analizar sucursales para obtener categorías de productos
    echo "<h3>🏢 Analizando sucursales para obtener categorías de productos:</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT * FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $todasLasCategorias = [];
    $categoriasConProductos = [];
    
    foreach ($sucursales as $sucursal) {
        echo "<h4>🔍 Analizando sucursal: {$sucursal['nombre']}</h4>";
        
        try {
            // Conectar a sucursal
            $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']}";
            $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
            $pdoSucursal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Verificar estructura de tabla productos
            $stmt = $pdoSucursal->prepare("DESCRIBE productos");
            $stmt->execute();
            $estructuraProductos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p>📊 Campos en tabla productos:</p>";
            echo "<ul>";
            foreach ($estructuraProductos as $campo) {
                echo "<li>{$campo['Field']} ({$campo['Type']})</li>";
            }
            echo "</ul>";
            
            // Buscar campo de categoría (puede ser categoria, categoria_id, id_categoria, etc.)
            $camposCategoria = [];
            foreach ($estructuraProductos as $campo) {
                if (stripos($campo['Field'], 'categoria') !== false) {
                    $camposCategoria[] = $campo['Field'];
                }
            }
            
            if (count($camposCategoria) > 0) {
                echo "<p>🔍 Campos relacionados con categoría: " . implode(', ', $camposCategoria) . "</p>";
                
                // Intentar obtener categorías de productos
                foreach ($camposCategoria as $campoCategoria) {
                    try {
                        $stmt = $pdoSucursal->prepare("
                            SELECT DISTINCT $campoCategoria, COUNT(*) as total_productos
                            FROM productos 
                            WHERE $campoCategoria IS NOT NULL 
                              AND $campoCategoria != ''
                              AND $campoCategoria != 'NULL'
                            GROUP BY $campoCategoria
                            ORDER BY total_productos DESC
                        ");
                        $stmt->execute();
                        $categoriasSucursal = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($categoriasSucursal) > 0) {
                            echo "<p>📦 Categorías encontradas en campo '$campoCategoria': " . count($categoriasSucursal) . "</p>";
                            
                            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                            echo "<tr><th>Categoría</th><th>Productos</th></tr>";
                            foreach ($categoriasSucursal as $cat) {
                                echo "<tr><td>{$cat[$campoCategoria]}</td><td>{$cat['total_productos']}</td></tr>";
                                
                                // Acumular para análisis general
                                $todasLasCategorias[] = $cat[$campoCategoria];
                                if (!isset($categoriasConProductos[$cat[$campoCategoria]])) {
                                    $categoriasConProductos[$cat[$campoCategoria]] = 0;
                                }
                                $categoriasConProductos[$cat[$campoCategoria]] += $cat['total_productos'];
                            }
                            echo "</table>";
                        }
                    } catch (Exception $e) {
                        echo "<p>⚠️ Error consultando campo '$campoCategoria': " . $e->getMessage() . "</p>";
                    }
                }
            } else {
                echo "<p>⚠️ No se encontraron campos relacionados con categoría en tabla productos</p>";
            }
            
        } catch (Exception $e) {
            echo "<p>❌ Error conectando a sucursal {$sucursal['nombre']}: " . $e->getMessage() . "</p>";
        }
    }
    
    // 5. Procesar categorías encontradas
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
        
        // 6. Crear categorías faltantes en tabla 'categorias'
        $categoriasExistentesNombres = array_column($categoriasExistentes, 'categoria');
        $categoriasFaltantes = array_diff($todasLasCategorias, $categoriasExistentesNombres);
        
        echo "<h3>🆕 Categorías Faltantes en tabla 'categorias'</h3>";
        echo "<p>Total: " . count($categoriasFaltantes) . "</p>";
        
        if (count($categoriasFaltantes) > 0) {
            echo "<h4>📝 Creando categorías faltantes:</h4>";
            
            $stmt = $pdoCentral->prepare("
                INSERT INTO categorias (categoria, descripcion, activo, fecha_creacion) 
                VALUES (?, '', 1, NOW())
            ");
            
            $categoriasCreadas = 0;
            foreach ($categoriasFaltantes as $categoria) {
                try {
                    $stmt->execute([$categoria]);
                    $categoriasCreadas++;
                    echo "<p>✅ Categoría creada: $categoria</p>";
                } catch (Exception $e) {
                    echo "<p>❌ Error creando categoría $categoria: " . $e->getMessage() . "</p>";
                }
            }
            
            echo "<p>📊 Total de categorías creadas: $categoriasCreadas</p>";
        } else {
            echo "<p>ℹ️ Todas las categorías ya existen en tabla 'categorias'</p>";
        }
    }
    
    // 7. Verificación final
    echo "<h3>✅ Verificación Final</h3>";
    
    $stmt = $pdoCentral->prepare("SELECT COUNT(*) FROM categorias");
    $stmt->execute();
    $totalCategorias = $stmt->fetchColumn();
    
    echo "<p>📊 Total de categorías en tabla 'categorias': $totalCategorias</p>";
    
    $stmt = $pdoCentral->prepare("SELECT * FROM categorias ORDER BY categoria");
    $stmt->execute();
    $todasCategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>📋 Lista completa de categorías:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Categoría</th><th>Activa</th><th>Fecha Creación</th></tr>";
    foreach ($todasCategorias as $cat) {
        $activa = $cat['activo'] ? 'Sí' : 'No';
        echo "<tr>";
        echo "<td>{$cat['id']}</td>";
        echo "<td>{$cat['categoria']}</td>";
        echo "<td>$activa</td>";
        echo "<td>{$cat['fecha_creacion']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>🎯 Próximos Pasos</h3>";
echo "<ol>";
echo "<li>Verificar que todas las categorías estén en tabla 'categorias'</li>";
echo "<li>Modificar el sistema de sincronización para usar tabla 'categorias' en lugar de 'categorias_central'</li>";
echo "<li>Actualizar el código de sincronización para que funcione con la estructura existente</li>";
echo "<li>Probar la sincronización con la tabla original</li>";
echo "</ol>";
?>
