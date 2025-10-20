<?php
// Script para verificar solicitudes de stock en BD local
require_once "modelos/conexion.php";

echo "<h2>🔍 Verificación de Solicitudes de Stock - BD Local</h2>";

try {
    $pdo = Conexion::conectar();
    
    // Verificar si la tabla existe
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'solicitudes_stock'");
    $stmt->execute();
    $tablaExiste = $stmt->fetch();
    
    if (!$tablaExiste) {
        echo "❌ La tabla 'solicitudes_stock' NO existe en la BD Local<br>";
        exit;
    }
    
    echo "✅ La tabla 'solicitudes_stock' existe en BD Local<br><br>";
    
    // Contar total de solicitudes
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM solicitudes_stock");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    
    echo "📊 Total de solicitudes en BD Local: $total<br><br>";
    
    if ($total > 0) {
        // Mostrar todas las solicitudes
        echo "<h3>📄 Todas las solicitudes en BD Local:</h3>";
        $stmt = $pdo->prepare("
            SELECT 
                id,
                numero_solicitud,
                nombre_usuario_solicitante,
                nombre_sucursal_solicitante,
                estado,
                fecha_solicitud
            FROM solicitudes_stock 
            ORDER BY fecha_solicitud DESC
        ");
        $stmt->execute();
        $solicitudes = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Número</th><th>Usuario</th><th>Sucursal</th><th>Estado</th><th>Fecha</th></tr>";
        foreach ($solicitudes as $solicitud) {
            echo "<tr>";
            echo "<td>" . $solicitud['id'] . "</td>";
            echo "<td>" . $solicitud['numero_solicitud'] . "</td>";
            echo "<td>" . $solicitud['nombre_usuario_solicitante'] . "</td>";
            echo "<td>" . $solicitud['nombre_sucursal_solicitante'] . "</td>";
            echo "<td>" . $solicitud['estado'] . "</td>";
            echo "<td>" . $solicitud['fecha_solicitud'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Probar búsqueda específica
        echo "<h3>🔍 Búsqueda específica de 'SOL000005':</h3>";
        $stmt = $pdo->prepare("
            SELECT 
                id,
                numero_solicitud,
                nombre_usuario_solicitante,
                nombre_sucursal_solicitante,
                estado
            FROM solicitudes_stock 
            WHERE numero_solicitud LIKE :termino
            ORDER BY fecha_solicitud DESC
        ");
        
        $terminoBusqueda = "%SOL000005%";
        $stmt->bindParam(":termino", $terminoBusqueda, PDO::PARAM_STR);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        
        echo "Resultados encontrados: " . count($resultados) . "<br>";
        
        if (count($resultados) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Número</th><th>Usuario</th><th>Sucursal</th><th>Estado</th></tr>";
            foreach ($resultados as $resultado) {
                echo "<tr>";
                echo "<td>" . $resultado['id'] . "</td>";
                echo "<td>" . $resultado['numero_solicitud'] . "</td>";
                echo "<td>" . $resultado['nombre_usuario_solicitante'] . "</td>";
                echo "<td>" . $resultado['nombre_sucursal_solicitante'] . "</td>";
                echo "<td>" . $resultado['estado'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "⚠️ No hay solicitudes de stock en la BD Local<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
