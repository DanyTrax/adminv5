<?php
session_start();

if (!isset($_SESSION['instalacion_logueado'])) {
    die('No autenticado');
}

echo "<h1>🔍 Verificador de Archivo de Conexión</h1>";

$archivo_conexion = '../modelos/conexion.php';
$ruta_completa = dirname(__DIR__) . '/modelos/conexion.php';

echo "<h2>📋 Estado del archivo:</h2>";

if (file_exists($archivo_conexion)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ <strong>El archivo conexion.php EXISTE</strong><br>";
    echo "📁 Ruta: <code>{$ruta_completa}</code><br>";
    echo "📊 Tamaño: " . filesize($archivo_conexion) . " bytes<br>";
    echo "📅 Modificado: " . date('Y-m-d H:i:s', filemtime($archivo_conexion));
    echo "</div>";
    
    // Mostrar contenido del archivo
    echo "<h3>📄 Contenido del archivo:</h3>";
    echo "<textarea style='width: 100%; height: 300px; font-family: monospace; font-size: 12px;'>";
    echo htmlspecialchars(file_get_contents($archivo_conexion));
    echo "</textarea>";
    
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>El archivo conexion.php NO EXISTE</strong><br>";
    echo "📁 Ruta esperada: <code>{$ruta_completa}</code>";
    echo "</div>";
    
    // Crear el archivo manualmente
    echo "<h3>🔧 Crear archivo manualmente:</h3>";
    echo "<form method='POST' action=''>";
    echo "<button type='submit' name='crear_conexion' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
    echo "🚀 Crear archivo de conexión";
    echo "</button>";
    echo "</form>";
}

// Procesar creación manual
if (isset($_POST['crear_conexion'])) {
    $contenido_conexion = '<?php

class Conexion {

    static public function conectar() {

        $link = new PDO("mysql:host=localhost;dbname=epicosie_pruebas2", 
                        "epicosie_ricaurte", 
                        "m5Wwg)~M{i~*kFr{");

        $link->exec("set names utf8");

        return $link;
    }
}

?>';
    
    if (file_put_contents($archivo_conexion, $contenido_conexion)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ <strong>Archivo creado exitosamente</strong>";
        echo "</div>";
        echo "<script>window.location.reload();</script>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>Error creando el archivo</strong>";
        echo "</div>";
    }
}

echo "<br><p><a href='instalador.php'>← Volver al Instalador</a></p>";
?>