<?php
/**
 * Script de prueba final para verificar el sistema completo
 */

echo "<h2>🧪 Prueba Final del Sistema Completo</h2>\n";

try {
    // 1. Verificar conexión a BD central
    echo "<h3>1. 🔍 Verificando conexión a BD Central:</h3>\n";
    
    require_once 'api-transferencias/conexion-central.php';
    $pdo = ConexionCentral::conectar();
    
    echo "<p style='color: green;'>✅ Conexión a BD Central exitosa</p>\n";
    
    // 2. Obtener sucursales activas
    echo "<h3>2. 🏢 Obteniendo sucursales activas:</h3>\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sucursales WHERE activo = 1");
    $resultado = $stmt->fetch();
    
    echo "<p style='color: green;'>✅ Sucursales activas: {$resultado['total']}</p>\n";
    
    // 3. Probar API del central
    echo "<h3>3. 🧪 Probando API del Central:</h3>\n";
    
    $url_central = 'https://pruebas2.acplasticos.com/api-transferencias/obtener-medios-pago-activos.php';
    echo "<p><strong>URL Central:</strong> $url_central</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_central);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response_central = curl_exec($ch);
    $http_code_central = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code_central === 200) {
        $data_central = json_decode($response_central, true);
        if ($data_central && isset($data_central['success']) && $data_central['success']) {
            echo "<p style='color: green;'>✅ API Central funcionando correctamente</p>\n";
            echo "<p><strong>Medios de pago en Central:</strong> {$data_central['total']}</p>\n";
        } else {
            echo "<p style='color: red;'>❌ API Central no funciona correctamente</p>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ Error HTTP Central: $http_code_central</p>\n";
    }
    
    // 4. Probar sucursal SUC001
    echo "<h3>4. 🏢 Probando Sucursal SUC001:</h3>\n";
    
    $stmt = $pdo->query("SELECT url_api FROM sucursales WHERE codigo_sucursal = 'SUC001' LIMIT 1");
    $sucursal = $stmt->fetch();
    
    if ($sucursal) {
        $url_sucursal = rtrim($sucursal['url_api'], '/') . '/obtener-medios-pago-activos.php';
        echo "<p><strong>URL SUC001:</strong> $url_sucursal</p>\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_sucursal);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response_sucursal = curl_exec($ch);
        $http_code_sucursal = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "<p><strong>HTTP Code SUC001:</strong> $http_code_sucursal</p>\n";
        
        if ($http_code_sucursal === 200) {
            $data_sucursal = json_decode($response_sucursal, true);
            if ($data_sucursal && isset($data_sucursal['success']) && $data_sucursal['success']) {
                echo "<p style='color: green;'>✅ SUC001 funcionando correctamente</p>\n";
                echo "<p><strong>Medios de pago en SUC001:</strong> {$data_sucursal['total']}</p>\n";
                
                if (!empty($data_sucursal['medios_pago'])) {
                    echo "<h4>💳 Medios de Pago en SUC001:</h4>\n";
                    echo "<ul>\n";
                    foreach ($data_sucursal['medios_pago'] as $medio) {
                        echo "<li><strong>{$medio['nombre']}</strong> (ID: {$medio['id']})</li>\n";
                    }
                    echo "</ul>\n";
                }
            } else {
                echo "<p style='color: red;'>❌ SUC001 no funciona correctamente</p>\n";
                echo "<p><strong>Respuesta:</strong> " . htmlspecialchars($response_sucursal) . "</p>\n";
            }
        } elseif ($http_code_sucursal === 500) {
            echo "<p style='color: red;'>❌ Error 500 en SUC001</p>\n";
            echo "<p><strong>Problema:</strong> Archivo obtener-medios-pago-activos.php necesita corrección</p>\n";
            echo "<p><strong>Solución:</strong> Usar generar-archivo-sucursal.php para obtener el archivo correcto</p>\n";
        } else {
            echo "<p style='color: orange;'>⚠️ HTTP Code inesperado: $http_code_sucursal</p>\n";
        }
    }
    
    // 5. Conclusión
    echo "<h3>5. 🎯 Conclusión:</h3>\n";
    
    if ($http_code_central === 200 && $http_code_sucursal === 200) {
        echo "<p style='color: green; font-weight: bold;'>✅ ¡Sistema completamente funcional!</p>\n";
        echo "<p>El instalador puede importar medios de pago correctamente de SUC001 al central.</p>\n";
    } elseif ($http_code_central === 200 && $http_code_sucursal === 500) {
        echo "<p style='color: orange; font-weight: bold;'>⚠️ Sistema parcialmente funcional</p>\n";
        echo "<p>El central funciona, pero SUC001 necesita el archivo corregido.</p>\n";
        echo "<p><strong>Próximo paso:</strong> Instalar el archivo correcto en SUC001</p>\n";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Sistema necesita más correcciones</p>\n";
    }
    
    echo "<h3>📋 Resumen del Estado:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>BD Central:</strong> ✅ Funcionando</li>\n";
    echo "<li><strong>API Central:</strong> " . ($http_code_central === 200 ? '✅ Funcionando' : '❌ Error') . "</li>\n";
    echo "<li><strong>SUC001:</strong> " . ($http_code_sucursal === 200 ? '✅ Funcionando' : '❌ Error 500') . "</li>\n";
    echo "<li><strong>Instalador:</strong> " . ($http_code_central === 200 && $http_code_sucursal === 200 ? '✅ Listo' : '⚠️ Pendiente corrección SUC001') . "</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
