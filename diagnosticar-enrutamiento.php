<?php
/*=============================================
DIAGNÓSTICO DE ENRUTAMIENTO
=============================================*/

echo "<h1>🔍 Diagnóstico de Enrutamiento</h1>";

// 1. Verificar sesión
echo "<h2>1. Verificación de Sesión</h2>";
session_start();
if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {
    echo "✅ Sesión iniciada correctamente<br>";
    echo "👤 Usuario: " . ($_SESSION["nombre"] ?? "No definido") . "<br>";
    echo "🔑 Perfil: " . ($_SESSION["perfil"] ?? "No definido") . "<br>";
} else {
    echo "❌ No hay sesión iniciada<br>";
    echo "🔗 <a href='index.php'>Ir a login</a><br>";
}

// 2. Verificar parámetro ruta
echo "<h2>2. Verificación de Parámetro Ruta</h2>";
if (isset($_GET["ruta"])) {
    echo "✅ Parámetro 'ruta' recibido: " . $_GET["ruta"] . "<br>";
} else {
    echo "❌ No se recibió parámetro 'ruta'<br>";
}

// 3. Verificar archivo plantilla.php
echo "<h2>3. Verificación de Archivo plantilla.php</h2>";
if (file_exists("vistas/plantilla.php")) {
    echo "✅ Archivo vistas/plantilla.php existe<br>";
    $size = filesize("vistas/plantilla.php");
    echo "📏 Tamaño: " . $size . " bytes<br>";
    
    // Verificar si tiene lógica de enrutamiento
    $content = file_get_contents("vistas/plantilla.php");
    if (strpos($content, 'include "modulos/"') !== false) {
        echo "✅ Contiene lógica de enrutamiento<br>";
    } else {
        echo "❌ NO contiene lógica de enrutamiento<br>";
    }
} else {
    echo "❌ Archivo vistas/plantilla.php NO existe<br>";
}

// 4. Verificar archivos de módulos
echo "<h2>4. Verificación de Módulos</h2>";
$modulos = ["inicio", "entradas", "salidas", "reporte-detallado", "404"];
foreach ($modulos as $modulo) {
    $archivo = "vistas/modulos/" . $modulo . ".php";
    if (file_exists($archivo)) {
        echo "✅ $archivo existe<br>";
    } else {
        echo "❌ $archivo NO existe<br>";
    }
}

// 5. Verificar rutas definidas
echo "<h2>5. Verificación de Rutas</h2>";
if (isset($_SESSION["perfil"])) {
    $routes = [
        "inicio" => ["Administrador", "Especial", "Vendedor", "Contador", "Transportador"],
        "entradas" => ["Administrador", "Contador"],
        "salidas" => ["Administrador", "Contador"],
        "reporte-detallado" => ["Administrador", "Vendedor", "Contador"],
        "404" => ["Administrador", "Especial", "Vendedor", "Contador", "Transportador"]
    ];
    
    $perfil = $_SESSION["perfil"];
    echo "🔑 Perfil actual: $perfil<br>";
    
    foreach ($routes as $ruta => $perfiles) {
        if (in_array($perfil, $perfiles)) {
            echo "✅ $ruta: Acceso permitido<br>";
        } else {
            echo "❌ $ruta: Acceso denegado<br>";
        }
    }
}

// 6. Probar enrutamiento manual
echo "<h2>6. Prueba de Enrutamiento Manual</h2>";
if (isset($_GET["ruta"])) {
    $route = $_GET["ruta"];
    $profile = $_SESSION["perfil"] ?? "Sin perfil";
    
    if (in_array($profile, $routes[$route] ?? [])) {
        echo "✅ Ruta '$route' permitida para perfil '$profile'<br>";
        $archivo = "vistas/modulos/" . $route . ".php";
        if (file_exists($archivo)) {
            echo "✅ Archivo $archivo existe, debería cargarse<br>";
        } else {
            echo "❌ Archivo $archivo NO existe<br>";
        }
    } else {
        echo "❌ Ruta '$route' NO permitida para perfil '$profile'<br>";
    }
}

echo "<hr>";
echo "<p><strong>🔗 Enlaces de prueba:</strong></p>";
echo "<a href='index.php?ruta=inicio'>Inicio</a> | ";
echo "<a href='index.php?ruta=entradas'>Entradas</a> | ";
echo "<a href='index.php?ruta=salidas'>Salidas</a> | ";
echo "<a href='index.php?ruta=reporte-detallado'>Reporte Detallado</a> | ";
echo "<a href='index.php?ruta=404'>404</a>";

?>
