<?php
session_start();
require_once "api-transferencias/conexion-central.php";

echo "<h2>🔍 Debug de Stock en Tránsito</h2>";

try {
    // 1. Verificar estructura de la tabla
    echo "<h3>📋 Estructura de la tabla stock_transito:</h3>";
    $stmt = ConexionCentral::conectar()->prepare("DESCRIBE stock_transito");
    $stmt->execute();
    $estructura = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Por defecto</th></tr>";
    foreach($estructura as $campo) {
        echo "<tr><td><strong>{$campo['Field']}</strong></td><td>{$campo['Type']}</td><td>{$campo['Null']}</td><td>" . ($campo['Default'] ?? 'NULL') . "</td></tr>";
    }
    echo "</table>";
    
    // 2. Verificar datos actuales
    echo "<h3>📦 Datos actuales en stock_transito:</h3>";
    $stmt2 = ConexionCentral::conectar()->prepare("
        SELECT 
            id,
            codigo_producto,
            descripcion_producto,
            cantidad_disponible,
            transportador_id,
            nombre_transportador,
            sucursal_origen,
            id_despacho_origen,
            numero_despacho_origen,
            fecha_carga
        FROM stock_transito 
        ORDER BY fecha_carga DESC 
        LIMIT 10
    ");
    $stmt2->execute();
    $registros = $stmt2->fetchAll();
    
    if(count($registros) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach(array_keys($registros[0]) as $key) {
            if(!is_numeric($key)) {
                echo "<th>{$key}</th>";
            }
        }
        echo "</tr>";
        
        foreach($registros as $registro) {
            echo "<tr>";
            foreach($registro as $key => $valor) {
                if(!is_numeric($key)) {
                    echo "<td>" . htmlspecialchars($valor) . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ <strong>NO HAY DATOS en la tabla stock_transito</strong></p>";
    }
    
    // 3. Verificar conteos
    echo "<h3>📊 Estadísticas:</h3>";
    $stmt3 = ConexionCentral::conectar()->prepare("
        SELECT 
            COUNT(*) as total_registros,
            COUNT(DISTINCT codigo_producto) as productos_diferentes,
            SUM(cantidad_disponible) as total_unidades,
            COUNT(DISTINCT transportador_id) as transportadores_diferentes
        FROM stock_transito
    ");
    $stmt3->execute();
    $stats = $stmt3->fetch();
    
    echo "<ul>";
    echo "<li><strong>Total registros:</strong> " . $stats['total_registros'] . "</li>";
    echo "<li><strong>Productos diferentes:</strong> " . $stats['productos_diferentes'] . "</li>";
    echo "<li><strong>Total unidades:</strong> " . $stats['total_unidades'] . "</li>";
    echo "<li><strong>Transportadores diferentes:</strong> " . $stats['transportadores_diferentes'] . "</li>";
    echo "</ul>";
    
    // 4. Verificar despachos en tránsito
    echo "<h3>🚛 Despachos en estado 'en_transito':</h3>";
    $stmt4 = ConexionCentral::conectar()->prepare("
        SELECT 
            id,
            numero_despacho,
            estado,
            total_productos,
            total_cantidad,
            transportador_id,
            nombre_transportador,
            fecha_actualizacion
        FROM despachos 
        WHERE estado = 'en_transito'
        ORDER BY fecha_actualizacion DESC
    ");
    $stmt4->execute();
    $despachosEnTransito = $stmt4->fetchAll();
    
    if(count($despachosEnTransito) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Número</th><th>Estado</th><th>Productos</th><th>Cantidad</th><th>Transportador</th><th>Fecha Actualización</th></tr>";
        
        foreach($despachosEnTransito as $despacho) {
            echo "<tr>";
            echo "<td>{$despacho['id']}</td>";
            echo "<td><strong>{$despacho['numero_despacho']}</strong></td>";
            echo "<td><span style='color: green;'>{$despacho['estado']}</span></td>";
            echo "<td>{$despacho['total_productos']}</td>";
            echo "<td>{$despacho['total_cantidad']}</td>";
            echo "<td>{$despacho['nombre_transportador']}</td>";
            echo "<td>{$despacho['fecha_actualizacion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si estos despachos tienen stock en tránsito
        echo "<h4>🔍 Verificando stock en tránsito para estos despachos:</h4>";
        foreach($despachosEnTransito as $despacho) {
            $stmt5 = ConexionCentral::conectar()->prepare("
                SELECT COUNT(*) as registros
                FROM stock_transito 
                WHERE id_despacho_origen = :id_despacho
            ");
            $stmt5->bindParam(":id_despacho", $despacho['id']);
            $stmt5->execute();
            $stockCount = $stmt5->fetch();
            
            $status = $stockCount['registros'] > 0 ? "✅ {$stockCount['registros']} registros" : "❌ Sin registros";
            echo "<p>Despacho {$despacho['numero_despacho']}: <strong>{$status}</strong></p>";
        }
        
    } else {
        echo "<p>❌ <strong>NO HAY despachos en estado 'en_transito'</strong></p>";
    }
    
    // 5. Información de sesión
    echo "<h3>👤 Información de sesión actual:</h3>";
    echo "<ul>";
    echo "<li><strong>Usuario ID:</strong> " . ($_SESSION['id'] ?? 'No definido') . "</li>";
    echo "<li><strong>Usuario:</strong> " . ($_SESSION['nombre'] ?? 'No definido') . "</li>";
    echo "<li><strong>Perfil:</strong> " . ($_SESSION['perfil'] ?? 'No definido') . "</li>";
    echo "</ul>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>