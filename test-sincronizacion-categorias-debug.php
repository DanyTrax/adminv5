<?php
// Script de prueba para debuggear la sincronización de categorías
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Test de Sincronización de Categorías</h1>";

// Probar conexión a la base de datos central
echo "<h2>1. Probando conexión a base de datos central...</h2>";
try {
    require_once __DIR__ . "/api-transferencias/conexion-central.php";
    $pdo = ConexionCentral::conectar();
    echo "✅ Conexión a base de datos central exitosa<br>";
    
    // Verificar tabla categorias_central
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'categorias_central'");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "✅ Tabla categorias_central existe<br>";
        
        // Contar categorías
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categorias_central");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "📊 Total de categorías: " . $result['total'] . "<br>";
        
        // Mostrar categorías activas
        $stmt = $pdo->prepare("SELECT * FROM categorias_central WHERE activo = 1");
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "📋 Categorías activas: " . count($categorias) . "<br>";
        foreach ($categorias as $cat) {
            echo "&nbsp;&nbsp;- " . $cat['categoria'] . "<br>";
        }
    } else {
        echo "❌ Tabla categorias_central no existe<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
}

// Probar sucursales
echo "<h2>2. Probando sucursales...</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "📊 Total de sucursales activas: " . count($sucursales) . "<br>";
    
    foreach ($sucursales as $sucursal) {
        echo "&nbsp;&nbsp;- " . $sucursal['nombre'] . " (" . $sucursal['host_bd'] . ")<br>";
        
        // Probar conexión a sucursal
        try {
            $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']}";
            $pdoSucursal = new PDO($dsn, $sucursal['usuario_bd'], $sucursal['password_bd']);
            $pdoSucursal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "&nbsp;&nbsp;&nbsp;&nbsp;✅ Conexión exitosa<br>";
            
            // Verificar tabla categorias en sucursal
            $stmt = $pdoSucursal->prepare("SHOW TABLES LIKE 'categorias'");
            $stmt->execute();
            if ($stmt->fetch()) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;✅ Tabla categorias existe<br>";
            } else {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;❌ Tabla categorias no existe<br>";
            }
            
        } catch (Exception $e) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;❌ Error de conexión: " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error al obtener sucursales: " . $e->getMessage() . "<br>";
}

// Probar el método de sincronización
echo "<h2>3. Probando método de sincronización...</h2>";
try {
    require_once __DIR__ . "/modelos/categorias-central.modelo.php";
    
    echo "📡 Ejecutando sincronización...<br>";
    $resultado = ModeloCategoriasCentral::mdlSincronizarCategoriasSucursales();
    
    echo "<pre>";
    print_r($resultado);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error en sincronización: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Probar AJAX endpoint
echo "<h2>4. Probando endpoint AJAX...</h2>";
echo "🔗 URL: ajax/categorias-central.ajax.php<br>";
echo "📝 Acción: sincronizar<br>";

// Simular POST request
$_POST['accion'] = 'sincronizar';

echo "📡 Ejecutando AJAX...<br>";
try {
    ob_start();
    include __DIR__ . "/ajax/categorias-central.ajax.php";
    $output = ob_get_clean();
    
    echo "📥 Respuesta AJAX:<br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error en AJAX: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>✅ Test completado</h2>";
?>
