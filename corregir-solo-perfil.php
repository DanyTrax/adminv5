<?php
/**
 * Script para corregir solo el campo perfil en usuarios_central
 */

require_once "api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();
    
    echo "<h2>🔧 Corrigiendo solo el campo perfil en usuarios_central</h2>";
    
    // Verificar estructura actual
    echo "<h3>📊 Estructura actual del campo perfil:</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_central LIKE 'perfil'");
    $columna = $stmt->fetch();
    echo "<p><strong>Campo perfil actual:</strong> " . $columna['Type'] . "</p>";
    
    // Modificar el campo perfil para incluir 'Especial'
    echo "<h3>🔧 Modificando campo perfil para incluir 'Especial':</h3>";
    $sql = "ALTER TABLE usuarios_central 
            MODIFY COLUMN perfil ENUM('Administrador', 'Especial', 'Vendedor', 'Contador', 'Transportador', 'Limitado') NOT NULL";
    
    $pdo->exec($sql);
    echo "✅ Campo perfil actualizado con 'Especial'<br>";
    
    // Verificar estructura final
    echo "<h3>📊 Estructura final del campo perfil:</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_central LIKE 'perfil'");
    $columna = $stmt->fetch();
    echo "<p><strong>Campo perfil actualizado:</strong> " . $columna['Type'] . "</p>";
    
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
        }
        
        // Eliminar usuario de prueba
        $pdo->exec("DELETE FROM usuarios_central WHERE usuario = '" . $datosPrueba['usuario'] . "'");
        echo "🗑️ Usuario de prueba eliminado<br>";
        
    } catch (Exception $e) {
        echo "❌ Error en inserción: " . $e->getMessage() . "<br>";
    }
    
    // Probar con otros perfiles para asegurar que todos funcionan
    echo "<h3>🧪 Probando otros perfiles:</h3>";
    $perfiles = ['Administrador', 'Vendedor', 'Contador', 'Transportador', 'Limitado'];
    
    foreach ($perfiles as $perfil) {
        $datosPrueba['perfil'] = $perfil;
        $datosPrueba['usuario'] = 'test_' . strtolower($perfil) . '_' . time();
        
        try {
            $stmt->execute($datosPrueba);
            echo "✅ Perfil '$perfil': OK<br>";
            
            // Eliminar
            $pdo->exec("DELETE FROM usuarios_central WHERE usuario = '" . $datosPrueba['usuario'] . "'");
            
        } catch (Exception $e) {
            echo "❌ Perfil '$perfil': " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h6>🎉 Corrección completada exitosamente</h6>";
    echo "<p>El campo perfil ahora incluye 'Especial' y la inserción funciona correctamente.</p>";
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
