<?php
/*=============================================
PROBAR FLUJO EXACTO DE JAVASCRIPT
=============================================*/

echo "🧪 PROBANDO FLUJO EXACTO DE JAVASCRIPT\n";
echo "=====================================\n\n";

// Simular exactamente los datos que envía JavaScript
$_POST = [
    "accion" => "registrar_descarga",
    "codigo_producto" => "JS" . date('His'),
    "descripcion_producto" => "Producto desde JavaScript - Flujo Exacto",
    "cantidad_descargada" => 1,
    "usuario_id" => 1,
    "usuario_nombre" => "Administrador",
    "sucursal_id" => 1,
    "sucursal_nombre" => "Sucursal 2",
    "transportador_id" => "0",
    "transportador_nombre" => "Debug Test",
    "numero_despacho" => "",
    "observaciones" => "Prueba flujo exacto - " . date('Y-m-d H:i:s')
];

echo "📋 1. DATOS POST SIMULADOS:\n";
foreach($_POST as $key => $value) {
    echo "   $key: $value\n";
}

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
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🔍 3. PROBANDO MODELO DIRECTAMENTE:\n";
try {
    require_once "modelos/registro-descargas-simple.modelo.php";
    
    $datos = [
        "codigo_producto" => $_POST["codigo_producto"],
        "descripcion_producto" => $_POST["descripcion_producto"],
        "cantidad_descargada" => $_POST["cantidad_descargada"],
        "usuario_id" => $_POST["usuario_id"],
        "usuario_nombre" => $_POST["usuario_nombre"],
        "sucursal_id" => $_POST["sucursal_id"],
        "sucursal_nombre" => $_POST["sucursal_nombre"],
        "transportador_id" => $_POST["transportador_id"],
        "transportador_nombre" => $_POST["transportador_nombre"],
        "numero_despacho" => $_POST["numero_despacho"],
        "observaciones" => $_POST["observaciones"]
    ];
    
    $resultado = ModeloRegistroDescargasSimple::mdlRegistrarDescarga($datos);
    
    echo "✅ Modelo ejecutado\n";
    echo "   Resultado: $resultado\n";
    
    if ($resultado == "ok") {
        echo "✅ Registro en BD exitoso\n";
    } else {
        echo "❌ Error en modelo: $resultado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error con modelo: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🌐 4. PROBANDO CONEXIÓN CENTRAL:\n";
try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    
    if ($conexionCentral) {
        echo "✅ Conexión central exitosa\n";
        
        // Verificar que la tabla existe
        $stmt = $conexionCentral->prepare("SHOW TABLES LIKE 'registro_descargas_stock_transito'");
        $stmt->execute();
        $tabla = $stmt->fetch();
        
        if ($tabla) {
            echo "✅ Tabla existe\n";
            
            // Contar registros
            $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
            $stmt->execute();
            $count = $stmt->fetch();
            echo "   Total registros: " . $count['total'] . "\n";
            
            // Verificar último registro
            $stmt = $conexionCentral->prepare("SELECT * FROM registro_descargas_stock_transito ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $ultimo = $stmt->fetch();
            
            if ($ultimo) {
                echo "✅ Último registro:\n";
                echo "   ID: " . $ultimo['id'] . "\n";
                echo "   Código: " . $ultimo['codigo_producto'] . "\n";
                echo "   Sucursal: " . $ultimo['sucursal_nombre'] . "\n";
                echo "   Fecha: " . $ultimo['fecha_descarga'] . "\n";
            }
            
        } else {
            echo "❌ Tabla no existe\n";
        }
        
    } else {
        echo "❌ No se pudo conectar a BD central\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error con conexión central: " . $e->getMessage() . "\n";
}

echo "\n🔗 5. PROBANDO ENDPOINT AJAX:\n";
try {
    // Simular llamada AJAX
    $url = "ajax/registro-descargas-simple.ajax.php";
    
    // Crear contexto POST
    $postData = http_build_query($_POST);
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

echo "\n🎯 FLUJO COMPLETO PROBADO\n";
echo "========================\n";

?>
