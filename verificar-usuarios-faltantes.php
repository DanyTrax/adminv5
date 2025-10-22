<?php
/**
 * SCRIPT PARA VERIFICAR USUARIOS FALTANTES
 * Verifica por qué algunos usuarios no aparecen en la verificación final
 */

echo "=== VERIFICAR USUARIOS FALTANTES ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración BD Central
$hostCentral = "localhost";
$dbnameCentral = "epicosie_central";
$usernameCentral = "epicosie_central";
$passwordCentral = "=Nf?M#6A'QU&.6c";

// Configuración BD Local
$hostLocal = "localhost";
$dbnameLocal = "epicosie_pruebas";
$usernameLocal = "epicosie_ricaurte";
$passwordLocal = "m5Wwg)~M{i~*kFr{";

try {
    // Conectar a BD Central
    echo "Conectando a BD Central...\n";
    $pdoCentral = new PDO("mysql:host=$hostCentral;dbname=$dbnameCentral;charset=utf8mb4", $usernameCentral, $passwordCentral);
    $pdoCentral->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoCentral->exec("set names utf8");
    echo "✅ Conexión BD Central exitosa\n\n";
    
    // Conectar a BD Local
    echo "Conectando a BD Local...\n";
    $pdoLocal = new PDO("mysql:host=$hostLocal;dbname=$dbnameLocal;charset=utf8", $usernameLocal, $passwordLocal);
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoLocal->exec("set names utf8");
    echo "✅ Conexión BD Local exitosa\n\n";
    
    // Obtener usuarios centrales con sucursales asignadas
    echo "=== USUARIOS CENTRALES CON SUCURSALES ASIGNADAS ===\n";
    $stmt = $pdoCentral->prepare("SELECT id, usuario, nombre, sucursales_asignadas FROM usuarios_central WHERE activo = 1 AND sucursales_asignadas IS NOT NULL AND sucursales_asignadas != ''");
    $stmt->execute();
    $usuariosCentrales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios centrales con sucursales: " . count($usuariosCentrales) . "\n\n";
    
    foreach ($usuariosCentrales as $usuario) {
        echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "   - ID Central: {$usuario['id']}\n";
        echo "   - Sucursales asignadas: {$usuario['sucursales_asignadas']}\n";
        
        // Verificar si existe en BD Local
        $stmt2 = $pdoLocal->prepare("SELECT id, usuario, nombre, empresa FROM usuarios WHERE usuario = ?");
        $stmt2->execute([$usuario['usuario']]);
        $usuarioLocal = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($usuarioLocal) {
            echo "   - ✅ Existe en BD Local (ID: {$usuarioLocal['id']}, Empresa: {$usuarioLocal['empresa']})\n";
        } else {
            echo "   - ❌ NO existe en BD Local\n";
        }
        echo "\n";
    }
    
    // Obtener usuarios en BD Local
    echo "=== USUARIOS EN BD LOCAL ===\n";
    $stmt = $pdoLocal->prepare("SELECT id, usuario, nombre, empresa FROM usuarios ORDER BY id");
    $stmt->execute();
    $usuariosLocales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios locales encontrados: " . count($usuariosLocales) . "\n\n";
    
    foreach ($usuariosLocales as $usuario) {
        echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "   - ID Local: {$usuario['id']}\n";
        echo "   - Empresa: {$usuario['empresa']}\n";
        echo "\n";
    }
    
    // Comparar listas
    echo "=== COMPARACIÓN ===\n";
    $usuariosCentralesList = array_column($usuariosCentrales, 'usuario');
    $usuariosLocalesList = array_column($usuariosLocales, 'usuario');
    
    $usuariosFaltantes = array_diff($usuariosCentralesList, $usuariosLocalesList);
    $usuariosExtra = array_diff($usuariosLocalesList, $usuariosCentralesList);
    
    if (!empty($usuariosFaltantes)) {
        echo "❌ Usuarios centrales que NO están en BD Local:\n";
        foreach ($usuariosFaltantes as $usuario) {
            echo "   - $usuario\n";
        }
        echo "\n";
    }
    
    if (!empty($usuariosExtra)) {
        echo "ℹ️  Usuarios en BD Local que NO están en BD Central:\n";
        foreach ($usuariosExtra as $usuario) {
            echo "   - $usuario\n";
        }
        echo "\n";
    }
    
    if (empty($usuariosFaltantes) && empty($usuariosExtra)) {
        echo "✅ Todos los usuarios están sincronizados correctamente\n";
    }
    
    // Verificar sucursales específicas
    echo "=== VERIFICAR SUCURSALES ESPECÍFICAS ===\n";
    
    // Obtener datos de sucursales 8 y 9
    $stmt = $pdoCentral->prepare("SELECT id, nombre, host_bd, puerto_bd, nombre_bd, usuario_bd, password_bd FROM sucursales WHERE id IN (8, 9)");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sucursales as $sucursal) {
        echo "Sucursal: {$sucursal['nombre']} (ID: {$sucursal['id']})\n";
        echo "   - Host: {$sucursal['host_bd']}\n";
        echo "   - Puerto: {$sucursal['puerto_bd']}\n";
        echo "   - BD: {$sucursal['nombre_bd']}\n";
        echo "   - Usuario: {$sucursal['usuario_bd']}\n";
        echo "   - Password: " . (empty($sucursal['password_bd']) ? "❌ Vacía" : "✅ Configurada") . "\n";
        
        // Verificar si es la sucursal local actual
        if ($sucursal['host_bd'] === 'localhost' && $sucursal['nombre_bd'] === 'epicosie_pruebas') {
            echo "   - ✅ Es la sucursal local actual\n";
        } else {
            echo "   - ℹ️  Es una sucursal remota\n";
        }
        echo "\n";
    }
    
    echo "=== RESUMEN ===\n";
    echo "Usuarios centrales: " . count($usuariosCentrales) . "\n";
    echo "Usuarios locales: " . count($usuariosLocales) . "\n";
    echo "Usuarios faltantes: " . count($usuariosFaltantes) . "\n";
    echo "Usuarios extra: " . count($usuariosExtra) . "\n";
    
    echo "\n✅ VERIFICACIÓN COMPLETADA\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>
