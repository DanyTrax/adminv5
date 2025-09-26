<?php
session_start();

echo "<h2>🔍 Debug completo de sesión</h2>";

echo "<h3>Todas las variables de sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Variables específicas de sucursal:</h3>";
echo "<ul>";
echo "<li><strong>id_sucursal:</strong> " . ($_SESSION["id_sucursal"] ?? 'NO DEFINIDA') . "</li>";
echo "<li><strong>codigo_sucursal:</strong> " . ($_SESSION["codigo_sucursal"] ?? 'NO DEFINIDA') . "</li>";
echo "<li><strong>nombre_sucursal:</strong> " . ($_SESSION["nombre_sucursal"] ?? 'NO DEFINIDA') . "</li>";
echo "</ul>";

echo "<h3>Variables de usuario:</h3>";
echo "<ul>";
echo "<li><strong>id:</strong> " . ($_SESSION["id"] ?? 'NO DEFINIDA') . "</li>";
echo "<li><strong>nombre:</strong> " . ($_SESSION["nombre"] ?? 'NO DEFINIDA') . "</li>";
echo "<li><strong>perfil:</strong> " . ($_SESSION["perfil"] ?? 'NO DEFINIDA') . "</li>";
echo "</ul>";

// Probar obtener sucursal desde BD
try {
    require_once "modelos/conexion.php";
    
    $stmt = Conexion::conectar()->prepare("
        SELECT id, codigo_sucursal, nombre 
        FROM sucursal_local 
        WHERE activo = 1 
        ORDER BY es_principal DESC, id ASC 
        LIMIT 1
    ");
    $stmt->execute();
    $sucursal = $stmt->fetch();
    
    echo "<h3>Sucursal desde BD:</h3>";
    if($sucursal) {
        echo "<p>✅ <strong>ID:</strong> {$sucursal['id']}</p>";
        echo "<p>✅ <strong>Código:</strong> {$sucursal['codigo_sucursal']}</p>";
        echo "<p>✅ <strong>Nombre:</strong> {$sucursal['nombre']}</p>";
        
        echo "<h4>¿Actualizar variables de sesión?</h4>";
        echo "<a href='?actualizar=1' style='background: #007cba; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Actualizar variables de sesión</a>";
        
        if(isset($_GET['actualizar'])) {
            $_SESSION["id_sucursal"] = $sucursal["id"];
            $_SESSION["codigo_sucursal"] = $sucursal["codigo_sucursal"];
            $_SESSION["nombre_sucursal"] = $sucursal["nombre"];
            echo "<p style='color: green; font-weight: bold;'>✅ Variables de sesión actualizadas</p>";
            echo "<script>setTimeout(function(){ window.location = window.location.pathname; }, 2000);</script>";
        }
    } else {
        echo "<p>❌ No se encontró sucursal activa</p>";
    }
    
} catch(Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>