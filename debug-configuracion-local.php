<?php
// Script para debuggear la configuración local
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debug: Configuración Local</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>📋 Datos recibidos:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h2>🔍 Campos de BD específicos:</h2>";
    echo "<ul>";
    echo "<li><strong>usuarioBdLocal:</strong> " . ($_POST['usuarioBdLocal'] ?? 'NO ENVIADO') . "</li>";
    echo "<li><strong>passwordBdLocal:</strong> " . ($_POST['passwordBdLocal'] ?? 'NO ENVIADO') . "</li>";
    echo "<li><strong>nombreBdLocal:</strong> " . ($_POST['nombreBdLocal'] ?? 'NO ENVIADO') . "</li>";
    echo "<li><strong>hostBdLocal:</strong> " . ($_POST['hostBdLocal'] ?? 'NO ENVIADO') . "</li>";
    echo "<li><strong>puertoBdLocal:</strong> " . ($_POST['puertoBdLocal'] ?? 'NO ENVIADO') . "</li>";
    echo "</ul>";
} else {
    echo "<h2>📝 Formulario de prueba:</h2>";
    echo "<form method='POST'>";
    echo "<h3>Datos básicos:</h3>";
    echo "<p>Código: <input type='text' name='codigoLocal' value='SUC001'></p>";
    echo "<p>Nombre: <input type='text' name='nombreLocal' value='Sucursal Test'></p>";
    echo "<p>Dirección: <input type='text' name='direccionLocal' value='Calle 123'></p>";
    echo "<p>Teléfono: <input type='text' name='telefonoLocal' value='123-4567'></p>";
    echo "<p>Email: <input type='email' name='emailLocal' value='test@test.com'></p>";
    echo "<p>URL Base: <input type='url' name='urlBaseLocal' value='https://test.com/'></p>";
    echo "<p>URL API: <input type='url' name='urlApiLocal' value='https://test.com/api/'></p>";
    
    echo "<h3>Datos de BD:</h3>";
    echo "<p>Usuario BD: <input type='text' name='usuarioBdLocal' value='usuario_test'></p>";
    echo "<p>Password BD: <input type='password' name='passwordBdLocal' value='password_test'></p>";
    echo "<p>Nombre BD: <input type='text' name='nombreBdLocal' value='bd_test'></p>";
    echo "<p>Host BD: <input type='text' name='hostBdLocal' value='localhost'></p>";
    echo "<p>Puerto BD: <input type='number' name='puertoBdLocal' value='3306'></p>";
    
    echo "<p><input type='checkbox' name='esPrincipal'> Es principal</p>";
    echo "<p><button type='submit'>Enviar</button></p>";
    echo "</form>";
}
?>
