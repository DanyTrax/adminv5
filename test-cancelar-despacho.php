<?php
/**
 * Script para probar la cancelación de despachos paso a paso
 */

echo "<h2>🧪 Test - Cancelar Despacho</h2>\n";
echo "<hr>\n";

// Simular sesión
session_start();
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";
$_SESSION["perfil"] = "Administrador";

try {
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    echo "✅ <strong>Conexión a base central exitosa</strong><br>\n";
    
    // Buscar un despacho pendiente para probar
    $stmt = $conexion->query("SELECT * FROM despachos WHERE estado = 'pendiente' ORDER BY id DESC LIMIT 1");
    $despacho = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$despacho) {
        echo "❌ <strong>No hay despachos pendientes para probar</strong><br>\n";
        exit;
    }
    
    echo "<h3>📋 Despacho encontrado para probar:</h3>\n";
    echo "<strong>ID:</strong> " . $despacho["id"] . "<br>\n";
    echo "<strong>Número:</strong> " . $despacho["numero_despacho"] . "<br>\n";
    echo "<strong>Estado actual:</strong> " . $despacho["estado"] . "<br>\n";
    
    echo "<hr>\n";
    
    echo "<h3>🧪 Paso 1: Simular datos de cancelación</h3>\n";
    
    $idDespacho = $despacho["id"];
    $motivoCancelacion = "Prueba de cancelación desde script";
    
    $datos = array(
        "id" => $idDespacho,
        "estado" => "cancelado",
        "motivo_cancelacion" => $motivoCancelacion,
        "usuario_cancelacion" => $_SESSION["nombre"]
    );
    
    echo "<h4>📝 Datos a actualizar:</h4>\n";
    echo "<pre>" . print_r($datos, true) . "</pre>\n";
    
    echo "<h3>🧪 Paso 2: Ejecutar actualización</h3>\n";
    
    // Construir SQL como lo hace la función
    $campos = [];
    $valoresArray = [];
    
    foreach($datos as $key => $value) {
        $campos[] = "`$key` = ?";
        $valoresArray[] = $value;
    }
    
    $valoresArray[] = $idDespacho; // ID para WHERE
    $sql = "UPDATE `despachos` SET " . implode(", ", $campos) . " WHERE `id` = ?";
    
    echo "<h4>🔍 SQL generado:</h4>\n";
    echo "<pre>$sql</pre>\n";
    
    echo "<h4>📊 Valores:</h4>\n";
    echo "<pre>" . print_r($valoresArray, true) . "</pre>\n";
    
    // Ejecutar la actualización
    $stmt = $conexion->prepare($sql);
    $resultado = $stmt->execute($valoresArray);
    
    if($resultado) {
        echo "✅ <strong>UPDATE ejecutado exitosamente</strong><br>\n";
        echo "📊 <strong>Filas afectadas:</strong> " . $stmt->rowCount() . "<br>\n";
    } else {
        echo "❌ <strong>UPDATE falló</strong><br>\n";
        $errorInfo = $stmt->errorInfo();
        echo "🔍 <strong>Error PDO:</strong> " . print_r($errorInfo, true) . "<br>\n";
        exit;
    }
    
    echo "<h3>🧪 Paso 3: Verificar el cambio</h3>\n";
    
    $stmt = $conexion->prepare("SELECT id, numero_despacho, estado, motivo_cancelacion, usuario_cancelacion FROM despachos WHERE id = ?");
    $stmt->execute([$idDespacho]);
    $despachoActualizado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h4>📋 Despacho después de actualizar:</h4>\n";
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Campo</th><th>Valor</th></tr>\n";
    foreach($despachoActualizado as $campo => $valor) {
        $color = ($campo == 'estado' && $valor == 'cancelado') ? 'background-color: #d4edda;' : '';
        echo "<tr style='$color'>";
        echo "<td><strong>$campo</strong></td>";
        echo "<td>$valor</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    if($despachoActualizado["estado"] == "cancelado") {
        echo "✅ <strong>¡ÉXITO! El despacho se canceló correctamente</strong><br>\n";
    } else {
        echo "❌ <strong>FALLO: El estado no cambió a 'cancelado'</strong><br>\n";
        echo "🔍 <strong>Estado actual:</strong> " . $despachoActualizado["estado"] . "<br>\n";
    }
    
    echo "<h3>🧪 Paso 4: Restaurar despacho para futuras pruebas</h3>\n";
    
    $stmt = $conexion->prepare("UPDATE despachos SET estado = 'pendiente', motivo_cancelacion = NULL, usuario_cancelacion = NULL WHERE id = ?");
    $stmt->execute([$idDespacho]);
    
    echo "✅ <strong>Despacho restaurado a estado 'pendiente' para futuras pruebas</strong><br>\n";
    
} catch(Exception $e) {
    echo "❌ <strong>Error general:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<hr>\n";
echo "<p><strong>🎯 Test completado.</strong></p>\n";
?>
