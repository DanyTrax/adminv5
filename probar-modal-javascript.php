<?php
/*=============================================
PROBAR REGISTRO DESDE MODAL JAVASCRIPT
=============================================*/

echo "🧪 PROBANDO REGISTRO DESDE MODAL JAVASCRIPT\n";
echo "==========================================\n\n";

// Simular exactamente lo que hace el JavaScript
$datosJavaScript = [
    "accion" => "registrar_descarga",
    "codigo_producto" => "MODAL" . date('His'),
    "descripcion_producto" => "Producto desde JavaScript Optimizado",
    "cantidad_descargada" => 1,
    "usuario_id" => "999",
    "usuario_nombre" => "Usuario Sistema",
    "sucursal_id" => "1",
    "sucursal_nombre" => "Sucursal 2",
    "transportador_id" => "0",
    "transportador_nombre" => "Transportador Sistema",
    "numero_despacho" => "",
    "observaciones" => "Registro desde JavaScript - " . date('Y-m-d H:i:s')
];

echo "📋 1. DATOS QUE ENVIARÍA JAVASCRIPT:\n";
foreach($datosJavaScript as $key => $value) {
    echo "   $key: $value\n";
}

// Simular $_POST
$_POST = $datosJavaScript;

echo "\n🎮 2. PROBANDO CONTROLADOR:\n";
try {
    require_once "controladores/registro-descargas-simple.controlador.php";
    $controlador = new ControladorRegistroDescargasSimple();
    $resultado = $controlador->ctrRegistrarDescarga();
    
    echo "✅ Controlador ejecutado\n";
    echo "   Resultado: " . json_encode($resultado) . "\n";
    
    if ($resultado["success"]) {
        echo "✅ Registro exitoso\n";
    } else {
        echo "❌ Error en controlador: " . $resultado["error"] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error con controlador: " . $e->getMessage() . "\n";
}

echo "\n🔗 3. PROBANDO ENDPOINT AJAX:\n";
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
    
    echo "✅ Endpoint AJAX ejecutado\n";
    echo "   Respuesta: " . ($response ?: "Sin respuesta") . "\n";
    
    if ($data && $data['success']) {
        echo "✅ Endpoint funcionando\n";
    } else {
        echo "❌ Endpoint no funciona\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error probando endpoint: " . $e->getMessage() . "\n";
}

echo "\n🔍 4. VERIFICANDO REGISTRO EN BD:\n";
try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    
    if ($conexionCentral) {
        echo "✅ Conexión central exitosa\n";
        
        // Verificar último registro
        $stmt = $conexionCentral->prepare("SELECT * FROM registro_descargas_stock_transito ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $ultimo = $stmt->fetch();
        
        if ($ultimo) {
            echo "✅ Último registro:\n";
            echo "   ID: " . $ultimo['id'] . "\n";
            echo "   Código: " . $ultimo['codigo_producto'] . "\n";
            echo "   Sucursal: " . $ultimo['sucursal_nombre'] . "\n";
            echo "   Usuario: " . $ultimo['usuario_nombre'] . "\n";
            echo "   Fecha: " . $ultimo['fecha_descarga'] . "\n";
        } else {
            echo "❌ No hay registros\n";
        }
        
    } else {
        echo "❌ No se pudo conectar\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "\n";
}

echo "\n🎯 PRUEBA COMPLETADA\n";

?>
