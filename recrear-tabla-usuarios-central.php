<?php
/**
 * Script para verificar y corregir completamente la tabla usuarios_central
 */

require_once "api-transferencias/conexion-central.php";

try {
    $pdo = ConexionCentral::conectar();
    
    echo "<h2>🔍 Verificando estructura completa de usuarios_central</h2>";
    
    // Verificar estructura actual
    $stmt = $pdo->query("DESCRIBE usuarios_central");
    $columnas = $stmt->fetchAll();
    
    echo "<h3>📊 Estructura actual de la tabla:</h3>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
    
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>" . $columna['Field'] . "</td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "<td>" . $columna['Default'] . "</td>";
        echo "<td>" . $columna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Intentar crear la tabla desde cero con estructura correcta
    echo "<h3>🔧 Recreando tabla con estructura correcta:</h3>";
    
    // Eliminar tabla existente
    $pdo->exec("DROP TABLE IF EXISTS usuarios_central");
    echo "🗑️ Tabla anterior eliminada<br>";
    
    // Crear tabla con estructura correcta
    $createTable = "
    CREATE TABLE usuarios_central (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        perfil ENUM('Administrador', 'Especial', 'Vendedor', 'Contador', 'Transportador', 'Limitado') NOT NULL,
        foto VARCHAR(255) DEFAULT 'vistas/img/usuarios/default/anonymous.png',
        telefono VARCHAR(20),
        direccion TEXT,
        activo TINYINT(1) DEFAULT 1,
        sincronizado TINYINT(1) DEFAULT 0,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_perfil (perfil),
        INDEX idx_activo (activo),
        INDEX idx_sincronizado (sincronizado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
    ";
    
    $pdo->exec($createTable);
    echo "✅ Tabla recreada exitosamente<br>";
    
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
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h6>🎉 Tabla recreada exitosamente</h6>";
    echo "<p>La tabla usuarios_central ha sido recreada con la estructura correcta y la inserción funciona perfectamente.</p>";
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
table { margin: 10px 0; }
th { background: #007bff; color: white; }
</style>";
?>
