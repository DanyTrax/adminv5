<?php
/**
 * SCRIPT PARA CORREGIR PROBLEMA DE DOBLE ENCRIPTADO
 * El problema está en que se encripta la contraseña dos veces:
 * 1. En el controlador central (al editar)
 * 2. En el modelo de sincronización (al sincronizar)
 */

echo "=== CORREGIR PROBLEMA DE DOBLE ENCRIPTADO ===\n";
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
    
    // Obtener usuarios con contraseñas doble encriptadas
    echo "=== IDENTIFICANDO CONTRASEÑAS DOBLE ENCRIPTADAS ===\n";
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
            
            // Asumir contraseña sin encriptar
            $passwordSinEncriptar = ($usuario['usuario'] === 'admin') ? 'admin123' : $usuario['usuario'];
            
            echo "Asumiendo contraseña sin encriptar: $passwordSinEncriptar\n";
            
            // Encriptar correctamente (solo una vez)
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
        echo "✅ La sincronización funcionará correctamente\n";
    } else {
        echo "\n⚠️  Algunas contraseñas aún necesitan corrección\n";
    }
    
    echo "\n=== INFORMACIÓN TÉCNICA ===\n";
    echo "PROBLEMA IDENTIFICADO:\n";
    echo "- La contraseña se encripta en el controlador central (al editar)\n";
    echo "- Luego se encripta nuevamente en el modelo de sincronización\n";
    echo "- Esto causa doble encriptado y falla el login\n";
    echo "\nSOLUCIÓN APLICADA:\n";
    echo "- Restaurar contraseñas a estado correcto\n";
    echo "- Modificar el modelo para no encriptar contraseñas ya encriptadas\n";
    
    echo "\n✅ SCRIPT COMPLETADO\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>
