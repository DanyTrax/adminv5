<?php
/**
 * Script simple para probar cancelación en cPanel
 */

// Simular sesión
session_start();
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";
$_SESSION["perfil"] = "Administrador";

try {
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    
    echo "✅ Conexión exitosa<br>";
    
    // Buscar despacho pendiente
    $stmt = $conexion->query("SELECT id, numero_despacho, estado FROM despachos WHERE estado = 'pendiente' ORDER BY id DESC LIMIT 1");
    $despacho = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$despacho) {
        echo "❌ No hay despachos pendientes<br>";
        exit;
    }
    
    echo "📋 Despacho: " . $despacho["numero_despacho"] . " (ID: " . $despacho["id"] . ")<br>";
    echo "📊 Estado actual: " . $despacho["estado"] . "<br><br>";
    
    // Intentar cancelar
    $idDespacho = $despacho["id"];
    $sql = "UPDATE despachos SET estado = 'cancelado', motivo_cancelacion = 'Prueba', usuario_cancelacion = 'Test' WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $resultado = $stmt->execute([$idDespacho]);
    
    if($resultado) {
        echo "✅ UPDATE ejecutado<br>";
        echo "📊 Filas afectadas: " . $stmt->rowCount() . "<br><br>";
        
        // Verificar cambio
        $stmt = $conexion->query("SELECT estado FROM despachos WHERE id = $idDespacho");
        $nuevoEstado = $stmt->fetchColumn();
        
        echo "🔍 Estado después: " . $nuevoEstado . "<br>";
        
        if($nuevoEstado == "cancelado") {
            echo "✅ ¡ÉXITO! Se canceló correctamente<br>";
        } else {
            echo "❌ FALLO: No cambió a cancelado<br>";
        }
        
        // Restaurar
        $conexion->exec("UPDATE despachos SET estado = 'pendiente', motivo_cancelacion = NULL, usuario_cancelacion = NULL WHERE id = $idDespacho");
        echo "🔄 Restaurado a pendiente<br>";
        
    } else {
        echo "❌ UPDATE falló<br>";
        $error = $stmt->errorInfo();
        echo "Error: " . $error[2] . "<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
