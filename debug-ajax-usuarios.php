<?php
// Script de depuración para AJAX de usuarios
session_start();

// Simular sesión de administrador
$_SESSION["perfil"] = "Administrador";

echo "<h2>🔍 Debug AJAX Usuarios Sucursales</h2>";

// Probar el endpoint AJAX directamente
echo "<h3>1. Probando endpoint AJAX directamente:</h3>";

$url = "https://pruebas.acrilicosinfinito.com/ajax/consultar-usuarios-sucursales.ajax.php";

$data = [
    'tipo_consulta' => 'todas'
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "<pre>";
echo "URL: " . $url . "\n";
echo "Data: " . print_r($data, true) . "\n";
echo "Response: " . $result . "\n";
echo "</pre>";

// Probar controlador directamente
echo "<h3>2. Probando controlador directamente:</h3>";

require_once __DIR__ . "/controladores/usuarios-central.controlador.php";

try {
    echo "<h4>Usuarios locales:</h4>";
    $usuariosLocal = ControladorUsuariosCentral::ctrObtenerUsuariosLocal();
    echo "<pre>" . print_r($usuariosLocal, true) . "</pre>";
    
    echo "<h4>Usuarios sucursales:</h4>";
    $usuariosSucursales = ControladorUsuariosCentral::ctrConsultarUsuariosSucursales();
    echo "<pre>" . print_r($usuariosSucursales, true) . "</pre>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Probar modelo directamente
echo "<h3>3. Probando modelo directamente:</h3>";

require_once __DIR__ . "/modelos/usuarios-central.modelo.php";

try {
    echo "<h4>Usuarios locales (modelo):</h4>";
    $usuariosLocalModelo = ModeloUsuariosCentral::mdlObtenerUsuariosLocal();
    echo "<pre>" . print_r($usuariosLocalModelo, true) . "</pre>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>4. Verificar logs de error:</h3>";
$errorLog = file_get_contents(__DIR__ . "/error_log");
echo "<pre>" . htmlspecialchars($errorLog) . "</pre>";
?>
