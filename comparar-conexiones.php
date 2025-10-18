<?php
/**
 * COMPARAR DIFERENTES MÉTODOS DE CONEXIÓN
 * Identifica por qué un script encuentra la tabla y otro no
 */

echo "<h2>🔍 Comparar Diferentes Métodos de Conexión</h2>";

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Usuario Debug";

echo "<p>✅ Sesión simulada iniciada</p>";

echo "<hr>";
echo "<h3>🔍 Método 1: Conexión Directa (como test-todas-credenciales.php)</h3>";

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=epicosie_central;charset=utf8mb4",
        "epicosie_central",
        "=Nf?M#6A'QU&.6c",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p>✅ Conexión directa exitosa</p>";
    
    // Verificar tabla despachos
    $stmt = $pdo->query("SHOW TABLES LIKE 'despachos'");
    $tabla = $stmt->fetch();
    if ($tabla) {
        echo "<p>✅ Tabla 'despachos' encontrada con conexión directa</p>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM despachos");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p>📊 Registros en despachos: $total</p>";
    } else {
        echo "<p>❌ Tabla 'despachos' NO encontrada con conexión directa</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error en conexión directa: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Método 2: Usando ConexionCentral::conectar() (como debug-despachos.php)</h3>";

try {
    require_once "api-transferencias/conexion-central.php";
    
    $pdo = ConexionCentral::conectar();
    echo "<p>✅ ConexionCentral::conectar() exitoso</p>";
    
    // Verificar tabla despachos
    $stmt = $pdo->query("SHOW TABLES LIKE 'despachos'");
    $tabla = $stmt->fetch();
    if ($tabla) {
        echo "<p>✅ Tabla 'despachos' encontrada con ConexionCentral</p>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM despachos");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p>📊 Registros en despachos: $total</p>";
    } else {
        echo "<p>❌ Tabla 'despachos' NO encontrada con ConexionCentral</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error con ConexionCentral: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Método 3: Verificar contenido de conexion-central.php</h3>";

echo "<p>📁 Contenido del archivo conexion-central.php:</p>";
if (file_exists("api-transferencias/conexion-central.php")) {
    $content = file_get_contents("api-transferencias/conexion-central.php");
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "<p>❌ Archivo conexion-central.php no encontrado</p>";
}

echo "<hr>";
echo "<h3>🔍 Método 4: Probar datatable-despachos.ajax.php directamente</h3>";

try {
    // Simular petición AJAX
    $_POST["draw"] = 1;
    $_POST["start"] = 0;
    $_POST["length"] = 10;
    
    ob_start();
    require_once "ajax/datatable-despachos.ajax.php";
    $output = ob_get_clean();
    
    echo "<p>📤 Respuesta del datatable-despachos.ajax.php:</p>";
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
echo "<h3>🔍 Método 5: Verificar rutas y archivos</h3>";

echo "<p>📁 Verificando archivos:</p>";
echo "<ul>";
echo "<li>conexion-central.php existe: " . (file_exists("api-transferencias/conexion-central.php") ? "SÍ" : "NO") . "</li>";
echo "<li>datatable-despachos.ajax.php existe: " . (file_exists("ajax/datatable-despachos.ajax.php") ? "SÍ" : "NO") . "</li>";
echo "<li>Directorio actual: " . __DIR__ . "</li>";
echo "<li>Ruta conexion-central.php: " . realpath("api-transferencias/conexion-central.php") . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
