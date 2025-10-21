<?php
/**
 * DIAGNÓSTICO DE CONEXIÓN A SUCURSAL
 * Script para diagnosticar problemas de conexión con sucursales
 */

require_once "config.php";
require_once "modelos/sucursales.modelo.php";

echo "<h2>🔍 DIAGNÓSTICO DE CONEXIÓN A SUCURSAL</h2>";

// Obtener datos de sucursales
try {
    $stmt = Conexion::conectar()->prepare("
        SELECT id, nombre, url_api, activo 
        FROM sucursales 
        WHERE activo = 1 
        ORDER BY id
    ");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Sucursales Activas:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>URL API</th><th>Estado</th><th>Prueba</th></tr>";
    
    foreach ($sucursales as $sucursal) {
        echo "<tr>";
        echo "<td>" . $sucursal['id'] . "</td>";
        echo "<td>" . $sucursal['nombre'] . "</td>";
        echo "<td>" . $sucursal['url_api'] . "</td>";
        echo "<td>" . ($sucursal['activo'] ? 'Activa' : 'Inactiva') . "</td>";
        
        // Probar conexión
        $urlTest = rtrim($sucursal['url_api'], '/') . '/test_conexion.php';
        echo "<td>";
        
        // Probar conexión
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'header' => 'User-Agent: AdminV5-Diagnostic/1.0'
            ]
        ]);
        
        $inicioTiempo = microtime(true);
        $respuesta = @file_get_contents($urlTest, false, $context);
        $tiempoTranscurrido = round((microtime(true) - $inicioTiempo) * 1000);
        
        if ($respuesta !== false) {
            echo "<span style='color: green;'>✅ OK (" . $tiempoTranscurrido . "ms)</span>";
            echo "<br><small>Respuesta: " . substr($respuesta, 0, 100) . "...</small>";
        } else {
            echo "<span style='color: red;'>❌ FALLO</span>";
            echo "<br><small>URL probada: " . $urlTest . "</small>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error al obtener sucursales: " . $e->getMessage() . "</p>";
}

echo "<h3>🔧 DIAGNÓSTICO ADICIONAL:</h3>";

// Verificar configuración de PHP
echo "<h4>Configuración PHP:</h4>";
echo "<ul>";
echo "<li>allow_url_fopen: " . (ini_get('allow_url_fopen') ? '✅ Habilitado' : '❌ Deshabilitado') . "</li>";
echo "<li>user_agent: " . ini_get('user_agent') . "</li>";
echo "<li>default_socket_timeout: " . ini_get('default_socket_timeout') . " segundos</li>";
echo "</ul>";

// Verificar conectividad de red
echo "<h4>Pruebas de Conectividad:</h4>";
$hosts = ['google.com', 'github.com'];
foreach ($hosts as $host) {
    $resultado = @fsockopen($host, 80, $errno, $errstr, 5);
    if ($resultado) {
        echo "<p>✅ Conectividad a $host: OK</p>";
        fclose($resultado);
    } else {
        echo "<p>❌ Conectividad a $host: FALLO ($errstr)</p>";
    }
}

echo "<h3>💡 RECOMENDACIONES:</h3>";
echo "<ul>";
echo "<li>Verificar que la URL de la API sea correcta</li>";
echo "<li>Confirmar que el archivo test_conexion.php existe en la sucursal remota</li>";
echo "<li>Verificar que la sucursal remota esté accesible desde internet</li>";
echo "<li>Revisar logs de error del servidor remoto</li>";
echo "</ul>";

?>
