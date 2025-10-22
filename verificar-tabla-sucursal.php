<?php
/**
 * SCRIPT PARA VERIFICAR ESTRUCTURA DE TABLA sucursal_local
 */

echo "<h2>🔍 VERIFICAR ESTRUCTURA DE TABLA sucursal_local</h2>";

try {
    require_once "config.php";
    require_once "modelos/conexion.php";
    
    $conexion = Conexion::conectar();
    
    // Verificar estructura de la tabla
    $stmt = $conexion->prepare("DESCRIBE sucursal_local");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Estructura actual de sucursal_local:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $columnasBD = ['usuario_bd', 'password_bd', 'nombre_bd', 'host_bd', 'puerto_bd'];
    $tieneColumnasBD = false;
    
    foreach ($columnas as $columna) {
        $esColumnaBD = in_array($columna['Field'], $columnasBD);
        $estilo = $esColumnaBD ? "background: #d4edda;" : "";
        
        echo "<tr style='$estilo'>";
        echo "<td>" . $columna['Field'] . ($esColumnaBD ? " ✅" : "") . "</td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "<td>" . $columna['Default'] . "</td>";
        echo "<td>" . $columna['Extra'] . "</td>";
        echo "</tr>";
        
        if ($esColumnaBD) {
            $tieneColumnasBD = true;
        }
    }
    echo "</table>";
    
    // Verificar si tiene las columnas de BD
    echo "<h3>🔍 Verificación de columnas de BD:</h3>";
    
    $columnasExistentes = array_column($columnas, 'Field');
    $columnasFaltantes = array_diff($columnasBD, $columnasExistentes);
    
    if (empty($columnasFaltantes)) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Todas las columnas de BD están presentes</strong><br>";
        echo "La tabla tiene la estructura correcta para los campos de configuración de BD.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Faltan las siguientes columnas de BD:</strong><br>";
        foreach ($columnasFaltantes as $columna) {
            echo "• " . $columna . "<br>";
        }
        echo "<br><strong>Acción requerida:</strong> Ejecutar el script de actualización de tabla.";
        echo "</div>";
    }
    
    // Mostrar datos actuales si existen
    $stmt = $conexion->prepare("SELECT * FROM sucursal_local LIMIT 1");
    $stmt->execute();
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($datos) {
        echo "<h3>📊 Datos actuales en sucursal_local:</h3>";
        echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
        foreach ($datos as $campo => $valor) {
            $esColumnaBD = in_array($campo, $columnasBD);
            $estilo = $esColumnaBD ? "font-weight: bold; color: green;" : "";
            echo "<strong style='$estilo'>" . $campo . ":</strong> " . ($valor ?: 'NULL') . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "<strong>⚠️ No hay datos en la tabla sucursal_local</strong><br>";
        echo "La tabla está vacía. Puedes configurar la sucursal local desde el panel de administración.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Verificación completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
