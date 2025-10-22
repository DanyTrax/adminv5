<?php
/**
 * SCRIPT PARA ACTUALIZAR TABLA sucursal_local EN CPANEL
 * Este script agrega las columnas de base de datos que faltan
 */

echo "<h2>🔧 ACTUALIZAR TABLA sucursal_local EN CPANEL</h2>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";

try {
    // Conectar a la base de datos local
    require_once "config.php";
    require_once "modelos/conexion.php";
    
    $conexion = Conexion::conectar();
    
    echo "<h3>📋 1. Verificando estructura actual de la tabla:</h3>";
    
    // Verificar estructura actual
    $stmt = $conexion->prepare("DESCRIBE sucursal_local");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Columnas actuales:</strong><br>";
    foreach ($columnas as $columna) {
        echo "• " . $columna['Field'] . " (" . $columna['Type'] . ")<br>";
    }
    echo "</div>";
    
    // Verificar qué columnas faltan
    $columnasExistentes = array_column($columnas, 'Field');
    $columnasNecesarias = ['usuario_bd', 'password_bd', 'nombre_bd', 'host_bd', 'puerto_bd'];
    $columnasFaltantes = array_diff($columnasNecesarias, $columnasExistentes);
    
    if (empty($columnasFaltantes)) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Todas las columnas de BD ya existen</strong><br>";
        echo "La tabla ya tiene la estructura correcta.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Faltan las siguientes columnas:</strong><br>";
        foreach ($columnasFaltantes as $columna) {
            echo "• " . $columna . "<br>";
        }
        echo "</div>";
        
        echo "<h3>🔧 2. Agregando columnas faltantes:</h3>";
        
        // Agregar columnas faltantes
        $sqls = [
            "ALTER TABLE sucursal_local ADD COLUMN usuario_bd VARCHAR(50) AFTER email",
            "ALTER TABLE sucursal_local ADD COLUMN password_bd VARCHAR(255) AFTER usuario_bd",
            "ALTER TABLE sucursal_local ADD COLUMN nombre_bd VARCHAR(100) AFTER password_bd",
            "ALTER TABLE sucursal_local ADD COLUMN host_bd VARCHAR(255) AFTER nombre_bd",
            "ALTER TABLE sucursal_local ADD COLUMN puerto_bd INT DEFAULT 3306 AFTER host_bd"
        ];
        
        foreach ($sqls as $sql) {
            $nombreColumna = explode(" ", $sql)[5]; // Extraer nombre de columna
            
            if (in_array($nombreColumna, $columnasFaltantes)) {
                try {
                    $stmt = $conexion->prepare($sql);
                    if ($stmt->execute()) {
                        echo "<div style='background: #d4edda; padding: 5px; border-radius: 3px; margin: 2px;'>";
                        echo "✅ Columna agregada: " . $nombreColumna;
                        echo "</div>";
                    } else {
                        echo "<div style='background: #f8d7da; padding: 5px; border-radius: 3px; margin: 2px;'>";
                        echo "❌ Error agregando: " . $nombreColumna;
                        echo "</div>";
                    }
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
                        echo "<div style='background: #fff3cd; padding: 5px; border-radius: 3px; margin: 2px;'>";
                        echo "⚠️ Columna ya existe: " . $nombreColumna;
                        echo "</div>";
                    } else {
                        echo "<div style='background: #f8d7da; padding: 5px; border-radius: 3px; margin: 2px;'>";
                        echo "❌ Error: " . $e->getMessage();
                        echo "</div>";
                    }
                }
            }
        }
        
        echo "<h3>🔍 3. Verificando estructura final:</h3>";
        
        // Verificar estructura final
        $stmt = $conexion->prepare("DESCRIBE sucursal_local");
        $stmt->execute();
        $columnasFinales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Estructura final de sucursal_local:</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columnasFinales as $columna) {
            echo "<tr>";
            echo "<td>" . $columna['Field'] . "</td>";
            echo "<td>" . $columna['Type'] . "</td>";
            echo "<td>" . $columna['Null'] . "</td>";
            echo "<td>" . $columna['Key'] . "</td>";
            echo "<td>" . $columna['Default'] . "</td>";
            echo "<td>" . $columna['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
        // Verificar que todas las columnas necesarias existen
        $columnasFinalesNombres = array_column($columnasFinales, 'Field');
        $todasExisten = true;
        foreach ($columnasNecesarias as $columna) {
            if (!in_array($columna, $columnasFinalesNombres)) {
                $todasExisten = false;
                break;
            }
        }
        
        if ($todasExisten) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
            echo "<strong>🎉 ¡Actualización completada exitosamente!</strong><br>";
            echo "La tabla sucursal_local ahora tiene todas las columnas necesarias para los campos de BD.";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
            echo "<strong>❌ Error: Algunas columnas no se pudieron agregar</strong><br>";
            echo "Revisa los errores anteriores y ejecuta el script nuevamente.";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . " (línea " . $e->getLine() . ")<br>";
    echo "</div>";
}

echo "<h3>📝 Instrucciones:</h3>";
echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
echo "<strong>1. Subir este script al servidor cPanel</strong><br>";
echo "<strong>2. Ejecutar desde el navegador:</strong> https://tudominio.com/actualizar-tabla-cpanel.php<br>";
echo "<strong>3. Verificar que no haya errores</strong><br>";
echo "<strong>4. Probar el formulario de configuración local</strong><br>";
echo "<strong>5. Eliminar este script después de usarlo</strong>";
echo "</div>";

echo "<p><em>Script generado automáticamente - " . date('Y-m-d H:i:s') . "</em></p>";
?>
