<?php
// Script para verificar estructura de tabla sucursales en BD Central
require_once __DIR__ . "/api-transferencias/conexion-central.php";

echo "<h2>🔍 Verificación de Estructura - Tabla Sucursales (BD Central)</h2>";

try {
    $conexion = ConexionCentral::conectar();
    
    // Verificar si la tabla existe
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'sucursales'");
    $stmt->execute();
    $tablaExiste = $stmt->fetch();
    
    if($tablaExiste) {
        echo "<p style='color: green;'>✅ Tabla 'sucursales' existe</p>";
        
        // Obtener estructura de la tabla
        $stmt = $conexion->prepare("DESCRIBE sucursales");
        $stmt->execute();
        $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Estructura de la tabla 'sucursales':</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th><th>Extra</th></tr>";
        
        foreach($estructura as $campo) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($campo['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si existen campos de conexión a BD
        $camposConexion = ['usuario_bd', 'password_bd', 'nombre_bd', 'host_bd', 'puerto_bd'];
        $camposExistentes = array_column($estructura, 'Field');
        
        echo "<h3>🔌 Campos de conexión a BD:</h3>";
        foreach($camposConexion as $campo) {
            if(in_array($campo, $camposExistentes)) {
                echo "<p style='color: green;'>✅ Campo '$campo' existe</p>";
            } else {
                echo "<p style='color: red;'>❌ Campo '$campo' NO existe</p>";
            }
        }
        
        // Mostrar datos actuales
        $stmt = $conexion->prepare("SELECT * FROM sucursales WHERE activo = 1");
        $stmt->execute();
        $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📊 Datos actuales de sucursales activas:</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        if(!empty($sucursales)) {
            $headers = array_keys($sucursales[0]);
            echo "<tr>";
            foreach($headers as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            foreach($sucursales as $sucursal) {
                echo "<tr>";
                foreach($sucursal as $valor) {
                    echo "<td>" . htmlspecialchars($valor) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='10'>No hay sucursales activas</td></tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>❌ La tabla 'sucursales' NO existe</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
