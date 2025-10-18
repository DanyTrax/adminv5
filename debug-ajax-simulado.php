<?php
/**
 * Simular exactamente el flujo del AJAX de cancelación
 */

// Simular sesión
session_start();
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";
$_SESSION["perfil"] = "Administrador";

echo "<h2>🔍 Debug - Simulación del Flujo AJAX</h2>";
echo "<hr>";

try {
    // 1. Simular $_POST
    $_POST["cancelarDespacho"] = "18"; // ID del despacho
    $_POST["motivoCancelacion"] = "Prueba de cancelación AJAX";
    
    echo "<h3>📋 Datos POST simulados:</h3>";
    echo "cancelarDespacho: " . $_POST["cancelarDespacho"] . "<br>";
    echo "motivoCancelacion: " . $_POST["motivoCancelacion"] . "<br><br>";
    
    // 2. Simular el código del AJAX
    $idDespacho = $_POST["cancelarDespacho"];
    $motivoCancelacion = $_POST["motivoCancelacion"] ?? "Sin motivo especificado";
    
    echo "<h3>🧪 Paso 1: Obtener despacho</h3>";
    
    // Incluir las clases necesarias
    require_once "controladores/despachos.controlador.php";
    
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
    
    if(!$despacho) {
        echo "❌ <strong>Despacho no encontrado</strong><br>";
        exit;
    }
    
    echo "✅ <strong>Despacho encontrado:</strong><br>";
    echo "ID: " . $despacho["id"] . "<br>";
    echo "Número: " . $despacho["numero_despacho"] . "<br>";
    echo "Estado: " . $despacho["estado"] . "<br><br>";
    
    // 3. Verificar estado
    echo "<h3>🧪 Paso 2: Verificar estado</h3>";
    
    if($despacho["estado"] != "pendiente") {
        echo "❌ <strong>Solo se pueden cancelar despachos pendientes. Estado actual: " . $despacho["estado"] . "</strong><br>";
        exit;
    }
    
    echo "✅ <strong>Estado válido para cancelación</strong><br><br>";
    
    // 4. Simular la actualización directa
    echo "<h3>🧪 Paso 3: Actualización directa</h3>";
    
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    
    echo "✅ <strong>Conexión a base central exitosa</strong><br>";
    
    $sql = "UPDATE despachos SET estado = 'cancelado', motivo_cancelacion = ?, usuario_cancelacion = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $resultado = $stmt->execute([$motivoCancelacion, $_SESSION["nombre"] ?? "Usuario", $idDespacho]);
    
    echo "<strong>SQL:</strong> $sql<br>";
    echo "<strong>Resultado execute:</strong> " . ($resultado ? "true" : "false") . "<br>";
    echo "<strong>Filas afectadas:</strong> " . $stmt->rowCount() . "<br><br>";
    
    if($resultado && $stmt->rowCount() > 0) {
        echo "✅ <strong>UPDATE ejecutado exitosamente</strong><br><br>";
        
        // 5. Verificar cambio
        echo "<h3>🧪 Paso 4: Verificar cambio</h3>";
        
        $stmt = $conexion->query("SELECT estado FROM despachos WHERE id = $idDespacho");
        $estadoActualizado = $stmt->fetchColumn();
        
        echo "<strong>Estado después de actualizar:</strong> " . $estadoActualizado . "<br><br>";
        
        if($estadoActualizado == "cancelado") {
            echo "✅ <strong>¡ÉXITO! El despacho se canceló correctamente</strong><br>";
            
            // Simular la respuesta JSON
            $respuesta = [
                "success" => true, 
                "message" => "Despacho cancelado correctamente",
                "estado_actualizado" => $estadoActualizado
            ];
            
            echo "<h3>📨 Respuesta JSON simulada:</h3>";
            echo "<pre>" . json_encode($respuesta, JSON_PRETTY_PRINT) . "</pre>";
            
        } else {
            echo "❌ <strong>FALLO: El estado no cambió a 'cancelado'</strong><br>";
        }
        
        // 6. Restaurar para futuras pruebas
        echo "<h3>🔄 Paso 5: Restaurar despacho</h3>";
        
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
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<hr>";
echo "<p><strong>🎯 Simulación del flujo AJAX completada.</strong></p>";
?>
