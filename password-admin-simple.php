<?php
/**
 * SCRIPT SIMPLE PARA CONTRASEÑA DE ADMIN
 * Versión simplificada para casos de emergencia
 */

// Configuración de base de datos (ajustar según tu configuración)
$host = "localhost";
$dbname = "epicosie_pruebas";
$username = "epicosie_pruebas";
$password = "tu_password_aqui"; // Cambiar por la contraseña real

try {
    echo "=== SCRIPT SIMPLE - CONTRASEÑA ADMIN ===\n";
    
    // Conectar a BD
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión a BD exitosa\n";
    
    // Contraseña nueva
    $nuevaPassword = "admin123";
    $passwordEncriptada = crypt($nuevaPassword, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    
    echo "Contraseña: $nuevaPassword\n";
    echo "Encriptada: $passwordEncriptada\n";
    
    // Actualizar contraseña
    $sql = "UPDATE usuarios SET password = ? WHERE usuario = 'admin'";
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([$passwordEncriptada]);
    
    if ($resultado) {
        echo "✅ Contraseña actualizada\n";
        
        // Verificar
        $sql = "SELECT usuario, password FROM usuarios WHERE usuario = 'admin'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            echo "Usuario: {$usuario['usuario']}\n";
            echo "Password: {$usuario['password']}\n";
            echo "¿Coincide? " . ($usuario['password'] === $passwordEncriptada ? "✅ Sí" : "❌ No") . "\n";
        }
        
        echo "\n🎉 LISTO PARA USAR:\n";
        echo "Usuario: admin\n";
        echo "Contraseña: admin123\n";
        
    } else {
        echo "❌ Error actualizando contraseña\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nVerificar:\n";
    echo "1. Configuración de BD en el script\n";
    echo "2. Credenciales de acceso\n";
    echo "3. Conexión a la base de datos\n";
}
?>
