<?php
// Script para corregir registros de stock_transito con id_despacho_origen NULL
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Corrección de Stock en Tránsito</h2>";

try {
    require_once "api-transferencias/conexion-central.php";
    
    $conexionCentral = ConexionCentral::conectar();
    echo "✅ Conexión a base central exitosa<br><br>";
    
    // 1. Buscar registros con id_despacho_origen NULL
    echo "<h3>🔍 Registros con id_despacho_origen NULL</h3>";
    $stmt = $conexionCentral->prepare("
        SELECT id, codigo_producto, numero_despacho_origen, transportador_id, nombre_transportador
        FROM stock_transito 
        WHERE id_despacho_origen IS NULL
        ORDER BY fecha_carga DESC
    ");
    $stmt->execute();
    $registrosNulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($registrosNulos) > 0) {
        echo "<p>Se encontraron " . count($registrosNulos) . " registros con id_despacho_origen NULL</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Stock</th><th>Código</th><th>Número Despacho</th><th>Transportador</th><th>Acción</th></tr>";
        
        foreach($registrosNulos as $registro) {
            // Buscar el despacho correspondiente
            $stmtDespacho = $conexionCentral->prepare("
                SELECT id, numero_despacho, estado, transportador_id, nombre_transportador
                FROM despachos 
                WHERE numero_despacho = ? 
                AND transportador_id = ?
                AND estado = 'en_transito'
                ORDER BY fecha_actualizacion DESC
                LIMIT 1
            ");
            $stmtDespacho->execute([
                $registro['numero_despacho_origen'],
                $registro['transportador_id']
            ]);
            $despacho = $stmtDespacho->fetch(PDO::FETCH_ASSOC);
            
            if($despacho) {
                echo "<tr>";
                echo "<td>" . $registro['id'] . "</td>";
                echo "<td>" . $registro['codigo_producto'] . "</td>";
                echo "<td>" . $registro['numero_despacho_origen'] . "</td>";
                echo "<td>" . $registro['nombre_transportador'] . "</td>";
                echo "<td style='color: green;'>✅ Despacho encontrado (ID: " . $despacho['id'] . ")</td>";
                echo "</tr>";
                
                // Actualizar el registro
                $stmtUpdate = $conexionCentral->prepare("
                    UPDATE stock_transito 
                    SET id_despacho_origen = ? 
                    WHERE id = ?
                ");
                $stmtUpdate->execute([$despacho['id'], $registro['id']]);
                
            } else {
                echo "<tr>";
                echo "<td>" . $registro['id'] . "</td>";
                echo "<td>" . $registro['codigo_producto'] . "</td>";
                echo "<td>" . $registro['numero_despacho_origen'] . "</td>";
                echo "<td>" . $registro['nombre_transportador'] . "</td>";
                echo "<td style='color: red;'>❌ Despacho no encontrado</td>";
                echo "</tr>";
            }
        }
        echo "</table><br>";
        
    } else {
        echo "✅ No hay registros con id_despacho_origen NULL<br><br>";
    }
    
    // 2. Verificar corrección
    echo "<h3>✅ Verificación Post-Corrección</h3>";
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
    }
    
    // 3. Mostrar stock_transito corregido
    echo "<h3>📦 Stock en Tránsito Corregido</h3>";
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
            $idDespacho = $stock['id_despacho_origen'] ? $stock['id_despacho_origen'] : 'NULL';
            $color = $stock['id_despacho_origen'] ? 'black' : 'red';
            echo "<tr>";
            echo "<td>" . $stock['id'] . "</td>";
            echo "<td>" . $stock['codigo_producto'] . "</td>";
            echo "<td>" . substr($stock['descripcion_producto'], 0, 30) . "...</td>";
            echo "<td>" . $stock['cantidad_disponible'] . "</td>";
            echo "<td>" . $stock['numero_despacho_origen'] . "</td>";
            echo "<td style='color: $color; font-weight: bold;'>$idDespacho</td>";
            echo "<td>" . ($stock['nombre_transportador'] ?? 'N/A') . "</td>";
            echo "<td>" . $stock['sucursal_origen'] . "</td>";
            echo "<td>" . $stock['fecha_carga'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    echo "<h3>🎉 Corrección Completada</h3>";
    echo "<p>Los registros de stock_transito han sido actualizados con los id_despacho_origen correspondientes.</p>";
    echo "<p>Ahora la nueva interfaz de stock en tránsito debería mostrar los productos correctamente.</p>";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
