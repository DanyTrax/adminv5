<?php
/**
 * SCRIPT PARA PROBAR SINCRONIZACIÓN MANUAL
 * Prueba el proceso de sincronización de usuarios centrales a locales
 */

echo "=== PROBAR SINCRONIZACIÓN MANUAL ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Incluir el modelo de usuarios centrales
require_once "modelos/usuarios-central.modelo.php";

try {
    // Obtener usuarios centrales con sucursales asignadas
    echo "=== OBTENIENDO USUARIOS CENTRALES ===\n";
    $usuarios = ModeloUsuariosCentral::mdlObtenerUsuariosCentral();
    
    echo "Usuarios centrales encontrados: " . count($usuarios) . "\n\n";
    
    $usuariosConSucursales = [];
    foreach ($usuarios as $usuario) {
        if (!empty($usuario['sucursales_asignadas'])) {
            $usuariosConSucursales[] = $usuario;
            echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
            echo "   - Sucursales asignadas: {$usuario['sucursales_asignadas']}\n";
        }
    }
    
    echo "\nUsuarios con sucursales asignadas: " . count($usuariosConSucursales) . "\n\n";
    
    if (empty($usuariosConSucursales)) {
        echo "❌ No hay usuarios con sucursales asignadas\n";
        echo "✅ Crear un usuario y asignarle sucursales primero\n";
        exit;
    }
    
    // Probar sincronización para cada usuario
    foreach ($usuariosConSucursales as $usuario) {
        echo "=== SINCRONIZANDO USUARIO: {$usuario['usuario']} ===\n";
        
        $sucursalesAsignadas = explode(',', $usuario['sucursales_asignadas']);
        $sucursalesAsignadas = array_map('trim', $sucursalesAsignadas);
        $sucursalesAsignadas = array_filter($sucursalesAsignadas);
        
        echo "Sucursales a sincronizar: " . implode(', ', $sucursalesAsignadas) . "\n";
        
        // Llamar al método de sincronización
        $resultado = ModeloUsuariosCentral::mdlAsignarSucursalesUsuario($usuario['id'], $sucursalesAsignadas);
        
        echo "Resultado de sincronización:\n";
        echo "   - Success: " . ($resultado['success'] ? "✅ Sí" : "❌ No") . "\n";
        echo "   - Mensaje: " . ($resultado['message'] ?? 'N/A') . "\n";
        
        if (isset($resultado['error'])) {
            echo "   - Error: " . $resultado['error'] . "\n";
        }
        
        if (isset($resultado['resultados'])) {
            echo "   - Resultados por sucursal:\n";
            foreach ($resultado['resultados'] as $sucursalId => $resultadoSucursal) {
                echo "     * Sucursal ID $sucursalId: " . ($resultadoSucursal['usuario_creado'] ? "✅ Usuario creado" : "❌ Error") . "\n";
                if (isset($resultadoSucursal['error'])) {
                    echo "       Error: " . $resultadoSucursal['error'] . "\n";
                }
            }
        }
        
        echo "\n";
    }
    
    // Verificar resultado final
    echo "=== VERIFICACIÓN FINAL ===\n";
    
    // Conectar a BD Local para verificar
    $hostLocal = "localhost";
    $dbnameLocal = "epicosie_pruebas";
    $usernameLocal = "epicosie_ricaurte";
    $passwordLocal = "m5Wwg)~M{i~*kFr{";
    
    $pdoLocal = new PDO("mysql:host=$hostLocal;dbname=$dbnameLocal;charset=utf8", $usernameLocal, $passwordLocal);
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoLocal->exec("set names utf8");
    
    $stmt = $pdoLocal->prepare("SELECT id, usuario, nombre, empresa FROM usuarios ORDER BY id");
    $stmt->execute();
    $usuariosLocal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios en BD Local después de sincronización: " . count($usuariosLocal) . "\n\n";
    
    foreach ($usuariosLocal as $usuario) {
        echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "   - ID: {$usuario['id']}\n";
        echo "   - Empresa: {$usuario['empresa']}\n";
        echo "\n";
    }
    
    echo "✅ PRUEBA DE SINCRONIZACIÓN COMPLETADA\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
