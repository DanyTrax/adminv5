<?php
/**
 * SCRIPT PARA DIAGNOSTICAR PROBLEMA DE CONTRASEÑAS
 * Verifica BD Local y Central para entender el problema
 */

echo "=== DIAGNÓSTICO DE CONTRASEÑAS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración BD Local
$hostLocal = "localhost";
$dbnameLocal = "epicosie_pruebas";
$usernameLocal = "epicosie_ricaurte";
$passwordLocal = "m5Wwg)~M{i~*kFr{";

// Configuración BD Central
$hostCentral = "localhost";
$dbnameCentral = "epicosie_central";
$usernameCentral = "epicosie_central";
$passwordCentral = "=Nf?M#6A'QU&.6c";

try {
    // Conectar a BD Local
    echo "Conectando a BD Local...\n";
    $pdoLocal = new PDO("mysql:host=$hostLocal;dbname=$dbnameLocal;charset=utf8", $usernameLocal, $passwordLocal);
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoLocal->exec("set names utf8");
    echo "✅ Conexión BD Local exitosa\n\n";
    
    // Conectar a BD Central
    echo "Conectando a BD Central...\n";
    $pdoCentral = new PDO("mysql:host=$hostCentral;dbname=$dbnameCentral;charset=utf8mb4", $usernameCentral, $passwordCentral);
    $pdoCentral->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoCentral->exec("set names utf8");
    echo "✅ Conexión BD Central exitosa\n\n";
    
    // Verificar usuarios en BD Local
    echo "=== USUARIOS EN BD LOCAL ===\n";
    $stmt = $pdoLocal->prepare("SELECT id, usuario, password, nombre, perfil FROM usuarios ORDER BY id");
    $stmt->execute();
    $usuariosLocal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios encontrados: " . count($usuariosLocal) . "\n\n";
    
    foreach ($usuariosLocal as $usuario) {
        echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "   - ID: {$usuario['id']}\n";
        echo "   - Perfil: {$usuario['perfil']}\n";
        echo "   - Password: {$usuario['password']}\n";
        echo "   - Longitud: " . strlen($usuario['password']) . " caracteres\n";
        
        // Verificar formato de encriptado
        $esBcrypt = (strlen($usuario['password']) === 60 && strpos($usuario['password'], '$2a$') === 0);
        echo "   - Es bcrypt: " . ($esBcrypt ? "✅ Sí" : "❌ No") . "\n";
        
        // Probar login con contraseña asumida
        $passwordAsumida = ($usuario['usuario'] === 'admin') ? 'admin123' : $usuario['usuario'];
        $passwordEncriptadaTest = crypt($passwordAsumida, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
        $loginFunciona = ($usuario['password'] === $passwordEncriptadaTest);
        
        echo "   - Contraseña asumida: $passwordAsumida\n";
        echo "   - Login funciona: " . ($loginFunciona ? "✅ Sí" : "❌ No") . "\n";
        
        if (!$loginFunciona) {
            echo "   - Password esperada: $passwordEncriptadaTest\n";
            echo "   - Password actual: {$usuario['password']}\n";
        }
        echo "\n";
    }
    
    // Verificar usuarios en BD Central
    echo "=== USUARIOS EN BD CENTRAL ===\n";
    $stmt = $pdoCentral->prepare("SELECT id, usuario, password, nombre, perfil FROM usuarios_central WHERE activo = 1 ORDER BY id");
    $stmt->execute();
    $usuariosCentral = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios encontrados: " . count($usuariosCentral) . "\n\n";
    
    foreach ($usuariosCentral as $usuario) {
        echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "   - ID: {$usuario['id']}\n";
        echo "   - Perfil: {$usuario['perfil']}\n";
        echo "   - Password: {$usuario['password']}\n";
        echo "   - Longitud: " . strlen($usuario['password']) . " caracteres\n";
        
        // Verificar formato de encriptado
        $esBcrypt = (strlen($usuario['password']) === 60 && strpos($usuario['password'], '$2a$') === 0);
        echo "   - Es bcrypt: " . ($esBcrypt ? "✅ Sí" : "❌ No") . "\n";
        
        // Probar login con contraseña asumida
        $passwordAsumida = ($usuario['usuario'] === 'admin') ? 'admin123' : $usuario['usuario'];
        $passwordEncriptadaTest = crypt($passwordAsumida, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
        $loginFunciona = ($usuario['password'] === $passwordEncriptadaTest);
        
        echo "   - Contraseña asumida: $passwordAsumida\n";
        echo "   - Login funciona: " . ($loginFunciona ? "✅ Sí" : "❌ No") . "\n";
        
        if (!$loginFunciona) {
            echo "   - Password esperada: $passwordEncriptadaTest\n";
            echo "   - Password actual: {$usuario['password']}\n";
        }
        echo "\n";
    }
    
    echo "=== RESUMEN ===\n";
    echo "BD Local: " . count($usuariosLocal) . " usuarios\n";
    echo "BD Central: " . count($usuariosCentral) . " usuarios\n";
    
    echo "\n=== INSTRUCCIONES ===\n";
    echo "1. Si las contraseñas no funcionan, necesitamos restaurarlas\n";
    echo "2. Si las contraseñas funcionan, el problema está en otro lugar\n";
    echo "3. Verificar que el sistema use el mismo método de encriptado\n";
    
    echo "\n✅ DIAGNÓSTICO COMPLETADO\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>
