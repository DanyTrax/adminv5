<?php
// Script de debug para verificar datos del formulario de edición
session_start();

// Simular sesión de administrador
$_SESSION["perfil"] = "Administrador";

echo "<h2>🔍 Debug Formulario de Edición de Sucursales</h2>";

echo "<h3>📋 Datos POST recibidos:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>📋 Datos GET recibidos:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

// Probar el controlador directamente
if (isset($_POST["editarId"])) {
    echo "<h3>🔧 Probando controlador directamente:</h3>";
    
    require_once __DIR__ . "/controladores/sucursales.controlador.php";
    
    try {
        $controlador = new ControladorSucursales();
        $controlador->ctrActualizarSucursal();
        echo "<p style='color: green;'>✅ Controlador ejecutado</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en controlador: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No se detectó envío de formulario de edición</p>";
}

echo "<h3>🧪 Formulario de prueba:</h3>";
echo '<form method="post" action="">
    <input type="hidden" name="editarId" value="8">
    <input type="text" name="editarNombre" value="Pruebas Actualizada" placeholder="Nombre">
    <input type="text" name="editarUsuarioBd" value="usuario_test" placeholder="Usuario BD">
    <input type="text" name="editarPasswordBd" value="password_test" placeholder="Password BD">
    <input type="text" name="editarNombreBd" value="bd_test" placeholder="Nombre BD">
    <input type="text" name="editarHostBd" value="localhost" placeholder="Host BD">
    <input type="text" name="editarPuertoBd" value="3306" placeholder="Puerto BD">
    <button type="submit">Probar Actualización</button>
</form>';
?>
