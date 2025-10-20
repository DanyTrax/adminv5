<?php
// Script para agregar campos de conexión a BD en tabla sucursales
require_once __DIR__ . "/api-transferencias/conexion-central.php";

echo "<h2>🔧 Agregar Campos de Conexión a BD - Tabla Sucursales</h2>";

try {
    $conexion = ConexionCentral::conectar();
    
    // Verificar estructura actual
    $stmt = $conexion->prepare("DESCRIBE sucursales");
    $stmt->execute();
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $camposExistentes = array_column($estructura, 'Field');
    
    echo "<h3>📋 Campos actuales:</h3>";
    echo "<ul>";
    foreach($camposExistentes as $campo) {
        echo "<li>" . htmlspecialchars($campo) . "</li>";
    }
    echo "</ul>";
    
    // Campos de conexión que necesitamos
    $camposConexion = [
        'usuario_bd' => 'VARCHAR(100) NOT NULL DEFAULT ""',
        'password_bd' => 'VARCHAR(255) NOT NULL DEFAULT ""',
        'nombre_bd' => 'VARCHAR(100) NOT NULL DEFAULT ""',
        'host_bd' => 'VARCHAR(255) NOT NULL DEFAULT "localhost"',
        'puerto_bd' => 'INT(11) NOT NULL DEFAULT 3306'
    ];
    
    echo "<h3>🔌 Agregando campos de conexión:</h3>";
    
    foreach($camposConexion as $campo => $definicion) {
        if(!in_array($campo, $camposExistentes)) {
            try {
                $sql = "ALTER TABLE sucursales ADD COLUMN $campo $definicion";
                $conexion->exec($sql);
                echo "<p style='color: green;'>✅ Campo '$campo' agregado correctamente</p>";
            } catch(Exception $e) {
                echo "<p style='color: red;'>❌ Error agregando '$campo': " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Campo '$campo' ya existe</p>";
        }
    }
    
    // Actualizar datos de ejemplo para las sucursales existentes
    echo "<h3>📝 Actualizando datos de ejemplo:</h3>";
    
    $sucursales = [
        ['id' => 5, 'codigo' => 'SUC001', 'nombre' => 'PRUEBAS UNO', 'usuario_bd' => 'epicosie_pruebas', 'password_bd' => 'password123', 'nombre_bd' => 'epicosie_pruebas', 'host_bd' => 'localhost', 'puerto_bd' => 3306],
        ['id' => 6, 'codigo' => 'SUC002', 'nombre' => 'PRUEBAS DOS', 'usuario_bd' => 'epicosie_pruebas2', 'password_bd' => 'password123', 'nombre_bd' => 'epicosie_pruebas2', 'host_bd' => 'localhost', 'puerto_bd' => 3306],
        ['id' => 7, 'codigo' => 'SUC003', 'nombre' => 'infinito', 'usuario_bd' => 'epicosie_pruebas', 'password_bd' => 'password123', 'nombre_bd' => 'epicosie_pruebas', 'host_bd' => 'localhost', 'puerto_bd' => 3306]
    ];
    
    foreach($sucursales as $sucursal) {
        try {
            $stmt = $conexion->prepare("
                UPDATE sucursales SET 
                    usuario_bd = ?, 
                    password_bd = ?, 
                    nombre_bd = ?, 
                    host_bd = ?, 
                    puerto_bd = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $sucursal['usuario_bd'],
                $sucursal['password_bd'],
                $sucursal['nombre_bd'],
                $sucursal['host_bd'],
                $sucursal['puerto_bd'],
                $sucursal['id']
            ]);
            
            echo "<p style='color: green;'>✅ Sucursal '{$sucursal['nombre']}' actualizada</p>";
            
        } catch(Exception $e) {
            echo "<p style='color: red;'>❌ Error actualizando '{$sucursal['nombre']}': " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Verificar estructura final
    echo "<h3>📋 Estructura final:</h3>";
    $stmt = $conexion->prepare("DESCRIBE sucursales");
    $stmt->execute();
    $estructuraFinal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th><th>Extra</th></tr>";
    
    foreach($estructuraFinal as $campo) {
        $color = in_array($campo['Field'], array_keys($camposConexion)) ? 'background-color: #e8f5e8;' : '';
        echo "<tr style='$color'>";
        echo "<td>" . htmlspecialchars($campo['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 4px; margin: 15px 0;'>";
    echo "<h4>✅ Campos de conexión agregados correctamente</h4>";
    echo "<p>Ahora cada sucursal tiene la información necesaria para conectarse a su base de datos local.</p>";
    echo "</div>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
