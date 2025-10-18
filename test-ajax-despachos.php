<?php
// Test para verificar si la función AJAX de despachos se ejecuta

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test AJAX Despachos\n";
echo "=====================\n\n";

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";

echo "📋 Sesión simulada:\n";
echo "- perfil: " . $_SESSION["perfil"] . "\n";
echo "- id: " . $_SESSION["id"] . "\n";
echo "- nombre: " . $_SESSION["nombre"] . "\n\n";

// Incluir Logger
require_once "src/Logger.php";

echo "🔍 Test 1: Verificar si el archivo AJAX existe\n";
echo "-----------------------------------------------\n";

$ajaxFile = "ajax/despachos.ajax.php";
if (file_exists($ajaxFile)) {
    echo "✅ Archivo AJAX existe: $ajaxFile\n";
    echo "📊 Tamaño: " . filesize($ajaxFile) . " bytes\n";
} else {
    echo "❌ Archivo AJAX NO existe: $ajaxFile\n";
    exit;
}

echo "\n🔍 Test 2: Simular petición AJAX para Aceptar Despacho\n";
echo "-----------------------------------------------------\n";

// Simular POST data
$_POST["aceptarDespacho"] = 21;

echo "📝 Simulando: Aceptar Despacho ID 21\n";
echo "📤 POST data: " . json_encode($_POST) . "\n\n";

// Limpiar log antes del test
file_put_contents("logs/sistema.log", "# Log del Sistema - Despachos\n# Formato: [FECHA] [NIVEL] [ARCHIVO] [FUNCIÓN] - MENSAJE\n");

echo "🧹 Log limpiado para el test\n\n";

// Capturar output
ob_start();

try {
    // Incluir el archivo AJAX
    include $ajaxFile;
} catch (Exception $e) {
    echo "❌ Error incluyendo archivo AJAX: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();

echo "📤 Respuesta del servidor:\n";
echo $output . "\n\n";

echo "🔍 Test 3: Verificar logs generados\n";
echo "-----------------------------------\n";

$logFile = "logs/sistema.log";
if (file_exists($logFile)) {
    echo "✅ Archivo de log existe\n";
    echo "📊 Tamaño: " . filesize($logFile) . " bytes\n";
    echo "📄 Contenido:\n";
    echo file_get_contents($logFile);
} else {
    echo "❌ Archivo de log NO existe\n";
}

echo "\n🏁 Test completado\n";
?>
