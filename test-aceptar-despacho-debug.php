<?php
// Test específico para "Aceptar Despacho" con debug detallado

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test Aceptar Despacho - Debug Detallado\n";
echo "==========================================\n\n";

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";

echo "📋 Sesión simulada:\n";
echo "- perfil: " . $_SESSION["perfil"] . "\n";
echo "- id: " . $_SESSION["id"] . "\n";
echo "- nombre: " . $_SESSION["nombre"] . "\n\n";

// Incluir archivos necesarios
require_once "modelos/conexion.php";
require_once "api-transferencias/conexion-central.php";
require_once "controladores/despachos.controlador.php";
require_once "modelos/despachos.modelo.php";
require_once "modelos/productos.modelo.php";

echo "🔍 Test 1: Verificar despacho ID 21\n";
echo "-----------------------------------\n";

$despacho = ControladorDespachos::ctrMostrarDespachos("id", 21);

if($despacho) {
    echo "✅ Despacho encontrado:\n";
    echo "- ID: " . $despacho["id"] . "\n";
    echo "- Número: " . $despacho["numero_despacho"] . "\n";
    echo "- Estado: " . $despacho["estado"] . "\n";
    echo "- Productos: " . $despacho["productos_despacho"] . "\n\n";
    
    // Decodificar productos
    $productos = json_decode($despacho["productos_despacho"], true);
    echo "📦 Productos del despacho:\n";
    foreach($productos as $producto) {
        echo "- Código: " . $producto["codigo"] . " | Cantidad: " . $producto["cantidad"] . "\n";
    }
    echo "\n";
} else {
    echo "❌ Despacho no encontrado\n\n";
    exit;
}

echo "🔍 Test 2: Verificar stock local antes del descuento\n";
echo "----------------------------------------------------\n";

foreach($productos as $producto) {
    $stmt = Conexion::conectar()->prepare("SELECT stock FROM productos WHERE codigo = ?");
    $stmt->execute([$producto["codigo"]]);
    $stock = $stmt->fetch();
    
    if($stock) {
        echo "✅ Producto " . $producto["codigo"] . ": Stock actual = " . $stock["stock"] . " | Requerido = " . $producto["cantidad"] . "\n";
    } else {
        echo "❌ Producto " . $producto["codigo"] . ": No encontrado en BD local\n";
    }
}
echo "\n";

echo "🔍 Test 3: Simular AJAX request para Aceptar Despacho\n";
echo "-----------------------------------------------------\n";

// Simular POST data
$_POST["aceptarDespacho"] = 21;

echo "📝 Simulando: Aceptar Despacho ID 21\n";

// Capturar output
ob_start();

// Incluir el archivo AJAX
include "ajax/despachos.ajax.php";

$output = ob_get_clean();

echo "📤 Respuesta del servidor:\n";
echo $output . "\n\n";

echo "🔍 Test 4: Verificar stock local después del descuento\n";
echo "-----------------------------------------------------\n";

foreach($productos as $producto) {
    $stmt = Conexion::conectar()->prepare("SELECT stock FROM productos WHERE codigo = ?");
    $stmt->execute([$producto["codigo"]]);
    $stock = $stmt->fetch();
    
    if($stock) {
        echo "✅ Producto " . $producto["codigo"] . ": Stock actual = " . $stock["stock"] . "\n";
    } else {
        echo "❌ Producto " . $producto["codigo"] . ": No encontrado en BD local\n";
    }
}
echo "\n";

echo "🔍 Test 5: Verificar stock en tránsito\n";
echo "-------------------------------------\n";

$stmt = ConexionCentral::conectar()->prepare("SELECT * FROM stock_transito WHERE transportador_id = ?");
$stmt->execute([$_SESSION["id"]]);
$stockTransito = $stmt->fetchAll();

if($stockTransito) {
    echo "✅ Stock en tránsito encontrado:\n";
    foreach($stockTransito as $item) {
        echo "- Código: " . $item["codigo_producto"] . " | Cantidad: " . $item["cantidad_disponible"] . "\n";
    }
} else {
    echo "❌ No hay stock en tránsito\n";
}
echo "\n";

echo "🏁 Test completado\n";
?>
