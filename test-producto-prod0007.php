<?php
// Test específico para el producto PROD0007

echo "🔍 Test Producto PROD0007 - Verificar producto específico\n";
echo "======================================================\n\n";

// Test 1: Verificar producto en base de datos local
echo "🔍 Test 1: Verificar producto PROD0007 en BD local\n";
echo "------------------------------------------------\n";

try {
    require_once "modelos/conexion.php";
    $conexionLocal = Conexion::conectar();
    
    $stmt = $conexionLocal->prepare("SELECT * FROM productos WHERE codigo = 'PROD0007'");
    $stmt->execute();
    $producto = $stmt->fetch();
    
    if($producto) {
        echo "✅ Producto PROD0007 encontrado en BD local:\n";
        echo "   - Código: " . $producto['codigo'] . "\n";
        echo "   - Descripción: " . $producto['descripcion'] . "\n";
        echo "   - Stock: " . $producto['stock'] . "\n\n";
    } else {
        echo "❌ Producto PROD0007 no encontrado en BD local\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error obteniendo producto PROD0007: " . $e->getMessage() . "\n\n";
}

// Test 2: Verificar si existe en stock_transito
echo "🔍 Test 2: Verificar producto PROD0007 en stock_transito\n";
echo "------------------------------------------------------\n";

try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    
    $stmt = $conexionCentral->prepare("SELECT * FROM stock_transito WHERE codigo_producto = 'PROD0007'");
    $stmt->execute();
    $stockTransito = $stmt->fetchAll();
    
    if(count($stockTransito) > 0) {
        echo "✅ Producto PROD0007 encontrado en stock_transito:\n";
        foreach($stockTransito as $item) {
            echo "   - ID: " . $item['id'] . ", Cantidad: " . $item['cantidad_disponible'] . "\n";
        }
        echo "\n";
    } else {
        echo "❌ Producto PROD0007 no encontrado en stock_transito\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error verificando stock_transito: " . $e->getMessage() . "\n\n";
}

// Test 3: Verificar función de verificación de stock
echo "🔍 Test 3: Verificar función de verificación de stock\n";
echo "---------------------------------------------------\n";

try {
    require_once "modelos/despachos.modelo.php";
    
    $stockDisponible = ModeloDespachos::mdlVerificarStockLocal("PROD0007", 100);
    
    if($stockDisponible) {
        echo "✅ Stock disponible para PROD0007 (cantidad 100): Sí\n\n";
    } else {
        echo "❌ Stock disponible para PROD0007 (cantidad 100): No\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error verificando stock disponible: " . $e->getMessage() . "\n\n";
}

// Test 4: Simular descuento de stock
echo "🔍 Test 4: Simular descuento de stock\n";
echo "-----------------------------------\n";

try {
    $productos = array(
        array(
            "codigo" => "PROD0007",
            "cantidad" => 100
        )
    );
    
    $descuentoStock = ModeloDespachos::mdlDescontarStockLocal($productos);
    
    if($descuentoStock) {
        echo "✅ Descuento de stock simulado: Exitoso\n\n";
    } else {
        echo "❌ Descuento de stock simulado: Falló\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error simulando descuento de stock: " . $e->getMessage() . "\n\n";
}

echo "🏁 Test completado\n";
?>
