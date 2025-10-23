<?php
// Script para probar el login del usuario creado por el instalador
require_once "config.php";

echo "<h2>🔐 Probar Login de Usuario Creado</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener todos los usuarios
    $stmt = $pdo->query("SELECT id, nombre, usuario, password, perfil, estado, ultimo_login, fecha FROM usuarios ORDER BY id DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>👥 Usuarios en la base de datos:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Nombre</th><th>Usuario</th><th>Perfil</th><th>Estado</th><th>Último Login</th><th>Fecha Creación</th>";
    echo "</tr>";
    
    foreach ($usuarios as $usuario) {
        echo "<tr>";
        echo "<td>" . $usuario['id'] . "</td>";
        echo "<td>" . $usuario['nombre'] . "</td>";
        echo "<td>" . $usuario['usuario'] . "</td>";
        echo "<td>" . $usuario['perfil'] . "</td>";
        echo "<td>" . ($usuario['estado'] ? 'Activo' : 'Inactivo') . "</td>";
        echo "<td>" . ($usuario['ultimo_login'] ?? 'Nunca') . "</td>";
        echo "<td>" . $usuario['fecha'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Probar login con diferentes contraseñas
    if (!empty($usuarios)) {
        $usuario_test = $usuarios[0]; // Tomar el primer usuario
        
        echo "<h3>🧪 Probar Login:</h3>";
        echo "<p><strong>Usuario:</strong> " . $usuario_test['usuario'] . "</p>";
        echo "<p><strong>Password encriptado:</strong> " . $usuario_test['password'] . "</p>";
        
        // Probar diferentes contraseñas
        $passwords_test = [
            'admin123',
            'password',
            '123456',
            'admin',
            'password123'
        ];
        
        echo "<h4>Probando diferentes contraseñas:</h4>";
        echo "<ul>";
        
        foreach ($passwords_test as $password_test) {
            $password_encriptado = crypt($password_test, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
            $coincide = ($password_encriptado === $usuario_test['password']);
            
            echo "<li><strong>" . $password_test . "</strong>: " . ($coincide ? "✅ COINCIDE" : "❌ No coincide") . "</li>";
        }
        echo "</ul>";
        
        // Mostrar información de encriptación
        echo "<h4>🔍 Información de Encriptación:</h4>";
        echo "<p><strong>Longitud del hash:</strong> " . strlen($usuario_test['password']) . " caracteres</p>";
        echo "<p><strong>Prefijo del hash:</strong> " . substr($usuario_test['password'], 0, 7) . "</p>";
        echo "<p><strong>Salt usado:</strong> \$2a\$07\$asxx54ahjppf45sd87a5a4dDDGsystemdev\$</p>";
        
        // Crear un nuevo usuario de prueba
        echo "<h4>🆕 Crear Usuario de Prueba:</h4>";
        if (isset($_POST['crear_usuario_test'])) {
            $nombre_test = $_POST['nombre_test'] ?? 'Usuario Test';
            $usuario_test = $_POST['usuario_test'] ?? 'test';
            $password_test = $_POST['password_test'] ?? 'test123';
            
            $password_encriptado = crypt($password_test, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nombre, usuario, password, perfil, estado, ultimo_login, fecha) 
                    VALUES (?, ?, ?, 'Administrador', 1, NOW(), NOW())
                ");
                $stmt->execute([$nombre_test, $usuario_test, $password_encriptado]);
                
                echo "<div style='color: green;'>✅ Usuario de prueba creado exitosamente</div>";
                echo "<p><strong>Usuario:</strong> " . $usuario_test . "</p>";
                echo "<p><strong>Contraseña:</strong> " . $password_test . "</p>";
                echo "<p><strong>Hash:</strong> " . $password_encriptado . "</p>";
                
            } catch (Exception $e) {
                echo "<div style='color: red;'>❌ Error creando usuario: " . $e->getMessage() . "</div>";
            }
        }
        
        echo "<form method='POST' style='background: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin: 10px 0;'>";
        echo "<h5>Crear Usuario de Prueba:</h5>";
        echo "<p><label>Nombre: <input type='text' name='nombre_test' value='Usuario Test' required></label></p>";
        echo "<p><label>Usuario: <input type='text' name='usuario_test' value='test' required></label></p>";
        echo "<p><label>Contraseña: <input type='text' name='password_test' value='test123' required></label></p>";
        echo "<p><button type='submit' name='crear_usuario_test'>Crear Usuario de Prueba</button></p>";
        echo "</form>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
