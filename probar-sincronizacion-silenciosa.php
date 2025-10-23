<?php
/**
 * SCRIPT PARA PROBAR SINCRONIZACIÓN SIN NOTIFICACIONES
 */

echo "<h2>🔇 PROBAR SINCRONIZACIÓN SILENCIOSA</h2>";

try {
    require_once "config.php";
    require_once "modelos/sucursales.modelo.php";
    
    // 1. Obtener configuración local
    echo "<h3>📋 1. Configuración Local:</h3>";
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
    echo "<h3>📋 2. Sucursal Central:</h3>";
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
    
    // 3. Aplicar nueva lógica de comparación
    echo "<h3>🔍 3. Aplicando nueva lógica de comparación:</h3>";
    
    $necesitaSincronizacion = false;
    $cambios = [];
    
    // Comparar URLs (solo si ambos tienen valores)
    $urlBaseLocal = $configLocal['url_base'] ?? '';
    $urlBaseCentral = $sucursalCentral['url_base'] ?? '';
    if (!empty($urlBaseLocal) && !empty($urlBaseCentral) && $urlBaseLocal !== $urlBaseCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "URL Base: '{$urlBaseLocal}' → '{$urlBaseCentral}'";
    }
    
    $urlApiLocal = $configLocal['url_api'] ?? '';
    $urlApiCentral = $sucursalCentral['url_api'] ?? '';
    if (!empty($urlApiLocal) && !empty($urlApiCentral) && $urlApiLocal !== $urlApiCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "URL API: '{$urlApiLocal}' → '{$urlApiCentral}'";
    }
    
    // Comparar campos de BD (solo si ambos tienen valores)
    $usuarioBdLocal = $configLocal['usuario_bd'] ?? '';
    $usuarioBdCentral = $sucursalCentral['usuario_bd'] ?? '';
    if (!empty($usuarioBdLocal) && !empty($usuarioBdCentral) && $usuarioBdLocal !== $usuarioBdCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "Usuario BD: '{$usuarioBdLocal}' → '{$usuarioBdCentral}'";
    }
    
    $passwordBdLocal = $configLocal['password_bd'] ?? '';
    $passwordBdCentral = $sucursalCentral['password_bd'] ?? '';
    if (!empty($passwordBdLocal) && !empty($passwordBdCentral) && $passwordBdLocal !== $passwordBdCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "Password BD: [Actualizada]";
    }
    
    $nombreBdLocal = $configLocal['nombre_bd'] ?? '';
    $nombreBdCentral = $sucursalCentral['nombre_bd'] ?? '';
    if (!empty($nombreBdLocal) && !empty($nombreBdCentral) && $nombreBdLocal !== $nombreBdCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "Nombre BD: '{$nombreBdLocal}' → '{$nombreBdCentral}'";
    }
    
    $hostBdLocal = $configLocal['host_bd'] ?? '';
    $hostBdCentral = $sucursalCentral['host_bd'] ?? '';
    if (!empty($hostBdLocal) && !empty($hostBdCentral) && $hostBdLocal !== $hostBdCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "Host BD: '{$hostBdLocal}' → '{$hostBdCentral}'";
    }
    
    $puertoBdLocal = $configLocal['puerto_bd'] ?? '';
    $puertoBdCentral = $sucursalCentral['puerto_bd'] ?? '';
    if (!empty($puertoBdLocal) && !empty($puertoBdCentral) && $puertoBdLocal != $puertoBdCentral) {
        $necesitaSincronizacion = true;
        $cambios[] = "Puerto BD: '{$puertoBdLocal}' → '{$puertoBdCentral}'";
    }
    
    // Mostrar resultado
    if ($necesitaSincronizacion) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>⚠️ Se detectaron diferencias:</strong><br>";
        foreach ($cambios as $cambio) {
            echo "• " . $cambio . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ No hay diferencias que requieran sincronización</strong><br>";
        echo "Los datos están sincronizados correctamente.";
        echo "</div>";
    }
    
    // 4. Mostrar comparación detallada
    echo "<h3>📊 4. Comparación detallada:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Local</th><th>Central</th><th>¿Diferente?</th></tr>";
    
    $campos = [
        'URL Base' => ['url_base', 'url_base'],
        'URL API' => ['url_api', 'url_api'],
        'Usuario BD' => ['usuario_bd', 'usuario_bd'],
        'Password BD' => ['password_bd', 'password_bd'],
        'Nombre BD' => ['nombre_bd', 'nombre_bd'],
        'Host BD' => ['host_bd', 'host_bd'],
        'Puerto BD' => ['puerto_bd', 'puerto_bd']
    ];
    
    foreach ($campos as $nombre => $claves) {
        $valorLocal = $configLocal[$claves[0]] ?? '';
        $valorCentral = $sucursalCentral[$claves[1]] ?? '';
        $diferente = ($valorLocal !== $valorCentral) ? 'Sí' : 'No';
        $color = ($valorLocal !== $valorCentral) ? '#f8d7da' : '#d4edda';
        
        echo "<tr style='background: {$color};'>";
        echo "<td><strong>{$nombre}</strong></td>";
        echo "<td>" . ($valorLocal ?: 'Vacío') . "</td>";
        echo "<td>" . ($valorCentral ?: 'Vacío') . "</td>";
        echo "<td><strong>{$diferente}</strong></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Prueba completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
