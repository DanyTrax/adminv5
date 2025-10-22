<?php
/**
 * SCRIPT PARA ESTABLECER CONTRASEÑA DE ADMIN MANUALMENTE
 * Ejecutar este script para corregir la contraseña de admin
 */

// Incluir archivos necesarios
require_once "modelos/usuarios.modelo.php";

try {
    echo "=== ESTABLECER CONTRASEÑA DE ADMIN ===\n";
    echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    
    $conexion = Conexion::conectar();
    
    // Verificar si existe el usuario admin
    $stmt = $conexion->prepare("SELECT id, usuario, nombre, perfil FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "❌ Usuario 'admin' no encontrado en la base de datos\n";
        echo "Creando usuario admin...\n";
        
        // Crear usuario admin si no existe
        $stmt = $conexion->prepare("
            INSERT INTO usuarios (
                nombre, usuario, password, perfil, foto, 
                empresa, telefono, direccion, estado, fecha
            ) VALUES (
                :nombre, :usuario, :password, :perfil, :foto,
                :empresa, :telefono, :direccion, 1, NOW()
            )
        ");
        
        $nombre = "Administrador";
        $usuario = "admin";
        $password = crypt("admin123", '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
        $perfil = "Administrador";
        $foto = "vistas/img/usuarios/default/anonymous.png";
        $empresa = "Central";
        $telefono = "";
        $direccion = "";
        
        $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
        $stmt->bindParam(":password", $password, PDO::PARAM_STR);
        $stmt->bindParam(":perfil", $perfil, PDO::PARAM_STR);
        $stmt->bindParam(":foto", $foto, PDO::PARAM_STR);
        $stmt->bindParam(":empresa", $empresa, PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $direccion, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo "✅ Usuario admin creado exitosamente\n";
        } else {
            echo "❌ Error creando usuario admin\n";
            exit;
        }
    } else {
        echo "✅ Usuario admin encontrado:\n";
        echo "   - ID: {$admin['id']}\n";
        echo "   - Nombre: {$admin['nombre']}\n";
        echo "   - Usuario: {$admin['usuario']}\n";
        echo "   - Perfil: {$admin['perfil']}\n";
    }
    
    // Establecer contraseña de admin
    echo "\n=== ESTABLECIENDO CONTRASEÑA ===\n";
    
    $nuevaPassword = "admin123";
    $passwordEncriptada = crypt($nuevaPassword, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    
    echo "Contraseña nueva: $nuevaPassword\n";
    echo "Contraseña encriptada: $passwordEncriptada\n";
    
    // Actualizar contraseña en BD
    $stmt = $conexion->prepare("UPDATE usuarios SET password = :password WHERE usuario = 'admin'");
    $stmt->bindParam(":password", $passwordEncriptada, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        echo "✅ Contraseña de admin actualizada exitosamente\n";
    } else {
        echo "❌ Error actualizando contraseña de admin\n";
        exit;
    }
    
    // Verificar que la contraseña se guardó correctamente
    echo "\n=== VERIFICACIÓN ===\n";
    
    $stmt = $conexion->prepare("SELECT password FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    $passwordBD = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($passwordBD) {
        echo "Contraseña en BD: {$passwordBD['password']}\n";
        echo "¿Coincide? " . ($passwordBD['password'] === $passwordEncriptada ? "✅ Sí" : "❌ No") . "\n";
    }
    
    // Probar login
    echo "\n=== PROBAR LOGIN ===\n";
    
    $usuarioTest = "admin";
    $passwordTest = "admin123";
    $passwordEncriptadaTest = crypt($passwordTest, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
    $stmt->bindParam(":usuario", $usuarioTest, PDO::PARAM_STR);
    $stmt->execute();
    $usuarioLogin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuarioLogin && $usuarioLogin['password'] === $passwordEncriptadaTest) {
        echo "✅ LOGIN EXITOSO\n";
        echo "   - Usuario: {$usuarioLogin['usuario']}\n";
        echo "   - Nombre: {$usuarioLogin['nombre']}\n";
        echo "   - Perfil: {$usuarioLogin['perfil']}\n";
        echo "   - Estado: " . ($usuarioLogin['estado'] ? "Activo" : "Inactivo") . "\n";
    } else {
        echo "❌ LOGIN FALLIDO\n";
    }
    
    echo "\n=== INSTRUCCIONES ===\n";
    echo "1. Usuario: admin\n";
    echo "2. Contraseña: admin123\n";
    echo "3. Acceder a: https://pruebas.acrilicosinfinito.com/\n";
    echo "4. Si el login funciona, eliminar este archivo por seguridad\n";
    
    echo "\n✅ SCRIPT COMPLETADO EXITOSAMENTE\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Verificar conexión a la base de datos\n";
}
?>
