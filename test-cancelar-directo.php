<?php
/**
 * Test directo de cancelación - Sin usar funciones del sistema
 */

// Simular sesión
session_start();
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";

echo "<h2>🧪 Test Directo - Cancelación de Despachos</h2>";
echo "<hr>";

try {
    // Conectar directamente a la base central
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    echo "✅ <strong>Conexión exitosa</strong><br><br>";
    
    // Buscar despacho pendiente
    $stmt = $conexion->query("SELECT id, numero_despacho, estado FROM despachos WHERE estado = 'pendiente' ORDER BY id DESC LIMIT 1");
    $despacho = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$despacho) {
        echo "❌ <strong>No hay despachos pendientes</strong><br>";
        exit;
    }
    
    echo "📋 <strong>Despacho encontrado:</strong><br>";
    echo "ID: " . $despacho["id"] . "<br>";
    echo "Número: " . $despacho["numero_despacho"] . "<br>";
    echo "Estado: " . $despacho["estado"] . "<br><br>";
    
    $idDespacho = $despacho["id"];
    
    // Cancelar directamente
    echo "<h3>🧪 Cancelando despacho...</h3>";
    
    $sql = "UPDATE despachos SET estado = 'cancelado', motivo_cancelacion = ?, usuario_cancelacion = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $resultado = $stmt->execute(["Prueba directa", "Administrador", $idDespacho]);
    
    echo "<strong>SQL:</strong> $sql<br>";
    echo "<strong>Resultado execute:</strong> " . ($resultado ? "true" : "false") . "<br>";
    echo "<strong>Filas afectadas:</strong> " . $stmt->rowCount() . "<br><br>";
    
    if($resultado && $stmt->rowCount() > 0) {
        echo "✅ <strong>UPDATE ejecutado exitosamente</strong><br><br>";
        
        // Verificar cambio
        $stmt = $conexion->query("SELECT id, numero_despacho, estado, motivo_cancelacion, usuario_cancelacion FROM despachos WHERE id = $idDespacho");
        $despachoActualizado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Despacho después de cancelar:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        foreach($despachoActualizado as $campo => $valor) {
            $color = ($campo == 'estado' && $valor == 'cancelado') ? 'background-color: #d4edda;' : '';
            echo "<tr style='$color'>";
            echo "<td><strong>$campo</strong></td>";
            echo "<td>$valor</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        if($despachoActualizado["estado"] == "cancelado") {
            echo "✅ <strong>¡ÉXITO! El despacho se canceló correctamente</strong><br>";
        } else {
            echo "❌ <strong>FALLO: El estado no cambió a 'cancelado'</strong><br>";
        }
        
        // Restaurar para futuras pruebas
        echo "<h3>🔄 Restaurando despacho...</h3>";
        $stmt = $conexion->prepare("UPDATE despachos SET estado = 'pendiente', motivo_cancelacion = NULL, usuario_cancelacion = NULL WHERE id = ?");
        $stmt->execute([$idDespacho]);
        echo "✅ <strong>Despacho restaurado a 'pendiente'</strong><br>";
        
    } else {
        echo "❌ <strong>UPDATE falló</strong><br>";
        $errorInfo = $stmt->errorInfo();
        echo "Error: " . print_r($errorInfo, true) . "<br>";
    }
    
} catch(Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>🎯 Test directo completado.</strong></p>";
?>
