<?php
/**
 * TEST DE CONEXIÓN A BASE DE DATOS CENTRAL
 */

echo "<h2>🔍 Test de Conexión a Base de Datos Central</h2>";

try {
    // Incluir el archivo de conexión
    require_once "api-transferencias/conexion-central.php";
    
    echo "<p>✅ Archivo conexion-central.php cargado correctamente</p>";
    
    // Intentar conectar
    $pdo = ConexionCentral::conectar();
    
    if ($pdo) {
        echo "<p>✅ Conexión a base de datos central exitosa</p>";
        
        // Verificar que la tabla despachos existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'despachos'");
        $tabla = $stmt->fetch();
        
        if ($tabla) {
            echo "<p>✅ Tabla 'despachos' encontrada en la base de datos central</p>";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM despachos");
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "<p>📊 Registros en tabla despachos: $total</p>";
            
            // Probar consulta específica
            $stmt = $pdo->prepare("SELECT * FROM despachos LIMIT 1");
            $stmt->execute();
            $despacho = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($despacho) {
                echo "<p>✅ Consulta de prueba exitosa</p>";
                echo "<pre>Primer despacho: " . print_r($despacho, true) . "</pre>";
            } else {
                echo "<p>⚠️ No hay registros en la tabla despachos</p>";
            }
            
        } else {
            echo "<p>❌ Tabla 'despachos' NO encontrada en la base de datos central</p>";
        }
        
    } else {
        echo "<p>❌ No se pudo conectar a la base de datos central</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>📍 Archivo: " . $e->getFile() . "</p>";
    echo "<p>📍 Línea: " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<h3>🔍 Información de Debug</h3>";
echo "<p><strong>Directorio actual:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Archivo conexion-central.php existe:</strong> " . (file_exists("api-transferencias/conexion-central.php") ? "SÍ" : "NO") . "</p>";
echo "<p><strong>Ruta completa:</strong> " . realpath("api-transferencias/conexion-central.php") . "</p>";
?>