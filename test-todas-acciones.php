<?php
/**
 * TEST DE TODAS LAS ACCIONES DE DESPACHOS
 * Prueba cada acción individualmente para identificar el error 500
 */

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Test de Todas las Acciones de Despachos</h2>";

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Usuario Debug";

echo "<p>✅ Sesión simulada iniciada</p>";

echo "<hr>";
echo "<h3>🔍 Test 1: Ver Despacho (idDespacho)</h3>";

try {
    // Limpiar POST anterior
    unset($_POST);
    
    // Simular petición Ver Despacho
    $_POST["idDespacho"] = "1";
    
    echo "<p>📝 Simulando: Ver Despacho ID 1</p>";
    
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
echo "<h3>🔍 Test 2: Aceptar Despacho (aceptarDespacho)</h3>";

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
echo "<h3>🔍 Test 3: Cancelar Despacho (cancelarDespacho)</h3>";

try {
    // Limpiar POST anterior
    unset($_POST);
    
    // Simular petición Cancelar Despacho
    $_POST["cancelarDespacho"] = "1";
    $_POST["idDespachoCancelar"] = "1";
    $_POST["motivoCancelacion"] = "Prueba de cancelación";
    
    echo "<p>📝 Simulando: Cancelar Despacho ID 1</p>";
    
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
echo "<h3>🔍 Test 4: Eliminar Despacho (eliminarDespacho)</h3>";

try {
    // Limpiar POST anterior
    unset($_POST);
    
    // Simular petición Eliminar Despacho
    $_POST["eliminarDespacho"] = "1";
    $_POST["idDespachoEliminar"] = "1";
    
    echo "<p>📝 Simulando: Eliminar Despacho ID 1</p>";
    
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
echo "<h3>🔍 Test 5: Verificar archivo AJAX</h3>";

echo "<p>📁 Verificando sintaxis del archivo AJAX:</p>";

// Verificar sintaxis PHP
$syntaxCheck = shell_exec("php -l ajax/despachos.ajax.php 2>&1");
echo "<pre>" . htmlspecialchars($syntaxCheck) . "</pre>";

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
