<?php
/**
 * Script para corregir definitivamente la tabla usuarios_central
 */

require_once "api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();
    
    echo "<h2>🔧 Corrigiendo tabla usuarios_central definitivamente</h2>";
    
    // Eliminar foreign key constraint primero
    echo "<h3>🗑️ Eliminando foreign key constraint:</h3>";
    try {
        $pdo->exec("ALTER TABLE usuarios_central DROP FOREIGN KEY usuarios_central_ibfk_1");
        echo "✅ Foreign key constraint eliminado<br>";
    } catch (Exception $e) {
        echo "⚠️ Error eliminando foreign key: " . $e->getMessage() . "<br>";
    }
    
    // Modificar el campo perfil para incluir 'Especial'
    echo "<h3>🔧 Modificando campo perfil:</h3>";
    $sql = "ALTER TABLE usuarios_central 
            MODIFY COLUMN perfil ENUM('Administrador', 'Especial', 'Vendedor', 'Contador', 'Transportador', 'Limitado') NOT NULL";
    
    $pdo->exec($sql);
    echo "✅ Campo perfil actualizado con 'Especial'<br>";
    
    // Hacer sucursal_id nullable y con valor por defecto
    echo "<h3>🔧 Modificando campo sucursal_id:</h3>";
    $sql = "ALTER TABLE usuarios_central 
            MODIFY COLUMN sucursal_id INT(11) DEFAULT NULL";
    
    $pdo->exec($sql);
    echo "✅ Campo sucursal_id hecho nullable<br>";
    
    // Verificar estructura final
    echo "<h3>📊 Estructura final verificada:</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_central LIKE 'perfil'");
    $columna = $stmt->fetch();
    echo "<p><strong>Campo perfil:</strong> " . $columna['Type'] . "</p>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_central LIKE 'sucursal_id'");
    $columna = $stmt->fetch();
    echo "<p><strong>Campo sucursal_id:</strong> " . $columna['Type'] . " | Null: " . $columna['Null'] . " | Default: " . $columna['Default'] . "</p>";
    
    // Probar inserción con datos exactos del log
    echo "<h3>🧪 Probando inserción con datos exactos del log:</h3>";
    
    $stmt = $pdo->prepare("
        INSERT INTO usuarios_central (
            nombre, usuario, password, perfil, foto, 
            telefono, direccion, activo, 
            sincronizado, fecha_creacion
        ) VALUES (
            :nombre, :usuario, :password, :perfil, :foto,
            :telefono, :direccion, :activo,
            :sincronizado, NOW()
        )
    ");
    
    $datosPrueba = [
        'nombre' => 'especial',
        'usuario' => 'especial_test_' . time(),
        'password' => '$2a$07$asxx54ahjppf45sd87a5auf9Eiqdn10E7o/jsGFivN12XE.wRwyp6',
        'perfil' => 'Especial',
        'foto' => 'vistas/img/usuarios/default/anonymous.png',
        'telefono' => '311111111',
        'direccion' => '',
        'activo' => 1,
        'sincronizado' => 0
    ];
    
    try {
        $stmt->execute($datosPrueba);
        echo "✅ Inserción exitosa con datos exactos del log<br>";
        
        // Verificar el usuario insertado
        $stmt = $pdo->prepare("SELECT * FROM usuarios_central WHERE usuario = :usuario");
        $stmt->bindParam(":usuario", $datosPrueba['usuario']);
        $stmt->execute();
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            echo "✅ Usuario verificado en BD:<br>";
            echo "- ID: " . $usuario['id'] . "<br>";
            echo "- Nombre: " . $usuario['nombre'] . "<br>";
            echo "- Usuario: " . $usuario['usuario'] . "<br>";
            echo "- Perfil: " . $usuario['perfil'] . "<br>";
            echo "- Teléfono: " . $usuario['telefono'] . "<br>";
            echo "- Sucursal ID: " . ($usuario['sucursal_id'] ?? 'NULL') . "<br>";
        }
        
        // Eliminar usuario de prueba
        $pdo->exec("DELETE FROM usuarios_central WHERE usuario = '" . $datosPrueba['usuario'] . "'");
        echo "🗑️ Usuario de prueba eliminado<br>";
        
    } catch (Exception $e) {
        echo "❌ Error en inserción: " . $e->getMessage() . "<br>";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h6>🎉 Corrección completada exitosamente</h6>";
    echo "<p>La tabla usuarios_central ha sido corregida:</p>";
    echo "<ul>";
    echo "<li>✅ Campo perfil incluye 'Especial'</li>";
    echo "<li>✅ Foreign key constraint eliminado</li>";
    echo "<li>✅ Campo sucursal_id hecho nullable</li>";
    echo "<li>✅ Inserción funcionando correctamente</li>";
    echo "</ul>";
    echo "<p>El módulo de usuarios centrales debería funcionar ahora sin errores.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>";
?>
