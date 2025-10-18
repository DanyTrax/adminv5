<?php
// Test específico para el despacho ID 21

echo "🔍 Test Despacho ID 21 - Verificar despacho específico\n";
echo "====================================================\n\n";

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

// Test 1: Verificar despacho ID 21 directamente
echo "🔍 Test 1: Verificar despacho ID 21 directamente\n";
echo "----------------------------------------------\n";

try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    
    $stmt = $conexionCentral->prepare("SELECT * FROM despachos WHERE id = 21");
    $stmt->execute();
    $despacho = $stmt->fetch();
    
    if($despacho) {
        echo "✅ Despacho ID 21 encontrado:\n";
        echo "   - ID: " . $despacho['id'] . "\n";
        echo "   - Número: " . $despacho['numero_despacho'] . "\n";
        echo "   - Estado: " . $despacho['estado'] . "\n";
        echo "   - Productos: " . $despacho['productos_despacho'] . "\n\n";
        
        $productos = json_decode($despacho['productos_despacho'], true);
        if($productos) {
            echo "📦 Productos del despacho:\n";
            foreach($productos as $producto) {
                echo "   - Código: " . $producto['codigo'] . ", Cantidad: " . $producto['cantidad'] . "\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ Despacho ID 21 no encontrado\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error obteniendo despacho ID 21: " . $e->getMessage() . "\n\n";
}

// Test 2: Verificar a través del controlador
echo "🔍 Test 2: Verificar a través del controlador\n";
echo "--------------------------------------------\n";

try {
    require_once "controladores/despachos.controlador.php";
    
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", 21);
    
    if($despacho) {
        echo "✅ Despacho obtenido a través del controlador:\n";
        echo "   - ID: " . $despacho['id'] . "\n";
        echo "   - Número: " . $despacho['numero_despacho'] . "\n";
        echo "   - Estado: " . $despacho['estado'] . "\n\n";
    } else {
        echo "❌ Despacho no obtenido a través del controlador\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error obteniendo despacho a través del controlador: " . $e->getMessage() . "\n\n";
}

// Test 3: Verificar stock local del producto 001
echo "🔍 Test 3: Verificar stock local del producto 001\n";
echo "------------------------------------------------\n";

try {
    require_once "modelos/conexion.php";
    $conexionLocal = Conexion::conectar();
    
    $stmt = $conexionLocal->prepare("SELECT codigo, descripcion, stock FROM productos WHERE codigo = '001'");
    $stmt->execute();
    $producto = $stmt->fetch();
    
    if($producto) {
        echo "✅ Producto 001 encontrado:\n";
        echo "   - Código: " . $producto['codigo'] . "\n";
        echo "   - Descripción: " . $producto['descripcion'] . "\n";
        echo "   - Stock: " . $producto['stock'] . "\n\n";
    } else {
        echo "❌ Producto 001 no encontrado\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error obteniendo producto 001: " . $e->getMessage() . "\n\n";
}

echo "🏁 Test completado\n";
?>
