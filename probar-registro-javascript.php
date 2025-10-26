<?php
/*=============================================
PROBAR REGISTRO DESDE JAVASCRIPT (SIMULACIÓN)
=============================================*/

echo "🧪 SIMULANDO REGISTRO DESDE JAVASCRIPT\n";
echo "=====================================\n\n";

// 1. Simular datos que enviaría JavaScript
echo "📋 1. DATOS QUE ENVIARÍA JAVASCRIPT:\n";

// Obtener datos de sucursal real
require_once "modelos/conexion.php";
$stmt = Conexion::conectar()->prepare("SELECT * FROM sucursal_local WHERE id = 1");
$stmt->execute();
$sucursal = $stmt->fetch(PDO::FETCH_ASSOC);

$datosJavaScript = [
    "accion" => "registrar_descarga",
    "codigo_producto" => "JS" . date('His'),
    "descripcion_producto" => "Producto desde JavaScript - Sucursal 2",
    "cantidad_descargada" => 1,
    "usuario_id" => "999", // Valor por defecto del fallback
    "usuario_nombre" => "Usuario Sistema", // Valor por defecto del fallback
    "sucursal_id" => $sucursal['id'],
    "sucursal_nombre" => $sucursal['nombre'],
    "transportador_id" => "0",
    "transportador_nombre" => "Transportador Test",
    "numero_despacho" => "",
    "observaciones" => "Prueba desde JavaScript - " . date('Y-m-d H:i:s')
];

foreach($datosJavaScript as $key => $value) {
    echo "   $key: $value\n";
}

// 2. Simular $_POST
echo "\n📝 2. SIMULANDO \$_POST:\n";
$_POST = $datosJavaScript;

// 3. Probar el controlador
echo "\n🎮 3. PROBANDO CONTROLADOR:\n";
try {
    require_once "controladores/registro-descargas-simple.controlador.php";
    $controlador = new ControladorRegistroDescargasSimple();
    $resultado = $controlador->ctrRegistrarDescarga();
    
    if ($resultado["success"]) {
        echo "✅ Controlador funcionando\n";
        echo "   Mensaje: " . $resultado["message"] . "\n";
    } else {
        echo "❌ Error en controlador: " . $resultado["error"] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error con controlador: " . $e->getMessage() . "\n";
}

// 4. Probar el endpoint AJAX directamente
echo "\n🔗 4. PROBANDO ENDPOINT AJAX:\n";
try {
    // Simular llamada AJAX
    $url = "ajax/registro-descargas-simple.ajax.php";
    
    // Crear contexto POST
    $postData = http_build_query($datosJavaScript);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postData,
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        echo "✅ Endpoint AJAX funcionando\n";
        echo "   Mensaje: " . $data['message'] . "\n";
    } else {
        echo "❌ Endpoint AJAX no funciona: " . ($response ?: "Sin respuesta") . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error probando endpoint: " . $e->getMessage() . "\n";
}

// 5. Verificar que se creó el registro
echo "\n🔍 5. VERIFICANDO REGISTRO CREADO:\n";
try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    
    $stmt = $conexionCentral->prepare("
        SELECT * FROM registro_descargas_stock_transito 
        WHERE codigo_producto = ? 
        ORDER BY fecha_descarga DESC 
        LIMIT 1
    ");
    $stmt->execute([$datosJavaScript["codigo_producto"]]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($registro) {
        echo "✅ Registro encontrado:\n";
        echo "   ID: " . $registro['id'] . "\n";
        echo "   Código: " . $registro['codigo_producto'] . "\n";
        echo "   Sucursal: " . $registro['sucursal_nombre'] . "\n";
        echo "   Usuario: " . $registro['usuario_nombre'] . "\n";
        echo "   Fecha: " . $registro['fecha_descarga'] . "\n";
    } else {
        echo "❌ No se encontró el registro\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error verificando registro: " . $e->getMessage() . "\n";
}

echo "\n🎯 SIMULACIÓN COMPLETADA\n";
echo "========================\n";

?>
