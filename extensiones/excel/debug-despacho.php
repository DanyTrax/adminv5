<?php

session_start();

echo "=== DEBUG EXPORTACIÓN EXCEL DESPACHO ===<br>";
echo "ID Despacho: " . ($_GET['id'] ?? 'NO DEFINIDO') . "<br>";
echo "Sesión ID: " . ($_SESSION['id'] ?? 'NO DEFINIDO') . "<br>";
echo "Sesión Perfil: " . ($_SESSION['perfil'] ?? 'NO DEFINIDO') . "<br><br>";

// Verificar que el usuario esté logueado
if(!isset($_SESSION['id'])) {
    die('Error: Sesión no iniciada');
}

// Incluir conexiones y modelos necesarios
require_once __DIR__ . "/../../../modelos/conexion.php";
require_once __DIR__ . "/../../../api-transferencias/conexion-central.php";
require_once __DIR__ . "/../../../controladores/despachos.controlador.php";

echo "✅ Archivos incluidos correctamente<br>";

// Obtener ID del despacho
$idDespacho = isset($_GET['id']) ? $_GET['id'] : null;

if (!$idDespacho || !is_numeric($idDespacho)) {
    die('Error: ID de despacho inválido');
}

echo "✅ ID Despacho válido: " . $idDespacho . "<br>";

try {
    // Obtener datos del despacho
    echo "🔍 Obteniendo datos del despacho...<br>";
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
    
    if (!$despacho) {
        die('Error: Despacho no encontrado');
    }
    
    echo "✅ Despacho encontrado: " . $despacho["numero_despacho"] . "<br>";
    echo "📊 Datos del despacho:<br>";
    echo "- Número: " . $despacho["numero_despacho"] . "<br>";
    echo "- Estado: " . $despacho["estado"] . "<br>";
    echo "- Sucursal: " . ($despacho["sucursal_origen"] ?? 'Sin especificar') . "<br>";
    echo "- Creador: " . ($despacho["nombre_usuario_creador"] ?? 'Sin especificar') . "<br>";
    echo "- Total productos: " . $despacho["total_productos"] . "<br>";
    echo "- Total cantidad: " . $despacho["total_cantidad"] . "<br>";
    
    // Decodificar productos
    echo "🔍 Decodificando productos...<br>";
    $productos = json_decode($despacho["productos_despacho"], true);
    
    if (!$productos || !is_array($productos)) {
        echo "❌ Error: No se pudieron obtener los productos del despacho<br>";
        echo "Raw productos_despacho: " . $despacho["productos_despacho"] . "<br>";
        die();
    }
    
    echo "✅ Productos decodificados correctamente: " . count($productos) . " productos<br>";
    
    foreach ($productos as $i => $producto) {
        echo "- Producto " . ($i+1) . ": " . $producto['codigo'] . " - " . $producto['descripcion'] . " (Cant: " . $producto['cantidad'] . ")<br>";
    }
    
    echo "<br>✅ Todos los datos obtenidos correctamente<br>";
    echo "El archivo Excel debería generarse correctamente.<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

?>
