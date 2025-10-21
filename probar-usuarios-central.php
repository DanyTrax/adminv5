<?php
require_once "config.php";
require_once "controladores/usuarios-central.controlador.php";

echo "<h2>🔍 PROBAR USUARIOS CENTRAL</h2>";

try {
    // Probar obtener sucursales
    echo "<h3>📋 Sucursales Disponibles:</h3>";
    $sucursales = ControladorUsuariosCentral::ctrObtenerSucursalesDisponibles();
    echo "<p>Total sucursales: " . count($sucursales) . "</p>";
    
    // Probar obtener estadísticas
    echo "<h3>📊 Estadísticas:</h3>";
    $estadisticas = ControladorUsuariosCentral::ctrObtenerEstadisticasSincronizacion();
    echo "<pre>" . print_r($estadisticas, true) . "</pre>";
    
    // Probar obtener usuarios centrales
    echo "<h3>👥 Usuarios Centrales:</h3>";
    $usuarios = ControladorUsuariosCentral::ctrObtenerUsuariosCentral();
    echo "<p>Total usuarios: " . count($usuarios) . "</p>";
    
    // Probar obtener usuarios locales
    echo "<h3>🏠 Usuarios Locales:</h3>";
    $usuariosLocal = ControladorUsuariosCentral::ctrObtenerUsuariosLocal();
    echo "<p>Total usuarios locales: " . count($usuariosLocal) . "</p>";
    
    echo "<h3>✅ TODAS LAS FUNCIONES FUNCIONAN CORRECTAMENTE</h3>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
