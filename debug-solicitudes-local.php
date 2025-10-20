<?php
// Script para diagnosticar solicitudes de stock en BD local
require_once "modelos/conexion.php";

echo "<h2>🔍 Diagnóstico de Solicitudes de Stock - BD Local</h2>";

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
        // Mostrar primeras 5 solicitudes
        echo "<h3>📄 Primeras 5 solicitudes en BD Local:</h3>";
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
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h3>🧪 Prueba del endpoint AJAX local:</h3>";
echo "<form method='POST' action='ajax/productos-despacho.ajax.php'>";
echo "<input type='hidden' name='buscarSolicitudes' value='1'>";
echo "<input type='text' name='termino' placeholder='Término de búsqueda' value='SOL'>";
echo "<button type='submit'>Probar AJAX</button>";
echo "</form>";
?>
