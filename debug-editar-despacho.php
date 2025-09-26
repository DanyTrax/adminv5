<?php
session_start();

if(isset($_GET['editar'])) {
    $idDespacho = $_GET['editar'];
    
    echo "<h2>🔍 Debugging edición de despacho ID: $idDespacho</h2>";
    
    try {
        // Verificar conexión a BD central
        echo "<h3>🔗 Verificando conexión a BD central:</h3>";
        require_once "api-transferencias/conexion-central.php";
        $conexion = ConexionCentral::conectar();
        echo "<p>✅ Conexión establecida</p>";
        
        // Buscar el despacho directamente en la BD
        $stmt = $conexion->prepare("SELECT * FROM despachos WHERE id = :id");
        $stmt->bindParam(":id", $idDespacho);
        $stmt->execute();
        $despacho = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($despacho) {
            echo "<h3>✅ Despacho encontrado:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            foreach($despacho as $key => $value) {
                echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
            }
            echo "</table>";
            
            echo "<h3>🔍 Productos del despacho:</h3>";
            $productos = json_decode($despacho["productos_despacho"], true);
            if($productos) {
                echo "<p>✅ JSON parseado correctamente. Total productos: " . count($productos) . "</p>";
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Observación</th></tr>";
                foreach($productos as $producto) {
                    echo "<tr>";
                    echo "<td>" . ($producto['codigo'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($producto['descripcion'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($producto['cantidad'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($producto['observacion'] ?? 'Sin observación') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>❌ Error al parsear productos JSON</p>";
                echo "<p><strong>JSON original:</strong></p>";
                echo "<pre>" . htmlspecialchars($despacho["productos_despacho"]) . "</pre>";
                echo "<p><strong>Error JSON:</strong> " . json_last_error_msg() . "</p>";
            }
            
            // Verificar si el despacho está pendiente para edición
            if($despacho["estado"] == "pendiente") {
                echo "<h3>✅ Despacho disponible para edición</h3>";
            } else {
                echo "<h3>⚠️ Despacho en estado: " . $despacho["estado"] . " (no editable)</h3>";
            }
            
        } else {
            echo "<p>❌ Despacho no encontrado en la base de datos</p>";
            
            // Verificar si hay despachos en la tabla
            $stmtCount = $conexion->prepare("SELECT COUNT(*) as total FROM despachos");
            $stmtCount->execute();
            $total = $stmtCount->fetch(PDO::FETCH_ASSOC);
            echo "<p>Total de despachos en la tabla: " . $total['total'] . "</p>";
            
            // Mostrar los últimos despachos
            $stmtUltimos = $conexion->prepare("SELECT id, numero_despacho, estado FROM despachos ORDER BY id DESC LIMIT 5");
            $stmtUltimos->execute();
            $ultimos = $stmtUltimos->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>Últimos 5 despachos:</h4>";
            echo "<ul>";
            foreach($ultimos as $u) {
                echo "<li>ID: {$u['id']} - {$u['numero_despacho']} - Estado: {$u['estado']}</li>";
            }
            echo "</ul>";
        }
        
    } catch(Exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
        echo "<p><strong>Stack trace:</strong></p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    echo "<p>❌ No se proporcionó ID de despacho</p>";
    echo "<p><strong>URL esperada:</strong> debug-editar-despacho.php?editar=ID_DEL_DESPACHO</p>";
}
?>