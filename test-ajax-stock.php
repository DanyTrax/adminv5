<?php

// Simular la consulta AJAX para stock por sucursales
$_POST["accion"] = "consultar_stock_sucursales";
$_POST["productos"] = json_encode([
    [
        "codigo" => "PROD001",
        "descripcion" => "Producto de Prueba",
        "cantidad" => 5
    ]
]);

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";

echo "<h2>🧪 Probando consulta AJAX de stock por sucursales</h2>";

// Capturar la salida
ob_start();

try {
    include "ajax/stock-disponible-sucursales.ajax.php";
    $output = ob_get_clean();
    
    echo "<h3>✅ Respuesta AJAX:</h3>";
    echo "<pre>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // Intentar decodificar JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<h3>📊 Datos decodificados:</h3>";
        echo "<pre>";
        print_r($json);
        echo "</pre>";
    } else {
        echo "<h3>❌ Error decodificando JSON</h3>";
        echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "<h3>❌ Excepción capturada</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}

?>
