<?php
/**
 * SCRIPT PARA PROBAR SINCRONIZACIÓN LOCAL → CENTRAL
 */

echo "<h2>🔄 PROBAR SINCRONIZACIÓN LOCAL → CENTRAL</h2>";

try {
    require_once "config.php";
    require_once "modelos/sucursales.modelo.php";
    require_once "controladores/sucursales.controlador.php";
    
    // 1. Obtener configuración local actual
    echo "<h3>📋 1. Configuración Local Actual:</h3>";
    $configLocal = ModeloSucursales::mdlObtenerConfiguracionLocal();
    
    if ($configLocal) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Configuración local:</strong><br>";
        echo "Código: " . $configLocal['codigo_sucursal'] . "<br>";
        echo "Nombre: " . $configLocal['nombre'] . "<br>";
        echo "Dirección: " . $configLocal['direccion'] . "<br>";
        echo "Teléfono: " . $configLocal['telefono'] . "<br>";
        echo "Email: " . $configLocal['email'] . "<br>";
        echo "URL Base: " . ($configLocal['url_base'] ?? 'No definida') . "<br>";
        echo "URL API: " . ($configLocal['url_api'] ?? 'No definida') . "<br>";
        echo "Usuario BD: " . ($configLocal['usuario_bd'] ?? 'No definido') . "<br>";
        echo "Password BD: " . (empty($configLocal['password_bd']) ? 'No definido' : '[Definido]') . "<br>";
        echo "Nombre BD: " . ($configLocal['nombre_bd'] ?? 'No definido') . "<br>";
        echo "Host BD: " . ($configLocal['host_bd'] ?? 'No definido') . "<br>";
        echo "Puerto BD: " . ($configLocal['puerto_bd'] ?? 'No definido') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay configuración local</strong>";
        echo "</div>";
        exit;
    }
    
    // 2. Obtener sucursal central actual
    echo "<h3>📋 2. Sucursal Central Actual:</h3>";
    $sucursalesCentral = ModeloSucursales::mdlObtenerSucursales();
    $sucursalCentral = null;
    
    if ($sucursalesCentral && $sucursalesCentral['success']) {
        foreach ($sucursalesCentral['data'] as $sucursal) {
            if ($sucursal['codigo_sucursal'] === $configLocal['codigo_sucursal']) {
                $sucursalCentral = $sucursal;
                break;
            }
        }
    }
    
    if ($sucursalCentral) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Sucursal central encontrada:</strong><br>";
        echo "ID: " . $sucursalCentral['id'] . "<br>";
        echo "Código: " . $sucursalCentral['codigo_sucursal'] . "<br>";
        echo "Nombre: " . $sucursalCentral['nombre'] . "<br>";
        echo "Dirección: " . $sucursalCentral['direccion'] . "<br>";
        echo "Teléfono: " . $sucursalCentral['telefono'] . "<br>";
        echo "Email: " . $sucursalCentral['email'] . "<br>";
        echo "URL Base: " . ($sucursalCentral['url_base'] ?? 'No definida') . "<br>";
        echo "URL API: " . ($sucursalCentral['url_api'] ?? 'No definida') . "<br>";
        echo "Usuario BD: " . ($sucursalCentral['usuario_bd'] ?? 'No definido') . "<br>";
        echo "Password BD: " . (empty($sucursalCentral['password_bd']) ? 'No definido' : '[Definido]') . "<br>";
        echo "Nombre BD: " . ($sucursalCentral['nombre_bd'] ?? 'No definido') . "<br>";
        echo "Host BD: " . ($sucursalCentral['host_bd'] ?? 'No definido') . "<br>";
        echo "Puerto BD: " . ($sucursalCentral['puerto_bd'] ?? 'No definido') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Sucursal central no encontrada</strong>";
        echo "</div>";
        exit;
    }
    
    // 3. Simular cambio en configuración local
    echo "<h3>📝 3. Simulando cambio en configuración local:</h3>";
    
    $nuevosDatos = [
        'codigo_sucursal' => $configLocal['codigo_sucursal'],
        'nombre' => $configLocal['nombre'] . ' - Actualizado ' . date('H:i:s'),
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
    echo "<strong>Cambiando nombre a:</strong> " . $nuevosDatos['nombre'] . "<br>";
    echo "</div>";
    
    // 4. Guardar configuración local
    echo "<h3>💾 4. Guardando configuración local:</h3>";
    
    $resultadoLocal = ModeloSucursales::mdlConfigurarSucursalLocal("sucursal_local", $nuevosDatos);
    
    if ($resultadoLocal && $resultadoLocal['success']) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Configuración local guardada exitosamente</strong><br>";
        echo "Mensaje: " . $resultadoLocal['message'] . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Error guardando configuración local</strong><br>";
        echo "Error: " . ($resultadoLocal['error'] ?? 'Error desconocido') . "<br>";
        echo "</div>";
        exit;
    }
    
    // 5. Ejecutar sincronización Local → Central
    echo "<h3>🔄 5. Ejecutando sincronización Local → Central:</h3>";
    
    $resultadoSincronizacion = ControladorSucursales::sincronizarConCentral($nuevosDatos);
    
    if ($resultadoSincronizacion) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Sincronización Local → Central exitosa</strong><br>";
        echo "Los datos se han sincronizado correctamente al sistema central.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Error en sincronización Local → Central</strong><br>";
        echo "Revisar logs para más detalles.";
        echo "</div>";
    }
    
    // 6. Verificar cambios en central
    echo "<h3>🔍 6. Verificando cambios en central:</h3>";
    
    $sucursalesCentralActualizada = ModeloSucursales::mdlObtenerSucursales();
    $sucursalCentralActualizada = null;
    
    if ($sucursalesCentralActualizada && $sucursalesCentralActualizada['success']) {
        foreach ($sucursalesCentralActualizada['data'] as $sucursal) {
            if ($sucursal['codigo_sucursal'] === $configLocal['codigo_sucursal']) {
                $sucursalCentralActualizada = $sucursal;
                break;
            }
        }
    }
    
    if ($sucursalCentralActualizada) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Sucursal central actualizada:</strong><br>";
        echo "Nombre: " . $sucursalCentralActualizada['nombre'] . "<br>";
        echo "Dirección: " . $sucursalCentralActualizada['direccion'] . "<br>";
        echo "Teléfono: " . $sucursalCentralActualizada['telefono'] . "<br>";
        echo "Email: " . $sucursalCentralActualizada['email'] . "<br>";
        echo "URL Base: " . ($sucursalCentralActualizada['url_base'] ?? 'No definida') . "<br>";
        echo "URL API: " . ($sucursalCentralActualizada['url_api'] ?? 'No definida') . "<br>";
        echo "Usuario BD: " . ($sucursalCentralActualizada['usuario_bd'] ?? 'No definido') . "<br>";
        echo "Password BD: " . (empty($sucursalCentralActualizada['password_bd']) ? 'No definido' : '[Definido]') . "<br>";
        echo "Nombre BD: " . ($sucursalCentralActualizada['nombre_bd'] ?? 'No definido') . "<br>";
        echo "Host BD: " . ($sucursalCentralActualizada['host_bd'] ?? 'No definido') . "<br>";
        echo "Puerto BD: " . ($sucursalCentralActualizada['puerto_bd'] ?? 'No definido') . "<br>";
        echo "</div>";
        
        // Verificar si el nombre cambió
        if ($sucursalCentralActualizada['nombre'] === $nuevosDatos['nombre']) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ El nombre se sincronizó correctamente</strong><br>";
            echo "Local: " . $nuevosDatos['nombre'] . "<br>";
            echo "Central: " . $sucursalCentralActualizada['nombre'] . "<br>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ El nombre NO se sincronizó</strong><br>";
            echo "Local: " . $nuevosDatos['nombre'] . "<br>";
            echo "Central: " . $sucursalCentralActualizada['nombre'] . "<br>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No se pudo verificar la sucursal central actualizada</strong>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Prueba completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
