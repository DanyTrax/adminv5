<?php
/**
 * SCRIPT CORREGIDO PARA VERIFICAR CONTRASEÑAS
 * Corrige el problema de verificación de salt
 */

echo "=== VERIFICAR CONTRASEÑAS CORREGIDAS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración BD Central
$hostCentral = "localhost";
$dbnameCentral = "epicosie_central";
$usernameCentral = "epicosie_central";
$passwordCentral = "=Nf?M#6A'QU&.6c";

try {
    echo "Conectando a BD Central...\n";
    $pdoCentral = new PDO("mysql:host=$hostCentral;dbname=$dbnameCentral;charset=utf8mb4", $usernameCentral, $passwordCentral);
    $pdoCentral->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoCentral->exec("set names utf8");
    echo "✅ Conexión BD Central exitosa\n\n";
    
    // Obtener usuarios
    echo "=== VERIFICANDO CONTRASEÑAS ===\n";
    $stmt = $pdoCentral->prepare("SELECT id, usuario, password, nombre FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios encontrados: " . count($usuarios) . "\n\n";
    
    $contraseñasCorrectas = 0;
    $contraseñasIncorrectas = 0;
    
    foreach ($usuarios as $usuario) {
        echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "Password: {$usuario['password']}\n";
        echo "Longitud: " . strlen($usuario['password']) . " caracteres\n";
        
        // Verificar si es bcrypt (empieza con $2a$ y tiene 60 caracteres)
        $esBcrypt = (strlen($usuario['password']) === 60 && strpos($usuario['password'], '$2a$') === 0);
        echo "Es bcrypt: " . ($esBcrypt ? "✅ Sí" : "❌ No") . "\n";
        
        // Probar login con contraseña asumida
        $passwordAsumida = ($usuario['usuario'] === 'admin') ? 'admin123' : $usuario['usuario'];
        $passwordEncriptadaTest = crypt($passwordAsumida, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
        $loginFunciona = ($usuario['password'] === $passwordEncriptadaTest);
        
        echo "Contraseña asumida: $passwordAsumida\n";
        echo "Password esperada: $passwordEncriptadaTest\n";
        echo "Login funciona: " . ($loginFunciona ? "✅ Sí" : "❌ No") . "\n";
        
        if ($loginFunciona) {
            $contraseñasCorrectas++;
            echo "Estado: ✅ CORRECTO\n";
        } else {
            $contraseñasIncorrectas++;
            echo "Estado: ❌ INCORRECTO\n";
        }
        echo "\n";
    }
    
    echo "=== RESUMEN FINAL ===\n";
    echo "Contraseñas correctas: $contraseñasCorrectas\n";
    echo "Contraseñas incorrectas: $contraseñasIncorrectas\n";
    echo "Total usuarios: " . count($usuarios) . "\n";
    
    if ($contraseñasCorrectas == count($usuarios)) {
        echo "\n✅ TODAS LAS CONTRASEÑAS ESTÁN CORRECTAS\n";
        echo "✅ El problema de encriptado está resuelto\n";
        echo "✅ La edición de usuarios centrales funcionará correctamente\n";
    } else {
        echo "\n⚠️  Algunas contraseñas aún necesitan corrección\n";
        echo "⚠️  Ejecutar el script de corrección nuevamente\n";
    }
    
    echo "\n=== INFORMACIÓN TÉCNICA ===\n";
    echo "Método de encriptado: crypt() con salt personalizado\n";
    echo "Salt utilizado: \$2a\$07\$asxx54ahjppf45sd87a5a4dDDGsystemdev\$\n";
    echo "Longitud esperada: 60 caracteres\n";
    echo "Formato: bcrypt estándar\n";
    
    echo "\n✅ VERIFICACIÓN COMPLETADA\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>
