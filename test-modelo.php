<?php
// Test directo del modelo de catálogo maestro
session_start();

if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
    die("Sin permisos");
}

echo "<h1>🧪 Test del Modelo de Catálogo Maestro</h1>";

try {
    
    // Cargar modelo
    require_once "modelos/catalogo-maestro.modelo.php";
    
    // Obtener primer producto para probar
    require_once "api-transferencias/conexion-central.php";
    $db = ConexionCentral::conectar();
    
    $stmt = $db->prepare("SELECT * FROM catalogo_maestro WHERE activo = 1 LIMIT 1");
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        die("No hay productos en catálogo maestro");
    }
    
    echo "<h2>📋 Producto de prueba:</h2>";
    echo "<pre>" . print_r($producto, true) . "</pre>";
    
    // Preparar datos para actualizar
    $datos = array(
        "id" => $producto['id'],
        "descripcion" => $producto['descripcion'] . " [TEST MODIFICADO]",
        "id_categoria" => $producto['id_categoria'],
        "precio_venta" => $producto['precio_venta'],
        "imagen" => $producto['imagen'] ?? "vistas/img/productos/default/anonymous.png",
        "es_divisible" => $producto['es_divisible'] ?? 0,
        "codigo_hijo_mitad" => $producto['codigo_hijo_mitad'] ?? '',
        "codigo_hijo_tercio" => $producto['codigo_hijo_tercio'] ?? '',
        "codigo_hijo_cuarto" => $producto['codigo_hijo_cuarto'] ?? ''
    );
    
    echo "<h2>🔧 Datos para actualizar:</h2>";
    echo "<pre>" . print_r($datos, true) . "</pre>";
    
    // Intentar actualizar
    echo "<h2>🚀 Ejecutando actualización...</h2>";
    $respuesta = ModeloCatalogoMaestro::mdlEditarProductoMaestro($datos);
    
    echo "<h2>📤 Respuesta del modelo:</h2>";
    echo "<strong>Resultado:</strong> " . $respuesta . "<br>";
    
    if ($respuesta === "ok") {
        echo "<div style='background: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb;'>";
        echo "✅ <strong>ÉXITO:</strong> El modelo funciona correctamente";
        echo "</div>";
        
        // Verificar que realmente se actualizó
        $stmtVerificar = $db->prepare("SELECT descripcion FROM catalogo_maestro WHERE id = ?");
        $stmtVerificar->execute([$producto['id']]);
        $productoActualizado = $stmtVerificar->fetch();
        
        echo "<h2>✅ Verificación:</h2>";
        echo "<p><strong>Descripción actualizada:</strong> " . $productoActualizado['descripcion'] . "</p>";
        
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb;'>";
        echo "❌ <strong>ERROR:</strong> El modelo falló - " . $respuesta;
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb;'>";
    echo "❌ <strong>EXCEPCIÓN:</strong> " . $e->getMessage();
    echo "<br><strong>Archivo:</strong> " . $e->getFile();
    echo "<br><strong>Línea:</strong> " . $e->getLine();
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>📅 Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='catalogo-maestro'>← Volver al catálogo</a></p>";
?>