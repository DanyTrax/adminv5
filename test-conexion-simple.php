<?php
// Test simple para verificar conexiones

echo "🔍 Test Conexión Simple - Verificar conexiones básicas\n";
echo "=====================================================\n\n";

// Test 1: Verificar conexión local
echo "🔍 Test 1: Conexión local\n";
echo "------------------------\n";

try {
    require_once "modelos/conexion.php";
    $conexionLocal = Conexion::conectar();
    echo "✅ Conexión local exitosa\n";
    
    // Verificar tabla productos
    $stmt = $conexionLocal->prepare("SELECT COUNT(*) as total FROM productos");
    $stmt->execute();
    $resultado = $stmt->fetch();
    echo "✅ Tabla productos: " . $resultado['total'] . " registros\n\n";
    
} catch(Exception $e) {
    echo "❌ Error conexión local: " . $e->getMessage() . "\n\n";
}

// Test 2: Verificar conexión central
echo "🔍 Test 2: Conexión central\n";
echo "--------------------------\n";

try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    echo "✅ Conexión central exitosa\n";
    
    // Verificar tabla despachos
    $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM despachos");
    $stmt->execute();
    $resultado = $stmt->fetch();
    echo "✅ Tabla despachos: " . $resultado['total'] . " registros\n";
    
    // Verificar tabla stock_transito
    $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM stock_transito");
    $stmt->execute();
    $resultado = $stmt->fetch();
    echo "✅ Tabla stock_transito: " . $resultado['total'] . " registros\n\n";
    
} catch(Exception $e) {
    echo "❌ Error conexión central: " . $e->getMessage() . "\n\n";
}

// Test 3: Verificar despachos disponibles
echo "🔍 Test 3: Despachos disponibles\n";
echo "-------------------------------\n";

try {
    $stmt = $conexionCentral->prepare("SELECT id, numero_despacho, estado FROM despachos ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $despachos = $stmt->fetchAll();
    
    echo "✅ Despachos encontrados: " . count($despachos) . "\n";
    foreach($despachos as $despacho) {
        echo "   - ID: " . $despacho['id'] . ", Número: " . $despacho['numero_despacho'] . ", Estado: " . $despacho['estado'] . "\n";
    }
    echo "\n";
    
} catch(Exception $e) {
    echo "❌ Error obteniendo despachos: " . $e->getMessage() . "\n\n";
}

// Test 4: Verificar productos locales
echo "🔍 Test 4: Productos locales\n";
echo "---------------------------\n";

try {
    $stmt = $conexionLocal->prepare("SELECT codigo, descripcion, stock FROM productos ORDER BY codigo LIMIT 5");
    $stmt->execute();
    $productos = $stmt->fetchAll();
    
    echo "✅ Productos encontrados: " . count($productos) . "\n";
    foreach($productos as $producto) {
        echo "   - Código: " . $producto['codigo'] . ", Stock: " . $producto['stock'] . "\n";
    }
    echo "\n";
    
} catch(Exception $e) {
    echo "❌ Error obteniendo productos: " . $e->getMessage() . "\n\n";
}

echo "🏁 Test completado\n";
?>
