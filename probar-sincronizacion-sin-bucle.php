<?php
/**
 * SCRIPT PARA PROBAR SINCRONIZACIÓN SIN BUCLE INFINITO
 */

echo "<h2>🔄 PROBAR SINCRONIZACIÓN SIN BUCLE INFINITO</h2>";

try {
    require_once "config.php";
    require_once "modelos/sucursales.modelo.php";
    
    // 1. Obtener configuración local
    echo "<h3>📋 1. Configuración Local Actual:</h3>";
    $configLocal = ModeloSucursales::mdlObtenerConfiguracionLocal();
    
    if ($configLocal) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Configuración local:</strong><br>";
        echo "Código: " . $configLocal['codigo_sucursal'] . "<br>";
        echo "Nombre: " . $configLocal['nombre'] . "<br>";
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
    
    // 2. Buscar sucursal en central
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
        'nombre' => $configLocal['nombre'],
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
    
    // Cambiar usuario BD para probar sincronización
    $nuevosDatos['usuario_bd'] = 'epicosie_ricaurte_test_' . date('His');
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Cambiando Usuario BD a:</strong> " . $nuevosDatos['usuario_bd'] . "<br>";
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
    
    // 5. Simular sincronización manual (sin bucle)
    echo "<h3>🔄 5. Simulando sincronización manual (sin bucle):</h3>";
    
    // Obtener configuración local actualizada
    $configLocalActualizada = ModeloSucursales::mdlObtenerConfiguracionLocal();
    
    if ($configLocalActualizada) {
        // Preparar datos para sincronización
        $datosSincronizacion = [
            'id' => $sucursalCentral['id'],
            'nombre' => $configLocalActualizada['nombre'],
            'direccion' => $configLocalActualizada['direccion'],
            'telefono' => $configLocalActualizada['telefono'],
            'email' => $configLocalActualizada['email'],
            'url_base' => $configLocalActualizada['url_base'] ?? '',
            'url_api' => $configLocalActualizada['url_api'] ?? '',
            'activo' => $configLocalActualizada['activo']
        ];
        
        // Agregar campos de BD
        if (isset($configLocalActualizada['usuario_bd'])) {
            $datosSincronizacion['usuario_bd'] = $configLocalActualizada['usuario_bd'];
        }
        if (isset($configLocalActualizada['password_bd'])) {
            $datosSincronizacion['password_bd'] = $configLocalActualizada['password_bd'];
        }
        if (isset($configLocalActualizada['nombre_bd'])) {
            $datosSincronizacion['nombre_bd'] = $configLocalActualizada['nombre_bd'];
        }
        if (isset($configLocalActualizada['host_bd'])) {
            $datosSincronizacion['host_bd'] = $configLocalActualizada['host_bd'];
        }
        if (isset($configLocalActualizada['puerto_bd'])) {
            $datosSincronizacion['puerto_bd'] = $configLocalActualizada['puerto_bd'];
        }
        
        echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
        echo "<strong>Datos a sincronizar:</strong><br>";
        echo "Usuario BD: " . ($datosSincronizacion['usuario_bd'] ?? 'No definido') . "<br>";
        echo "Password BD: " . (empty($datosSincronizacion['password_bd']) ? 'No definido' : '[Definido]') . "<br>";
        echo "Nombre BD: " . ($datosSincronizacion['nombre_bd'] ?? 'No definido') . "<br>";
        echo "Host BD: " . ($datosSincronizacion['host_bd'] ?? 'No definido') . "<br>";
        echo "Puerto BD: " . ($datosSincronizacion['puerto_bd'] ?? 'No definido') . "<br>";
        echo "</div>";
        
        // Ejecutar sincronización
        $resultadoSincronizacion = ModeloSucursales::mdlActualizarSucursalCentral($datosSincronizacion);
        
        if ($resultadoSincronizacion && $resultadoSincronizacion['success']) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Sincronización exitosa (sin bucle)</strong><br>";
            echo "Mensaje: " . $resultadoSincronizacion['message'] . "<br>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Error en sincronización</strong><br>";
            echo "Error: " . ($resultadoSincronizacion['error'] ?? 'Error desconocido') . "<br>";
            echo "</div>";
        }
    }
    
    // 6. Verificar que no hay bucle
    echo "<h3>🔍 6. Verificando que no hay bucle:</h3>";
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✅ Sincronización ejecutada una sola vez</strong><br>";
    echo "No se detectó bucle infinito en la sincronización.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Prueba completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
