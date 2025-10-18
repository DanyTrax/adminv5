<?php
/**
 * Test directo de eliminación de despachos
 */

// Simular sesión
session_start();
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";
$_SESSION["perfil"] = "Administrador";

echo "<h2>🧪 Test Directo - Eliminación de Despachos</h2>";
echo "<hr>";

try {
    // Conectar directamente a la base central
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    echo "✅ <strong>Conexión exitosa</strong><br><br>";
    
    // Buscar un despacho para eliminar
    $stmt = $conexion->query("SELECT id, numero_despacho, estado FROM despachos ORDER BY id DESC LIMIT 1");
    $despacho = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$despacho) {
        echo "❌ <strong>No hay despachos para probar</strong><br>";
        exit;
    }
    
    echo "📋 <strong>Despacho encontrado:</strong><br>";
    echo "ID: " . $despacho["id"] . "<br>";
    echo "Número: " . $despacho["numero_despacho"] . "<br>";
    echo "Estado: " . $despacho["estado"] . "<br><br>";
    
    $idDespacho = $despacho["id"];
    
    // Crear una copia de respaldo antes de eliminar
    echo "<h3>🔄 Creando respaldo...</h3>";
    
    $stmt = $conexion->prepare("CREATE TEMPORARY TABLE respaldo_despacho AS SELECT * FROM despachos WHERE id = ?");
    $stmt->execute([$idDespacho]);
    echo "✅ <strong>Respaldo creado</strong><br><br>";
    
    // Probar eliminación usando la función del modelo
    echo "<h3>🧪 Probando eliminación con mdlBorrarDespacho...</h3>";
    
    require_once "modelos/despachos.modelo.php";
    $respuesta = ModeloDespachos::mdlBorrarDespacho("despachos", "id", $idDespacho);
    
    echo "<strong>Respuesta de mdlBorrarDespacho:</strong> " . $respuesta . "<br><br>";
    
    if($respuesta == "ok") {
        echo "✅ <strong>mdlBorrarDespacho devolvió 'ok'</strong><br><br>";
        
        // Verificar que realmente se eliminó
        $stmt = $conexion->query("SELECT COUNT(*) FROM despachos WHERE id = $idDespacho");
        $count = $stmt->fetchColumn();
        
        echo "<strong>Verificación: Despachos con ID $idDespacho:</strong> $count<br><br>";
        
        if($count == 0) {
            echo "✅ <strong>¡ÉXITO! El despacho se eliminó correctamente</strong><br>";
        } else {
            echo "❌ <strong>FALLO: El despacho no se eliminó</strong><br>";
        }
        
        // Restaurar desde el respaldo
        echo "<h3>🔄 Restaurando despacho...</h3>";
        
        $stmt = $conexion->prepare("INSERT INTO despachos SELECT * FROM respaldo_despacho");
        $stmt->execute();
        
        echo "✅ <strong>Despacho restaurado desde respaldo</strong><br>";
        
    } else {
        echo "❌ <strong>mdlBorrarDespacho falló: " . $respuesta . "</strong><br>";
    }
    
    // Limpiar tabla temporal
    $conexion->exec("DROP TEMPORARY TABLE respaldo_despacho");
    
} catch(Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<hr>";
echo "<p><strong>🎯 Test de eliminación completado.</strong></p>";
?>
