<?php
/**
 * Script para medir el rendimiento de las consultas
 * Comparar Stock en Tránsito vs Reporte Detallado
 */

echo "<h2>🔍 Debug de Rendimiento - Stock en Tránsito vs Reporte Detallado</h2>";

// Incluir conexiones
require_once "api-transferencias/conexion-central.php";
require_once "modelos/conexion.php";

try {
    echo "<h3>📊 1. CONSULTA STOCK EN TRÁNSITO</h3>";
    
    $inicio = microtime(true);
    
    $stmt = ConexionCentral::conectar()->prepare("
        SELECT 
            st.*,
            COALESCE(SUM(sd.cantidad_solicitada), 0) as cantidad_solicitada_pendiente,
            COUNT(sd.id) as solicitudes_pendientes
        FROM stock_transito st
        LEFT JOIN solicitudes_descarga sd ON st.id = sd.id_stock_transito 
            AND sd.estado = 'pendiente'
        WHERE st.cantidad_disponible > 0 
        GROUP BY st.id
        ORDER BY st.nombre_transportador ASC, st.codigo_producto ASC
    ");
    
    $stmt->execute();
    $stockTransito = $stmt->fetchAll();
    
    $fin = microtime(true);
    $tiempoStock = ($fin - $inicio) * 1000; // Convertir a milisegundos
    
    echo "✅ Tiempo de consulta: " . number_format($tiempoStock, 2) . " ms<br>";
    echo "📊 Registros encontrados: " . count($stockTransito) . "<br>";
    
    // Mostrar los primeros 3 registros
    echo "<h4>Primeros 3 registros:</h4>";
    echo "<pre>";
    foreach(array_slice($stockTransito, 0, 3) as $i => $stock) {
        echo "Registro " . ($i + 1) . ":\n";
        echo "  ID: " . $stock['id'] . "\n";
        echo "  Código: " . $stock['codigo_producto'] . "\n";
        echo "  Cantidad: " . $stock['cantidad_disponible'] . "\n";
        echo "  Transportador: " . $stock['nombre_transportador'] . "\n";
        echo "  Solicitudes pendientes: " . $stock['solicitudes_pendientes'] . "\n";
        echo "---\n";
    }
    echo "</pre>";
    
    echo "<h3>📊 2. CONSULTA REPORTE DETALLADO (ÚLTIMOS 7 DÍAS)</h3>";
    
    $inicio = microtime(true);
    
    $fechaInicial = date('Y-m-d', strtotime('-7 days'));
    $fechaFinal = date('Y-m-d');
    
    $stmt = Conexion::conectar()->prepare("
        SELECT 
            v.fecha_venta, 
            v.codigo AS codigo_factura, 
            v.medio_pago,
            vendedor.nombre AS nombre_vendedor, 
            cliente.nombre AS nombre_cliente,
            vp.descripcion AS producto_descripcion,
            vp.cantidad AS producto_cantidad,
            vp.total AS producto_total
        FROM venta_productos AS vp
        JOIN ventas AS v ON vp.id_venta = v.id
        JOIN usuarios AS vendedor ON v.id_vendedor = vendedor.id
        JOIN clientes AS cliente ON v.id_cliente = cliente.id
        WHERE v.fecha_venta BETWEEN :fechaInicial AND :fechaFinal
        ORDER BY v.fecha_venta DESC
        LIMIT 100
    ");
    
    $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
    $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
    $stmt->execute();
    $ventas = $stmt->fetchAll();
    
    $fin = microtime(true);
    $tiempoVentas = ($fin - $inicio) * 1000; // Convertir a milisegundos
    
    echo "✅ Tiempo de consulta: " . number_format($tiempoVentas, 2) . " ms<br>";
    echo "📊 Registros encontrados: " . count($ventas) . "<br>";
    
    echo "<h3>📊 3. ANÁLISIS DE RENDIMIENTO</h3>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Consulta</th><th>Tiempo (ms)</th><th>Registros</th><th>ms/registro</th></tr>";
    echo "<tr>";
    echo "<td>Stock en Tránsito</td>";
    echo "<td>" . number_format($tiempoStock, 2) . "</td>";
    echo "<td>" . count($stockTransito) . "</td>";
    echo "<td>" . number_format($tiempoStock / max(count($stockTransito), 1), 2) . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>Reporte Detallado</td>";
    echo "<td>" . number_format($tiempoVentas, 2) . "</td>";
    echo "<td>" . count($ventas) . "</td>";
    echo "<td>" . number_format($tiempoVentas / max(count($ventas), 1), 2) . "</td>";
    echo "</tr>";
    echo "</table>";
    
    echo "<h3>🔍 4. DIAGNÓSTICO</h3>";
    
    if($tiempoStock > 1000) {
        echo "❌ <strong>PROBLEMA:</strong> Stock en Tránsito es muy lento (>1 segundo)<br>";
        echo "🔍 <strong>Posibles causas:</strong><br>";
        echo "   • LEFT JOIN con solicitudes_descarga puede ser costoso<br>";
        echo "   • GROUP BY puede ser lento sin índices apropiados<br>";
        echo "   • Conexión a base central puede tener latencia<br>";
    } else {
        echo "✅ Stock en Tránsito tiene rendimiento aceptable<br>";
    }
    
    if($tiempoVentas < 500) {
        echo "✅ Reporte Detallado tiene buen rendimiento<br>";
    } else {
        echo "⚠️ Reporte Detallado podría optimizarse<br>";
    }
    
    echo "<h3>💡 5. RECOMENDACIONES</h3>";
    
    if($tiempoStock > $tiempoVentas) {
        echo "🔧 <strong>Optimizar Stock en Tránsito:</strong><br>";
        echo "   • Simplificar la consulta eliminando el LEFT JOIN si no es necesario<br>";
        echo "   • Agregar índices en stock_transito.id y solicitudes_descarga.id_stock_transito<br>";
        echo "   • Considerar cachear los datos de solicitudes pendientes<br>";
        echo "   • Verificar la latencia de la conexión central<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

echo "<br><br>🎯 <strong>Análisis completado</strong>";
?>
