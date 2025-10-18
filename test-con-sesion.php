<?php
/**
 * TEST CON SESIÓN SIMULADA
 * Simula una sesión válida para probar la funcionalidad
 */

echo "<h2>🔍 Test con Sesión Simulada</h2>";

// Iniciar sesión
session_start();

// Simular una sesión de administrador
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Usuario de Prueba";

echo "<p>✅ Sesión simulada iniciada:</p>";
echo "<ul>";
echo "<li>Perfil: " . $_SESSION["perfil"] . "</li>";
echo "<li>ID: " . $_SESSION["id"] . "</li>";
echo "<li>Nombre: " . $_SESSION["nombre"] . "</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>🔍 Test de Petición AJAX con Sesión</h3>";

try {
    // Simular la petición AJAX
    $_POST["idDespacho"] = "1";
    
    echo "<p>📝 Simulando petición AJAX con ID: " . $_POST["idDespacho"] . "</p>";
    
    // Capturar la salida del archivo AJAX
    ob_start();
    require_once "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta del servidor:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Intentar decodificar JSON
    $response = json_decode($output, true);
    if ($response) {
        echo "<p>✅ Respuesta JSON válida:</p>";
        echo "<pre>" . print_r($response, true) . "</pre>";
    } else {
        echo "<p>⚠️ La respuesta no es JSON válido</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test de Conexión Directa (sin AJAX)</h3>";

try {
    require_once "api-transferencias/conexion-central.php";
    
    $pdo = ConexionCentral::conectar();
    echo "<p>✅ Conexión central exitosa</p>";
    
    // Probar consulta directa
    $id = 1;
    $stmt = $pdo->prepare("SELECT * FROM despachos WHERE id = :id ORDER BY fecha_creacion DESC");
    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
    $stmt->execute();
    $despacho = $stmt->fetch();
    
    if ($despacho) {
        echo "<p>✅ Despacho encontrado directamente:</p>";
        echo "<pre>" . print_r($despacho, true) . "</pre>";
    } else {
        echo "<p>⚠️ No se encontró despacho con ID 1</p>";
        
        // Mostrar despachos disponibles
        $stmt = $pdo->query("SELECT id, numero_despacho, estado FROM despachos LIMIT 5");
        $despachos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>📋 Despachos disponibles:</p>";
        echo "<pre>" . print_r($despachos, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en conexión: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Conclusión:</strong> El problema es que necesitas iniciar sesión en el sistema antes de usar el botón 'Ver Despacho'.</p>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
