<?php
// Script para corregir datos de conexión de la sucursal Pruebas
require_once __DIR__ . "/api-transferencias/conexion-central.php";

echo "<h2>🔧 Corregir Datos de Conexión - Sucursal Pruebas</h2>";

try {
    $conexion = ConexionCentral::conectar();
    
    // Datos de conexión para la sucursal Pruebas (SUC001)
    $datosConexion = [
        'usuario_bd' => 'epicosie_pruebas',
        'password_bd' => 'password123', // Cambiar por la contraseña real
        'nombre_bd' => 'epicosie_pruebas',
        'host_bd' => 'localhost',
        'puerto_bd' => 3306
    ];
    
    echo "<h3>📝 Actualizando sucursal 'Pruebas' (SUC001):</h3>";
    echo "<ul>";
    echo "<li><strong>Usuario BD:</strong> " . $datosConexion['usuario_bd'] . "</li>";
    echo "<li><strong>Contraseña BD:</strong> " . $datosConexion['password_bd'] . "</li>";
    echo "<li><strong>Nombre BD:</strong> " . $datosConexion['nombre_bd'] . "</li>";
    echo "<li><strong>Host BD:</strong> " . $datosConexion['host_bd'] . "</li>";
    echo "<li><strong>Puerto BD:</strong> " . $datosConexion['puerto_bd'] . "</li>";
    echo "</ul>";
    
    $stmt = $conexion->prepare("
        UPDATE sucursales SET 
            usuario_bd = ?, 
            password_bd = ?, 
            nombre_bd = ?, 
            host_bd = ?, 
            puerto_bd = ?
        WHERE codigo_sucursal = 'SUC001'
    ");
    
    $resultado = $stmt->execute([
        $datosConexion['usuario_bd'],
        $datosConexion['password_bd'],
        $datosConexion['nombre_bd'],
        $datosConexion['host_bd'],
        $datosConexion['puerto_bd']
    ]);
    
    if($resultado) {
        echo "<p style='color: green;'>✅ Sucursal 'Pruebas' actualizada correctamente</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al actualizar la sucursal</p>";
    }
    
    // Verificar datos actualizados
    echo "<h3>📊 Verificación de datos actualizados:</h3>";
    $stmt = $conexion->prepare("
        SELECT id, codigo_sucursal, nombre, usuario_bd, password_bd, nombre_bd, host_bd, puerto_bd
        FROM sucursales 
        WHERE activo = 1
        ORDER BY nombre
    ");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Usuario BD</th><th>Password BD</th><th>Nombre BD</th><th>Host BD</th><th>Puerto BD</th></tr>";
    
    foreach($sucursales as $sucursal) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($sucursal['id']) . "</td>";
        echo "<td>" . htmlspecialchars($sucursal['codigo_sucursal']) . "</td>";
        echo "<td>" . htmlspecialchars($sucursal['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($sucursal['usuario_bd']) . "</td>";
        echo "<td>" . htmlspecialchars($sucursal['password_bd']) . "</td>";
        echo "<td>" . htmlspecialchars($sucursal['nombre_bd']) . "</td>";
        echo "<td>" . htmlspecialchars($sucursal['host_bd']) . "</td>";
        echo "<td>" . htmlspecialchars($sucursal['puerto_bd']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 4px; margin: 15px 0;'>";
    echo "<h4>⚠️ Importante:</h4>";
    echo "<p>Si las contraseñas no son correctas, necesitarás actualizarlas con las credenciales reales de cada base de datos.</p>";
    echo "<p>Puedes hacerlo directamente en la tabla <code>sucursales</code> de la BD central.</p>";
    echo "</div>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
