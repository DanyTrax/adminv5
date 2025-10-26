<?php
/*=============================================
PROBAR REGISTRO DESDE PRUEBAS2.ACPLASTICOS.COM
=============================================*/

echo "🧪 PROBANDO REGISTRO DESDE PRUEBAS2.ACPLASTICOS.COM\n";
echo "==================================================\n\n";

// Verificar datos de sucursal actual
echo "🏢 1. DATOS DE SUCURSAL ACTUAL:\n";
try {
    require_once "modelos/conexion.php";
    $stmt = Conexion::conectar()->prepare("SELECT * FROM sucursal_local WHERE id = 1");
    $stmt->execute();
    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sucursal) {
        echo "✅ Sucursal encontrada:\n";
        echo "   ID: " . $sucursal['id'] . "\n";
        echo "   Código: " . $sucursal['codigo_sucursal'] . "\n";
        echo "   Nombre: " . $sucursal['nombre'] . "\n";
        echo "   Host BD: " . $sucursal['host_bd'] . "\n";
        echo "   Nombre BD: " . $sucursal['nombre_bd'] . "\n";
        echo "   URL Base: " . $sucursal['url_base'] . "\n";
    } else {
        echo "❌ No se encontró configuración de sucursal\n";
    }
} catch (Exception $e) {
    echo "❌ Error obteniendo sucursal: " . $e->getMessage() . "\n";
}

// Verificar conexión central
echo "\n🌐 2. CONEXIÓN CENTRAL:\n";
try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    
    if ($conexionCentral) {
        echo "✅ Conexión central exitosa\n";
        
        // Verificar registros existentes
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
            echo "   Usuario: " . $ultimo['usuario_nombre'] . "\n";
            echo "   Fecha: " . $ultimo['fecha_descarga'] . "\n";
        }
        
    } else {
        echo "❌ No se pudo conectar a BD central\n";
    }
} catch (Exception $e) {
    echo "❌ Error con conexión central: " . $e->getMessage() . "\n";
}

// Probar registro con datos específicos de esta sucursal
echo "\n📝 3. PROBANDO REGISTRO DESDE ESTA SUCURSAL:\n";

$datosRegistro = [
    "accion" => "registrar_descarga",
    "codigo_producto" => "PRUEBAS2" . date('His'),
    "descripcion_producto" => "Producto desde pruebas2.acplasticos.com",
    "cantidad_descargada" => 1,
    "usuario_id" => "999",
    "usuario_nombre" => "Usuario Sistema",
    "sucursal_id" => $sucursal['id'] ?? "1",
    "sucursal_nombre" => $sucursal['nombre'] ?? "Sucursal 2",
    "transportador_id" => "0",
    "transportador_nombre" => "Transportador Sistema",
    "numero_despacho" => "",
    "observaciones" => "Prueba desde pruebas2.acplasticos.com - " . date('Y-m-d H:i:s')
];

echo "📋 Datos a registrar:\n";
foreach($datosRegistro as $key => $value) {
    echo "   $key: $value\n";
}

// Simular $_POST
$_POST = $datosRegistro;

// Probar controlador
echo "\n🎮 4. PROBANDO CONTROLADOR:\n";
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

// Probar endpoint AJAX
echo "\n🔗 5. PROBANDO ENDPOINT AJAX:\n";
try {
    $url = "ajax/registro-descargas-simple.ajax.php";
    
    $postData = http_build_query($datosRegistro);
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

// Verificar que se creó el registro
echo "\n🔍 6. VERIFICANDO REGISTRO CREADO:\n";
try {
    $stmt = $conexionCentral->prepare("
        SELECT * FROM registro_descargas_stock_transito 
        WHERE codigo_producto = ? 
        ORDER BY fecha_descarga DESC 
        LIMIT 1
    ");
    $stmt->execute([$datosRegistro["codigo_producto"]]);
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

echo "\n🎯 PRUEBA COMPLETADA\n";
echo "===================\n";

?>
