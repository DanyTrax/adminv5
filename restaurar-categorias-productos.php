<?php
/**
 * Script para restaurar las categorías de productos basándose en un backup
 * Este script permite restaurar las categorías desde un archivo SQL de backup
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔄 Restaurador de Categorías de Productos</h1>";

// Función para leer archivo SQL
function leerArchivoSQL($archivo) {
    if (!file_exists($archivo)) {
        throw new Exception("El archivo $archivo no existe");
    }
    
    $contenido = file_get_contents($archivo);
    if ($contenido === false) {
        throw new Exception("No se pudo leer el archivo $archivo");
    }
    
    return $contenido;
}

// Función para extraer datos de productos desde SQL
function extraerProductosDesdeSQL($sql) {
    $productos = [];
    
    // Buscar INSERT statements de productos
    preg_match_all('/INSERT INTO `productos`[^;]+;/i', $sql, $matches);
    
    foreach ($matches[0] as $insert) {
        // Extraer valores del INSERT
        preg_match('/VALUES\s*\((.*?)\);?$/is', $insert, $valuesMatch);
        if (isset($valuesMatch[1])) {
            $values = $valuesMatch[1];
            
            // Parsear valores (simplificado)
            $valores = explode(',', $values);
            if (count($valores) >= 3) {
                $codigo = trim($valores[1], "'\"");
                $descripcion = trim($valores[2], "'\"");
                $categoria = isset($valores[3]) ? trim($valores[3], "'\"") : '';
                
                if (!empty($codigo) && !empty($categoria)) {
                    $productos[] = [
                        'codigo' => $codigo,
                        'descripcion' => $descripcion,
                        'categoria' => $categoria
                    ];
                }
            }
        }
    }
    
    return $productos;
}

// Función para actualizar categorías en base de datos
function actualizarCategoriasProductos($pdo, $productos) {
    $stmt = $pdo->prepare("UPDATE productos SET categoria = ? WHERE codigo = ?");
    $actualizados = 0;
    $errores = 0;
    
    foreach ($productos as $producto) {
        try {
            $stmt->execute([$producto['categoria'], $producto['codigo']]);
            $actualizados++;
        } catch (Exception $e) {
            $errores++;
            echo "<p>❌ Error actualizando producto {$producto['codigo']}: " . $e->getMessage() . "</p>";
        }
    }
    
    return ['actualizados' => $actualizados, 'errores' => $errores];
}

// Procesar archivo de backup si se proporciona
if (isset($_POST['archivo_backup']) && !empty($_POST['archivo_backup'])) {
    $archivoBackup = $_POST['archivo_backup'];
    
    try {
        echo "<h3>📁 Procesando archivo: $archivoBackup</h3>";
        
        // Leer archivo SQL
        $sql = leerArchivoSQL($archivoBackup);
        echo "<p>✅ Archivo leído correctamente (" . number_format(strlen($sql)) . " bytes)</p>";
        
        // Extraer productos
        $productos = extraerProductosDesdeSQL($sql);
        echo "<p>📦 Productos encontrados en backup: " . count($productos) . "</p>";
        
        if (count($productos) > 0) {
            // Mostrar muestra de productos
            echo "<h4>🔍 Muestra de productos encontrados:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Código</th><th>Descripción</th><th>Categoría</th></tr>";
            for ($i = 0; $i < min(10, count($productos)); $i++) {
                $p = $productos[$i];
                echo "<tr><td>{$p['codigo']}</td><td>{$p['descripcion']}</td><td>{$p['categoria']}</td></tr>";
            }
            echo "</table>";
            
            // Conectar a base de datos
            require_once __DIR__ . "/config.php";
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "<p>✅ Conexión a base de datos establecida</p>";
            
            // Actualizar categorías
            echo "<h3>🔄 Actualizando categorías de productos...</h3>";
            $resultado = actualizarCategoriasProductos($pdo, $productos);
            
            echo "<p>✅ Productos actualizados: {$resultado['actualizados']}</p>";
            echo "<p>❌ Errores: {$resultado['errores']}</p>";
            
            // Verificar resultado
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria IS NOT NULL AND categoria != ''");
            $stmt->execute();
            $productosConCategoria = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos");
            $stmt->execute();
            $totalProductos = $stmt->fetchColumn();
            
            echo "<h3>📊 Resultado Final</h3>";
            echo "<p>Total de productos: $totalProductos</p>";
            echo "<p>Productos con categoría: $productosConCategoria</p>";
            echo "<p>Productos sin categoría: " . ($totalProductos - $productosConCategoria) . "</p>";
            
        } else {
            echo "<p>⚠️ No se encontraron productos con categorías en el backup</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error procesando backup: " . $e->getMessage() . "</p>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Restaurador de Categorías</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 4px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="info">
        <h3>📋 Instrucciones</h3>
        <ol>
            <li>Sube tu archivo de backup SQL a la carpeta del proyecto</li>
            <li>Ingresa la ruta del archivo en el campo de abajo</li>
            <li>El script extraerá las categorías de productos del backup</li>
            <li>Actualizará la base de datos actual con esas categorías</li>
        </ol>
    </div>
    
    <div class="warning">
        <h3>⚠️ Advertencia</h3>
        <p>Este proceso actualizará las categorías de todos los productos. Asegúrate de tener un backup actual antes de proceder.</p>
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label for="archivo_backup">Ruta del archivo de backup SQL:</label>
            <input type="text" name="archivo_backup" id="archivo_backup" 
                   placeholder="Ej: backup_2024_01_15.sql" required>
            <small>Ejemplo: backup_2024_01_15.sql o /ruta/completa/backup.sql</small>
        </div>
        
        <button type="submit">🔄 Restaurar Categorías</button>
    </form>
    
    <div class="info">
        <h3>🔍 Comandos SQL para Verificar</h3>
        <p>Antes de ejecutar, puedes verificar el estado actual con estos comandos:</p>
        <pre>
-- Ver productos sin categoría
SELECT COUNT(*) as productos_sin_categoria 
FROM productos 
WHERE categoria IS NULL OR categoria = '';

-- Ver productos con categoría
SELECT COUNT(*) as productos_con_categoria 
FROM productos 
WHERE categoria IS NOT NULL AND categoria != '';

-- Ver categorías más usadas
SELECT categoria, COUNT(*) as total_productos 
FROM productos 
WHERE categoria IS NOT NULL AND categoria != ''
GROUP BY categoria 
ORDER BY total_productos DESC 
LIMIT 10;
        </pre>
    </div>
</body>
</html>
