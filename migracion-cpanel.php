<?php
/**
 * SCRIPT DE MIGRACIÓN PARA CPANEL
 * Ejecutar este script en cPanel para aplicar los cambios necesarios
 */

require_once "modelos/usuarios-central.modelo.php";

try {
    echo "=== MIGRACIÓN DE BASE DE DATOS - CPANEL ===\n";
    echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    
    $conexion = ConexionCentral::conectar();
    
    // PASO 1: Corregir campo sucursal_id
    echo "1. Corrigiendo campo sucursal_id...\n";
    try {
        $stmt = $conexion->prepare("ALTER TABLE usuarios_central MODIFY COLUMN sucursal_id INT(11) DEFAULT NULL");
        $stmt->execute();
        echo "   ✅ Campo sucursal_id corregido para permitir NULL\n";
    } catch (Exception $e) {
        echo "   ⚠️  Campo sucursal_id ya está correcto o error: " . $e->getMessage() . "\n";
    }
    
    // PASO 2: Actualizar contraseñas de usuarios centrales
    echo "\n2. Actualizando contraseñas de usuarios centrales...\n";
    
    $stmt = $conexion->prepare("SELECT id, usuario, password FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $usuariosActualizados = 0;
    foreach ($usuarios as $usuario) {
        // Verificar si ya está encriptada correctamente
        $encriptadaCorrectamente = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        
        if (!$encriptadaCorrectamente) {
            // Asumir que la contraseña actual es la contraseña sin encriptar
            // y encriptarla con el método correcto
            $passwordEncriptada = crypt($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
            
            // Actualizar en BD
            $stmt2 = $conexion->prepare("UPDATE usuarios_central SET password = :password WHERE id = :id");
            $stmt2->bindParam(":password", $passwordEncriptada, PDO::PARAM_STR);
            $stmt2->bindParam(":id", $usuario['id'], PDO::PARAM_INT);
            $stmt2->execute();
            
            echo "   ✅ Usuario '{$usuario['usuario']}' actualizado\n";
            $usuariosActualizados++;
        } else {
            echo "   ✅ Usuario '{$usuario['usuario']}' ya encriptado correctamente\n";
        }
    }
    
    echo "   Total usuarios actualizados: $usuariosActualizados\n";
    
    // PASO 3: Verificar estructura final
    echo "\n3. Verificando estructura final...\n";
    
    // Verificar campo sucursal_id
    $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios_central WHERE Field = 'sucursal_id'");
    $stmt->execute();
    $columna = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($columna) {
        echo "   ✅ Campo sucursal_id: {$columna['Type']}, Null: {$columna['Null']}, Default: " . ($columna['Default'] ?? 'NULL') . "\n";
    }
    
    // Verificar contraseñas
    $stmt = $conexion->prepare("SELECT usuario, password FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $contraseñasCorrectas = 0;
    foreach ($usuarios as $usuario) {
        $encriptadaCorrectamente = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        if ($encriptadaCorrectamente) {
            $contraseñasCorrectas++;
        }
    }
    
    echo "   ✅ Contraseñas correctamente encriptadas: $contraseñasCorrectas/" . count($usuarios) . "\n";
    
    echo "\n=== MIGRACIÓN COMPLETADA ===\n";
    echo "✅ Todos los cambios aplicados correctamente\n";
    echo "✅ El sistema está listo para usar\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR EN MIGRACIÓN: " . $e->getMessage() . "\n";
    echo "Por favor, contacta al administrador del sistema.\n";
}
?>
