<?php
/**
 * SCRIPT PARA VERIFICAR USUARIOS EN SUCURSAL LOCAL
 */

echo "<h2>🔍 VERIFICAR USUARIOS EN SUCURSAL LOCAL</h2>";

try {
    require_once "config.php";
    require_once "modelos/conexion.php";
    
    // 1. Verificar conexión local
    echo "<h3>📋 1. Verificando conexión local:</h3>";
    $conexionLocal = Conexion::conectar();
    if ($conexionLocal) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Conexión local exitosa</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Error en conexión local</strong>";
        echo "</div>";
        exit;
    }
    
    // 2. Verificar tabla usuarios
    echo "<h3>📋 2. Verificando tabla usuarios:</h3>";
    $stmt = $conexionLocal->prepare("SHOW TABLES LIKE 'usuarios'");
    $stmt->execute();
    $tablaExiste = $stmt->fetch();
    
    if ($tablaExiste) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Tabla usuarios existe</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Tabla usuarios no existe</strong>";
        echo "</div>";
        exit;
    }
    
    // 3. Contar usuarios locales
    echo "<h3>📋 3. Contando usuarios locales:</h3>";
    $stmt = $conexionLocal->prepare("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
    $stmt->execute();
    $totalUsuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Total usuarios locales activos: {$totalUsuarios}</strong>";
    echo "</div>";
    
    // 4. Mostrar usuarios locales
    echo "<h3>📋 4. Mostrando usuarios locales:</h3>";
    $stmt = $conexionLocal->prepare("SELECT id, nombre, usuario, perfil, activo FROM usuarios WHERE activo = 1 ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $usuariosLocales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($usuariosLocales) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Usuarios locales (últimos 10):</strong><br>";
        foreach ($usuariosLocales as $usuario) {
            echo "• ID: {$usuario['id']} - {$usuario['nombre']} ({$usuario['usuario']}) - {$usuario['perfil']}<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay usuarios locales activos</strong>";
        echo "</div>";
    }
    
    // 5. Verificar estructura de tabla usuarios
    echo "<h3>📋 5. Verificando estructura de tabla usuarios:</h3>";
    $stmt = $conexionLocal->prepare("DESCRIBE usuarios");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Estructura de tabla usuarios:</strong><br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>{$columna['Field']}</td>";
        echo "<td>{$columna['Type']}</td>";
        echo "<td>{$columna['Null']}</td>";
        echo "<td>{$columna['Key']}</td>";
        echo "<td>{$columna['Default']}</td>";
        echo "<td>{$columna['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 6. Verificar configuración de sucursal local
    echo "<h3>📋 6. Verificando configuración de sucursal local:</h3>";
    $stmt = $conexionLocal->prepare("SELECT * FROM sucursal_local WHERE id = 1");
    $stmt->execute();
    $configLocal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($configLocal) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Configuración local encontrada:</strong><br>";
        echo "Código: " . $configLocal['codigo_sucursal'] . "<br>";
        echo "Nombre: " . $configLocal['nombre'] . "<br>";
        echo "Host BD: " . ($configLocal['host_bd'] ?? 'No definido') . "<br>";
        echo "Nombre BD: " . ($configLocal['nombre_bd'] ?? 'No definido') . "<br>";
        echo "Usuario BD: " . ($configLocal['usuario_bd'] ?? 'No definido') . "<br>";
        echo "Activo: " . ($configLocal['activo'] ? 'Sí' : 'No') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay configuración local</strong>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Verificación completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
