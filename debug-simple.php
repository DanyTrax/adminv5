<?php
/*=============================================
DEBUG SIMPLE - CAPTURAR ERRORES
=============================================*/

// Habilitar mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 DEBUG SIMPLE - CAPTURANDO ERRORES\n";
echo "====================================\n\n";

// Simular datos POST
$_POST = [
    "accion" => "registrar_descarga",
    "codigo_producto" => "DEBUG" . date('His'),
    "descripcion_producto" => "Producto Debug",
    "cantidad_descargada" => 1,
    "usuario_id" => 1,
    "usuario_nombre" => "Administrador",
    "sucursal_id" => 1,
    "sucursal_nombre" => "Sucursal 2",
    "transportador_id" => "0",
    "transportador_nombre" => "Debug",
    "numero_despacho" => "",
    "observaciones" => "Debug - " . date('Y-m-d H:i:s')
];

echo "📋 Datos POST:\n";
print_r($_POST);

echo "\n🎮 Probando controlador...\n";

try {
    // Verificar si el archivo existe
    if (!file_exists("controladores/registro-descargas-simple.controlador.php")) {
        echo "❌ Archivo controlador no existe\n";
        exit;
    }
    
    echo "✅ Archivo controlador existe\n";
    
    // Incluir controlador
    require_once "controladores/registro-descargas-simple.controlador.php";
    echo "✅ Controlador incluido\n";
    
    // Crear instancia
    $controlador = new ControladorRegistroDescargasSimple();
    echo "✅ Instancia creada\n";
    
    // Ejecutar método
    $resultado = $controlador->ctrRegistrarDescarga();
    echo "✅ Método ejecutado\n";
    
    echo "📊 Resultado: " . json_encode($resultado) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    echo "📍 Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}

echo "\n🔍 Verificando archivos...\n";

$archivos = [
    "controladores/registro-descargas-simple.controlador.php",
    "modelos/registro-descargas-simple.modelo.php",
    "ajax/registro-descargas-simple.ajax.php",
    "api-transferencias/conexion-central.php"
];

foreach($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ $archivo existe\n";
    } else {
        echo "❌ $archivo NO existe\n";
    }
}

echo "\n🎯 DEBUG COMPLETADO\n";

?>
