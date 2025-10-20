<?php
/**
 * Script para verificar la estructura de la tabla sucursales en BD Central
 */

echo "<h2>🔍 Verificación de Estructura - Tabla Sucursales</h2>";

try {
    require_once "api-transferencias/conexion-central.php";
    
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
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Estructura de la tabla 'sucursales':</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th><th>Extra</th></tr>";
        
        foreach($columnas as $columna) {
            echo "<tr>";
            echo "<td><strong>" . $columna['Field'] . "</strong></td>";
            echo "<td>" . $columna['Type'] . "</td>";
            echo "<td>" . $columna['Null'] . "</td>";
            echo "<td>" . $columna['Key'] . "</td>";
            echo "<td>" . $columna['Default'] . "</td>";
            echo "<td>" . $columna['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si hay datos
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM sucursales");
        $stmt->execute();
        $total = $stmt->fetch()['total'];
        
        echo "<h3>📊 Datos en la tabla:</h3>";
        echo "<p><strong>Total de registros:</strong> $total</p>";
        
        if($total > 0) {
            // Mostrar algunos registros de ejemplo
            $stmt = $conexion->prepare("SELECT * FROM sucursales LIMIT 5");
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>🔍 Primeros 5 registros:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            if(!empty($registros)) {
                // Encabezados
                echo "<tr>";
                foreach(array_keys($registros[0]) as $campo) {
                    echo "<th>$campo</th>";
                }
                echo "</tr>";
                
                // Datos
                foreach($registros as $registro) {
                    echo "<tr>";
                    foreach($registro as $valor) {
                        echo "<td>" . htmlspecialchars($valor) . "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        }
        
        // Verificar si existe columna 'activa'
        $tieneActiva = false;
        foreach($columnas as $columna) {
            if($columna['Field'] === 'activa') {
                $tieneActiva = true;
                break;
            }
        }
        
        echo "<h3>🔍 Verificación de columna 'activa':</h3>";
        if($tieneActiva) {
            echo "<p style='color: green;'>✅ La columna 'activa' existe</p>";
        } else {
            echo "<p style='color: red;'>❌ La columna 'activa' NO existe</p>";
            echo "<h4>💡 Solución:</h4>";
            echo "<p>Necesitas agregar la columna 'activa' a la tabla sucursales:</p>";
            echo "<pre>ALTER TABLE sucursales ADD COLUMN activa BOOLEAN DEFAULT TRUE;</pre>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ La tabla 'sucursales' NO existe</p>";
        echo "<h4>💡 Solución:</h4>";
        echo "<p>Necesitas crear la tabla sucursales primero:</p>";
        echo "<pre>CREATE TABLE sucursales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo_sucursal VARCHAR(50) UNIQUE NOT NULL,
    activa BOOLEAN DEFAULT TRUE
);</pre>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

echo "<h3>📋 Próximos pasos:</h3>";
echo "<p>1. Si falta la columna 'activa', ejecuta el ALTER TABLE</p>";
echo "<p>2. Si falta la tabla, ejecuta el CREATE TABLE</p>";
echo "<p>3. Luego prueba nuevamente <a href='usuarios-central' target='_blank'>usuarios-central</a></p>";
?>
