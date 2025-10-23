<?php
/**
 * SCRIPT PARA VERIFICAR ACTUALIZACIÓN AUTOMÁTICA DEL CAMPO EMPRESA
 */

echo "<h2>🔍 VERIFICAR ACTUALIZACIÓN AUTOMÁTICA DEL CAMPO EMPRESA</h2>";

try {
    require_once "config.php";
    require_once "modelos/conexion.php";
    require_once "modelos/sucursales.modelo.php";
    require_once "controladores/sucursales.controlador.php";
    
    // 1. Verificar configuración local actual
    echo "<h3>📋 1. Configuración Local Actual:</h3>";
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
    
    // 2. Verificar columna empresa en tabla usuarios
    echo "<h3>📋 2. Verificando columna empresa:</h3>";
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios LIKE 'empresa'");
    $stmt->execute();
    $columnaEmpresa = $stmt->fetch();
    
    if ($columnaEmpresa) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Columna 'empresa' existe</strong><br>";
        echo "Tipo: " . $columnaEmpresa['Type'] . "<br>";
        echo "Null: " . $columnaEmpresa['Null'] . "<br>";
        echo "Default: " . ($columnaEmpresa['Default'] ?: 'NULL') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Columna 'empresa' no existe</strong><br>";
        echo "Necesitas ejecutar agregar-columna-empresa.php primero";
        echo "</div>";
        exit;
    }
    
    // 3. Verificar usuarios actuales
    echo "<h3>📋 3. Usuarios actuales:</h3>";
    $stmt = $conexion->prepare("SELECT id, nombre, usuario, empresa FROM usuarios WHERE activo = 1 ORDER BY id LIMIT 5");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Usuarios actuales (primeros 5):</strong><br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Empresa Actual</th></tr>";
    foreach ($usuarios as $usuario) {
        $empresaActual = $usuario['empresa'] ?: 'Sin asignar';
        echo "<tr>";
        echo "<td>{$usuario['id']}</td>";
        echo "<td>{$usuario['nombre']}</td>";
        echo "<td>{$usuario['usuario']}</td>";
        echo "<td>{$empresaActual}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 4. Probar función actualizarEmpresaUsuarios manualmente
    echo "<h3>🧪 4. Probando función actualizarEmpresaUsuarios:</h3>";
    
    $resultado = ControladorSucursales::actualizarEmpresaUsuarios($configLocal['nombre']);
    
    if ($resultado) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Función actualizarEmpresaUsuarios ejecutada exitosamente</strong><br>";
        echo "Nombre de sucursal: " . $configLocal['nombre'];
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Error ejecutando actualizarEmpresaUsuarios</strong><br>";
        echo "Revisar logs para más detalles";
        echo "</div>";
    }
    
    // 5. Verificar usuarios después de actualización
    echo "<h3>📋 5. Usuarios después de actualización:</h3>";
    $stmt = $conexion->prepare("SELECT id, nombre, usuario, empresa FROM usuarios WHERE activo = 1 ORDER BY id LIMIT 5");
    $stmt->execute();
    $usuariosActualizados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Usuarios después de actualización (primeros 5):</strong><br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Empresa Actualizada</th></tr>";
    foreach ($usuariosActualizados as $usuario) {
        $empresaActualizada = $usuario['empresa'] ?: 'Sin asignar';
        $color = ($empresaActualizada === $configLocal['nombre']) ? '#d4edda' : '#f8d7da';
        echo "<tr style='background: {$color};'>";
        echo "<td>{$usuario['id']}</td>";
        echo "<td>{$usuario['nombre']}</td>";
        echo "<td>{$usuario['usuario']}</td>";
        echo "<td><strong>{$empresaActualizada}</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 6. Verificar logs recientes
    echo "<h3>📋 6. Verificando logs recientes:</h3>";
    $logFile = "logs/sistema.log";
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $lineas = explode("\n", $logs);
        $lineasRecientes = array_slice($lineas, -10); // Últimas 10 líneas
        
        echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
        echo "<strong>Últimas 10 líneas del log:</strong><br>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;'>";
        foreach ($lineasRecientes as $linea) {
            if (!empty(trim($linea))) {
                echo htmlspecialchars($linea) . "\n";
            }
        }
        echo "</pre>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "<strong>⚠️ Archivo de log no encontrado</strong>";
        echo "</div>";
    }
    
    // 7. Simular cambio de nombre de sucursal
    echo "<h3>🧪 7. Simulando cambio de nombre de sucursal:</h3>";
    
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
    
    // Probar actualización manual
    $resultadoSimulacion = ControladorSucursales::actualizarEmpresaUsuarios($nuevoNombre);
    
    if ($resultadoSimulacion) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Simulación exitosa</strong><br>";
        echo "Campo empresa actualizado a: {$nuevoNombre}";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Error en simulación</strong>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Verificación completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
