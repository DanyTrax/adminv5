<?php
// Test para debuggear específicamente la acción de aceptar despacho

echo "🔍 Test Debug Aceptar Despacho - Diagnosticar problema específico\n";
echo "==============================================================\n\n";

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

// Test 1: Verificar despacho antes de aceptar
echo "🔍 Test 1: Verificar despacho antes de aceptar\n";
echo "---------------------------------------------\n";

require_once "modelos/conexion.php";
require_once "api-transferencias/conexion-central.php";
require_once "controladores/despachos.controlador.php";

$idDespacho = 21; // ID del despacho a probar

try {
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
    
    if($despacho) {
        echo "✅ Despacho encontrado:\n";
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
        echo "❌ Despacho no encontrado\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error obteniendo despacho: " . $e->getMessage() . "\n\n";
}

// Test 2: Verificar stock local antes
echo "🔍 Test 2: Verificar stock local antes\n";
echo "-------------------------------------\n";

try {
    $conexionLocal = Conexion::conectar();
    $stmt = $conexionLocal->prepare("SELECT codigo, descripcion, stock FROM productos WHERE codigo = '001'");
    $stmt->execute();
    $productoLocal = $stmt->fetch();
    
    if($productoLocal) {
        echo "✅ Stock local antes:\n";
        echo "   - Código: " . $productoLocal['codigo'] . "\n";
        echo "   - Stock: " . $productoLocal['stock'] . "\n\n";
    } else {
        echo "❌ Producto local no encontrado\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error verificando stock local: " . $e->getMessage() . "\n\n";
}

// Test 3: Verificar stock en tránsito antes
echo "🔍 Test 3: Verificar stock en tránsito antes\n";
echo "------------------------------------------\n";

try {
    $conexionCentral = ConexionCentral::conectar();
    $stmt = $conexionCentral->prepare("SELECT * FROM stock_transito WHERE codigo_producto = '001'");
    $stmt->execute();
    $stockTransito = $stmt->fetchAll();
    
    echo "✅ Stock en tránsito antes: " . count($stockTransito) . " registros\n";
    foreach($stockTransito as $item) {
        echo "   - ID: " . $item['id'] . ", Cantidad: " . $item['cantidad_disponible'] . "\n";
    }
    echo "\n";
    
} catch(Exception $e) {
    echo "❌ Error verificando stock en tránsito: " . $e->getMessage() . "\n\n";
}

// Test 4: Simular aceptar despacho con debug detallado
echo "🔍 Test 4: Simular aceptar despacho con debug\n";
echo "--------------------------------------------\n";

// Habilitar reporte de errores detallado
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_POST = array();
$_POST['aceptarDespacho'] = $idDespacho;

echo "📝 Datos POST: " . print_r($_POST, true) . "\n";

try {
    ob_start();
    
    // Incluir el archivo con debug
    include 'ajax/despachos.ajax.php';
    
    $output = ob_get_clean();
    
    echo "✅ Respuesta de aceptar despacho:\n";
    echo $output . "\n\n";
    
} catch(Exception $e) {
    echo "❌ Error aceptando despacho: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
} catch(Error $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
}

// Test 5: Verificar despacho después
echo "🔍 Test 5: Verificar despacho después\n";
echo "------------------------------------\n";

try {
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
    
    if($despacho) {
        echo "✅ Despacho después:\n";
        echo "   - ID: " . $despacho['id'] . "\n";
        echo "   - Estado: " . $despacho['estado'] . "\n";
        echo "   - Transportador: " . $despacho['nombre_transportador'] . "\n\n";
    } else {
        echo "❌ Despacho no encontrado\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error verificando despacho después: " . $e->getMessage() . "\n\n";
}

// Test 6: Verificar stock local después
echo "🔍 Test 6: Verificar stock local después\n";
echo "---------------------------------------\n";

try {
    $stmt = $conexionLocal->prepare("SELECT codigo, descripcion, stock FROM productos WHERE codigo = '001'");
    $stmt->execute();
    $productoLocal = $stmt->fetch();
    
    if($productoLocal) {
        echo "✅ Stock local después:\n";
        echo "   - Código: " . $productoLocal['codigo'] . "\n";
        echo "   - Stock: " . $productoLocal['stock'] . "\n\n";
    } else {
        echo "❌ Producto local no encontrado\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error verificando stock local después: " . $e->getMessage() . "\n\n";
}

// Test 7: Verificar stock en tránsito después
echo "🔍 Test 7: Verificar stock en tránsito después\n";
echo "--------------------------------------------\n";

try {
    $stmt = $conexionCentral->prepare("SELECT * FROM stock_transito WHERE codigo_producto = '001'");
    $stmt->execute();
    $stockTransito = $stmt->fetchAll();
    
    echo "✅ Stock en tránsito después: " . count($stockTransito) . " registros\n";
    foreach($stockTransito as $item) {
        echo "   - ID: " . $item['id'] . ", Cantidad: " . $item['cantidad_disponible'] . "\n";
    }
    echo "\n";
    
} catch(Exception $e) {
    echo "❌ Error verificando stock en tránsito después: " . $e->getMessage() . "\n\n";
}

echo "🏁 Test completado\n";
?>
