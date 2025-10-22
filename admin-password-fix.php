<?php
/**
 * SCRIPT ESPECÍFICO PARA CONTRASEÑA DE ADMIN
 * Usa la configuración exacta de tu servidor
 */

echo "=== SCRIPT ESPECÍFICO - CONTRASEÑA ADMIN ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración específica de tu servidor
$host = "localhost";
$dbname = "epicosie_pruebas";
$username = "epicosie_ricaurte";
$password = "m5Wwg)~M{i~*kFr{";

try {
    echo "Conectando a la base de datos...\n";
    echo "Host: $host\n";
    echo "Base de datos: $dbname\n";
    echo "Usuario: $username\n\n";
    
    // Conectar a BD
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
    
    echo "✅ Conexión exitosa\n\n";
    
    // Verificar si existe admin
    echo "=== VERIFICANDO USUARIO ADMIN ===\n";
    $stmt = $pdo->prepare("SELECT id, usuario, nombre, perfil, estado FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Usuario admin encontrado:\n";
        echo "   - ID: {$admin['id']}\n";
        echo "   - Nombre: {$admin['nombre']}\n";
        echo "   - Usuario: {$admin['usuario']}\n";
        echo "   - Perfil: {$admin['perfil']}\n";
        echo "   - Estado: " . ($admin['estado'] ? "Activo" : "Inactivo") . "\n";
    } else {
        echo "⚠️  Usuario admin no encontrado, creando...\n";
        
        // Crear usuario admin
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (
                nombre, usuario, password, perfil, foto, 
                empresa, telefono, direccion, estado, fecha
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $nombre = "Administrador";
        $usuario = "admin";
        $perfil = "Administrador";
        $foto = "vistas/img/usuarios/default/anonymous.png";
        $empresa = "Central";
        $telefono = "";
        $direccion = "";
        $estado = 1;
        
        $stmt->execute([$nombre, $usuario, "", $perfil, $foto, $empresa, $telefono, $direccion, $estado]);
        echo "✅ Usuario admin creado\n";
    }
    
    // Establecer contraseña
    echo "\n=== ESTABLECIENDO CONTRASEÑA ===\n";
    
    $nuevaPassword = "admin123";
    $passwordEncriptada = crypt($nuevaPassword, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    
    echo "Contraseña nueva: $nuevaPassword\n";
    echo "Contraseña encriptada: $passwordEncriptada\n";
    
    // Actualizar contraseña
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE usuario = 'admin'");
    $resultado = $stmt->execute([$passwordEncriptada]);
    
    if ($resultado) {
        echo "✅ Contraseña actualizada exitosamente\n";
    } else {
        echo "❌ Error actualizando contraseña\n";
        exit;
    }
    
    // Verificar que se guardó correctamente
    echo "\n=== VERIFICACIÓN ===\n";
    
    $stmt = $pdo->prepare("SELECT usuario, password FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "Usuario: {$usuario['usuario']}\n";
        echo "Password en BD: {$usuario['password']}\n";
        echo "¿Coincide? " . ($usuario['password'] === $passwordEncriptada ? "✅ Sí" : "❌ No") . "\n";
    }
    
    // Probar login
    echo "\n=== PROBAR LOGIN ===\n";
    
    $usuarioTest = "admin";
    $passwordTest = "admin123";
    $passwordEncriptadaTest = crypt($passwordTest, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuarioTest]);
    $usuarioLogin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuarioLogin && $usuarioLogin['password'] === $passwordEncriptadaTest) {
        echo "✅ LOGIN EXITOSO\n";
        echo "   - Usuario: {$usuarioLogin['usuario']}\n";
        echo "   - Nombre: {$usuarioLogin['nombre']}\n";
        echo "   - Perfil: {$usuarioLogin['perfil']}\n";
        echo "   - Estado: " . ($usuarioLogin['estado'] ? "Activo" : "Inactivo") . "\n";
    } else {
        echo "❌ LOGIN FALLIDO\n";
        if ($usuarioLogin) {
            echo "   Password en BD: {$usuarioLogin['password']}\n";
            echo "   Password esperada: $passwordEncriptadaTest\n";
        } else {
            echo "   Usuario no encontrado\n";
        }
    }
    
    echo "\n=== INSTRUCCIONES FINALES ===\n";
    echo "1. Usuario: admin\n";
    echo "2. Contraseña: admin123\n";
    echo "3. URL: https://pruebas.acrilicosinfinito.com/\n";
    echo "4. Si el login funciona, eliminar este archivo por seguridad\n";
    
    echo "\n✅ SCRIPT COMPLETADO EXITOSAMENTE\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nVerificar:\n";
    echo "1. Credenciales de base de datos\n";
    echo "2. Conexión a la base de datos\n";
    echo "3. Permisos de usuario\n";
}
?>
