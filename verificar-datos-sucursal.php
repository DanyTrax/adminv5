<?php
/**
 * SCRIPT PARA VERIFICAR DATOS DE SUCURSAL EN CENTRAL
 */

echo "<h2>🔍 VERIFICAR DATOS DE SUCURSAL EN CENTRAL</h2>";

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
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ No hay configuración local</strong>";
        echo "</div>";
        exit;
    }
    
    // 2. Buscar sucursal en central por código
    echo "<h3>📋 2. Buscando sucursal en central por código:</h3>";
    $sucursalCentral = ModeloSucursales::mdlMostrarSucursal("codigo_sucursal", $configLocal['codigo_sucursal']);
    
    if ($sucursalCentral) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Sucursal central encontrada:</strong><br>";
        echo "ID: " . $sucursalCentral['id'] . "<br>";
        echo "Código: " . $sucursalCentral['codigo_sucursal'] . "<br>";
        echo "Nombre: " . $sucursalCentral['nombre'] . "<br>";
        echo "URL Base: " . ($sucursalCentral['url_base'] ?? 'No definida') . "<br>";
        echo "URL API: " . ($sucursalCentral['url_api'] ?? 'No definida') . "<br>";
        echo "Usuario BD: " . ($sucursalCentral['usuario_bd'] ?? 'No definido') . "<br>";
        echo "Password BD: " . ($sucursalCentral['password_bd'] ?? 'No definido') . "<br>";
        echo "Nombre BD: " . ($sucursalCentral['nombre_bd'] ?? 'No definido') . "<br>";
        echo "Host BD: " . ($sucursalCentral['host_bd'] ?? 'No definido') . "<br>";
        echo "Puerto BD: " . ($sucursalCentral['puerto_bd'] ?? 'No definido') . "<br>";
        echo "Estado: " . ($sucursalCentral['activo'] ? 'Activo' : 'Inactivo') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Sucursal no encontrada en central</strong>";
        echo "</div>";
        exit;
    }
    
    // 3. Comparar datos
    echo "<h3>🔍 3. Comparación de datos:</h3>";
    
    $diferencias = [];
    
    if (($configLocal['nombre'] ?? '') !== ($sucursalCentral['nombre'] ?? '')) {
        $diferencias[] = "Nombre: Local='{$configLocal['nombre']}' vs Central='{$sucursalCentral['nombre']}'";
    }
    
    if (($configLocal['url_base'] ?? '') !== ($sucursalCentral['url_base'] ?? '')) {
        $diferencias[] = "URL Base: Local='{$configLocal['url_base']}' vs Central='{$sucursalCentral['url_base']}'";
    }
    
    if (($configLocal['url_api'] ?? '') !== ($sucursalCentral['url_api'] ?? '')) {
        $diferencias[] = "URL API: Local='{$configLocal['url_api']}' vs Central='{$sucursalCentral['url_api']}'";
    }
    
    if (empty($diferencias)) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Los datos están sincronizados</strong><br>";
        echo "No hay diferencias entre local y central.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Hay diferencias:</strong><br>";
        foreach ($diferencias as $diferencia) {
            echo "• " . $diferencia . "<br>";
        }
        echo "</div>";
    }
    
    // 4. Probar AJAX de edición
    echo "<h3>🧪 4. Probando AJAX de edición:</h3>";
    
    // Simular la llamada AJAX
    $_POST['idSucursal'] = $sucursalCentral['id'];
    $_POST['accion'] = ''; // Sin acción para simular edición
    
    ob_start();
    include 'ajax/sucursales.ajax.php';
    $respuestaAjax = ob_get_clean();
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Respuesta AJAX:</strong><br>";
    echo "<pre>" . htmlspecialchars($respuestaAjax) . "</pre>";
    echo "</div>";
    
    // Decodificar JSON si es válido
    $datosAjax = json_decode($respuestaAjax, true);
    if ($datosAjax && isset($datosAjax['data'])) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
        echo "<strong>✅ Datos del AJAX decodificados:</strong><br>";
        echo "URL Base: " . ($datosAjax['data']['url_base'] ?? 'No definida') . "<br>";
        echo "URL API: " . ($datosAjax['data']['url_api'] ?? 'No definida') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
        echo "<strong>❌ Error decodificando respuesta AJAX</strong><br>";
        echo "La respuesta no es JSON válido.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Verificación completada - " . date('Y-m-d H:i:s') . "</em></p>";
?>
