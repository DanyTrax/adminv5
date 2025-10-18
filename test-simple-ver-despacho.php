<?php
/**
 * TEST SIMPLE PARA VER DESPACHO
 * Diagnostica el problema específico con la acción Ver Despacho
 */

echo "<h2>🔍 Test Simple para Ver Despacho</h2>";

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 30); // Límite de 30 segundos

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Usuario Debug";

echo "<p>✅ Sesión simulada iniciada</p>";

echo "<hr>";
echo "<h3>🔍 Test 1: Verificar conexión central</h3>";

try {
    require_once "api-transferencias/conexion-central.php";
    $pdo = ConexionCentral::conectar();
    echo "<p>✅ Conexión central exitosa</p>";
    
    // Verificar que la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'despachos'");
    $tabla = $stmt->fetch();
    if ($tabla) {
        echo "<p>✅ Tabla despachos existe</p>";
    } else {
        echo "<p>❌ Tabla despachos NO existe</p>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en conexión: " . $e->getMessage() . "</p>";
    exit;
}

echo "<hr>";
echo "<h3>🔍 Test 2: Consulta directa a despachos</h3>";

try {
    $id = 1;
    $stmt = $pdo->prepare("SELECT * FROM despachos WHERE id = :id ORDER BY fecha_creacion DESC");
    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
    $stmt->execute();
    $despacho = $stmt->fetch();
    
    if ($despacho) {
        echo "<p>✅ Despacho encontrado directamente</p>";
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
    echo "<p>❌ Error en consulta: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 3: Probar modelo de despachos</h3>";

try {
    require_once "modelos/despachos.modelo.php";
    
    $despacho = ModeloDespachos::mdlMostrarDespachos("despachos", "id", 1);
    
    if ($despacho) {
        echo "<p>✅ Modelo funcionando correctamente</p>";
        echo "<pre>" . print_r($despacho, true) . "</pre>";
    } else {
        echo "<p>⚠️ Modelo no retornó datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en modelo: " . $e->getMessage() . "</p>";
    echo "<p>📍 Archivo: " . $e->getFile() . "</p>";
    echo "<p>📍 Línea: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 4: Probar controlador de despachos</h3>";

try {
    require_once "controladores/despachos.controlador.php";
    
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", 1);
    
    if ($despacho) {
        echo "<p>✅ Controlador funcionando correctamente</p>";
        echo "<pre>" . print_r($despacho, true) . "</pre>";
    } else {
        echo "<p>⚠️ Controlador no retornó datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en controlador: " . $e->getMessage() . "</p>";
    echo "<p>📍 Archivo: " . $e->getFile() . "</p>";
    echo "<p>📍 Línea: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Test 5: Probar AJAX con timeout</h3>";

try {
    // Configurar timeout
    set_time_limit(10);
    
    $_POST["idDespacho"] = "1";
    
    echo "<p>📝 Simulando petición AJAX con timeout de 10 segundos...</p>";
    
    ob_start();
    require_once "ajax/despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta recibida:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $response = json_decode($output, true);
    if ($response) {
        echo "<p>✅ Respuesta JSON válida</p>";
        if (isset($response['error'])) {
            echo "<p>❌ Error en respuesta: " . $response['error'] . "</p>";
        }
    } else {
        echo "<p>⚠️ La respuesta no es JSON válido</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en AJAX: " . $e->getMessage() . "</p>";
    echo "<p>📍 Archivo: " . $e->getFile() . "</p>";
    echo "<p>📍 Línea: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Tiempo de ejecución:</strong> " . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . " segundos</p>";
?>
