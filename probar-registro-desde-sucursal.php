<?php
/*=============================================
PROBAR REGISTRO DE DESCARGA DESDE SUCURSAL
=============================================*/

echo "🧪 PROBANDO REGISTRO DE DESCARGA DESDE ESTA SUCURSAL\n";
echo "===================================================\n\n";

// 1. Obtener datos de la sucursal actual
echo "🏢 1. DATOS DE SUCURSAL ACTUAL:\n";
try {
    require_once "modelos/conexion.php";
    $stmt = Conexion::conectar()->prepare("SELECT * FROM sucursal_local WHERE id = 1");
    $stmt->execute();
    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sucursal) {
        echo "✅ Sucursal: " . $sucursal['nombre'] . " (ID: " . $sucursal['id'] . ")\n";
        echo "   Código: " . $sucursal['codigo_sucursal'] . "\n";
        echo "   Host BD: " . $sucursal['host_bd'] . "\n";
        echo "   Nombre BD: " . $sucursal['nombre_bd'] . "\n";
    } else {
        echo "❌ No se encontró configuración de sucursal\n";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit;
}

// 2. Verificar conexión central
echo "\n🌐 2. CONEXIÓN CENTRAL:\n";
try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    echo "✅ Conexión central exitosa\n";
} catch (Exception $e) {
    echo "❌ Error de conexión central: " . $e->getMessage() . "\n";
    exit;
}

// 3. Simular registro de descarga
echo "\n📝 3. SIMULANDO REGISTRO DE DESCARGA:\n";

// Datos de prueba
$datosPrueba = [
    "codigo_producto" => "TEST" . date('His'),
    "descripcion_producto" => "Producto de prueba desde " . $sucursal['nombre'],
    "cantidad_descargada" => 1,
    "usuario_id" => "999",
    "usuario_nombre" => "Usuario Prueba",
    "sucursal_id" => $sucursal['id'],
    "sucursal_nombre" => $sucursal['nombre'],
    "transportador_id" => "0",
    "transportador_nombre" => "Transportador Prueba",
    "numero_despacho" => "",
    "observaciones" => "Prueba desde " . $sucursal['nombre'] . " - " . date('Y-m-d H:i:s')
];

echo "📋 Datos a registrar:\n";
foreach($datosPrueba as $key => $value) {
    echo "   $key: $value\n";
}

// 4. Intentar registrar directamente en la BD central
echo "\n💾 4. REGISTRANDO EN BD CENTRAL:\n";
try {
    date_default_timezone_set('America/Bogota');
    
    $stmt = $conexionCentral->prepare("INSERT INTO registro_descargas_stock_transito (
        codigo_producto, 
        descripcion_producto, 
        cantidad_descargada, 
        usuario_id, 
        usuario_nombre, 
        sucursal_id, 
        sucursal_nombre, 
        transportador_id, 
        transportador_nombre, 
        numero_despacho, 
        observaciones, 
        fecha_descarga, 
        ip_usuario, 
        user_agent, 
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $resultado = $stmt->execute([
        $datosPrueba["codigo_producto"],
        $datosPrueba["descripcion_producto"],
        $datosPrueba["cantidad_descargada"],
        $datosPrueba["usuario_id"],
        $datosPrueba["usuario_nombre"],
        $datosPrueba["sucursal_id"],
        $datosPrueba["sucursal_nombre"],
        $datosPrueba["transportador_id"],
        $datosPrueba["transportador_nombre"],
        $datosPrueba["numero_despacho"],
        $datosPrueba["observaciones"],
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Script',
        date('Y-m-d H:i:s')
    ]);
    
    if ($resultado) {
        echo "✅ Registro creado exitosamente\n";
        echo "   ID del registro: " . $conexionCentral->lastInsertId() . "\n";
        
        // Verificar que se creó
        $stmt = $conexionCentral->prepare("
            SELECT * FROM registro_descargas_stock_transito 
            WHERE codigo_producto = ? 
            ORDER BY fecha_descarga DESC 
            LIMIT 1
        ");
        $stmt->execute([$datosPrueba["codigo_producto"]]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($registro) {
            echo "✅ Registro verificado:\n";
            echo "   ID: " . $registro['id'] . "\n";
            echo "   Código: " . $registro['codigo_producto'] . "\n";
            echo "   Sucursal: " . $registro['sucursal_nombre'] . "\n";
            echo "   Usuario: " . $registro['usuario_nombre'] . "\n";
            echo "   Fecha: " . $registro['fecha_descarga'] . "\n";
        }
        
    } else {
        echo "❌ Error creando registro\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en registro: " . $e->getMessage() . "\n";
}

// 5. Probar usando el controlador
echo "\n🎮 5. PROBANDO CON CONTROLADOR:\n";
try {
    // Simular POST
    $_POST["accion"] = "registrar_descarga";
    $_POST["codigo_producto"] = "CTRL" . date('His');
    $_POST["cantidad_descargada"] = 1;
    $_POST["descripcion_producto"] = "Prueba con controlador desde " . $sucursal['nombre'];
    $_POST["usuario_id"] = "888";
    $_POST["usuario_nombre"] = "Controlador Prueba";
    $_POST["sucursal_id"] = $sucursal['id'];
    $_POST["sucursal_nombre"] = $sucursal['nombre'];
    $_POST["transportador_id"] = "0";
    $_POST["transportador_nombre"] = "Controlador Test";
    $_POST["numero_despacho"] = "";
    $_POST["observaciones"] = "Prueba con controlador - " . date('Y-m-d H:i:s');
    
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

echo "\n🎯 PRUEBA COMPLETADA\n";
echo "===================\n";
echo "Si hay ✅, el registro funciona desde esta sucursal\n";
echo "Si hay ❌, hay problemas de configuración\n";

?>
