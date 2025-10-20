<?php
// Debug específico para verificar stock_transito y despachos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug - Stock en Tránsito y Despachos</h2>";

try {
    require_once "api-transferencias/conexion-central.php";
    
    $conexionCentral = ConexionCentral::conectar();
    echo "✅ Conexión a base central exitosa<br><br>";
    
    // 1. Verificar despachos en estado "en_transito"
    echo "<h3>📦 Despachos en Estado 'en_transito'</h3>";
    $stmt = $conexionCentral->prepare("
        SELECT id, numero_despacho, sucursal_origen, estado, fecha_creacion, fecha_actualizacion, 
               transportador_id, nombre_transportador
        FROM despachos 
        WHERE estado = 'en_transito'
        ORDER BY fecha_actualizacion DESC
    ");
    $stmt->execute();
    $despachos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($despachos) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Número</th><th>Origen</th><th>Estado</th><th>Transportador</th><th>Fecha Actualización</th></tr>";
        foreach($despachos as $despacho) {
            echo "<tr>";
            echo "<td>" . $despacho['id'] . "</td>";
            echo "<td>" . $despacho['numero_despacho'] . "</td>";
            echo "<td>" . $despacho['sucursal_origen'] . "</td>";
            echo "<td>" . $despacho['estado'] . "</td>";
            echo "<td>" . ($despacho['nombre_transportador'] ?? 'N/A') . "</td>";
            echo "<td>" . $despacho['fecha_actualizacion'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "❌ No hay despachos en estado 'en_transito'<br><br>";
    }
    
    // 2. Verificar stock_transito
    echo "<h3>📦 Stock en Tránsito</h3>";
    $stmt = $conexionCentral->prepare("
        SELECT id, codigo_producto, descripcion_producto, cantidad_disponible, 
               numero_despacho_origen, id_despacho_origen, transportador_id, nombre_transportador,
               sucursal_origen, fecha_carga
        FROM stock_transito 
        WHERE cantidad_disponible > 0
        ORDER BY fecha_carga DESC
    ");
    $stmt->execute();
    $stockTransito = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($stockTransito) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Despacho</th><th>ID Despacho</th><th>Transportador</th><th>Origen</th><th>Fecha</th></tr>";
        foreach($stockTransito as $stock) {
            echo "<tr>";
            echo "<td>" . $stock['id'] . "</td>";
            echo "<td>" . $stock['codigo_producto'] . "</td>";
            echo "<td>" . substr($stock['descripcion_producto'], 0, 30) . "...</td>";
            echo "<td>" . $stock['cantidad_disponible'] . "</td>";
            echo "<td>" . $stock['numero_despacho_origen'] . "</td>";
            echo "<td>" . $stock['id_despacho_origen'] . "</td>";
            echo "<td>" . ($stock['nombre_transportador'] ?? 'N/A') . "</td>";
            echo "<td>" . $stock['sucursal_origen'] . "</td>";
            echo "<td>" . $stock['fecha_carga'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "❌ No hay productos en stock_transito<br><br>";
    }
    
    // 3. Verificar relación entre despachos y stock_transito
    echo "<h3>🔗 Relación Despachos - Stock en Tránsito</h3>";
    $stmt = $conexionCentral->prepare("
        SELECT 
            d.id as despacho_id,
            d.numero_despacho,
            d.estado as despacho_estado,
            d.transportador_id as despacho_transportador,
            d.nombre_transportador as despacho_nombre_transportador,
            COUNT(st.id) as productos_en_stock,
            SUM(st.cantidad_disponible) as total_cantidad
        FROM despachos d
        LEFT JOIN stock_transito st ON d.id = st.id_despacho_origen
        WHERE d.estado = 'en_transito'
        GROUP BY d.id, d.numero_despacho, d.estado, d.transportador_id, d.nombre_transportador
        ORDER BY d.fecha_actualizacion DESC
    ");
    $stmt->execute();
    $relaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($relaciones) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Despacho ID</th><th>Número</th><th>Estado</th><th>Transportador</th><th>Productos en Stock</th><th>Total Cantidad</th><th>Estado</th></tr>";
        foreach($relaciones as $rel) {
            $estado = $rel['productos_en_stock'] > 0 ? '✅ Con Stock' : '❌ Sin Stock';
            $color = $rel['productos_en_stock'] > 0 ? 'green' : 'red';
            echo "<tr>";
            echo "<td>" . $rel['despacho_id'] . "</td>";
            echo "<td>" . $rel['numero_despacho'] . "</td>";
            echo "<td>" . $rel['despacho_estado'] . "</td>";
            echo "<td>" . ($rel['despacho_nombre_transportador'] ?? 'N/A') . "</td>";
            echo "<td>" . $rel['productos_en_stock'] . "</td>";
            echo "<td>" . $rel['total_cantidad'] . "</td>";
            echo "<td style='color: $color; font-weight: bold;'>$estado</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "❌ No hay despachos en estado 'en_transito'<br><br>";
    }
    
    // 4. Verificar estructura de stock_transito
    echo "<h3>📋 Estructura de Tabla 'stock_transito'</h3>";
    $stmt = $conexionCentral->prepare("DESCRIBE stock_transito");
    $stmt->execute();
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach($campos as $campo) {
        echo "<tr>";
        echo "<td>" . $campo['Field'] . "</td>";
        echo "<td>" . $campo['Type'] . "</td>";
        echo "<td>" . $campo['Null'] . "</td>";
        echo "<td>" . $campo['Key'] . "</td>";
        echo "<td>" . $campo['Default'] . "</td>";
        echo "<td>" . $campo['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
