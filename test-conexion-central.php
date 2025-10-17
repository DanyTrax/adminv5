<?php
// Test de conexión a BD central
session_start();

if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
    die("Sin permisos");
}

echo "<h1>🌐 Test Conexión BD Central</h1>";

try {
    
    require_once "api-transferencias/conexion-central.php";
    $db = ConexionCentral::conectar();
    
    echo "✅ <strong>Conexión exitosa</strong><br>";
    
    // Probar consulta simple
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM catalogo_maestro WHERE activo = 1");
    $stmt->execute();
    $resultado = $stmt->fetch();
    
    echo "📊 <strong>Total productos activos:</strong> " . $resultado['total'] . "<br>";
    
    // Probar UPDATE simple
    $stmt = $db->prepare("SELECT id, descripcion FROM catalogo_maestro WHERE activo = 1 LIMIT 1");
    $stmt->execute();
    $producto = $stmt->fetch();
    
    if ($producto) {
        echo "<br><strong>🧪 Probando UPDATE simple...</strong><br>";
        echo "ID del producto: " . $producto['id'] . "<br>";
        echo "Descripción actual: " . $producto['descripcion'] . "<br>";
        
        // Intentar UPDATE
        $nuevaDescripcion = $producto['descripcion'] . " [TEST " . date('H:i:s') . "]";
        $stmtUpdate = $db->prepare("UPDATE catalogo_maestro SET descripcion = ? WHERE id = ?");
        $resultadoUpdate = $stmtUpdate->execute([$nuevaDescripcion, $producto['id']]);
        
        if ($resultadoUpdate) {
            echo "✅ <strong>UPDATE exitoso</strong><br>";
            echo "Nueva descripción: " . $nuevaDescripcion . "<br>";
        } else {
            $errorInfo = $stmtUpdate->errorInfo();
            echo "❌ <strong>Error en UPDATE:</strong> " . implode(" - ", $errorInfo) . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
}

echo "<hr>";
echo "<p><a href='catalogo-maestro'>← Volver</a></p>";
?>