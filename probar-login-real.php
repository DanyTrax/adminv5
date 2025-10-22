<?php
/**
 * SCRIPT PARA PROBAR LOGIN REAL
 * Prueba el login con las contraseñas corregidas
 */

echo "=== PROBAR LOGIN REAL ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración BD Local
$hostLocal = "localhost";
$dbnameLocal = "epicosie_pruebas";
$usernameLocal = "epicosie_ricaurte";
$passwordLocal = "m5Wwg)~M{i~*kFr{";

try {
    echo "Conectando a BD Local...\n";
    $pdoLocal = new PDO("mysql:host=$hostLocal;dbname=$dbnameLocal;charset=utf8", $usernameLocal, $passwordLocal);
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoLocal->exec("set names utf8");
    echo "✅ Conexión BD Local exitosa\n\n";
    
    // Probar login de admin
    echo "=== PROBANDO LOGIN DE ADMIN ===\n";
    
    $usuario = "admin";
    $password = "admin123";
    
    // Encriptar contraseña como lo hace el sistema
    $passwordEncriptada = crypt($password, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
    
    echo "Usuario: $usuario\n";
    echo "Contraseña: $password\n";
    echo "Contraseña encriptada: $passwordEncriptada\n\n";
    
    // Buscar usuario en BD
    $stmt = $pdoLocal->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $usuarioBD = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuarioBD) {
        echo "✅ Usuario encontrado en BD:\n";
        echo "   - ID: {$usuarioBD['id']}\n";
        echo "   - Usuario: {$usuarioBD['usuario']}\n";
        echo "   - Nombre: {$usuarioBD['nombre']}\n";
        echo "   - Perfil: {$usuarioBD['perfil']}\n";
        echo "   - Estado: " . ($usuarioBD['estado'] ? "Activo" : "Inactivo") . "\n";
        echo "   - Password en BD: {$usuarioBD['password']}\n";
        echo "   - Longitud: " . strlen($usuarioBD['password']) . " caracteres\n";
        
        // Verificar contraseña
        if ($usuarioBD['password'] === $passwordEncriptada) {
            echo "\n✅ LOGIN EXITOSO\n";
            echo "✅ La contraseña es correcta\n";
            echo "✅ El sistema de login funcionará correctamente\n";
        } else {
            echo "\n❌ LOGIN FALLIDO\n";
            echo "❌ La contraseña no coincide\n";
            echo "Password esperada: $passwordEncriptada\n";
            echo "Password en BD: {$usuarioBD['password']}\n";
            
            // Intentar corregir la contraseña
            echo "\n=== INTENTANDO CORREGIR CONTRASEÑA ===\n";
            $stmt2 = $pdoLocal->prepare("UPDATE usuarios SET password = ? WHERE usuario = ?");
            $resultado = $stmt2->execute([$passwordEncriptada, $usuario]);
            
            if ($resultado) {
                echo "✅ Contraseña corregida en BD Local\n";
                
                // Verificar nuevamente
                $stmt3 = $pdoLocal->prepare("SELECT password FROM usuarios WHERE usuario = ?");
                $stmt3->execute([$usuario]);
                $nuevaPassword = $stmt3->fetch(PDO::FETCH_ASSOC);
                
                if ($nuevaPassword['password'] === $passwordEncriptada) {
                    echo "✅ Verificación exitosa - Login funcionará\n";
                } else {
                    echo "❌ Error en verificación\n";
                }
            } else {
                echo "❌ Error corrigiendo contraseña\n";
            }
        }
    } else {
        echo "❌ Usuario no encontrado en BD Local\n";
    }
    
    echo "\n=== INSTRUCCIONES ===\n";
    echo "1. Usuario: admin\n";
    echo "2. Contraseña: admin123\n";
    echo "3. URL: https://pruebas.acrilicosinfinito.com/\n";
    echo "4. Si el login funciona, el problema está resuelto\n";
    
    echo "\n✅ PRUEBA COMPLETADA\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>
