<?php
/**
 * SCRIPT PARA ACTUALIZAR CONTRASEÑAS DE USUARIOS LOCALES
 * Ejecutar este script en cPanel para actualizar las contraseñas locales
 */

require_once "modelos/usuarios.modelo.php";

try {
    echo "=== ACTUALIZAR CONTRASEÑAS DE USUARIOS LOCALES ===\n";
    echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    
    $conexion = Conexion::conectar();
    
    // Obtener todos los usuarios locales
    $stmt = $conexion->prepare("SELECT id, usuario, password FROM usuarios WHERE estado = 1");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios encontrados: " . count($usuarios) . "\n\n";
    
    $usuariosActualizados = 0;
    foreach ($usuarios as $usuario) {
        echo "Procesando usuario: {$usuario['usuario']}\n";
        
        // Verificar si ya está encriptada correctamente
        $encriptadaCorrectamente = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        
        if (!$encriptadaCorrectamente) {
            // Si no está encriptada correctamente, asumir que es la contraseña sin encriptar
            // y encriptarla con el método correcto
            $passwordEncriptada = crypt($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
            
            // Actualizar en BD
            $stmt2 = $conexion->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
            $stmt2->bindParam(":password", $passwordEncriptada, PDO::PARAM_STR);
            $stmt2->bindParam(":id", $usuario['id'], PDO::PARAM_INT);
            $stmt2->execute();
            
            echo "  ✅ Contraseña actualizada\n";
            $usuariosActualizados++;
        } else {
            echo "  ✅ Ya encriptada correctamente\n";
        }
    }
    
    echo "\n=== RESULTADO ===\n";
    echo "Total usuarios actualizados: $usuariosActualizados\n";
    echo "✅ Migración de usuarios locales completada\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>
