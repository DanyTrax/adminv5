<?php
/**
 * SCRIPT PARA PROBAR SINCRONIZACIÓN
 * Prueba la sincronización de usuarios centrales a locales
 */

echo "=== PROBAR SINCRONIZACIÓN DE USUARIOS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Incluir archivos necesarios
require_once "modelos/usuarios-central.modelo.php";

try {
    echo "Iniciando prueba de sincronización...\n\n";
    
    // Obtener usuarios centrales con sucursales asignadas
    $conexion = ConexionCentral::conectar();
    $stmt = $conexion->prepare("
        SELECT id, usuario, password, nombre, perfil, sucursales_asignadas 
        FROM usuarios_central 
        WHERE activo = 1 
        AND sucursales_asignadas IS NOT NULL 
        AND sucursales_asignadas != ''
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios centrales con sucursales asignadas: " . count($usuarios) . "\n\n";
    
    foreach ($usuarios as $usuario) {
        echo "Procesando usuario: {$usuario['usuario']} ({$usuario['nombre']})\n";
        echo "Sucursales asignadas: {$usuario['sucursales_asignadas']}\n";
        
        // Verificar encriptado
        $encriptadaCorrectamente = strpos($usuario['password'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$') === 0;
        echo "Encriptado correcto: " . ($encriptadaCorrectamente ? "✅ Sí" : "❌ No") . "\n";
        
        if (!$encriptadaCorrectamente) {
            echo "⚠️  Este usuario puede causar problemas en sincronización\n";
        }
        
        echo "\n";
    }
    
    // Probar sincronización real
    echo "=== PROBANDO SINCRONIZACIÓN REAL ===\n";
    
    $resultado = ModeloUsuariosCentral::mdlSincronizarTodosUsuarios();
    
    if ($resultado['success']) {
        echo "✅ Sincronización exitosa\n";
        echo "Mensaje: {$resultado['message']}\n";
        echo "Usuarios sincronizados: {$resultado['usuarios_sincronizados']}\n";
        echo "Errores: {$resultado['errores']}\n";
        
        if (isset($resultado['resultados'])) {
            echo "\nDetalles por usuario:\n";
            foreach ($resultado['resultados'] as $resultadoUsuario) {
                echo "- {$resultadoUsuario['usuario']}: {$resultadoUsuario['estado']}\n";
                if (isset($resultadoUsuario['error'])) {
                    echo "  Error: {$resultadoUsuario['error']}\n";
                }
            }
        }
    } else {
        echo "❌ Error en sincronización\n";
        echo "Error: {$resultado['error']}\n";
    }
    
    echo "\n=== VERIFICAR USUARIOS EN SUCURSALES ===\n";
    
    // Verificar usuarios en sucursales locales
    $conexionLocal = Conexion::conectar();
    $stmt = $conexionLocal->prepare("SELECT usuario, empresa FROM usuarios WHERE estado = 1");
    $stmt->execute();
    $usuariosLocales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Usuarios en sucursal local: " . count($usuariosLocales) . "\n";
    foreach ($usuariosLocales as $usuario) {
        echo "- {$usuario['usuario']} (Empresa: {$usuario['empresa']})\n";
    }
    
    echo "\n✅ PRUEBA DE SINCRONIZACIÓN COMPLETADA\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nVerificar:\n";
    echo "1. Conexiones a bases de datos\n";
    echo "2. Configuración de sucursales\n";
    echo "3. Permisos de usuario\n";
}
?>
