<?php
/**
 * SCRIPT PARA DIAGNOSTICAR PROBLEMA DE ASIGNACIÓN DE SUCURSALES
 * Verifica por qué no se están creando usuarios en las sucursales locales
 */

echo "=== DIAGNÓSTICO DE ASIGNACIÓN DE SUCURSALES ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración BD Central
$hostCentral = "localhost";
$dbnameCentral = "epicosie_central";
$usernameCentral = "epicosie_central";
$passwordCentral = "=Nf?M#6A'QU&.6c";

// Configuración BD Local (Sucursal Principal)
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
    echo "Conectando a BD Local (Sucursal Principal)...\n";
    $pdoLocal = new PDO("mysql:host=$hostLocal;dbname=$dbnameLocal;charset=utf8", $usernameLocal, $passwordLocal);
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoLocal->exec("set names utf8");
    echo "✅ Conexión BD Local exitosa\n\n";
    
    // Verificar sucursales disponibles
    echo "=== SUCURSALES DISPONIBLES ===\n";
    $stmt = $pdoCentral->prepare("SELECT id, nombre, activo, usuario_bd, password_bd, nombre_bd, host_bd, puerto_bd FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Sucursales encontradas: " . count($sucursales) . "\n\n";
    
    foreach ($sucursales as $sucursal) {
        echo "Sucursal: {$sucursal['nombre']} (ID: {$sucursal['id']})\n";
        echo "   - Activa: " . ($sucursal['activo'] ? "✅ Sí" : "❌ No") . "\n";
        echo "   - Host BD: {$sucursal['host_bd']}\n";
        echo "   - Puerto BD: {$sucursal['puerto_bd']}\n";
        echo "   - Nombre BD: {$sucursal['nombre_bd']}\n";
        echo "   - Usuario BD: {$sucursal['usuario_bd']}\n";
        echo "   - Password BD: " . (empty($sucursal['password_bd']) ? "❌ Vacía" : "✅ Configurada") . "\n";
        echo "\n";
    }
    
    // Verificar usuarios centrales con sucursales asignadas
    echo "=== USUARIOS CENTRALES CON SUCURSALES ASIGNADAS ===\n";
    $stmt = $pdoCentral->prepare("SELECT id, usuario, nombre, sucursales_asignadas FROM usuarios_central WHERE activo = 1 AND sucursales_asignadas IS NOT NULL AND sucursales_asignadas != ''");
    $stmt->execute();
    $usuariosConSucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios con sucursales asignadas: " . count($usuariosConSucursales) . "\n\n";
    
    foreach ($usuariosConSucursales as $usuario) {
        echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "   - ID: {$usuario['id']}\n";
        echo "   - Sucursales asignadas: {$usuario['sucursales_asignadas']}\n";
        
        // Verificar si existe en sucursales locales
        $sucursalesAsignadas = explode(',', $usuario['sucursales_asignadas']);
        echo "   - Sucursales ID: " . implode(', ', $sucursalesAsignadas) . "\n";
        
        foreach ($sucursalesAsignadas as $sucursalId) {
            $sucursalId = trim($sucursalId);
            if (empty($sucursalId)) continue;
            
            // Buscar la sucursal
            $stmt2 = $pdoCentral->prepare("SELECT nombre, host_bd, puerto_bd, nombre_bd, usuario_bd, password_bd FROM sucursales WHERE id = ?");
            $stmt2->execute([$sucursalId]);
            $sucursal = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if ($sucursal) {
                echo "     - Sucursal ID $sucursalId: {$sucursal['nombre']}\n";
                
                // Verificar si el usuario existe en esta sucursal
                try {
                    if ($sucursal['host_bd'] === 'localhost' && $sucursal['nombre_bd'] === 'epicosie_pruebas') {
                        // Es la sucursal local actual
                        $stmt3 = $pdoLocal->prepare("SELECT id, usuario, nombre, empresa FROM usuarios WHERE usuario = ?");
                        $stmt3->execute([$usuario['usuario']]);
                        $usuarioLocal = $stmt3->fetch(PDO::FETCH_ASSOC);
                        
                        if ($usuarioLocal) {
                            echo "       ✅ Existe en BD Local (ID: {$usuarioLocal['id']}, Empresa: {$usuarioLocal['empresa']})\n";
                        } else {
                            echo "       ❌ NO existe en BD Local\n";
                        }
                    } else {
                        // Es otra sucursal, intentar conectar
                        $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8";
                        $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd'], [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]);
                        
                        $stmt3 = $pdoSucursal->prepare("SELECT id, usuario, nombre, empresa FROM usuarios WHERE usuario = ?");
                        $stmt3->execute([$usuario['usuario']]);
                        $usuarioSucursal = $stmt3->fetch(PDO::FETCH_ASSOC);
                        
                        if ($usuarioSucursal) {
                            echo "       ✅ Existe en sucursal (ID: {$usuarioSucursal['id']}, Empresa: {$usuarioSucursal['empresa']})\n";
                        } else {
                            echo "       ❌ NO existe en sucursal\n";
                        }
                    }
                } catch (Exception $e) {
                    echo "       ❌ Error conectando a sucursal: " . $e->getMessage() . "\n";
                }
            } else {
                echo "     - Sucursal ID $sucursalId: ❌ No encontrada\n";
            }
        }
        echo "\n";
    }
    
    // Verificar usuarios en BD Local
    echo "=== USUARIOS EN BD LOCAL ===\n";
    $stmt = $pdoLocal->prepare("SELECT id, usuario, nombre, empresa FROM usuarios ORDER BY id");
    $stmt->execute();
    $usuariosLocal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios locales encontrados: " . count($usuariosLocal) . "\n\n";
    
    foreach ($usuariosLocal as $usuario) {
        echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "   - ID: {$usuario['id']}\n";
        echo "   - Empresa: {$usuario['empresa']}\n";
        echo "\n";
    }
    
    echo "=== RESUMEN ===\n";
    echo "Sucursales disponibles: " . count($sucursales) . "\n";
    echo "Usuarios con sucursales asignadas: " . count($usuariosConSucursales) . "\n";
    echo "Usuarios en BD Local: " . count($usuariosLocal) . "\n";
    
    echo "\n=== INSTRUCCIONES ===\n";
    echo "1. Si hay usuarios con sucursales asignadas pero no existen en BD Local:\n";
    echo "   - El proceso de sincronización no está funcionando\n";
    echo "   - Verificar logs de error en el servidor\n";
    echo "   - Ejecutar sincronización manual\n";
    echo "\n2. Si faltan datos de conexión de sucursales:\n";
    echo "   - Configurar datos de BD en tabla sucursales\n";
    echo "   - Verificar que las sucursales estén activas\n";
    
    echo "\n✅ DIAGNÓSTICO COMPLETADO\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
?>
