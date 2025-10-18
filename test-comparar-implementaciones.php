<?php
/**
 * COMPARAR IMPLEMENTACIONES
 * Compara solicitudes-stock (que funciona) con despachos (que falla)
 */

echo "<h2>🔍 Comparar Implementaciones</h2>";

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Usuario Debug";

echo "<p>✅ Sesión simulada iniciada</p>";

echo "<hr>";
echo "<h3>🔍 Test 1: Solicitudes Stock (que funciona)</h3>";

try {
    // Simular petición a solicitudes-stock
    $_POST["idSolicitud"] = "1";
    
    echo "<p>📝 Simulando: Ver Solicitud ID 1</p>";
    
    ob_start();
    include "ajax/solicitudes-stock.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $response = json_decode($output, true);
    if ($response) {
        echo "<p>✅ JSON válido</p>";
    } else {
        echo "<p>❌ JSON inválido</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Excepción: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p>❌ Error fatal: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 2: Despachos - Aceptar (que falla)</h3>";

try {
    // Limpiar POST anterior
    unset($_POST);
    
    // Simular petición Aceptar Despacho
    $_POST["aceptarDespacho"] = "1";
    $_POST["idDespachoAceptar"] = "1";
    
    echo "<p>📝 Simulando: Aceptar Despacho ID 1</p>";
    
    ob_start();
    include "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $response = json_decode($output, true);
    if ($response) {
        echo "<p>✅ JSON válido</p>";
    } else {
        echo "<p>❌ JSON inválido</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Excepción: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p>❌ Error fatal: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 3: Verificar diferencias en el código</h3>";

echo "<p>📁 Comparando archivos:</p>";

// Verificar si solicitudes-stock tiene sendJsonResponse
$solicitudesContent = file_get_contents("ajax/solicitudes-stock.ajax.php");
if (strpos($solicitudesContent, "sendJsonResponse") !== false) {
    echo "<p>✅ solicitudes-stock.ajax.php usa sendJsonResponse</p>";
} else {
    echo "<p>❌ solicitudes-stock.ajax.php NO usa sendJsonResponse</p>";
}

// Verificar si despachos tiene sendJsonResponse
$despachosContent = file_get_contents("ajax/despachos.ajax.php");
if (strpos($despachosContent, "sendJsonResponse") !== false) {
    echo "<p>✅ despachos.ajax.php usa sendJsonResponse</p>";
} else {
    echo "<p>❌ despachos.ajax.php NO usa sendJsonResponse</p>";
}

// Verificar si hay diferencias en el manejo de sesión
if (strpos($solicitudesContent, "session_start()") !== false) {
    echo "<p>✅ solicitudes-stock.ajax.php tiene session_start()</p>";
} else {
    echo "<p>❌ solicitudes-stock.ajax.php NO tiene session_start()</p>";
}

if (strpos($despachosContent, "session_start()") !== false) {
    echo "<p>✅ despachos.ajax.php tiene session_start()</p>";
} else {
    echo "<p>❌ despachos.ajax.php NO tiene session_start()</p>";
}

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
