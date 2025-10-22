<?php
/**
 * SCRIPT PARA SINCRONIZAR USUARIOS FALTANTES
 * Sincroniza usuarios que no aparecieron en la verificación final
 */

echo "=== SINCRONIZAR USUARIOS FALTANTES ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Incluir el modelo de usuarios centrales
require_once "modelos/usuarios-central.modelo.php";

try {
    // Obtener usuarios centrales con sucursales asignadas
    echo "=== OBTENIENDO USUARIOS CENTRALES ===\n";
    $usuarios = ModeloUsuariosCentral::mdlObtenerUsuariosCentral();
    
    $usuariosConSucursales = [];
    foreach ($usuarios as $usuario) {
        if (!empty($usuario['sucursales_asignadas'])) {
            $usuariosConSucursales[] = $usuario;
        }
    }
    
    echo "Usuarios con sucursales asignadas: " . count($usuariosConSucursales) . "\n\n";
    
    // Sincronizar cada usuario individualmente
    foreach ($usuariosConSucursales as $usuario) {
        echo "=== SINCRONIZANDO: {$usuario['usuario']} ===\n";
        
        $sucursalesAsignadas = explode(',', $usuario['sucursales_asignadas']);
        $sucursalesAsignadas = array_map('trim', $sucursalesAsignadas);
        $sucursalesAsignadas = array_filter($sucursalesAsignadas);
        
        echo "Sucursales: " . implode(', ', $sucursalesAsignadas) . "\n";
        
        // Sincronizar
        $resultado = ModeloUsuariosCentral::mdlAsignarSucursalesUsuario($usuario['id'], $sucursalesAsignadas);
        
        echo "Resultado: " . ($resultado['success'] ? "✅ Éxito" : "❌ Error") . "\n";
        
        if (isset($resultado['error'])) {
            echo "Error: " . $resultado['error'] . "\n";
        }
        
        if (isset($resultado['resultados'])) {
            foreach ($resultado['resultados'] as $sucursalId => $resultadoSucursal) {
                $estado = $resultadoSucursal['usuario_creado'] ? "✅ Creado" : "❌ Error";
                echo "  Sucursal $sucursalId: $estado\n";
                if (isset($resultadoSucursal['error'])) {
                    echo "    Error: " . $resultadoSucursal['error'] . "\n";
                }
            }
        }
        
        echo "\n";
    }
    
    // Verificar resultado final
    echo "=== VERIFICACIÓN FINAL ===\n";
    
    // Conectar a BD Local
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
    
    echo "Usuarios en BD Local: " . count($usuariosLocal) . "\n\n";
    
    foreach ($usuariosLocal as $usuario) {
        echo "Usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "   - ID: {$usuario['id']}\n";
        echo "   - Empresa: {$usuario['empresa']}\n";
        echo "\n";
    }
    
    echo "✅ SINCRONIZACIÓN COMPLETADA\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
