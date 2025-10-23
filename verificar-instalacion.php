<?php
// Script para verificar si la instalación se completó correctamente
echo "<h2>🔍 Verificación de Instalación</h2>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=epicosie_pruebas;charset=utf8", "epicosie_ricaurte", "m5Wwg)~M{i~*kFr{");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Conexión a BD exitosa</p>";
    
    // Verificar tablas esperadas
    $tablas_esperadas = [
        'usuarios',
        'sucursal_local', 
        'productos',
        'categorias',
        'clientes',
        'ventas',
        'venta_productos',
        'abonos_historial',
        'solicitudes_stock',
        'despachos',
        'stock_transito'
    ];
    
    echo "<h3>📊 Verificación de Tablas:</h3>";
    $tablas_faltantes = [];
    
    foreach ($tablas_esperadas as $tabla) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        $existe = $stmt->fetch();
        
        if ($existe) {
            echo "<p>✅ <strong>$tabla</strong> - Existe</p>";
        } else {
            echo "<p>❌ <strong>$tabla</strong> - NO EXISTE</p>";
            $tablas_faltantes[] = $tabla;
        }
    }
    
    if (empty($tablas_faltantes)) {
        echo "<h3>🎉 ¡Todas las tablas están presentes!</h3>";
    } else {
        echo "<h3>⚠️ Faltan " . count($tablas_faltantes) . " tablas:</h3>";
        echo "<ul>";
        foreach ($tablas_faltantes as $tabla) {
            echo "<li>$tabla</li>";
        }
        echo "</ul>";
    }
    
    // Verificar usuarios
    echo "<h3>👥 Verificación de Usuarios:</h3>";
    $stmt = $pdo->query("SELECT id, nombre, usuario, perfil, estado FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($usuarios)) {
        echo "<p>❌ No hay usuarios en la base de datos</p>";
    } else {
        echo "<p>✅ Usuarios encontrados: " . count($usuarios) . "</p>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Perfil</th><th>Estado</th></tr>";
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>" . $usuario['id'] . "</td>";
            echo "<td>" . $usuario['nombre'] . "</td>";
            echo "<td>" . $usuario['usuario'] . "</td>";
            echo "<td>" . $usuario['perfil'] . "</td>";
            echo "<td>" . ($usuario['estado'] ? 'Activo' : 'Inactivo') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar configuración de sucursal
    echo "<h3>🏢 Verificación de Sucursal:</h3>";
    $stmt = $pdo->query("SELECT * FROM sucursal_local");
    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sucursal) {
        echo "<p>✅ Configuración de sucursal encontrada</p>";
        echo "<p><strong>Nombre:</strong> " . $sucursal['nombre'] . "</p>";
        echo "<p><strong>Código:</strong> " . $sucursal['codigo_sucursal'] . "</p>";
    } else {
        echo "<p>❌ No hay configuración de sucursal</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}
?>
