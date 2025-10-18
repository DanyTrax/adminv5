<?php
/**
 * Debug específico del flujo AJAX de cancelación
 */

// Simular sesión
session_start();
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";
$_SESSION["perfil"] = "Administrador";

echo "<h2>🔍 Debug - Flujo AJAX de Cancelación</h2>";
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
    
    // 3. Simular el flujo exacto del AJAX
    echo "<h3>🧪 Paso 1: Simular ctrMostrarDespachos (antes)</h3>";
    
    require_once "controladores/despachos.controlador.php";
    $despachoAntes = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
    
    echo "<strong>Despacho ANTES de actualizar:</strong><br>";
    echo "ID: " . $despachoAntes["id"] . "<br>";
    echo "Estado: " . $despachoAntes["estado"] . "<br><br>";
    
    // 4. Simular mdlActualizarDespacho
    echo "<h3>🧪 Paso 2: Simular mdlActualizarDespacho</h3>";
    
    $datos = array(
        "id" => $idDespacho,
        "estado" => "cancelado",
        "motivo_cancelacion" => "Prueba de cancelación AJAX",
        "usuario_cancelacion" => $_SESSION["nombre"]
    );
    
    require_once "modelos/despachos.modelo.php";
    $respuesta = ModeloDespachos::mdlActualizarDespacho("despachos", $datos, "id", $idDespacho);
    
    echo "<strong>Respuesta de mdlActualizarDespacho:</strong> " . $respuesta . "<br><br>";
    
    if($respuesta == "ok") {
        echo "✅ <strong>mdlActualizarDespacho devolvió 'ok'</strong><br><br>";
        
        // 5. Simular ctrMostrarDespachos (después)
        echo "<h3>🧪 Paso 3: Simular ctrMostrarDespachos (después)</h3>";
        
        $despachoDespues = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        echo "<strong>Despacho DESPUÉS de actualizar:</strong><br>";
        echo "ID: " . $despachoDespues["id"] . "<br>";
        echo "Estado: " . $despachoDespues["estado"] . "<br>";
        echo "Motivo: " . $despachoDespues["motivo_cancelacion"] . "<br>";
        echo "Usuario: " . $despachoDespues["usuario_cancelacion"] . "<br><br>";
        
        if($despachoDespues["estado"] == "cancelado") {
            echo "✅ <strong>¡ÉXITO! El estado se actualizó correctamente</strong><br>";
        } else {
            echo "❌ <strong>FALLO: El estado no cambió a 'cancelado'</strong><br>";
            echo "Estado actual: " . $despachoDespues["estado"] . "<br>";
        }
        
        // 6. Verificar directamente en la base de datos
        echo "<h3>🧪 Paso 4: Verificación directa en BD</h3>";
        
        $stmt = $conexion->query("SELECT id, estado, motivo_cancelacion, usuario_cancelacion FROM despachos WHERE id = $idDespacho");
        $despachoBD = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<strong>Verificación directa en BD:</strong><br>";
        echo "ID: " . $despachoBD["id"] . "<br>";
        echo "Estado: " . $despachoBD["estado"] . "<br>";
        echo "Motivo: " . $despachoBD["motivo_cancelacion"] . "<br>";
        echo "Usuario: " . $despachoBD["usuario_cancelacion"] . "<br><br>";
        
        if($despachoBD["estado"] == "cancelado") {
            echo "✅ <strong>BD confirma: Estado es 'cancelado'</strong><br>";
        } else {
            echo "❌ <strong>BD confirma: Estado NO es 'cancelado'</strong><br>";
        }
        
        // 7. Comparar resultados
        echo "<h3>🧪 Paso 5: Comparación de resultados</h3>";
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Fuente</th><th>Estado</th><th>Motivo</th><th>Usuario</th></tr>";
        
        echo "<tr>";
        echo "<td><strong>ctrMostrarDespachos</strong></td>";
        echo "<td>" . $despachoDespues["estado"] . "</td>";
        echo "<td>" . $despachoDespues["motivo_cancelacion"] . "</td>";
        echo "<td>" . $despachoDespues["usuario_cancelacion"] . "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td><strong>Consulta directa BD</strong></td>";
        echo "<td>" . $despachoBD["estado"] . "</td>";
        echo "<td>" . $despachoBD["motivo_cancelacion"] . "</td>";
        echo "<td>" . $despachoBD["usuario_cancelacion"] . "</td>";
        echo "</tr>";
        echo "</table><br>";
        
        if($despachoDespues["estado"] == $despachoBD["estado"]) {
            echo "✅ <strong>Los resultados coinciden</strong><br>";
        } else {
            echo "❌ <strong>Los resultados NO coinciden - posible problema de cache</strong><br>";
        }
        
    } else {
        echo "❌ <strong>mdlActualizarDespacho falló: " . $respuesta . "</strong><br>";
    }
    
    // 8. Restaurar para futuras pruebas
    echo "<h3>🔄 Paso 6: Restaurar despacho</h3>";
    
    $stmt = $conexion->prepare("UPDATE despachos SET estado = 'pendiente', motivo_cancelacion = NULL, usuario_cancelacion = NULL WHERE id = ?");
    $stmt->execute([$idDespacho]);
    
    echo "✅ <strong>Despacho restaurado a 'pendiente'</strong><br>";
    
} catch(Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<hr>";
echo "<p><strong>🎯 Debug del flujo AJAX completado.</strong></p>";
?>
