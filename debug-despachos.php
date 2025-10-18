<?php
/**
 * DEBUG ESPECÍFICO PARA DESPACHOS
 * Verifica exactamente qué está pasando con la página de despachos
 */

echo "<h2>🔍 Debug Específico para Despachos</h2>";

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Usuario Debug";

echo "<p>✅ Sesión simulada iniciada</p>";

echo "<hr>";
echo "<h3>🔍 Test 1: Conexión Central</h3>";

try {
    require_once "api-transferencias/conexion-central.php";
    $pdo = ConexionCentral::conectar();
    echo "<p>✅ Conexión central exitosa</p>";
    
    // Verificar tabla despachos
    $stmt = $pdo->query("SHOW TABLES LIKE 'despachos'");
    $tabla = $stmt->fetch();
    if ($tabla) {
        echo "<p>✅ Tabla 'despachos' existe en base central</p>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM despachos");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p>📊 Registros en despachos: $total</p>";
    } else {
        echo "<p>❌ Tabla 'despachos' NO existe en base central</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en conexión central: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 2: DataTable Despachos</h3>";

try {
    // Simular petición AJAX para datatable
    $_POST["draw"] = 1;
    $_POST["start"] = 0;
    $_POST["length"] = 10;
    
    ob_start();
    require_once "ajax/datatable-despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta del datatable:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $response = json_decode($output, true);
    if ($response) {
        echo "<p>✅ Respuesta JSON válida</p>";
        if (isset($response['error'])) {
            echo "<p>❌ Error en respuesta: " . $response['error'] . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en datatable: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 3: DataTable Stock Tránsito</h3>";

try {
    // Simular petición AJAX para stock transito
    $_POST["draw"] = 1;
    $_POST["start"] = 0;
    $_POST["length"] = 10;
    
    ob_start();
    require_once "ajax/datatable-stock-transito.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta del stock transito:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $response = json_decode($output, true);
    if ($response) {
        echo "<p>✅ Respuesta JSON válida</p>";
        if (isset($response['error'])) {
            echo "<p>❌ Error en respuesta: " . $response['error'] . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en stock transito: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 4: Verificar Configuración de Base de Datos</h3>";

// Verificar archivo de conexión central
echo "<p>📁 Archivo conexion-central.php:</p>";
if (file_exists("api-transferencias/conexion-central.php")) {
    $content = file_get_contents("api-transferencias/conexion-central.php");
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "<p>❌ Archivo no encontrado</p>";
}

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
