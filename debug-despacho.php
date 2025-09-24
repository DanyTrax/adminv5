<?php
session_start();

echo "<h2>🔧 DEBUG - CREAR DESPACHO</h2>";

// 1. Verificar conexión local
echo "<h3>1. Conexión BD Local</h3>";
try {
    require_once "modelos/conexion.php";
    $conn = Conexion::conectar();
    echo "✅ Conexión local exitosa<br>";
    
    // Verificar tabla productos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos WHERE estado = 1");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "📦 Productos activos: " . $result['total'] . "<br>";
    
    // Mostrar algunos productos
    $stmt = $conn->prepare("SELECT codigo, descripcion, stock FROM productos WHERE estado = 1 ORDER BY descripcion LIMIT 5");
    $stmt->execute();
    $productos = $stmt->fetchAll();
    
    echo "<strong>Productos de ejemplo:</strong><br>";
    foreach($productos as $p) {
        echo "- {$p['codigo']}: {$p['descripcion']} (Stock: {$p['stock']})<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Error BD local: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 2. Verificar conexión central
echo "<h3>2. Conexión BD Central</h3>";
try {
    require_once "api-transferencias/conexion-central.php";
    $conn = ConexionCentral::conectar();
    echo "✅ Conexión central exitosa<br>";
    
    // Verificar tabla solicitudes_stock
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM solicitudes_stock");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "📋 Total solicitudes: " . $result['total'] . "<br>";
    
    // Mostrar algunas solicitudes
    $stmt = $conn->prepare("SELECT codigo_solicitud, nombre_usuario_solicitante, estado FROM solicitudes_stock ORDER BY fecha_solicitud DESC LIMIT 5");
    $stmt->execute();
    $solicitudes = $stmt->fetchAll();
    
    echo "<strong>Solicitudes de ejemplo:</strong><br>";
    foreach($solicitudes as $s) {
        echo "- {$s['codigo_solicitud']}: {$s['nombre_usuario_solicitante']} ({$s['estado']})<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Error BD central: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 3. Simular AJAX de inventario
echo "<h3>3. Simular AJAX Inventario</h3>";
try {
    require_once "modelos/conexion.php";
    
    $stmt = Conexion::conectar()->prepare("
        SELECT 
            codigo,
            descripcion,
            stock,
            precio_venta,
            imagen
        FROM productos 
        WHERE estado = 1 
        AND stock > 0
        ORDER BY descripcion ASC
        LIMIT 10
    ");
    
    $stmt->execute();
    $productos = $stmt->fetchAll();
    
    $respuesta = [
        "success" => true,
        "productos" => $productos,
        "total" => count($productos)
    ];
    
    echo "✅ Simulación exitosa<br>";
    echo "<strong>JSON que debería devolver:</strong><br>";
    echo "<pre>" . json_encode($respuesta, JSON_PRETTY_PRINT) . "</pre>";
    
} catch(Exception $e) {
    echo "❌ Error simulando inventario: " . $e->getMessage() . "<br>";
}

?>