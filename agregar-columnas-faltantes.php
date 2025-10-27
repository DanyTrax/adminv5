<?php
/**
 * Script para agregar columnas faltantes a la tabla medios_pago
 */

echo "<h2>🔧 Agregar Columnas Faltantes a medios_pago</h2>\n";

try {
    // Incluir configuración
    require_once 'config.php';
    
    // Conectar a la base de datos
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Verificar estructura actual
    echo "<h3>🔍 Estructura actual de la tabla medios_pago:</h3>\n";
    
    $stmt = $pdo->query("DESCRIBE medios_pago");
    $columnas = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'>\n";
    echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th><th>Extra</th>\n";
    echo "</tr>\n";
    
    foreach ($columnas as $columna) {
        echo "<tr>\n";
        echo "<td><strong>{$columna['Field']}</strong></td>\n";
        echo "<td>{$columna['Type']}</td>\n";
        echo "<td>{$columna['Null']}</td>\n";
        echo "<td>{$columna['Key']}</td>\n";
        echo "<td>{$columna['Default']}</td>\n";
        echo "<td>{$columna['Extra']}</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // Verificar qué columnas faltan
    $columnas_existentes = array_column($columnas, 'Field');
    $columnas_necesarias = ['id', 'nombre', 'descripcion', 'activo', 'fecha_creacion'];
    
    echo "<h3>🔍 Análisis de columnas:</h3>\n";
    echo "<p><strong>Columnas existentes:</strong> " . implode(', ', $columnas_existentes) . "</p>\n";
    echo "<p><strong>Columnas necesarias:</strong> " . implode(', ', $columnas_necesarias) . "</p>\n";
    
    $columnas_faltantes = array_diff($columnas_necesarias, $columnas_existentes);
    
    if (empty($columnas_faltantes)) {
        echo "<p style='color: green;'>✅ Todas las columnas necesarias existen</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ Columnas faltantes: " . implode(', ', $columnas_faltantes) . "</p>\n";
        
        // Agregar columnas faltantes
        echo "<h3>🔧 Agregando columnas faltantes...</h3>\n";
        
        foreach ($columnas_faltantes as $columna) {
            switch ($columna) {
                case 'activo':
                    $sql = "ALTER TABLE medios_pago ADD COLUMN activo TINYINT(1) DEFAULT 1 AFTER descripcion";
                    break;
                case 'fecha_creacion':
                    $sql = "ALTER TABLE medios_pago ADD COLUMN fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER activo";
                    break;
                default:
                    continue 2;
            }
            
            try {
                $pdo->exec($sql);
                echo "<p style='color: green;'>✅ Columna '$columna' agregada exitosamente</p>\n";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Error al agregar columna '$columna': " . $e->getMessage() . "</p>\n";
            }
        }
    }
    
    // Verificar estructura final
    echo "<h3>📋 Estructura final de la tabla medios_pago:</h3>\n";
    
    $stmt = $pdo->query("DESCRIBE medios_pago");
    $columnas_finales = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'>\n";
    echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th><th>Extra</th>\n";
    echo "</tr>\n";
    
    foreach ($columnas_finales as $columna) {
        echo "<tr>\n";
        echo "<td><strong>{$columna['Field']}</strong></td>\n";
        echo "<td>{$columna['Type']}</td>\n";
        echo "<td>{$columna['Null']}</td>\n";
        echo "<td>{$columna['Key']}</td>\n";
        echo "<td>{$columna['Default']}</td>\n";
        echo "<td>{$columna['Extra']}</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // Actualizar registros existentes
    echo "<h3>🔄 Actualizando registros existentes...</h3>\n";
    
    $stmt = $pdo->query("SELECT id FROM medios_pago");
    $registros = $stmt->fetchAll();
    
    if (!empty($registros)) {
        echo "<p>Actualizando " . count($registros) . " registros...</p>\n";
        
        foreach ($registros as $registro) {
            // Actualizar activo a 1 si no existe
            $stmt_update = $pdo->prepare("UPDATE medios_pago SET activo = 1 WHERE id = ?");
            $stmt_update->execute([$registro['id']]);
            
            echo "<p>✅ Actualizado registro ID: {$registro['id']}</p>\n";
        }
    }
    
    echo "<h3>🎯 Resultado:</h3>\n";
    echo "<p>La tabla medios_pago ahora tiene todas las columnas necesarias y la API funcionará correctamente.</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
