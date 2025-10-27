<?php
/**
 * Script para verificar la estructura de la tabla medios_pago
 */

echo "<h2>🔍 Verificación de Estructura de Tabla medios_pago</h2>\n";

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
    
    // Verificar estructura de la tabla medios_pago
    echo "<h3>📋 Estructura de la tabla medios_pago:</h3>\n";
    
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
    
    // Verificar datos existentes
    echo "<h3>📊 Datos existentes en medios_pago:</h3>\n";
    
    $stmt = $pdo->query("SELECT * FROM medios_pago LIMIT 5");
    $datos = $stmt->fetchAll();
    
    if (empty($datos)) {
        echo "<p style='color: orange;'>⚠️ No hay datos en la tabla medios_pago</p>\n";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr style='background: #f0f0f0;'>\n";
        
        // Mostrar columnas disponibles
        $primera_fila = $datos[0];
        foreach (array_keys($primera_fila) as $columna) {
            echo "<th>$columna</th>\n";
        }
        echo "</tr>\n";
        
        foreach ($datos as $fila) {
            echo "<tr>\n";
            foreach ($fila as $valor) {
                echo "<td>" . htmlspecialchars($valor ?? 'NULL') . "</td>\n";
            }
            echo "</tr>\n";
        }
        
        echo "</table>\n";
    }
    
    // Verificar si existe la columna descripcion
    $tiene_descripcion = false;
    foreach ($columnas as $columna) {
        if ($columna['Field'] === 'descripcion') {
            $tiene_descripcion = true;
            break;
        }
    }
    
    echo "<h3>🔍 Análisis de Columnas:</h3>\n";
    echo "<p><strong>¿Tiene columna 'descripcion'?</strong> " . ($tiene_descripcion ? '✅ Sí' : '❌ No') . "</p>\n";
    
    if (!$tiene_descripcion) {
        echo "<h3>🔧 Solución Requerida:</h3>\n";
        echo "<p>La tabla medios_pago no tiene la columna 'descripcion'. Necesitas:</p>\n";
        echo "<ol>\n";
        echo "<li><strong>Agregar la columna:</strong> ALTER TABLE medios_pago ADD COLUMN descripcion VARCHAR(255) DEFAULT ''</li>\n";
        echo "<li><strong>O modificar la consulta:</strong> Quitar 'descripcion' del SELECT</li>\n";
        echo "</ol>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>\n";
}
?>
