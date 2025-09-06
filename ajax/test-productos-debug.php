<?php

// Headers para debug
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>🔧 Debug Completo - ajax/productos.ajax.php</h3>";

try {
    
    // Paso 1: Verificar sesión
    session_start();
    echo "<strong>Paso 1 - Sesión:</strong> ";
    if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {
        echo "✅ Sesión OK - Usuario: " . $_SESSION["nombre"] . "<br>";
    } else {
        echo "❌ Sin sesión válida<br>";
    }
    
    // Paso 2: Verificar archivos
    echo "<strong>Paso 2 - Archivos:</strong><br>";
    
    $archivos = [
        "../controladores/productos.controlador.php",
        "../modelos/productos.modelo.php", 
        "../modelos/conexion.php"
    ];
    
    foreach ($archivos as $archivo) {
        if (file_exists($archivo)) {
            echo "✅ " . basename($archivo) . " existe<br>";
        } else {
            echo "❌ " . basename($archivo) . " NO existe<br>";
        }
    }
    
    // Paso 3: Incluir archivos
    echo "<strong>Paso 3 - Includes:</strong><br>";
    
    try {
        require_once "../controladores/productos.controlador.php";
        echo "✅ Controlador incluido<br>";
    } catch (Exception $e) {
        echo "❌ Error en controlador: " . $e->getMessage() . "<br>";
        throw $e;
    }
    
    try {
        require_once "../modelos/productos.modelo.php";
        echo "✅ Modelo incluido<br>";
    } catch (Exception $e) {
        echo "❌ Error en modelo: " . $e->getMessage() . "<br>";
        throw $e;
    }
    
    // Paso 4: Verificar clases
    echo "<strong>Paso 4 - Clases:</strong><br>";
    
    if (class_exists('ControladorProductos')) {
        echo "✅ ControladorProductos existe<br>";
    } else {
        echo "❌ ControladorProductos NO existe<br>";
    }
    
    if (class_exists('ModeloProductos')) {
        echo "✅ ModeloProductos existe<br>";
    } else {
        echo "❌ ModeloProductos NO existe<br>";
    }
    
    // Paso 5: Probar conexión a BD
    echo "<strong>Paso 5 - Base de Datos:</strong><br>";
    
    require_once "../modelos/conexion.php";
    $conexion = Conexion::conectar();
    
    if ($conexion) {
        echo "✅ Conexión a BD OK<br>";
        
        // Probar consulta simple
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM productos");
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        echo "✅ Total productos en BD: " . $resultado['total'] . "<br>";
        
    } else {
        echo "❌ Error de conexión a BD<br>";
    }
    
    // Paso 6: Probar controlador
    echo "<strong>Paso 6 - Controlador:</strong><br>";
    
    $productos = ControladorProductos::ctrMostrarProductos(null, null);
    
    if (is_array($productos)) {
        echo "✅ Controlador devuelve " . count($productos) . " productos<br>";
        
        // Mostrar un producto de ejemplo
        if (!empty($productos)) {
            echo "<strong>Producto ejemplo:</strong><br>";
            echo "- Código: " . ($productos[0]['codigo'] ?? 'N/A') . "<br>";
            echo "- Descripción: " . ($productos[0]['descripcion'] ?? 'N/A') . "<br>";
            echo "- Stock: " . ($productos[0]['stock'] ?? 'N/A') . "<br>";
        }
        
        // Contar productos con stock
        $conStock = 0;
        foreach ($productos as $producto) {
            if (isset($producto['stock']) && floatval($producto['stock']) > 0) {
                $conStock++;
            }
        }
        
        echo "✅ Productos con stock > 0: " . $conStock . "<br>";
        
    } else {
        echo "❌ Error en controlador o sin productos<br>";
        var_dump($productos);
    }
    
    // Paso 7: Generar JSON de prueba
    echo "<strong>Paso 7 - JSON generado:</strong><br>";
    
    $productosDisponibles = [];
    
    if (is_array($productos)) {
        foreach ($productos as $producto) {
            if (isset($producto['stock']) && floatval($producto['stock']) > 0) {
                $productosDisponibles[] = [
                    'codigo' => $producto['codigo'],
                    'descripcion' => $producto['descripcion'],
                    'stock' => floatval($producto['stock']),
                    'precio_venta' => floatval($producto['precio_venta'] ?? 0)
                ];
                
                // Solo mostrar primeros 3 para no saturar
                if (count($productosDisponibles) >= 3) break;
            }
        }
    }
    
    echo "<pre>" . json_encode($productosDisponibles, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<h4>🎯 CONCLUSIÓN: Sistema funcionando correctamente</h4>";
    echo "<p>Ahora ve a <strong>ajax/productos.ajax.php</strong> y busca el error específico</p>";
    
} catch (Exception $e) {
    echo "<h4>❌ ERROR FATAL:</h4>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Trace:</strong><br><pre>" . $e->getTraceAsString() . "</pre>";
}

?>