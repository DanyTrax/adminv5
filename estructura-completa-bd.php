<?php
// Script para mostrar la estructura completa de la base de datos local
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🗄️ Estructura Completa de Base de Datos Local</h1>";

try {
    // Conexión directa a la BD local
    $pdo = new PDO("mysql:host=localhost;dbname=epicosie_pruebas;charset=utf8", "epicosie_ricaurte", "m5Wwg)~M{i~*kFr{");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Conexión exitosa a la base de datos local</p>";
    
    // Obtener todas las tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>📋 Resumen de Tablas (" . count($tablas) . " tablas encontradas)</h2>";
    echo "<ol>";
    foreach ($tablas as $tabla) {
        echo "<li><strong>" . $tabla . "</strong></li>";
    }
    echo "</ol>";
    
    echo "<hr>";
    
    // Para cada tabla, mostrar estructura completa
    foreach ($tablas as $tabla) {
        echo "<h2>📊 Tabla: <code>" . $tabla . "</code></h2>";
        
        // Obtener estructura de la tabla
        $stmt = $pdo->query("DESCRIBE `$tabla`");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>🔧 Estructura de la tabla:</h3>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0; font-weight: bold;'>";
        echo "<th style='padding: 8px;'>Campo</th>";
        echo "<th style='padding: 8px;'>Tipo</th>";
        echo "<th style='padding: 8px;'>Nulo</th>";
        echo "<th style='padding: 8px;'>Clave</th>";
        echo "<th style='padding: 8px;'>Por defecto</th>";
        echo "<th style='padding: 8px;'>Extra</th>";
        echo "</tr>";
        
        foreach ($columnas as $columna) {
            $color_fila = '';
            if ($columna['Key'] == 'PRI') $color_fila = 'background: #e8f5e8;';
            if ($columna['Key'] == 'UNI') $color_fila = 'background: #fff3cd;';
            if ($columna['Key'] == 'MUL') $color_fila = 'background: #d1ecf1;';
            
            echo "<tr style='$color_fila'>";
            echo "<td style='padding: 8px; font-weight: bold;'>" . $columna['Field'] . "</td>";
            echo "<td style='padding: 8px;'>" . $columna['Type'] . "</td>";
            echo "<td style='padding: 8px;'>" . $columna['Null'] . "</td>";
            echo "<td style='padding: 8px;'>" . $columna['Key'] . "</td>";
            echo "<td style='padding: 8px;'>" . ($columna['Default'] ?? 'NULL') . "</td>";
            echo "<td style='padding: 8px;'>" . $columna['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Obtener índices
        $stmt = $pdo->query("SHOW INDEX FROM `$tabla`");
        $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($indices)) {
            echo "<h4>🔗 Índices:</h4>";
            echo "<ul>";
            $indices_unicos = [];
            foreach ($indices as $indice) {
                if (!in_array($indice['Key_name'], $indices_unicos)) {
                    $indices_unicos[] = $indice['Key_name'];
                    $tipo = $indice['Non_unique'] == 0 ? 'UNIQUE' : 'INDEX';
                    echo "<li><strong>" . $indice['Key_name'] . "</strong> ($tipo) - " . $indice['Column_name'] . "</li>";
                }
            }
            echo "</ul>";
        }
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p><strong>📈 Registros:</strong> " . $total . "</p>";
        
        // Generar SQL CREATE TABLE
        $stmt = $pdo->query("SHOW CREATE TABLE `$tabla`");
        $create = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h4>💻 SQL CREATE TABLE:</h4>";
        echo "<textarea style='width: 100%; height: 150px; font-family: monospace; font-size: 12px; background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px;' readonly>";
        echo htmlspecialchars($create['Create Table']);
        echo "</textarea>";
        
        echo "<hr style='margin: 30px 0; border: 2px solid #007bff;'>";
    }
    
    // Generar resumen para el instalador
    echo "<h2>🎯 Resumen para Instalador</h2>";
    echo "<p>Basado en la estructura actual, el instalador debería crear estas tablas:</p>";
    
    echo "<h3>📝 Lista de tablas para el instalador:</h3>";
    echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px;'>";
    echo "// Tablas que debe crear el instalador:\n";
    foreach ($tablas as $i => $tabla) {
        echo ($i + 1) . ". " . $tabla . "\n";
    }
    echo "</pre>";
    
    echo "<h3>⚠️ Notas importantes:</h3>";
    echo "<ul>";
    echo "<li>Verificar que todas las tablas tengan la estructura correcta</li>";
    echo "<li>Revisar los tipos de datos y restricciones</li>";
    echo "<li>Verificar que los índices estén incluidos</li>";
    echo "<li>Comprobar que las claves foráneas sean correctas</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error de conexión:</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    
    echo "<h3>🔧 Para solucionar:</h3>";
    echo "<ul>";
    echo "<li>Verificar que la base de datos existe</li>";
    echo "<li>Verificar las credenciales de conexión</li>";
    echo "<li>Verificar que el usuario tiene permisos</li>";
    echo "</ul>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3, h4 { color: #333; }
table { margin: 10px 0; }
th { background: #007bff; color: white; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
textarea { resize: vertical; }
hr { border: none; border-top: 2px solid #dee2e6; margin: 20px 0; }
</style>";
?>
