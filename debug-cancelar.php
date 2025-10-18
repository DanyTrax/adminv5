<?php
/**
 * Debug específico para cancelación de despachos
 */

// Simular sesión
session_start();
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";
$_SESSION["perfil"] = "Administrador";

echo "<h2>🔍 Debug - Cancelación de Despachos</h2>";
echo "<hr>";

try {
    // 1. Conectar a la base central
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    echo "✅ <strong>Conexión exitosa</strong><br><br>";
    
    // 2. Buscar despacho pendiente
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
    
    // 3. Simular exactamente lo que hace el AJAX
    echo "<h3>🧪 Paso 1: Simular mdlActualizarDespacho</h3>";
    
    $datos = array(
        "id" => $idDespacho,
        "estado" => "cancelado",
        "motivo_cancelacion" => "Prueba de cancelación",
        "usuario_cancelacion" => "Administrador"
    );
    
    echo "<strong>Datos a actualizar:</strong><br>";
    echo "<pre>" . print_r($datos, true) . "</pre>";
    
    // Construir SQL como lo hace la función
    $campos = [];
    $valoresArray = [];
    
    foreach($datos as $key => $value) {
        $campos[] = "`$key` = ?";
        $valoresArray[] = $value;
    }
    
    $valoresArray[] = $idDespacho; // ID para WHERE
    $sql = "UPDATE `despachos` SET " . implode(", ", $campos) . " WHERE `id` = ?";
    
    echo "<strong>SQL generado:</strong><br>";
    echo "<pre>$sql</pre>";
    
    echo "<strong>Valores:</strong><br>";
    echo "<pre>" . print_r($valoresArray, true) . "</pre>";
    
    // 4. Ejecutar la actualización
    echo "<h3>🧪 Paso 2: Ejecutar actualización</h3>";
    
    $stmt = $conexion->prepare($sql);
    $resultado = $stmt->execute($valoresArray);
    
    if($resultado) {
        echo "✅ <strong>execute() devolvió true</strong><br>";
        echo "📊 <strong>Filas afectadas:</strong> " . $stmt->rowCount() . "<br>";
        
        if($stmt->rowCount() > 0) {
            echo "✅ <strong>Se afectaron filas - actualización exitosa</strong><br>";
        } else {
            echo "⚠️ <strong>No se afectaron filas - posible problema</strong><br>";
        }
    } else {
        echo "❌ <strong>execute() devolvió false</strong><br>";
        $errorInfo = $stmt->errorInfo();
        echo "Error: " . print_r($errorInfo, true) . "<br>";
    }
    
    // 5. Verificar el cambio
    echo "<h3>🧪 Paso 3: Verificar cambio</h3>";
    
    $stmt = $conexion->query("SELECT id, numero_despacho, estado, motivo_cancelacion, usuario_cancelacion FROM despachos WHERE id = $idDespacho");
    $despachoActualizado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<strong>Despacho después de actualizar:</strong><br>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    foreach($despachoActualizado as $campo => $valor) {
        $color = ($campo == 'estado' && $valor == 'cancelado') ? 'background-color: #d4edda;' : '';
        echo "<tr style='$color'>";
        echo "<td><strong>$campo</strong></td>";
        echo "<td>$valor</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if($despachoActualizado["estado"] == "cancelado") {
        echo "<br>✅ <strong>¡ÉXITO! El despacho se canceló correctamente</strong><br>";
    } else {
        echo "<br>❌ <strong>FALLO: El estado no cambió a 'cancelado'</strong><br>";
        echo "Estado actual: " . $despachoActualizado["estado"] . "<br>";
    }
    
    // 6. Restaurar para futuras pruebas
    echo "<h3>🔄 Paso 4: Restaurar despacho</h3>";
    
    $stmt = $conexion->prepare("UPDATE despachos SET estado = 'pendiente', motivo_cancelacion = NULL, usuario_cancelacion = NULL WHERE id = ?");
    $stmt->execute([$idDespacho]);
    
    echo "✅ <strong>Despacho restaurado a 'pendiente'</strong><br>";
    
} catch(Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<hr>";
echo "<p><strong>🎯 Debug completado.</strong></p>";
?>
