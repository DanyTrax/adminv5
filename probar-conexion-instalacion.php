<?php
// Script para probar la conexión después de la instalación
echo "<h2>🔌 Probar Conexión Después de Instalación</h2>";

try {
    // Probar conexión usando modelos/conexion.php
    require_once "modelos/conexion.php";
    
    echo "<p>✅ Archivo modelos/conexion.php cargado correctamente</p>";
    
    $pdo = Conexion::conectar();
    echo "<p>✅ Conexión a base de datos exitosa</p>";
    
    // Probar una consulta simple
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>✅ Consulta a tabla usuarios exitosa</p>";
    echo "<p><strong>Usuarios en la BD:</strong> " . $resultado['total'] . "</p>";
    
    // Mostrar información de conexión
    echo "<h3>📊 Información de Conexión:</h3>";
    echo "<p><strong>Host:</strong> " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "</p>";
    
    // Probar otras tablas
    $tablas_test = ['productos', 'clientes', 'ventas', 'sucursal_local'];
    
    echo "<h3>📋 Verificación de Tablas:</h3>";
    foreach ($tablas_test as $tabla) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM `$tabla`");
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>✅ <strong>$tabla</strong> - " . $resultado['total'] . " registros</p>";
        } catch (Exception $e) {
            echo "<p>❌ <strong>$tabla</strong> - Error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>🎉 ¡Instalación verificada correctamente!</h3>";
    echo "<p>El sistema está listo para usar.</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error de Conexión:</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    
    echo "<h4>🔧 Posibles soluciones:</h4>";
    echo "<ul>";
    echo "<li>Verificar que el archivo modelos/conexion.php fue actualizado</li>";
    echo "<li>Verificar que los datos de conexión son correctos</li>";
    echo "<li>Verificar que la base de datos existe</li>";
    echo "<li>Verificar que el usuario tiene permisos</li>";
    echo "</ul>";
    
    echo "<h4>📝 Para verificar manualmente:</h4>";
    echo "<p>Revisa el archivo <code>modelos/conexion.php</code> y verifica que tenga los datos correctos:</p>";
    echo "<pre>";
    if (file_exists("modelos/conexion.php")) {
        echo htmlspecialchars(file_get_contents("modelos/conexion.php"));
    } else {
        echo "El archivo modelos/conexion.php no existe";
    }
    echo "</pre>";
}
?>
