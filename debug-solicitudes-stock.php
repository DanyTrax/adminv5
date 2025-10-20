<?php
// Script para diagnosticar solicitudes de stock
require_once "api-transferencias/conexion-central.php";

echo "<h2>🔍 Diagnóstico de Solicitudes de Stock</h2>";

try {
    $pdo = ConexionCentral::conectar();
    
    // Verificar si la tabla existe
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'solicitudes_stock'");
    $stmt->execute();
    $tablaExiste = $stmt->fetch();
    
    if (!$tablaExiste) {
        echo "❌ La tabla 'solicitudes_stock' NO existe en la BD Central<br>";
        exit;
    }
    
    echo "✅ La tabla 'solicitudes_stock' existe<br><br>";
    
    // Contar total de solicitudes
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM solicitudes_stock");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    
    echo "📊 Total de solicitudes: $total<br><br>";
    
    if ($total > 0) {
        // Mostrar estructura de la tabla
        echo "<h3>📋 Estructura de la tabla:</h3>";
        $stmt = $pdo->prepare("DESCRIBE solicitudes_stock");
        $stmt->execute();
        $estructura = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
        foreach ($estructura as $campo) {
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
        
        // Mostrar primeras 5 solicitudes
        echo "<h3>📄 Primeras 5 solicitudes:</h3>";
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
            LIMIT 5
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
        
        // Probar búsqueda
        echo "<h3>🔍 Prueba de búsqueda:</h3>";
        $termino = "SOL";
        $stmt = $pdo->prepare("
            SELECT 
                id,
                numero_solicitud,
                nombre_usuario_solicitante,
                nombre_sucursal_solicitante,
                estado
            FROM solicitudes_stock 
            WHERE estado IN ('aprobado', 'pendiente')
            AND (numero_solicitud LIKE :termino 
                 OR nombre_usuario_solicitante LIKE :termino
                 OR nombre_sucursal_solicitante LIKE :termino)
            ORDER BY fecha_solicitud DESC
            LIMIT 10
        ");
        
        $terminoBusqueda = "%" . $termino . "%";
        $stmt->bindParam(":termino", $terminoBusqueda, PDO::PARAM_STR);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        
        echo "Búsqueda con término '$termino': " . count($resultados) . " resultados<br>";
        
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
        echo "⚠️ No hay solicitudes de stock en la BD Central<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>🧪 Prueba del endpoint AJAX:</h3>";
echo "<form method='POST' action='ajax/productos-despacho.ajax.php'>";
echo "<input type='hidden' name='buscarSolicitudes' value='1'>";
echo "<input type='text' name='termino' placeholder='Término de búsqueda' value='SOL'>";
echo "<button type='submit'>Probar AJAX</button>";
echo "</form>";
?>
