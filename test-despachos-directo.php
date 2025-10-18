<?php
/**
 * TEST DIRECTO DE LA FUNCIONALIDAD DE DESPACHOS
 * Simula exactamente lo que hace el botón "Ver Despacho"
 */

echo "<h2>🔍 Test Directo de Funcionalidad de Despachos</h2>";

try {
    // Simular la petición AJAX exactamente como lo hace el botón
    $_POST["idDespacho"] = "1"; // ID de prueba
    
    echo "<p>📝 Simulando petición AJAX con ID: " . $_POST["idDespacho"] . "</p>";
    
    // Incluir el archivo AJAX de despachos
    require_once "ajax/despachos.ajax.php";
    
    echo "<p>✅ Archivo ajax/despachos.ajax.php procesado correctamente</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error en el procesamiento: " . $e->getMessage() . "</p>";
    echo "<p>📍 Archivo: " . $e->getFile() . "</p>";
    echo "<p>📍 Línea: " . $e->getLine() . "</p>";
    echo "<p>📍 Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>🔍 Test de Conexión Central Directa</h3>";

try {
    require_once "api-transferencias/conexion-central.php";
    
    $pdo = ConexionCentral::conectar();
    echo "<p>✅ Conexión central exitosa</p>";
    
    // Probar la consulta exacta que usa el modelo
    $stmt = $pdo->prepare("SELECT * FROM despachos WHERE id = :id ORDER BY fecha_creacion DESC");
    $stmt->bindParam(":id", 1, PDO::PARAM_STR);
    $stmt->execute();
    $despacho = $stmt->fetch();
    
    if ($despacho) {
        echo "<p>✅ Consulta de despacho exitosa</p>";
        echo "<pre>Despacho encontrado: " . print_r($despacho, true) . "</pre>";
    } else {
        echo "<p>⚠️ No se encontró despacho con ID 1</p>";
        
        // Mostrar todos los despachos disponibles
        $stmt = $pdo->query("SELECT id, numero_despacho, estado FROM despachos LIMIT 5");
        $despachos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>📋 Despachos disponibles:</p>";
        echo "<pre>" . print_r($despachos, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en conexión central: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test del Modelo de Despachos</h3>";

try {
    require_once "modelos/despachos.modelo.php";
    
    $despacho = ModeloDespachos::mdlMostrarDespachos("id", 1);
    
    if ($despacho) {
        echo "<p>✅ Modelo de despachos funcionando correctamente</p>";
        echo "<pre>Despacho desde modelo: " . print_r($despacho, true) . "</pre>";
    } else {
        echo "<p>⚠️ Modelo no retornó datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en modelo: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
