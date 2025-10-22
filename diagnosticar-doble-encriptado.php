<?php
/**
 * SCRIPT PARA DIAGNOSTICAR PROBLEMA DE DOBLE ENCRIPTADO
 * Verifica qué está pasando cuando se edita desde usuarios central
 */

echo "=== DIAGNÓSTICO DE DOBLE ENCRIPTADO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración BD Local
$hostLocal = "localhost";
$dbnameLocal = "epicosie_pruebas";
$usernameLocal = "epicosie_ricaurte";
$passwordLocal = "m5Wwg)~M{i~*kFr{";

// Configuración BD Central
$hostCentral = "localhost";
$dbnameCentral = "epicosie_central";
$usernameCentral = "epicosie_ricaurte";
$passwordCentral = "m5Wwg)~M{i~*kFr{";

try {
    // Conectar a BD Local
    echo "Conectando a BD Local...\n";
    $pdoLocal = new PDO("mysql:host=$hostLocal;dbname=$dbnameLocal;charset=utf8", $usernameLocal, $passwordLocal);
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoLocal->exec("set names utf8");
    echo "✅ Conexión BD Local exitosa\n\n";
    
    // Conectar a BD Central
    echo "Conectando a BD Central...\n";
    $pdoCentral = new PDO("mysql:host=$hostCentral;dbname=$dbnameCentral;charset=utf8", $usernameCentral, $passwordCentral);
    $pdoCentral->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoCentral->exec("set names utf8");
    echo "✅ Conexión BD Central exitosa\n\n";
    
    // Verificar usuario admin en BD Local
    echo "=== USUARIO ADMIN EN BD LOCAL ===\n";
    $stmt = $pdoLocal->prepare("SELECT id, usuario, password, nombre FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    $adminLocal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminLocal) {
        echo "✅ Usuario admin encontrado en BD Local:\n";
        echo "   - ID: {$adminLocal['id']}\n";
        echo "   - Usuario: {$adminLocal['usuario']}\n";
        echo "   - Nombre: {$adminLocal['nombre']}\n";
        echo "   - Password: {$adminLocal['password']}\n";
        echo "   - Longitud: " . strlen($adminLocal['password']) . " caracteres\n";
        
        // Verificar método de encriptado
        $encriptadoCorrecto = strpos($adminLocal['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        echo "   - Encriptado correcto: " . ($encriptadoCorrecto ? "✅ Sí" : "❌ No") . "\n";
        
        // Probar login
        $passwordTest = "admin123";
        $passwordEncriptadaTest = crypt($passwordTest, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
        $loginFunciona = ($adminLocal['password'] === $passwordEncriptadaTest);
        echo "   - Login funciona: " . ($loginFunciona ? "✅ Sí" : "❌ No") . "\n";
        
        if (!$loginFunciona) {
            echo "   - Password esperada: $passwordEncriptadaTest\n";
            echo "   - Password actual: {$adminLocal['password']}\n";
        }
    } else {
        echo "❌ Usuario admin no encontrado en BD Local\n";
    }
    
    echo "\n";
    
    // Verificar si existe usuario admin en BD Central
    echo "=== USUARIO ADMIN EN BD CENTRAL ===\n";
    $stmt = $pdoCentral->prepare("SELECT id, usuario, password, nombre FROM usuarios_central WHERE usuario = 'admin'");
    $stmt->execute();
    $adminCentral = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminCentral) {
        echo "✅ Usuario admin encontrado en BD Central:\n";
        echo "   - ID: {$adminCentral['id']}\n";
        echo "   - Usuario: {$adminCentral['usuario']}\n";
        echo "   - Nombre: {$adminCentral['nombre']}\n";
        echo "   - Password: {$adminCentral['password']}\n";
        echo "   - Longitud: " . strlen($adminCentral['password']) . " caracteres\n";
        
        // Verificar método de encriptado
        $encriptadoCorrecto = strpos($adminCentral['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        echo "   - Encriptado correcto: " . ($encriptadoCorrecto ? "✅ Sí" : "❌ No") . "\n";
        
        // Verificar si es doble encriptado
        if (strlen($adminCentral['password']) > 60) {
            echo "   - ⚠️  POSIBLE DOBLE ENCRIPTADO (longitud > 60)\n";
        }
        
        // Intentar desencriptar (esto no funcionará, pero nos dará pistas)
        echo "   - Intentando verificar si es doble encriptado...\n";
        
        // Si la contraseña tiene más de 60 caracteres, probablemente está doble encriptada
        if (strlen($adminCentral['password']) > 60) {
            echo "   - ❌ CONFIRMADO: Contraseña doble encriptada\n";
            echo "   - Esto causa el problema de login\n";
        }
    } else {
        echo "ℹ️  Usuario admin no encontrado en BD Central (esto es normal)\n";
    }
    
    echo "\n=== DIAGNÓSTICO COMPLETO ===\n";
    
    if (isset($adminLocal) && isset($adminCentral)) {
        if ($adminLocal['password'] === $adminCentral['password']) {
            echo "✅ Las contraseñas coinciden entre BD Local y Central\n";
        } else {
            echo "❌ Las contraseñas NO coinciden entre BD Local y Central\n";
            echo "   - BD Local: {$adminLocal['password']}\n";
            echo "   - BD Central: {$adminCentral['password']}\n";
        }
    }
    
    echo "\n=== SOLUCIÓN RECOMENDADA ===\n";
    echo "1. El problema es que cuando editas desde 'usuarios central'\n";
    echo "2. Se está aplicando doble encriptado a la contraseña\n";
    echo "3. Esto hace que el login falle\n";
    echo "4. Necesitamos corregir el método de edición en usuarios central\n";
    
    echo "\n✅ DIAGNÓSTICO COMPLETADO\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>
