<?php
/**
 * TEST DE ACCIONES DE DESPACHOS
 * Diagnostica qué acciones están fallando y por qué
 */

echo "<h2>🔍 Test de Acciones de Despachos</h2>";

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Usuario Debug";

echo "<p>✅ Sesión simulada iniciada</p>";

echo "<hr>";
echo "<h3>🔍 Test 1: Ver Despacho (btnVerDespacho)</h3>";

try {
    $_POST["idDespacho"] = "1";
    
    ob_start();
    require_once "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $response = json_decode($output, true);
    if ($response) {
        if (isset($response['error'])) {
            echo "<p>❌ Error: " . $response['error'] . "</p>";
        } else {
            echo "<p>✅ Acción exitosa</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Excepción: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 2: Aceptar Despacho</h3>";

try {
    unset($_POST["idDespacho"]);
    $_POST["aceptarDespacho"] = "1";
    $_POST["idDespachoAceptar"] = "1";
    
    ob_start();
    require_once "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $response = json_decode($output, true);
    if ($response) {
        if (isset($response['error'])) {
            echo "<p>❌ Error: " . $response['error'] . "</p>";
        } else {
            echo "<p>✅ Acción exitosa</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Excepción: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 3: Cancelar Despacho</h3>";

try {
    unset($_POST["aceptarDespacho"]);
    unset($_POST["idDespachoAceptar"]);
    $_POST["cancelarDespacho"] = "1";
    $_POST["idDespachoCancelar"] = "1";
    $_POST["motivoCancelacion"] = "Prueba de cancelación";
    
    ob_start();
    require_once "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $response = json_decode($output, true);
    if ($response) {
        if (isset($response['error'])) {
            echo "<p>❌ Error: " . $response['error'] . "</p>";
        } else {
            echo "<p>✅ Acción exitosa</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Excepción: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 4: Eliminar Despacho</h3>";

try {
    unset($_POST["cancelarDespacho"]);
    unset($_POST["idDespachoCancelar"]);
    unset($_POST["motivoCancelacion"]);
    $_POST["eliminarDespacho"] = "1";
    $_POST["idDespachoEliminar"] = "1";
    
    ob_start();
    require_once "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $response = json_decode($output, true);
    if ($response) {
        if (isset($response['error'])) {
            echo "<p>❌ Error: " . $response['error'] . "</p>";
        } else {
            echo "<p>✅ Acción exitosa</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Excepción: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 5: Verificar Logs de Error</h3>";

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<p>📋 Configuración de errores:</p>";
echo "<ul>";
echo "<li>error_reporting: " . error_reporting() . "</li>";
echo "<li>display_errors: " . ini_get('display_errors') . "</li>";
echo "<li>log_errors: " . ini_get('log_errors') . "</li>";
echo "<li>error_log: " . ini_get('error_log') . "</li>";
echo "</ul>";

// Verificar si hay archivos de log
$logFiles = [
    'error_log',
    'php_errors.log',
    'errors.log',
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log'
];

echo "<p>📁 Archivos de log encontrados:</p>";
foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "<p>✅ $logFile existe</p>";
        $content = file_get_contents($logFile);
        $lines = explode("\n", $content);
        $recentLines = array_slice($lines, -10); // Últimas 10 líneas
        echo "<pre>" . htmlspecialchars(implode("\n", $recentLines)) . "</pre>";
    } else {
        echo "<p>❌ $logFile no existe</p>";
    }
}

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
