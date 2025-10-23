<?php
/**
 * SCRIPT PARA PROBAR SINCRONIZACIÓN DESPUÉS DE GUARDAR CONFIGURACIÓN LOCAL
 */

echo "<h2>🔄 PROBAR SINCRONIZACIÓN DESPUÉS DE GUARDAR CONFIGURACIÓN</h2>";

try {
    require_once "config.php";
    require_once "modelos/sucursales.modelo.php";
    
    // 1. Simular guardar configuración local
    echo "<h3>📝 1. Simulando guardar configuración local:</h3>";
    
    $datosConfiguracion = [
        'codigo_sucursal' => 'SUC001',
        'nombre' => 'Sucursal Prueba',
        'direccion' => 'Calle 123 #45-67',
        'telefono' => '(601) 123-4567',
        'email' => 'prueba@ejemplo.com',
        'url_base' => 'https://pruebas.acrilicosinfinito.com/',
        'url_api' => 'https://pruebas.acrilicosinfinito.com/api-transferencias/',
        'usuario_bd' => 'epicosie_ricaurte',
        'password_bd' => 'password123',
        'nombre_bd' => 'epicosie_pruebas',
        'host_bd' => 'localhost',
        'puerto_bd' => '3306',
        'activo' => 1
    ];
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Datos a guardar:</strong><br>";
    foreach ($datosConfiguracion as $campo => $valor) {
        if ($campo === 'password_bd') {
            echo "• {$campo}: [Oculto]<br>";
        } else {
            echo "• {$campo}: {$valor}<br>";
        }
    }
    echo "</div>";
    
    // 2. Guardar configuración local
    echo "<h3>💾 2. Guardando configuración local:</h3>";
    
    $resultadoLocal = ModeloSucursales::mdlConfigurarSucursalLocal("sucursal_local", $datosConfiguracion);
    
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
    
    // 3. Obtener configuración local actualizada
    echo "<h3>📋 3. Configuración local actualizada:</h3>";
    $configLocal = ModeloSucursales::mdlObtenerConfiguracionLocal();
    
    if ($configLocal) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Configuración local obtenida:</strong><br>";
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
        echo "<strong>❌ No se pudo obtener configuración local</strong>";
        echo "</div>";
        exit;
    }
    
    // 4. Buscar sucursal en central
    echo "<h3>📋 4. Buscando sucursal en central:</h3>";
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
    
    // 5. Aplicar lógica de comparación
    echo "<h3>🔍 5. Aplicando lógica de comparación:</h3>";
    
    $necesitaSincronizacion = false;
    $cambios = [];
    
    // Comparar campos básicos
    if ($configLocal['nombre'] !== $sucursalCentral['nombre']) {
        $necesitaSincronizacion = true;
        $cambios[] = "Nombre: '{$configLocal['nombre']}' → '{$sucursalCentral['nombre']}'";
    }
    
    if ($configLocal['direccion'] !== $sucursalCentral['direccion']) {
        $necesitaSincronizacion = true;
        $cambios[] = "Dirección: '{$configLocal['direccion']}' → '{$sucursalCentral['direccion']}'";
    }
    
    if ($configLocal['telefono'] !== $sucursalCentral['telefono']) {
        $necesitaSincronizacion = true;
        $cambios[] = "Teléfono: '{$configLocal['telefono']}' → '{$sucursalCentral['telefono']}'";
    }
    
    if ($configLocal['email'] !== $sucursalCentral['email']) {
        $necesitaSincronizacion = true;
        $cambios[] = "Email: '{$configLocal['email']}' → '{$sucursalCentral['email']}'";
    }
    
    if ($configLocal['activo'] != $sucursalCentral['activo']) {
        $necesitaSincronizacion = true;
        $cambios[] = "Estado: " . ($configLocal['activo'] ? 'Activo' : 'Inactivo') . " → " . ($sucursalCentral['activo'] ? 'Activo' : 'Inactivo');
    }
    
    // Comparar URLs
    $urlBaseLocal = $configLocal['url_base'] ?? '';
    $urlBaseCentral = $sucursalCentral['url_base'] ?? '';
    if ($urlBaseLocal !== $urlBaseCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "URL Base: '{$urlBaseLocal}' → '{$urlBaseCentral}'";
    }
    
    $urlApiLocal = $configLocal['url_api'] ?? '';
    $urlApiCentral = $sucursalCentral['url_api'] ?? '';
    if ($urlApiLocal !== $urlApiCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "URL API: '{$urlApiLocal}' → '{$urlApiCentral}'";
    }
    
    // Comparar campos de BD
    $usuarioBdLocal = $configLocal['usuario_bd'] ?? '';
    $usuarioBdCentral = $sucursalCentral['usuario_bd'] ?? '';
    if ($usuarioBdLocal !== $usuarioBdCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "Usuario BD: '{$usuarioBdLocal}' → '{$usuarioBdCentral}'";
    }
    
    $passwordBdLocal = $configLocal['password_bd'] ?? '';
    $passwordBdCentral = $sucursalCentral['password_bd'] ?? '';
    if ($passwordBdLocal !== $passwordBdCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "Password BD: [Actualizada]";
    }
    
    $nombreBdLocal = $configLocal['nombre_bd'] ?? '';
    $nombreBdCentral = $sucursalCentral['nombre_bd'] ?? '';
    if ($nombreBdLocal !== $nombreBdCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "Nombre BD: '{$nombreBdLocal}' → '{$nombreBdCentral}'";
    }
    
    $hostBdLocal = $configLocal['host_bd'] ?? '';
    $hostBdCentral = $sucursalCentral['host_bd'] ?? '';
    if ($hostBdLocal !== $hostBdCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "Host BD: '{$hostBdLocal}' → '{$hostBdCentral}'";
    }
    
    $puertoBdLocal = $configLocal['puerto_bd'] ?? '';
    $puertoBdCentral = $sucursalCentral['puerto_bd'] ?? '';
    if ($puertoBdLocal != $puertoBdCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "Puerto BD: '{$puertoBdLocal}' → '{$puertoBdCentral}'";
    }
    
    // Mostrar resultado
    if ($necesitaSincronizacion) {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "<strong>⚠️ Se detectaron diferencias que requieren sincronización:</strong><br>";
        foreach ($cambios as $cambio) {
            echo "• " . $cambio . "<br>";
        }
        echo "</div>";
        
        // 6. Ejecutar sincronización
        echo "<h3>🔄 6. Ejecutando sincronización:</h3>";
        
        $datosSincronizacion = [
            'id' => $sucursalCentral['id'],
            'nombre' => $configLocal['nombre'],
            'direccion' => $configLocal['direccion'],
            'telefono' => $configLocal['telefono'],
            'email' => $configLocal['email'],
            'url_base' => $configLocal['url_base'] ?? '',
            'url_api' => $configLocal['url_api'] ?? '',
            'activo' => $configLocal['activo']
        ];
        
        // Agregar campos de BD
        if (isset($configLocal['usuario_bd'])) {
            $datosSincronizacion['usuario_bd'] = $configLocal['usuario_bd'];
        }
        if (isset($configLocal['password_bd'])) {
            $datosSincronizacion['password_bd'] = $configLocal['password_bd'];
        }
        if (isset($configLocal['nombre_bd'])) {
            $datosSincronizacion['nombre_bd'] = $configLocal['nombre_bd'];
        }
        if (isset($configLocal['host_bd'])) {
            $datosSincronizacion['host_bd'] = $configLocal['host_bd'];
        }
        if (isset($configLocal['puerto_bd'])) {
            $datosSincronizacion['puerto_bd'] = $configLocal['puerto_bd'];
        }
        
        $resultadoSincronizacion = ModeloSucursales::mdlActualizarSucursalCentral($datosSincronizacion);
        
        if ($resultadoSincronizacion && $resultadoSincronizacion['success']) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Sincronización exitosa</strong><br>";
            echo "Mensaje: " . $resultadoSincronizacion['message'] . "<br>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Error en sincronización</strong><br>";
            echo "Error: " . ($resultadoSincronizacion['error'] ?? 'Error desconocido') . "<br>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ No hay diferencias que requieran sincronización</strong><br>";
        echo "Los datos ya están sincronizados.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Prueba completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
