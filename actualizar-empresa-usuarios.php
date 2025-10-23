<?php
/**
 * SCRIPT PARA ACTUALIZAR CAMPO EMPRESA EN USUARIOS LOCALES
 */

echo "<h2>🔄 ACTUALIZAR CAMPO EMPRESA EN USUARIOS LOCALES</h2>";

try {
    require_once "config.php";
    require_once "modelos/conexion.php";
    require_once "modelos/sucursales.modelo.php";
    
    // 1. Obtener configuración local actual
    echo "<h3>📋 1. Configuración Local Actual:</h3>";
    $configLocal = ModeloSucursales::mdlObtenerConfiguracionLocal();
    
    if ($configLocal) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Configuración local:</strong><br>";
        echo "Código: " . $configLocal['codigo_sucursal'] . "<br>";
        echo "Nombre: " . $configLocal['nombre'] . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay configuración local</strong>";
        echo "</div>";
        exit;
    }
    
    // 2. Verificar usuarios locales con campo empresa
    echo "<h3>📋 2. Verificando usuarios locales:</h3>";
    $conexionLocal = Conexion::conectar();
    
    // Verificar si existe la columna empresa
    $stmt = $conexionLocal->prepare("SHOW COLUMNS FROM usuarios LIKE 'empresa'");
    $stmt->execute();
    $columnaEmpresa = $stmt->fetch();
    
    if ($columnaEmpresa) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Columna 'empresa' existe en tabla usuarios</strong>";
        echo "</div>";
        
        // Mostrar usuarios actuales
        $stmt = $conexionLocal->prepare("SELECT id, nombre, usuario, empresa FROM usuarios WHERE activo = 1 ORDER BY id");
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
        echo "<strong>Usuarios locales actuales:</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Empresa Actual</th></tr>";
        foreach ($usuarios as $usuario) {
            $empresaActual = $usuario['empresa'] ?: 'Sin asignar';
            echo "<tr>";
            echo "<td>{$usuario['id']}</td>";
            echo "<td>{$usuario['nombre']}</td>";
            echo "<td>{$usuario['usuario']}</td>";
            echo "<td>{$empresaActual}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
        // 3. Actualizar campo empresa
        echo "<h3>🔄 3. Actualizando campo empresa:</h3>";
        
        $stmt = $conexionLocal->prepare("
            UPDATE usuarios 
            SET empresa = :nombre_sucursal 
            WHERE activo = 1
        ");
        $stmt->bindParam(":nombre_sucursal", $configLocal['nombre'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $filasAfectadas = $stmt->rowCount();
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Campo empresa actualizado exitosamente</strong><br>";
            echo "Filas afectadas: {$filasAfectadas}<br>";
            echo "Nuevo nombre de empresa: " . $configLocal['nombre'];
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Error actualizando campo empresa</strong>";
            echo "</div>";
        }
        
        // 4. Verificar usuarios después de actualización
        echo "<h3>📋 4. Verificando usuarios después de actualización:</h3>";
        $stmt = $conexionLocal->prepare("SELECT id, nombre, usuario, empresa FROM usuarios WHERE activo = 1 ORDER BY id");
        $stmt->execute();
        $usuariosActualizados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Usuarios actualizados:</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Empresa Actualizada</th></tr>";
        foreach ($usuariosActualizados as $usuario) {
            $empresaActualizada = $usuario['empresa'] ?: 'Sin asignar';
            $color = ($empresaActualizada === $configLocal['nombre']) ? '#d4edda' : '#f8d7da';
            echo "<tr style='background: {$color};'>";
            echo "<td>{$usuario['id']}</td>";
            echo "<td>{$usuario['nombre']}</td>";
            echo "<td>{$usuario['usuario']}</td>";
            echo "<td><strong>{$empresaActualizada}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ La columna 'empresa' no existe en la tabla usuarios</strong><br>";
        echo "Necesitas agregar la columna 'empresa' a la tabla usuarios primero.";
        echo "</div>";
        
        // Mostrar SQL para agregar la columna
        echo "<h3>📋 3. SQL para agregar columna empresa:</h3>";
        echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
        echo "<strong>Ejecuta este SQL en tu base de datos local:</strong><br>";
        echo "<code>ALTER TABLE usuarios ADD COLUMN empresa VARCHAR(100) NULL AFTER perfil;</code>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Actualización completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
