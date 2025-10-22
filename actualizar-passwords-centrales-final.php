<?php
/**
 * SCRIPT PARA ACTUALIZAR CONTRASEÑAS DE USUARIOS CENTRALES
 * Aplica el mismo encriptado que usuarios locales para evitar fallos en sincronización
 */

echo "=== ACTUALIZAR CONTRASEÑAS USUARIOS CENTRALES ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración específica de tu servidor central
$host = "localhost";
$dbname = "epicosie_central";
$username = "epicosie_ricaurte";
$password = "m5Wwg)~M{i~*kFr{";

try {
    echo "Conectando a BD central...\n";
    echo "Host: $host\n";
    echo "Base de datos: $dbname\n";
    echo "Usuario: $username\n\n";
    
    // Conectar a BD central
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
    
    echo "✅ Conexión a BD central exitosa\n\n";
    
    // Obtener todos los usuarios centrales
    echo "=== OBTENIENDO USUARIOS CENTRALES ===\n";
    $stmt = $pdo->prepare("SELECT id, usuario, password, nombre FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios encontrados: " . count($usuarios) . "\n\n";
    
    $usuariosActualizados = 0;
    $usuariosCorrectos = 0;
    
    foreach ($usuarios as $usuario) {
        echo "Procesando: {$usuario['usuario']} ({$usuario['nombre']})\n";
        
        // Verificar si ya está encriptada correctamente
        $encriptadaCorrectamente = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        
        if (!$encriptadaCorrectamente) {
            echo "  ⚠️  Contraseña no encriptada correctamente\n";
            echo "  Password actual: {$usuario['password']}\n";
            
            // Asumir que la contraseña actual es la contraseña sin encriptar
            // y encriptarla con el método correcto
            $passwordEncriptada = crypt($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
            
            echo "  Password encriptada: $passwordEncriptada\n";
            
            // Actualizar en BD
            $stmt2 = $pdo->prepare("UPDATE usuarios_central SET password = ? WHERE id = ?");
            $resultado = $stmt2->execute([$passwordEncriptada, $usuario['id']]);
            
            if ($resultado) {
                echo "  ✅ Contraseña actualizada\n";
                $usuariosActualizados++;
            } else {
                echo "  ❌ Error actualizando contraseña\n";
            }
        } else {
            echo "  ✅ Ya encriptada correctamente\n";
            $usuariosCorrectos++;
        }
        echo "\n";
    }
    
    // Verificar resultado final
    echo "=== VERIFICACIÓN FINAL ===\n";
    
    $stmt = $pdo->prepare("SELECT usuario, password FROM usuarios_central WHERE activo = 1");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $contraseñasCorrectas = 0;
    foreach ($usuarios as $usuario) {
        $encriptadaCorrectamente = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        if ($encriptadaCorrectamente) {
            $contraseñasCorrectas++;
        }
    }
    
    echo "Total usuarios: " . count($usuarios) . "\n";
    echo "Usuarios actualizados: $usuariosActualizados\n";
    echo "Usuarios ya correctos: $usuariosCorrectos\n";
    echo "Contraseñas correctamente encriptadas: $contraseñasCorrectas/" . count($usuarios) . "\n";
    
    if ($contraseñasCorrectas == count($usuarios)) {
        echo "\n✅ TODAS LAS CONTRASEÑAS ESTÁN CORRECTAMENTE ENCRIPTADAS\n";
        echo "✅ La sincronización funcionará sin problemas\n";
    } else {
        echo "\n⚠️  Algunas contraseñas aún necesitan corrección\n";
    }
    
    echo "\n=== INFORMACIÓN TÉCNICA ===\n";
    echo "Método de encriptado: crypt() con salt personalizado\n";
    echo "Salt utilizado: \$2a\$07\$asxx54ahjppf45sd87a5a4dDDGsystemdev\$\n";
    echo "Longitud de contraseña encriptada: 60 caracteres\n";
    echo "Formato: bcrypt estándar\n";
    
    echo "\n✅ SCRIPT COMPLETADO EXITOSAMENTE\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nVerificar:\n";
    echo "1. Credenciales de BD central\n";
    echo "2. Conexión a la base de datos\n";
    echo "3. Permisos de usuario\n";
}
?>
