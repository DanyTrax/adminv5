<?php
/**
 * SCRIPT PARA AGREGAR COLUMNA EMPRESA A TABLA USUARIOS
 */

echo "<h2>🔧 AGREGAR COLUMNA EMPRESA A TABLA USUARIOS</h2>";

try {
    require_once "config.php";
    require_once "modelos/conexion.php";
    
    // 1. Verificar conexión local
    echo "<h3>📋 1. Verificando conexión local:</h3>";
    $conexion = Conexion::conectar();
    if ($conexion) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Conexión local exitosa</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Error en conexión local</strong>";
        echo "</div>";
        exit;
    }
    
    // 2. Verificar si existe la columna empresa
    echo "<h3>📋 2. Verificando columna empresa:</h3>";
    $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios LIKE 'empresa'");
    $stmt->execute();
    $columnaEmpresa = $stmt->fetch();
    
    if ($columnaEmpresa) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ La columna 'empresa' ya existe</strong><br>";
        echo "Tipo: " . $columnaEmpresa['Type'] . "<br>";
        echo "Null: " . $columnaEmpresa['Null'] . "<br>";
        echo "Default: " . ($columnaEmpresa['Default'] ?: 'NULL') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "<strong>⚠️ La columna 'empresa' no existe</strong><br>";
        echo "Se procederá a crearla...";
        echo "</div>";
        
        // 3. Agregar columna empresa
        echo "<h3>🔧 3. Agregando columna empresa:</h3>";
        
        $stmt = $conexion->prepare("
            ALTER TABLE usuarios 
            ADD COLUMN empresa VARCHAR(100) NULL 
            AFTER perfil
        ");
        
        if ($stmt->execute()) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Columna 'empresa' agregada exitosamente</strong><br>";
            echo "Tipo: VARCHAR(100)<br>";
            echo "Null: YES<br>";
            echo "Posición: Después de 'perfil'";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Error agregando columna 'empresa'</strong><br>";
            echo "Error: " . implode(', ', $stmt->errorInfo());
            echo "</div>";
            exit;
        }
    }
    
    // 4. Verificar estructura actual de la tabla
    echo "<h3>📋 4. Estructura actual de tabla usuarios:</h3>";
    $stmt = $conexion->prepare("DESCRIBE usuarios");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Estructura de tabla usuarios:</strong><br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columnas as $columna) {
        $color = ($columna['Field'] === 'empresa') ? '#d4edda' : '';
        echo "<tr style='background: {$color};'>";
        echo "<td><strong>{$columna['Field']}</strong></td>";
        echo "<td>{$columna['Type']}</td>";
        echo "<td>{$columna['Null']}</td>";
        echo "<td>{$columna['Key']}</td>";
        echo "<td>{$columna['Default']}</td>";
        echo "<td>{$columna['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 5. Actualizar campo empresa con nombre de sucursal actual
    echo "<h3>🔄 5. Actualizando campo empresa con nombre actual:</h3>";
    
    // Obtener nombre de sucursal actual
    $stmt = $conexion->prepare("SELECT nombre FROM sucursal_local WHERE id = 1");
    $stmt->execute();
    $sucursalLocal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sucursalLocal) {
        $nombreSucursal = $sucursalLocal['nombre'];
        
        $stmt = $conexion->prepare("
            UPDATE usuarios 
            SET empresa = :nombre_sucursal 
            WHERE activo = 1
        ");
        $stmt->bindParam(":nombre_sucursal", $nombreSucursal, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $filasAfectadas = $stmt->rowCount();
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Campo empresa actualizado exitosamente</strong><br>";
            echo "Filas afectadas: {$filasAfectadas}<br>";
            echo "Nombre de empresa: {$nombreSucursal}";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Error actualizando campo empresa</strong>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No se pudo obtener nombre de sucursal local</strong>";
        echo "</div>";
    }
    
    // 6. Verificar usuarios actualizados
    echo "<h3>📋 6. Verificando usuarios actualizados:</h3>";
    $stmt = $conexion->prepare("SELECT id, nombre, usuario, perfil, empresa FROM usuarios WHERE activo = 1 ORDER BY id LIMIT 10");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($usuarios) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Usuarios con campo empresa actualizado (primeros 10):</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Perfil</th><th>Empresa</th></tr>";
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>{$usuario['id']}</td>";
            echo "<td>{$usuario['nombre']}</td>";
            echo "<td>{$usuario['usuario']}</td>";
            echo "<td>{$usuario['perfil']}</td>";
            echo "<td><strong>{$usuario['empresa']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay usuarios activos</strong>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Proceso completado - " . date('Y-m-d H:i:s') . "</em></p>";
?>
