<?php
/**
 * SCRIPT PARA PROBAR SINCRONIZACIÓN DE URL_BASE Y URL_API
 */

echo "<h2>🔄 PROBAR SINCRONIZACIÓN DE URL_BASE Y URL_API</h2>";

try {
    require_once "config.php";
    require_once "modelos/sucursales.modelo.php";
    
    // 1. Obtener configuración local
    echo "<h3>📋 1. Configuración Local Actual:</h3>";
    $configLocal = ModeloSucursales::mdlObtenerConfiguracionLocal();
    
    if ($configLocal) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Configuración local encontrada:</strong><br>";
        echo "Código: " . $configLocal['codigo_sucursal'] . "<br>";
        echo "Nombre: " . $configLocal['nombre'] . "<br>";
        echo "URL Base: " . ($configLocal['url_base'] ?? 'No definida') . "<br>";
        echo "URL API: " . ($configLocal['url_api'] ?? 'No definida') . "<br>";
        echo "Estado: " . ($configLocal['activo'] ? 'Activo' : 'Inactivo') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay configuración local</strong>";
        echo "</div>";
        exit;
    }
    
    // 2. Obtener sucursal central
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
        echo "Estado: " . ($sucursalCentral['activo'] ? 'Activo' : 'Inactivo') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Sucursal central no encontrada</strong>";
        echo "</div>";
        exit;
    }
    
    // 3. Comparar URLs
    echo "<h3>🔍 3. Comparación de URLs:</h3>";
    
    $urlBaseLocal = $configLocal['url_base'] ?? '';
    $urlApiLocal = $configLocal['url_api'] ?? '';
    $urlBaseCentral = $sucursalCentral['url_base'] ?? '';
    $urlApiCentral = $sucursalCentral['url_api'] ?? '';
    
    $urlsSincronizadas = true;
    
    if ($urlBaseLocal !== $urlBaseCentral) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ URL Base NO sincronizada:</strong><br>";
        echo "Local: " . $urlBaseLocal . "<br>";
        echo "Central: " . $urlBaseCentral . "<br>";
        echo "</div>";
        $urlsSincronizadas = false;
    } else {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ URL Base sincronizada:</strong> " . $urlBaseLocal . "<br>";
        echo "</div>";
    }
    
    if ($urlApiLocal !== $urlApiCentral) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ URL API NO sincronizada:</strong><br>";
        echo "Local: " . $urlApiLocal . "<br>";
        echo "Central: " . $urlApiCentral . "<br>";
        echo "</div>";
        $urlsSincronizadas = false;
    } else {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ URL API sincronizada:</strong> " . $urlApiLocal . "<br>";
        echo "</div>";
    }
    
    // 4. Forzar sincronización si es necesario
    if (!$urlsSincronizadas) {
        echo "<h3>🔄 4. Forzando sincronización Local → Central:</h3>";
        
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
        
        // Agregar campos de BD si existen
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
        
        $resultado = ModeloSucursales::mdlActualizarSucursalCentral($datosSincronizacion);
        
        if ($resultado && $resultado['success']) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Sincronización Local → Central exitosa</strong><br>";
            echo "Mensaje: " . $resultado['message'] . "<br>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Error en sincronización Local → Central</strong><br>";
            echo "Error: " . ($resultado['error'] ?? 'Error desconocido') . "<br>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Las URLs ya están sincronizadas</strong><br>";
        echo "No es necesario forzar la sincronización.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Prueba completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
