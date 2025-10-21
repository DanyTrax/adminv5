<?php
// Test directo del endpoint AJAX
session_start();

// Simular sesión
$_SESSION["iniciarSesion"] = "ok";
$_SESSION["perfil"] = "Administrador";

// Simular POST request
$_POST["buscarSolicitudes"] = true;
$_POST["termino"] = "test";

echo "<h1>Test Endpoint AJAX</h1>";
echo "<p>Probando endpoint: ajax/productos-despacho.ajax.php</p>";

// Capturar output
ob_start();
include "ajax/productos-despacho.ajax.php";
$output = ob_get_clean();

echo "<h2>Respuesta del endpoint:</h2>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Verificar si es JSON válido
$json = json_decode($output, true);
if($json) {
    echo "<h2>JSON parseado correctamente:</h2>";
    echo "<pre>" . print_r($json, true) . "</pre>";
} else {
    echo "<h2>Error: No es JSON válido</h2>";
    echo "<p>Error JSON: " . json_last_error_msg() . "</p>";
}
?>
