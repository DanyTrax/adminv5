<?php
/**
 * TEST ESPECÍFICO PARA ERROR 500
 * Simula exactamente la petición AJAX real para identificar el error
 */

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h2>🔍 Test Específico para Error 500</h2>";

// Simular sesión real
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Usuario Debug";

echo "<p>✅ Sesión simulada iniciada</p>";

echo "<hr>";
echo "<h3>🔍 Test 1: Simular petición AJAX real</h3>";

try {
    // Simular exactamente la petición que hace el botón "Ver Despacho"
    $_POST["idDespacho"] = "1";
    
    echo "<p>📝 Simulando petición POST con idDespacho = 1</p>";
    
    // Capturar cualquier salida
    ob_start();
    
    // Incluir el archivo AJAX
    include "ajax/despachos.ajax.php";
    
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta del servidor:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Intentar decodificar JSON
    $response = json_decode($output, true);
    if ($response) {
        echo "<p>✅ Respuesta JSON válida</p>";
        if (isset($response['error'])) {
            echo "<p>❌ Error en respuesta: " . $response['error'] . "</p>";
        }
    } else {
        echo "<p>⚠️ La respuesta no es JSON válido</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Excepción capturada: " . $e->getMessage() . "</p>";
    echo "<p>📍 Archivo: " . $e->getFile() . "</p>";
    echo "<p>📍 Línea: " . $e->getLine() . "</p>";
    echo "<p>📍 Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p>❌ Error fatal capturado: " . $e->getMessage() . "</p>";
    echo "<p>📍 Archivo: " . $e->getFile() . "</p>";
    echo "<p>📍 Línea: " . $e->getLine() . "</p>";
    echo "<p>📍 Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>🔍 Test 2: Verificar logs de error</h3>";

// Verificar logs de error de PHP
$logFiles = [
    'error_log',
    'php_errors.log',
    'errors.log',
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log',
    '/home/epicosie/error_log',
    '/home/epicosie/php_errors.log'
];

echo "<p>📁 Verificando archivos de log:</p>";
foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "<p>✅ $logFile existe</p>";
        $content = file_get_contents($logFile);
        $lines = explode("\n", $content);
        $recentLines = array_slice($lines, -20); // Últimas 20 líneas
        echo "<pre>" . htmlspecialchars(implode("\n", $recentLines)) . "</pre>";
    } else {
        echo "<p>❌ $logFile no existe</p>";
    }
}

echo "<hr>";
echo "<h3>🔍 Test 3: Verificar permisos de archivos</h3>";

$files = [
    'ajax/despachos.ajax.php',
    'controladores/despachos.controlador.php',
    'modelos/despachos.modelo.php',
    'api-transferencias/conexion-central.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $readable = is_readable($file) ? "SÍ" : "NO";
        echo "<p>✅ $file - Permisos: " . decoct($perms & 0777) . " - Legible: $readable</p>";
    } else {
        echo "<p>❌ $file no existe</p>";
    }
}

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Directorio actual:</strong> " . __DIR__ . "</p>";
?>
