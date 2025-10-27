<?php
/**
 * Script para agregar columna descripcion a la tabla medios_pago
 */

echo "<h2>🔧 Agregar Columna descripcion a medios_pago</h2>\n";

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
    
    // Verificar si existe la columna descripcion
    echo "<h3>🔍 Verificando columna descripcion...</h3>\n";
    
    $stmt = $pdo->query("DESCRIBE medios_pago");
    $columnas = $stmt->fetchAll();
    
    $tiene_descripcion = false;
    foreach ($columnas as $columna) {
        if ($columna['Field'] === 'descripcion') {
            $tiene_descripcion = true;
            break;
        }
    }
    
    if ($tiene_descripcion) {
        echo "<p style='color: green;'>✅ La columna 'descripcion' ya existe</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ La columna 'descripcion' no existe</p>\n";
        
        // Agregar la columna descripcion
        echo "<h3>🔧 Agregando columna descripcion...</h3>\n";
        
        $sql = "ALTER TABLE medios_pago ADD COLUMN descripcion VARCHAR(255) DEFAULT '' AFTER nombre";
        
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Columna 'descripcion' agregada exitosamente</p>\n";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Error al agregar columna: " . $e->getMessage() . "</p>\n";
        }
    }
    
    // Verificar estructura final
    echo "<h3>📋 Estructura final de la tabla medios_pago:</h3>\n";
    
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
    
    // Actualizar registros existentes con descripciones por defecto
    echo "<h3>🔄 Actualizando registros existentes...</h3>\n";
    
    $stmt = $pdo->query("SELECT id, nombre FROM medios_pago WHERE descripcion = '' OR descripcion IS NULL");
    $registros = $stmt->fetchAll();
    
    if (!empty($registros)) {
        echo "<p>Actualizando " . count($registros) . " registros...</p>\n";
        
        foreach ($registros as $registro) {
            $descripcion = "Medio de pago: " . $registro['nombre'];
            
            $stmt_update = $pdo->prepare("UPDATE medios_pago SET descripcion = ? WHERE id = ?");
            $stmt_update->execute([$descripcion, $registro['id']]);
            
            echo "<p>✅ Actualizado: {$registro['nombre']} → $descripcion</p>\n";
        }
    } else {
        echo "<p style='color: green;'>✅ Todos los registros ya tienen descripción</p>\n";
    }
    
    echo "<h3>🎯 Resultado:</h3>\n";
    echo "<p>La tabla medios_pago ahora tiene la columna 'descripcion' y la API funcionará correctamente.</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
