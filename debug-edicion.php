<?php
session_start();

echo "<h2>🔍 Debug de datos de edición</h2>";

if($_POST) {
    echo "<h3>Datos POST recibidos:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>Verificaciones:</h3>";
    echo "<ul>";
    echo "<li>¿Existe editarDespacho? " . (isset($_POST["editarDespacho"]) ? "✅ SÍ" : "❌ NO") . "</li>";
    echo "<li>¿Existe idDespachoEditar? " . (isset($_POST["idDespachoEditar"]) ? "✅ SÍ: " . $_POST["idDespachoEditar"] : "❌ NO") . "</li>";
    echo "<li>¿Existe productosDespacho? " . (isset($_POST["productosDespacho"]) ? "✅ SÍ" : "❌ NO") . "</li>";
    echo "<li>¿Existe totalProductos? " . (isset($_POST["totalProductos"]) ? "✅ SÍ: " . $_POST["totalProductos"] : "❌ NO") . "</li>";
    echo "</ul>";
    
} else {
    echo "<p>No se recibieron datos POST</p>";
}

if($_GET) {
    echo "<h3>Datos GET recibidos:</h3>";
    echo "<pre>";
    print_r($_GET);
    echo "</pre>";
}
?>