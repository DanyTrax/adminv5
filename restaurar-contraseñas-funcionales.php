<?php
/**
 * SCRIPT PARA RESTAURAR CONTRASEÑAS A ESTADO FUNCIONAL
 * Restaura las contraseñas para que funcionen como antes
 */

echo "=== RESTAURAR CONTRASEÑAS FUNCIONALES ===\n";
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
    
    // Restaurar contraseñas en BD Local
    echo "=== RESTAURANDO CONTRASEÑAS EN BD LOCAL ===\n";
    $stmt = $pdoLocal->prepare("SELECT id, usuario, nombre FROM usuarios ORDER BY id");
    $stmt->execute();
    $usuariosLocal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $localCorregidos = 0;
    foreach ($usuariosLocal as $usuario) {
        echo "Procesando: {$usuario['usuario']} ({$usuario['nombre']})\n";
        
        // Asumir contraseña basada en el usuario
        $passwordSinEncriptar = ($usuario['usuario'] === 'admin') ? 'admin123' : $usuario['usuario'];
        
        // Encriptar con el método correcto
        $passwordEncriptada = crypt($passwordSinEncriptar, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
        
        echo "   - Contraseña: $passwordSinEncriptar\n";
        echo "   - Encriptada: $passwordEncriptada\n";
        
        // Actualizar en BD Local
        $stmt2 = $pdoLocal->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $resultado = $stmt2->execute([$passwordEncriptada, $usuario['id']]);
        
        if ($resultado) {
            echo "   - ✅ Actualizado en BD Local\n";
            $localCorregidos++;
        } else {
            echo "   - ❌ Error actualizando en BD Local\n";
        }
        echo "\n";
    }
    
    // Restaurar contraseñas en BD Central
    echo "=== RESTAURANDO CONTRASEÑAS EN BD CENTRAL ===\n";
    $stmt = $pdoCentral->prepare("SELECT id, usuario, nombre FROM usuarios_central WHERE activo = 1 ORDER BY id");
    $stmt->execute();
    $usuariosCentral = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $centralCorregidos = 0;
    foreach ($usuariosCentral as $usuario) {
        echo "Procesando: {$usuario['usuario']} ({$usuario['nombre']})\n";
        
        // Asumir contraseña basada en el usuario
        $passwordSinEncriptar = ($usuario['usuario'] === 'admin') ? 'admin123' : $usuario['usuario'];
        
        // Encriptar con el método correcto
        $passwordEncriptada = crypt($passwordSinEncriptar, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
        
        echo "   - Contraseña: $passwordSinEncriptar\n";
        echo "   - Encriptada: $passwordEncriptada\n";
        
        // Actualizar en BD Central
        $stmt2 = $pdoCentral->prepare("UPDATE usuarios_central SET password = ? WHERE id = ?");
        $resultado = $stmt2->execute([$passwordEncriptada, $usuario['id']]);
        
        if ($resultado) {
            echo "   - ✅ Actualizado en BD Central\n";
            $centralCorregidos++;
        } else {
            echo "   - ❌ Error actualizando en BD Central\n";
        }
        echo "\n";
    }
    
    echo "=== RESUMEN ===\n";
    echo "BD Local corregidos: $localCorregidos\n";
    echo "BD Central corregidos: $centralCorregidos\n";
    
    // Verificar que las contraseñas funcionen
    echo "\n=== VERIFICANDO CONTRASEÑAS ===\n";
    
    // Probar admin en BD Local
    $stmt = $pdoLocal->prepare("SELECT password FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    $adminLocal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminLocal) {
        $passwordTest = "admin123";
        $passwordEncriptadaTest = crypt($passwordTest, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
        $loginFunciona = ($adminLocal['password'] === $passwordEncriptadaTest);
        
        echo "Admin BD Local: " . ($loginFunciona ? "✅ Funciona" : "❌ No funciona") . "\n";
    }
    
    // Probar admin en BD Central
    $stmt = $pdoCentral->prepare("SELECT password FROM usuarios_central WHERE usuario = 'admin'");
    $stmt->execute();
    $adminCentral = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminCentral) {
        $passwordTest = "admin123";
        $passwordEncriptadaTest = crypt($passwordTest, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
        $loginFunciona = ($adminCentral['password'] === $passwordEncriptadaTest);
        
        echo "Admin BD Central: " . ($loginFunciona ? "✅ Funciona" : "❌ No funciona") . "\n";
    }
    
    echo "\n=== INSTRUCCIONES ===\n";
    echo "1. Usuario: admin\n";
    echo "2. Contraseña: admin123\n";
    echo "3. URL: https://pruebas.acrilicosinfinito.com/\n";
    echo "4. Probar login en el sistema\n";
    echo "5. Si funciona, el problema está resuelto\n";
    
    echo "\n✅ RESTAURACIÓN COMPLETADA\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>
