<?php
// Test para verificar la lógica de stock en tránsito

echo "🔍 Test Stock en Tránsito - Verificar lógica de aceptar despacho\n";
echo "=============================================================\n\n";

// Simular sesión
session_start();
$_SESSION['iniciarSesion'] = 'ok';
$_SESSION['perfil'] = 'Administrador';
$_SESSION['id'] = 1;
$_SESSION['nombre'] = 'Administrador';

echo "📋 Sesión simulada:\n";
echo "   - perfil: " . $_SESSION['perfil'] . "\n";
echo "   - id: " . $_SESSION['id'] . "\n";
echo "   - nombre: " . $_SESSION['nombre'] . "\n\n";

// Test 1: Verificar stock local antes
echo "🔍 Test 1: Verificar stock local antes de aceptar despacho\n";
echo "--------------------------------------------------------\n";

require_once "modelos/conexion.php";
require_once "api-transferencias/conexion-central.php";

try {
    $conexionLocal = Conexion::conectar();
    $stmt = $conexionLocal->prepare("SELECT codigo, descripcion, stock FROM productos WHERE codigo = '001'");
    $stmt->execute();
    $productoLocal = $stmt->fetch();
    
    if($productoLocal) {
        echo "✅ Producto local encontrado:\n";
        echo "   - Código: " . $productoLocal['codigo'] . "\n";
        echo "   - Descripción: " . $productoLocal['descripcion'] . "\n";
        echo "   - Stock actual: " . $productoLocal['stock'] . "\n\n";
    } else {
        echo "❌ Producto local no encontrado\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error verificando stock local: " . $e->getMessage() . "\n\n";
}

// Test 2: Verificar stock en tránsito antes
echo "🔍 Test 2: Verificar stock en tránsito antes de aceptar despacho\n";
echo "--------------------------------------------------------------\n";

try {
    $conexionCentral = ConexionCentral::conectar();
    $stmt = $conexionCentral->prepare("SELECT * FROM stock_transito WHERE codigo_producto = '001'");
    $stmt->execute();
    $stockTransito = $stmt->fetchAll();
    
    echo "✅ Stock en tránsito encontrado: " . count($stockTransito) . " registros\n";
    foreach($stockTransito as $item) {
        echo "   - ID: " . $item['id'] . ", Código: " . $item['codigo_producto'] . ", Cantidad: " . $item['cantidad_disponible'] . "\n";
    }
    echo "\n";
    
} catch(Exception $e) {
    echo "❌ Error verificando stock en tránsito: " . $e->getMessage() . "\n\n";
}

// Test 3: Simular aceptar despacho
echo "🔍 Test 3: Simular aceptar despacho\n";
echo "----------------------------------\n";

$_POST = array();
$_POST['aceptarDespacho'] = '21'; // ID del despacho a aceptar

try {
    ob_start();
    include 'ajax/despachos.ajax.php';
    $output = ob_get_clean();
    
    echo "✅ Respuesta de aceptar despacho:\n";
    echo $output . "\n\n";
    
} catch(Exception $e) {
    echo "❌ Error aceptando despacho: " . $e->getMessage() . "\n\n";
}

// Test 4: Verificar stock local después
echo "🔍 Test 4: Verificar stock local después de aceptar despacho\n";
echo "----------------------------------------------------------\n";

try {
    $stmt = $conexionLocal->prepare("SELECT codigo, descripcion, stock FROM productos WHERE codigo = '001'");
    $stmt->execute();
    $productoLocal = $stmt->fetch();
    
    if($productoLocal) {
        echo "✅ Producto local después:\n";
        echo "   - Código: " . $productoLocal['codigo'] . "\n";
        echo "   - Descripción: " . $productoLocal['descripcion'] . "\n";
        echo "   - Stock actual: " . $productoLocal['stock'] . "\n\n";
    } else {
        echo "❌ Producto local no encontrado\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error verificando stock local después: " . $e->getMessage() . "\n\n";
}

// Test 5: Verificar stock en tránsito después
echo "🔍 Test 5: Verificar stock en tránsito después de aceptar despacho\n";
echo "----------------------------------------------------------------\n";

try {
    $stmt = $conexionCentral->prepare("SELECT * FROM stock_transito WHERE codigo_producto = '001'");
    $stmt->execute();
    $stockTransito = $stmt->fetchAll();
    
    echo "✅ Stock en tránsito después: " . count($stockTransito) . " registros\n";
    foreach($stockTransito as $item) {
        echo "   - ID: " . $item['id'] . ", Código: " . $item['codigo_producto'] . ", Cantidad: " . $item['cantidad_disponible'] . "\n";
    }
    echo "\n";
    
} catch(Exception $e) {
    echo "❌ Error verificando stock en tránsito después: " . $e->getMessage() . "\n\n";
}

echo "🏁 Test completado\n";
?>
