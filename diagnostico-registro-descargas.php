<?php
/*=============================================
DIAGNÓSTICO COMPLETO DE REGISTRO DE DESCARGAS
=============================================*/

echo "🔍 DIAGNÓSTICO COMPLETO DE REGISTRO DE DESCARGAS\n";
echo "===============================================\n\n";

// 1. Verificar archivos necesarios
echo "📋 1. VERIFICANDO ARCHIVOS NECESARIOS:\n";
$archivosNecesarios = [
    "ajax/obtener-sucursal-actual.ajax.php",
    "ajax/obtener-usuario-actual.ajax.php", 
    "ajax/registro-descargas-simple.ajax.php",
    "controladores/registro-descargas-simple.controlador.php",
    "modelos/registro-descargas-simple.modelo.php",
    "api-transferencias/conexion-central.php"
];

foreach($archivosNecesarios as $archivo) {
    $rutaCompleta = __DIR__ . "/" . $archivo;
    if (file_exists($rutaCompleta)) {
        echo "✅ $archivo\n";
    } else {
        echo "❌ $archivo - FALTANTE\n";
    }
}

// 2. Verificar datos de sucursal local
echo "\n🏢 2. VERIFICANDO DATOS DE SUCURSAL LOCAL:\n";
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
    } else {
        echo "❌ No se encontró configuración de sucursal\n";
    }
} catch (Exception $e) {
    echo "❌ Error obteniendo datos de sucursal: " . $e->getMessage() . "\n";
}

// 3. Verificar conexión central
echo "\n🌐 3. VERIFICANDO CONEXIÓN CENTRAL:\n";
try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    echo "✅ Conexión central exitosa\n";
    
    // Verificar tabla de registro
    $stmt = $conexionCentral->prepare("SHOW TABLES LIKE 'registro_descargas_stock_transito'");
    $stmt->execute();
    $tabla = $stmt->fetch();
    
    if ($tabla) {
        echo "✅ Tabla registro_descargas_stock_transito existe\n";
        
        // Contar registros existentes
        $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 Registros existentes: " . $resultado['total'] . "\n";
        
        // Mostrar últimos 3 registros
        $stmt = $conexionCentral->prepare("
            SELECT codigo_producto, sucursal_nombre, usuario_nombre, fecha_descarga 
            FROM registro_descargas_stock_transito 
            ORDER BY fecha_descarga DESC 
            LIMIT 3
        ");
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📋 Últimos registros:\n";
        foreach($registros as $registro) {
            echo "   • {$registro['codigo_producto']} - {$registro['sucursal_nombre']} - {$registro['usuario_nombre']} - {$registro['fecha_descarga']}\n";
        }
        
    } else {
        echo "❌ Tabla registro_descargas_stock_transito NO existe\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión central: " . $e->getMessage() . "\n";
}

// 4. Probar endpoint de sucursal actual
echo "\n🔗 4. PROBANDO ENDPOINT DE SUCURSAL ACTUAL:\n";
try {
    $url = "ajax/obtener-sucursal-actual.ajax.php?accion=obtener_sucursal_actual";
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        echo "✅ Endpoint funcionando\n";
        echo "   Sucursal ID: " . $data['sucursal']['id'] . "\n";
        echo "   Sucursal Nombre: " . $data['sucursal']['nombre'] . "\n";
    } else {
        echo "❌ Endpoint no funciona: " . ($response ?: "Sin respuesta") . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error probando endpoint: " . $e->getMessage() . "\n";
}

// 5. Probar endpoint de usuario actual
echo "\n👤 5. PROBANDO ENDPOINT DE USUARIO ACTUAL:\n";
try {
    $url = "ajax/obtener-usuario-actual.ajax.php";
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);
    
    if ($data && $data['success']) {
        echo "✅ Endpoint funcionando\n";
        echo "   Usuario ID: " . $data['usuario']['id'] . "\n";
        echo "   Usuario Nombre: " . $data['usuario']['nombre'] . "\n";
    } else {
        echo "❌ Endpoint no funciona: " . ($response ?: "Sin respuesta") . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error probando endpoint: " . $e->getMessage() . "\n";
}

// 6. Probar registro de descarga directo
echo "\n🧪 6. PROBANDO REGISTRO DE DESCARGA DIRECTO:\n";
try {
    // Simular datos POST
    $_POST["accion"] = "registrar_descarga";
    $_POST["codigo_producto"] = "TEST" . date('His');
    $_POST["cantidad_descargada"] = 1;
    $_POST["descripcion_producto"] = "Producto de prueba diagnóstico";
    $_POST["usuario_id"] = "999";
    $_POST["usuario_nombre"] = "Usuario Diagnóstico";
    $_POST["sucursal_id"] = $sucursal['id'] ?? "1";
    $_POST["sucursal_nombre"] = $sucursal['nombre'] ?? "Sucursal Test";
    $_POST["transportador_id"] = "0";
    $_POST["transportador_nombre"] = "Test";
    $_POST["numero_despacho"] = "";
    $_POST["observaciones"] = "Prueba de diagnóstico";
    
    require_once "controladores/registro-descargas-simple.controlador.php";
    $controlador = new ControladorRegistroDescargasSimple();
    $resultado = $controlador->ctrRegistrarDescarga();
    
    if ($resultado["success"]) {
        echo "✅ Registro de descarga funcionando\n";
        echo "   Mensaje: " . $resultado["message"] . "\n";
    } else {
        echo "❌ Error en registro: " . $resultado["error"] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error probando registro: " . $e->getMessage() . "\n";
}

echo "\n🎯 DIAGNÓSTICO COMPLETADO\n";
echo "=========================\n";
echo "Si hay ❌, esos son los problemas a resolver\n";
echo "Si todo está ✅, el registro debería funcionar\n";

?>
