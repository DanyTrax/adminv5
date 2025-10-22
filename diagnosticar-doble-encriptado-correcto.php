<?php
/**
 * SCRIPT PARA DIAGNOSTICAR PROBLEMA DE DOBLE ENCRIPTADO
 * Usa la configuración correcta de conexión
 */

echo "=== DIAGNÓSTICO DE DOBLE ENCRIPTADO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración BD Local
$hostLocal = "localhost";
$dbnameLocal = "epicosie_pruebas";
$usernameLocal = "epicosie_ricaurte";
$passwordLocal = "m5Wwg)~M{i~*kFr{";

// Configuración BD Central (CORRECTA)
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
    
    // Verificar usuarios en BD Central
    echo "=== USUARIOS EN BD CENTRAL ===\n";
    $stmt = $pdoCentral->prepare("SELECT id, usuario, password, nombre FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $usuariosCentrales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios centrales encontrados: " . count($usuariosCentrales) . "\n\n";
    
    foreach ($usuariosCentrales as $usuario) {
        echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "   - ID: {$usuario['id']}\n";
        echo "   - Password: {$usuario['password']}\n";
        echo "   - Longitud: " . strlen($usuario['password']) . " caracteres\n";
        
        // Verificar método de encriptado
        $encriptadoCorrecto = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        echo "   - Encriptado correcto: " . ($encriptadoCorrecto ? "✅ Sí" : "❌ No") . "\n";
        
        // Verificar si es doble encriptado
        if (strlen($usuario['password']) > 60) {
            echo "   - ⚠️  POSIBLE DOBLE ENCRIPTADO (longitud > 60)\n";
        }
        
        echo "\n";
    }
    
    echo "=== RESUMEN ===\n";
    $centralesCorrectos = 0;
    $centralesDobleEncriptados = 0;
    
    foreach ($usuariosCentrales as $usuario) {
        $encriptadoCorrecto = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        $longitudCorrecta = strlen($usuario['password']) === 60;
        
        if ($encriptadoCorrecto && $longitudCorrecta) {
            $centralesCorrectos++;
        } elseif (strlen($usuario['password']) > 60) {
            $centralesDobleEncriptados++;
        }
    }
    
    echo "Usuarios centrales correctos: $centralesCorrectos\n";
    echo "Usuarios centrales doble encriptados: $centralesDobleEncriptados\n";
    echo "Total usuarios centrales: " . count($usuariosCentrales) . "\n";
    
    if ($centralesDobleEncriptados > 0) {
        echo "\n⚠️  PROBLEMA DETECTADO: $centralesDobleEncriptados usuarios con doble encriptado\n";
        echo "⚠️  Esto causa problemas en la sincronización y login\n";
        echo "⚠️  Ejecutar 'corregir-doble-encriptado-correcto.php' para solucionarlo\n";
    } else {
        echo "\n✅ NO SE DETECTARON PROBLEMAS DE DOBLE ENCRIPTADO\n";
    }
    
    echo "\n✅ DIAGNÓSTICO COMPLETADO\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nVerificar:\n";
    echo "1. Credenciales de ambas bases de datos\n";
    echo "2. Conexiones a las bases de datos\n";
    echo "3. Permisos de usuario\n";
}
?>
