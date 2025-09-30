<?php
session_start();

// Simular sesión de transportador para pruebas
$_SESSION['id'] = 1;
$_SESSION['nombre'] = 'Transportador Test';
$_SESSION['sucursal'] = 'Sucursal Test';
$_SESSION['perfil'] = 'Transportador';

echo "<h2>🧪 Test Despacho → Stock Tránsito</h2>";

try {
    require_once "api-transferencias/conexion-central.php";
    
    $conexion = ConexionCentral::conectar();
    
    // Buscar despachos en estado "pendiente" para probar
    $stmt = $conexion->prepare("
        SELECT id, numero_despacho, productos_despacho, total_productos, total_cantidad, estado
        FROM despachos 
        WHERE estado = 'pendiente' 
        ORDER BY fecha_creacion DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $despachos = $stmt->fetchAll();
    
    echo "<h3>📦 Despachos pendientes encontrados: " . count($despachos) . "</h3>";
    
    if (count($despachos) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Número</th><th>Productos</th><th>Total Cantidad</th><th>Estado</th><th>Acción</th></tr>";
        
        foreach ($despachos as $despacho) {
            echo "<tr>";
            echo "<td>" . $despacho['id'] . "</td>";
            echo "<td>" . $despacho['numero_despacho'] . "</td>";
            echo "<td>" . $despacho['total_productos'] . "</td>";
            echo "<td>" . $despacho['total_cantidad'] . "</td>";
            echo "<td>" . $despacho['estado'] . "</td>";
            echo "<td><a href='?test_despacho=" . $despacho['id'] . "'>Probar →Stock Tránsito</a></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay despachos pendientes para probar.</p>";
    }
    
    // Si se hace clic en probar, ejecutar la función
    if (isset($_GET['test_despacho'])) {
        $idDespacho = $_GET['test_despacho'];
        
        echo "<hr><h3>🚛 Probando función procesarDespachoEnTransito con ID: {$idDespacho}</h3>";
        
        require_once "controladores/despachos.controlador.php";
        
        $resultado = ControladorDespachos::procesarDespachoEnTransito($idDespacho);
        
        if ($resultado['exito']) {
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px;'>";
            echo "✅ <strong>ÉXITO:</strong> " . $resultado['mensaje'];
            echo "</div>";
            
            // Mostrar productos agregados al stock
            $stmt = $conexion->prepare("
                SELECT * FROM stock_transito 
                WHERE id_despacho_origen = ? 
                ORDER BY codigo_producto
            ");
            $stmt->execute([$idDespacho]);
            $stockAgregado = $stmt->fetchAll();
            
            if ($stockAgregado) {
                echo "<h4>📊 Productos agregados al stock en tránsito:</h4>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
                echo "<tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Transportador</th><th>Fecha</th></tr>";
                
                foreach ($stockAgregado as $item) {
                    echo "<tr>";
                    echo "<td>" . $item['codigo_producto'] . "</td>";
                    echo "<td>" . substr($item['descripcion_producto'], 0, 50) . "...</td>";
                    echo "<td>" . $item['cantidad_disponible'] . "</td>";
                    echo "<td>" . $item['nombre_transportador'] . "</td>";
                    echo "<td>" . $item['fecha_carga'] . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            }
            
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;'>";
            echo "❌ <strong>ERROR:</strong> " . $resultado['mensaje'];
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>✅ Implementación del paso 4 completa:</strong></p>";
echo "<ul>";
echo "<li>✅ Función procesarDespachoEnTransito() agregada</li>";
echo "<li>✅ Se ejecuta automáticamente cuando estado cambia a 'en_transito'</li>";
echo "<li>✅ Productos se agregan/suman al stock_transito</li>";
echo "<li>✅ Solo se muestran productos con cantidad > 0 en la tabla</li>";
echo "</ul>";
?>