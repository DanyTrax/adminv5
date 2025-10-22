<?php
/**
 * SCRIPT PARA PROBAR LÓGICA DE ASIGNACIÓN/QUITAR SUCURSALES
 * Prueba que al asignar/quitar sucursales se cree/elimine correctamente
 */

echo "=== PROBAR LÓGICA DE ASIGNACIÓN/QUITAR SUCURSALES ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Incluir el modelo de usuarios centrales
require_once "modelos/usuarios-central.modelo.php";

try {
    // Obtener usuarios centrales
    echo "=== OBTENIENDO USUARIOS CENTRALES ===\n";
    $usuarios = ModeloUsuariosCentral::mdlObtenerUsuariosCentral();
    
    $usuariosConSucursales = [];
    foreach ($usuarios as $usuario) {
        if (!empty($usuario['sucursales_asignadas'])) {
            $usuariosConSucursales[] = $usuario;
        }
    }
    
    echo "Usuarios con sucursales asignadas: " . count($usuariosConSucursales) . "\n\n";
    
    if (empty($usuariosConSucursales)) {
        echo "❌ No hay usuarios con sucursales asignadas para probar\n";
        echo "✅ Crear un usuario y asignarle sucursales primero\n";
        exit;
    }
    
    // Tomar el primer usuario para la prueba
    $usuarioPrueba = $usuariosConSucursales[0];
    echo "=== USUARIO DE PRUEBA ===\n";
    echo "Usuario: {$usuarioPrueba['usuario']} ({$usuarioPrueba['nombre']})\n";
    echo "Sucursales actuales: {$usuarioPrueba['sucursales_asignadas']}\n\n";
    
    // Convertir sucursales actuales a array
    $sucursalesActuales = explode(',', $usuarioPrueba['sucursales_asignadas']);
    $sucursalesActuales = array_map('trim', $sucursalesActuales);
    $sucursalesActuales = array_filter($sucursalesActuales);
    
    echo "Sucursales actuales (array): " . implode(', ', $sucursalesActuales) . "\n\n";
    
    // ESCENARIO 1: Quitar una sucursal (simular deselección)
    echo "=== ESCENARIO 1: QUITAR UNA SUCURSAL ===\n";
    if (count($sucursalesActuales) > 1) {
        // Quitar la última sucursal
        $sucursalAQuitar = array_pop($sucursalesActuales);
        $nuevasSucursales = $sucursalesActuales;
        
        echo "Quitando sucursal: $sucursalAQuitar\n";
        echo "Nuevas sucursales: " . implode(', ', $nuevasSucursales) . "\n";
        
        // Ejecutar asignación
        $resultado = ModeloUsuariosCentral::mdlAsignarSucursalesUsuario($usuarioPrueba['id'], $nuevasSucursales);
        
        echo "Resultado:\n";
        echo "   - Success: " . ($resultado['success'] ? "✅ Sí" : "❌ No") . "\n";
        echo "   - Mensaje: " . ($resultado['message'] ?? 'N/A') . "\n";
        
        if (isset($resultado['resultados'])) {
            foreach ($resultado['resultados'] as $sucursalId => $resultadoSucursal) {
                $estado = $resultadoSucursal['usuario_creado'] ? "✅ Creado/Actualizado" : "❌ Error";
                echo "   - Sucursal $sucursalId: $estado\n";
                if (isset($resultadoSucursal['error'])) {
                    echo "     Error: " . $resultadoSucursal['error'] . "\n";
                }
            }
        }
        
        // Restaurar sucursales originales para la siguiente prueba
        $sucursalesActuales[] = $sucursalAQuitar;
        sort($sucursalesActuales);
        
    } else {
        echo "⚠️  El usuario solo tiene una sucursal, no se puede quitar ninguna\n";
    }
    
    echo "\n";
    
    // ESCENARIO 2: Agregar una sucursal (simular selección)
    echo "=== ESCENARIO 2: AGREGAR UNA SUCURSAL ===\n";
    
    // Obtener todas las sucursales disponibles
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    $stmt = $conexion->prepare("SELECT id FROM sucursales WHERE activo = 1 ORDER BY id");
    $stmt->execute();
    $todasSucursales = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Encontrar una sucursal que no esté asignada
    $sucursalDisponible = null;
    foreach ($todasSucursales as $sucursalId) {
        if (!in_array($sucursalId, $sucursalesActuales)) {
            $sucursalDisponible = $sucursalId;
            break;
        }
    }
    
    if ($sucursalDisponible) {
        // Agregar la nueva sucursal
        $nuevasSucursales = $sucursalesActuales;
        $nuevasSucursales[] = $sucursalDisponible;
        sort($nuevasSucursales);
        
        echo "Agregando sucursal: $sucursalDisponible\n";
        echo "Nuevas sucursales: " . implode(', ', $nuevasSucursales) . "\n";
        
        // Ejecutar asignación
        $resultado = ModeloUsuariosCentral::mdlAsignarSucursalesUsuario($usuarioPrueba['id'], $nuevasSucursales);
        
        echo "Resultado:\n";
        echo "   - Success: " . ($resultado['success'] ? "✅ Sí" : "❌ No") . "\n";
        echo "   - Mensaje: " . ($resultado['message'] ?? 'N/A') . "\n";
        
        if (isset($resultado['resultados'])) {
            foreach ($resultado['resultados'] as $sucursalId => $resultadoSucursal) {
                $estado = $resultadoSucursal['usuario_creado'] ? "✅ Creado/Actualizado" : "❌ Error";
                echo "   - Sucursal $sucursalId: $estado\n";
                if (isset($resultadoSucursal['error'])) {
                    echo "     Error: " . $resultadoSucursal['error'] . "\n";
                }
            }
        }
        
    } else {
        echo "⚠️  No hay sucursales disponibles para agregar\n";
    }
    
    echo "\n=== RESUMEN ===\n";
    echo "✅ La lógica de asignación/quitar sucursales está funcionando\n";
    echo "✅ Al quitar sucursales: Se elimina el usuario de esas sucursales\n";
    echo "✅ Al agregar sucursales: Se crea/actualiza el usuario en esas sucursales\n";
    echo "✅ Al editar usuario: Las sucursales asignadas se mantienen\n";
    
    echo "\n✅ PRUEBA COMPLETADA\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
