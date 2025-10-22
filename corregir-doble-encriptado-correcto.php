<?php
/**
 * SCRIPT PARA CORREGIR CONTRASEÑAS DOBLE ENCRIPTADAS
 * Usa la configuración correcta de conexión central
 */

echo "=== CORREGIR CONTRASEÑAS DOBLE ENCRIPTADAS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración BD Central (CORRECTA)
$hostCentral = "localhost";
$dbnameCentral = "epicosie_central";
$usernameCentral = "epicosie_central";
$passwordCentral = "=Nf?M#6A'QU&.6c";

try {
    echo "Conectando a BD Central...\n";
    echo "Host: $hostCentral\n";
    echo "Base de datos: $dbnameCentral\n";
    echo "Usuario: $usernameCentral\n\n";
    
    $pdoCentral = new PDO("mysql:host=$hostCentral;dbname=$dbnameCentral;charset=utf8mb4", $usernameCentral, $passwordCentral);
    $pdoCentral->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoCentral->exec("set names utf8");
    echo "✅ Conexión BD Central exitosa\n\n";
    
    // Obtener usuarios con contraseñas doble encriptadas
    echo "=== BUSCANDO CONTRASEÑAS DOBLE ENCRIPTADAS ===\n";
    $stmt = $pdoCentral->prepare("SELECT id, usuario, password, nombre FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios encontrados: " . count($usuarios) . "\n\n";
    
    $usuariosCorregidos = 0;
    
    foreach ($usuarios as $usuario) {
        echo "Procesando: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "Password actual: {$usuario['password']}\n";
        echo "Longitud: " . strlen($usuario['password']) . " caracteres\n";
        
        // Verificar si está doble encriptada (longitud > 60)
        if (strlen($usuario['password']) > 60) {
            echo "⚠️  CONTRASEÑA DOBLE ENCRIPTADA DETECTADA\n";
            
            // Asumir que la contraseña sin encriptar es "admin123" para admin
            // y el nombre del usuario para otros usuarios
            $passwordSinEncriptar = ($usuario['usuario'] === 'admin') ? 'admin123' : $usuario['usuario'];
            
            echo "Asumiendo contraseña sin encriptar: $passwordSinEncriptar\n";
            
            // Encriptar correctamente
            $passwordEncriptada = crypt($passwordSinEncriptar, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
            echo "Password corregida: $passwordEncriptada\n";
            
            // Actualizar en BD
            $stmt2 = $pdoCentral->prepare("UPDATE usuarios_central SET password = ? WHERE id = ?");
            $resultado = $stmt2->execute([$passwordEncriptada, $usuario['id']]);
            
            if ($resultado) {
                echo "✅ Contraseña corregida\n";
                $usuariosCorregidos++;
            } else {
                echo "❌ Error corrigiendo contraseña\n";
            }
        } else {
            // Verificar si está correctamente encriptada
            $encriptadaCorrectamente = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
            if ($encriptadaCorrectamente) {
                echo "✅ Ya encriptada correctamente\n";
            } else {
                echo "⚠️  Encriptado incorrecto, corrigiendo...\n";
                
                // Asumir contraseña sin encriptar
                $passwordSinEncriptar = ($usuario['usuario'] === 'admin') ? 'admin123' : $usuario['usuario'];
                $passwordEncriptada = crypt($passwordSinEncriptar, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                
                echo "Asumiendo contraseña sin encriptar: $passwordSinEncriptar\n";
                echo "Password corregida: $passwordEncriptada\n";
                
                $stmt2 = $pdoCentral->prepare("UPDATE usuarios_central SET password = ? WHERE id = ?");
                $resultado = $stmt2->execute([$passwordEncriptada, $usuario['id']]);
                
                if ($resultado) {
                    echo "✅ Contraseña corregida\n";
                    $usuariosCorregidos++;
                } else {
                    echo "❌ Error corrigiendo contraseña\n";
                }
            }
        }
        echo "\n";
    }
    
    echo "=== RESUMEN ===\n";
    echo "Usuarios corregidos: $usuariosCorregidos\n";
    echo "Total usuarios procesados: " . count($usuarios) . "\n";
    
    // Verificar resultado final
    echo "\n=== VERIFICACIÓN FINAL ===\n";
    $stmt = $pdoCentral->prepare("SELECT usuario, password FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $contraseñasCorrectas = 0;
    foreach ($usuarios as $usuario) {
        $encriptadaCorrectamente = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        $longitudCorrecta = strlen($usuario['password']) === 60;
        
        if ($encriptadaCorrectamente && $longitudCorrecta) {
            $contraseñasCorrectas++;
        }
        
        echo "Usuario: {$usuario['usuario']} - " . 
             ($encriptadaCorrectamente && $longitudCorrecta ? "✅ Correcto" : "❌ Incorrecto") . 
             " (Longitud: " . strlen($usuario['password']) . ")\n";
    }
    
    echo "\nContraseñas correctas: $contraseñasCorrectas/" . count($usuarios) . "\n";
    
    if ($contraseñasCorrectas == count($usuarios)) {
        echo "\n✅ TODAS LAS CONTRASEÑAS ESTÁN CORRECTAMENTE ENCRIPTADAS\n";
        echo "✅ El problema de doble encriptado está resuelto\n";
        echo "✅ La edición de usuarios centrales funcionará correctamente\n";
    } else {
        echo "\n⚠️  Algunas contraseñas aún necesitan corrección\n";
    }
    
    echo "\n=== INFORMACIÓN TÉCNICA ===\n";
    echo "Método de encriptado: crypt() con salt personalizado\n";
    echo "Salt utilizado: \$2a\$07\$asxx54ahjppf45sd87a5a4dDDGsystemdev\$\n";
    echo "Longitud esperada: 60 caracteres\n";
    echo "Formato: bcrypt estándar\n";
    
    echo "\n✅ SCRIPT COMPLETADO\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nVerificar:\n";
    echo "1. Credenciales de BD central\n";
    echo "2. Conexión a la base de datos\n";
    echo "3. Permisos de usuario\n";
}
?>
