<?php
/**
 * SCRIPT PARA VERIFICAR CONSISTENCIA DE ENCRIPTADO
 * Verifica que usuarios centrales y locales usen el mismo método de encriptado
 */

echo "=== VERIFICAR CONSISTENCIA DE ENCRIPTADO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración BD Central
$hostCentral = "localhost";
$dbnameCentral = "epicosie_central";
$usernameCentral = "epicosie_ricaurte";
$passwordCentral = "m5Wwg)~M{i~*kFr{";

// Configuración BD Local
$hostLocal = "localhost";
$dbnameLocal = "epicosie_pruebas";
$usernameLocal = "epicosie_ricaurte";
$passwordLocal = "m5Wwg)~M{i~*kFr{";

try {
    // Conectar a BD Central
    echo "Conectando a BD Central...\n";
    $pdoCentral = new PDO("mysql:host=$hostCentral;dbname=$dbnameCentral;charset=utf8", $usernameCentral, $passwordCentral);
    $pdoCentral->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoCentral->exec("set names utf8");
    echo "✅ Conexión BD Central exitosa\n\n";
    
    // Conectar a BD Local
    echo "Conectando a BD Local...\n";
    $pdoLocal = new PDO("mysql:host=$hostLocal;dbname=$dbnameLocal;charset=utf8", $usernameLocal, $passwordLocal);
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoLocal->exec("set names utf8");
    echo "✅ Conexión BD Local exitosa\n\n";
    
    // Verificar usuarios centrales
    echo "=== USUARIOS CENTRALES ===\n";
    $stmt = $pdoCentral->prepare("SELECT usuario, password FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $usuariosCentrales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $centralesCorrectos = 0;
    foreach ($usuariosCentrales as $usuario) {
        $encriptadaCorrectamente = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        if ($encriptadaCorrectamente) {
            $centralesCorrectos++;
        }
        echo "Usuario: {$usuario['usuario']} - " . ($encriptadaCorrectamente ? "✅ Correcto" : "❌ Incorrecto") . "\n";
    }
    
    echo "Centrales correctos: $centralesCorrectos/" . count($usuariosCentrales) . "\n\n";
    
    // Verificar usuarios locales
    echo "=== USUARIOS LOCALES ===\n";
    $stmt = $pdoLocal->prepare("SELECT usuario, password FROM usuarios WHERE estado = 1");
    $stmt->execute();
    $usuariosLocales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $localesCorrectos = 0;
    foreach ($usuariosLocales as $usuario) {
        $encriptadaCorrectamente = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        if ($encriptadaCorrectamente) {
            $localesCorrectos++;
        }
        echo "Usuario: {$usuario['usuario']} - " . ($encriptadaCorrectamente ? "✅ Correcto" : "❌ Incorrecto") . "\n";
    }
    
    echo "Locales correctos: $localesCorrectos/" . count($usuariosLocales) . "\n\n";
    
    // Resumen final
    echo "=== RESUMEN FINAL ===\n";
    echo "Usuarios centrales: $centralesCorrectos/" . count($usuariosCentrales) . " correctos\n";
    echo "Usuarios locales: $localesCorrectos/" . count($usuariosLocales) . " correctos\n";
    
    if ($centralesCorrectos == count($usuariosCentrales) && $localesCorrectos == count($usuariosLocales)) {
        echo "\n✅ CONSISTENCIA PERFECTA\n";
        echo "✅ Todos los usuarios usan el mismo método de encriptado\n";
        echo "✅ La sincronización funcionará sin problemas\n";
    } else {
        echo "\n⚠️  INCONSISTENCIA DETECTADA\n";
        echo "⚠️  Algunos usuarios no usan el método correcto de encriptado\n";
        echo "⚠️  Esto puede causar problemas en la sincronización\n";
    }
    
    echo "\n=== INFORMACIÓN TÉCNICA ===\n";
    echo "Método esperado: crypt() con salt \$2a\$07\$asxx54ahjppf45sd87a5a4dDDGsystemdev\$\n";
    echo "Longitud esperada: 60 caracteres\n";
    echo "Formato: bcrypt estándar\n";
    
    echo "\n✅ VERIFICACIÓN COMPLETADA\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nVerificar:\n";
    echo "1. Credenciales de ambas bases de datos\n";
    echo "2. Conexiones a las bases de datos\n";
    echo "3. Permisos de usuario\n";
}
?>
