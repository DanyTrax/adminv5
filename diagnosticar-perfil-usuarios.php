<?php
/**
 * Script para diagnosticar el problema del campo perfil
 */

require_once "api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();
    
    echo "<h2>🔍 Diagnosticando problema del campo perfil</h2>";
    
    // Verificar la estructura actual del campo perfil
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios_central LIKE 'perfil'");
    $columna = $stmt->fetch();
    
    echo "<h3>📊 Estructura actual del campo perfil:</h3>";
    echo "<p><strong>Tipo:</strong> " . $columna['Type'] . "</p>";
    echo "<p><strong>Nulo:</strong> " . $columna['Null'] . "</p>";
    echo "<p><strong>Por defecto:</strong> " . $columna['Default'] . "</p>";
    
    // Intentar insertar un usuario de prueba con perfil "Especial"
    echo "<h3>🧪 Probando inserción con perfil 'Especial':</h3>";
    
    $stmt = $pdo->prepare("
        INSERT INTO usuarios_central (
            nombre, usuario, password, perfil, foto, 
            telefono, direccion, activo, 
            sincronizado, fecha_creacion
        ) VALUES (
            :nombre, :usuario, :password, :perfil, :foto,
            :telefono, :direccion, 1,
            0, NOW()
        )
    ");
    
    $datosPrueba = [
        'nombre' => 'Usuario Prueba',
        'usuario' => 'prueba_' . time(),
        'password' => crypt('123456', '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$'),
        'perfil' => 'Especial',
        'foto' => 'vistas/img/usuarios/default/anonymous.png',
        'telefono' => '123456789',
        'direccion' => 'Dirección prueba'
    ];
    
    try {
        $stmt->execute($datosPrueba);
        echo "✅ Inserción exitosa con perfil 'Especial'<br>";
        
        // Eliminar el usuario de prueba
        $pdo->exec("DELETE FROM usuarios_central WHERE usuario = '" . $datosPrueba['usuario'] . "'");
        echo "🗑️ Usuario de prueba eliminado<br>";
        
    } catch (Exception $e) {
        echo "❌ Error en inserción: " . $e->getMessage() . "<br>";
    }
    
    // Probar con otros perfiles
    $perfiles = ['Administrador', 'Vendedor', 'Contador', 'Transportador', 'Limitado'];
    
    echo "<h3>🧪 Probando otros perfiles:</h3>";
    foreach ($perfiles as $perfil) {
        $datosPrueba['perfil'] = $perfil;
        $datosPrueba['usuario'] = 'prueba_' . $perfil . '_' . time();
        
        try {
            $stmt->execute($datosPrueba);
            echo "✅ Perfil '$perfil': OK<br>";
            
            // Eliminar
            $pdo->exec("DELETE FROM usuarios_central WHERE usuario = '" . $datosPrueba['usuario'] . "'");
            
        } catch (Exception $e) {
            echo "❌ Perfil '$perfil': " . $e->getMessage() . "<br>";
        }
    }
    
    // Verificar si hay caracteres especiales o espacios
    echo "<h3>🔍 Verificando caracteres especiales:</h3>";
    $perfilConEspacios = ' Especial ';
    echo "Perfil con espacios: '" . $perfilConEspacios . "' (longitud: " . strlen($perfilConEspacios) . ")<br>";
    
    $perfilTrimmed = trim($perfilConEspacios);
    echo "Perfil sin espacios: '" . $perfilTrimmed . "' (longitud: " . strlen($perfilTrimmed) . ")<br>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h6>💡 Posibles causas del error:</h6>";
    echo "<ul>";
    echo "<li>Espacios en blanco antes o después del valor</li>";
    echo "<li>Caracteres especiales ocultos</li>";
    echo "<li>Codificación de caracteres incorrecta</li>";
    echo "<li>El valor no coincide exactamente con los valores del ENUM</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>";
?>
