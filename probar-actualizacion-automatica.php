<?php
/**
 * SCRIPT PARA PROBAR ACTUALIZACIÓN AUTOMÁTICA DEL CAMPO EMPRESA
 */

echo "<h2>🧪 PROBAR ACTUALIZACIÓN AUTOMÁTICA DEL CAMPO EMPRESA</h2>";

try {
    require_once "config.php";
    require_once "modelos/conexion.php";
    require_once "modelos/sucursales.modelo.php";
    require_once "controladores/sucursales.controlador.php";
    
    // 1. Limpiar logs anteriores
    echo "<h3>🧹 1. Limpiando logs anteriores:</h3>";
    $logFile = "logs/sistema.log";
    if (file_exists($logFile)) {
        file_put_contents($logFile, "");
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Logs limpiados</strong>";
        echo "</div>";
    }
    
    // 2. Obtener configuración local actual
    echo "<h3>📋 2. Configuración Local Actual:</h3>";
    $configLocal = ModeloSucursales::mdlObtenerConfiguracionLocal();
    
    if ($configLocal) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Configuración local:</strong><br>";
        echo "Código: " . $configLocal['codigo_sucursal'] . "<br>";
        echo "Nombre: " . $configLocal['nombre'] . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay configuración local</strong>";
        echo "</div>";
        exit;
    }
    
    // 3. Verificar usuarios antes del cambio
    echo "<h3>📋 3. Usuarios ANTES del cambio:</h3>";
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT id, nombre, usuario, empresa FROM usuarios WHERE activo = 1 ORDER BY id LIMIT 5");
    $stmt->execute();
    $usuariosAntes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Usuarios ANTES (primeros 5):</strong><br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Empresa ANTES</th></tr>";
    foreach ($usuariosAntes as $usuario) {
        $empresaAntes = $usuario['empresa'] ?: 'Sin asignar';
        echo "<tr>";
        echo "<td>{$usuario['id']}</td>";
        echo "<td>{$usuario['nombre']}</td>";
        echo "<td>{$usuario['usuario']}</td>";
        echo "<td>{$empresaAntes}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 4. Simular cambio de nombre de sucursal
    echo "<h3>🔄 4. Simulando cambio de nombre de sucursal:</h3>";
    
    $nuevoNombre = $configLocal['nombre'] . ' - Test ' . date('H:i:s');
    
    // Simular datos como si vinieran del formulario
    $datosSimulados = [
        'codigo_sucursal' => $configLocal['codigo_sucursal'],
        'nombre' => $nuevoNombre,
        'direccion' => $configLocal['direccion'],
        'telefono' => $configLocal['telefono'],
        'email' => $configLocal['email'],
        'url_base' => $configLocal['url_base'] ?? '',
        'url_api' => $configLocal['url_api'] ?? '',
        'usuario_bd' => $configLocal['usuario_bd'] ?? '',
        'password_bd' => $configLocal['password_bd'] ?? '',
        'nombre_bd' => $configLocal['nombre_bd'] ?? '',
        'host_bd' => $configLocal['host_bd'] ?? 'localhost',
        'puerto_bd' => $configLocal['puerto_bd'] ?? 3306,
        'es_principal' => 1,
        'activo' => $configLocal['activo']
    ];
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Simulando cambio a:</strong> {$nuevoNombre}<br>";
    echo "</div>";
    
    // 5. Ejecutar actualización manual (simulando el flujo del controlador)
    echo "<h3>🔄 5. Ejecutando actualización manual:</h3>";
    
    // Simular guardar configuración local
    $tabla = "sucursal_local";
    $respuesta = ModeloSucursales::mdlConfigurarSucursalLocal($tabla, $datosSimulados);
    
    if ($respuesta && $respuesta['success']) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Configuración local guardada</strong><br>";
        echo "Mensaje: " . $respuesta['message'];
        echo "</div>";
        
        // Sincronizar con central
        $resultadoSincronizacion = ControladorSucursales::sincronizarConCentral($datosSimulados);
        if ($resultadoSincronizacion) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Sincronización con central exitosa</strong>";
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
            echo "<strong>⚠️ Sincronización con central falló</strong>";
            echo "</div>";
        }
        
        // Actualizar campo empresa
        $resultadoEmpresa = ControladorSucursales::actualizarEmpresaUsuarios($nuevoNombre);
        if ($resultadoEmpresa) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Campo empresa actualizado exitosamente</strong>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Error actualizando campo empresa</strong>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Error guardando configuración local</strong><br>";
        echo "Error: " . ($respuesta['error'] ?? 'Error desconocido');
        echo "</div>";
    }
    
    // 6. Verificar usuarios después del cambio
    echo "<h3>📋 6. Usuarios DESPUÉS del cambio:</h3>";
    $stmt = $conexion->prepare("SELECT id, nombre, usuario, empresa FROM usuarios WHERE activo = 1 ORDER BY id LIMIT 5");
    $stmt->execute();
    $usuariosDespues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Usuarios DESPUÉS (primeros 5):</strong><br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Empresa DESPUÉS</th></tr>";
    foreach ($usuariosDespues as $usuario) {
        $empresaDespues = $usuario['empresa'] ?: 'Sin asignar';
        $color = ($empresaDespues === $nuevoNombre) ? '#d4edda' : '#f8d7da';
        echo "<tr style='background: {$color};'>";
        echo "<td>{$usuario['id']}</td>";
        echo "<td>{$usuario['nombre']}</td>";
        echo "<td>{$usuario['usuario']}</td>";
        echo "<td><strong>{$empresaDespues}</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 7. Mostrar logs generados
    echo "<h3>📋 7. Logs generados:</h3>";
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        if (!empty($logs)) {
            echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
            echo "<strong>Logs del proceso:</strong><br>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px; max-height: 300px; overflow-y: auto;'>";
            echo htmlspecialchars($logs);
            echo "</pre>";
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
            echo "<strong>⚠️ No hay logs generados</strong>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Archivo de log no encontrado</strong>";
        echo "</div>";
    }
    
    // 8. Verificar si el cambio se aplicó correctamente
    echo "<h3>📋 8. Verificación final:</h3>";
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1 AND empresa = :nuevo_nombre");
    $stmt->bindParam(":nuevo_nombre", $nuevoNombre, PDO::PARAM_STR);
    $stmt->execute();
    $usuariosConNuevoNombre = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
    $stmt->execute();
    $totalUsuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($usuariosConNuevoNombre == $totalUsuarios) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ VERIFICACIÓN EXITOSA</strong><br>";
        echo "Todos los usuarios ({$totalUsuarios}) tienen empresa = '{$nuevoNombre}'";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ VERIFICACIÓN FALLIDA</strong><br>";
        echo "Usuarios con nuevo nombre: {$usuariosConNuevoNombre}<br>";
        echo "Total usuarios: {$totalUsuarios}<br>";
        echo "Diferencia: " . ($totalUsuarios - $usuariosConNuevoNombre);
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Prueba completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
