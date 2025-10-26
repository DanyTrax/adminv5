<?php
/*=============================================
VERIFICAR CONFIGURACIÓN DINÁMICA DE SUCURSAL
=============================================*/

echo "🔍 VERIFICANDO CONFIGURACIÓN DINÁMICA DE SUCURSAL\n";
echo "================================================\n\n";

// 1. Verificar configuración de sucursal local
echo "🏢 1. CONFIGURACIÓN DE SUCURSAL LOCAL:\n";
try {
    require_once "modelos/conexion.php";
    $stmt = Conexion::conectar()->prepare("SELECT * FROM sucursal_local WHERE id = 1");
    $stmt->execute();
    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sucursal) {
        echo "✅ Configuración encontrada:\n";
        echo "   ID: " . $sucursal['id'] . "\n";
        echo "   Código: " . $sucursal['codigo_sucursal'] . "\n";
        echo "   Nombre: " . $sucursal['nombre'] . "\n";
        echo "   URL Base: " . $sucursal['url_base'] . "\n";
        echo "   URL API: " . $sucursal['url_api'] . "\n";
        echo "   Host BD: " . $sucursal['host_bd'] . "\n";
        echo "   Nombre BD: " . $sucursal['nombre_bd'] . "\n";
        echo "   Usuario BD: " . $sucursal['usuario_bd'] . "\n";
        echo "   Puerto BD: " . $sucursal['puerto_bd'] . "\n";
        echo "   Activo: " . ($sucursal['activo'] ? 'Sí' : 'No') . "\n";
        
        // Verificar si las URLs están configuradas
        if (empty($sucursal['url_base'])) {
            echo "⚠️ URL Base está vacía\n";
        } else {
            echo "✅ URL Base configurada: " . $sucursal['url_base'] . "\n";
        }
        
        if (empty($sucursal['url_api'])) {
            echo "⚠️ URL API está vacía\n";
        } else {
            echo "✅ URL API configurada: " . $sucursal['url_api'] . "\n";
        }
        
    } else {
        echo "❌ No se encontró configuración de sucursal\n";
    }
} catch (Exception $e) {
    echo "❌ Error obteniendo configuración: " . $e->getMessage() . "\n";
}

// 2. Verificar endpoint AJAX
echo "\n🔗 2. VERIFICANDO ENDPOINT AJAX:\n";
try {
    $url = "ajax/obtener-sucursal-actual.ajax.php";
    
    if (file_exists($url)) {
        echo "✅ Archivo endpoint existe: $url\n";
        
        // Probar endpoint
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 10
            ]
        ]);
        
        $response = file_get_contents($url . "?accion=obtener_sucursal_actual", false, $context);
        $data = json_decode($response, true);
        
        if ($data && $data['success']) {
            echo "✅ Endpoint funcionando\n";
            echo "   Sucursal: " . $data['sucursal']['nombre'] . "\n";
            echo "   URL Base: " . $data['sucursal']['url_base'] . "\n";
            echo "   URL API: " . $data['sucursal']['url_api'] . "\n";
        } else {
            echo "❌ Endpoint no funciona: " . ($response ?: "Sin respuesta") . "\n";
        }
        
    } else {
        echo "❌ Archivo endpoint no existe: $url\n";
    }
} catch (Exception $e) {
    echo "❌ Error probando endpoint: " . $e->getMessage() . "\n";
}

// 3. Verificar JavaScript
echo "\n📜 3. VERIFICANDO JAVASCRIPT:\n";
try {
    $contenidoJS = file_get_contents("vistas/js/stock-transito-unificado.js");
    
    if (strpos($contenidoJS, "registrarDescargaOptimizada") !== false) {
        echo "✅ Función registrarDescargaOptimizada encontrada\n";
    } else {
        echo "❌ Función registrarDescargaOptimizada NO encontrada\n";
    }
    
    if (strpos($contenidoJS, "url_base") !== false) {
        echo "✅ Referencia a url_base encontrada en JavaScript\n";
    } else {
        echo "❌ Referencia a url_base NO encontrada en JavaScript\n";
    }
    
    if (strpos($contenidoJS, "url_api") !== false) {
        echo "✅ Referencia a url_api encontrada en JavaScript\n";
    } else {
        echo "❌ Referencia a url_api NO encontrada en JavaScript\n";
    }
    
    echo "📏 Tamaño del archivo JS: " . strlen($contenidoJS) . " caracteres\n";
    
} catch (Exception $e) {
    echo "❌ Error verificando JavaScript: " . $e->getMessage() . "\n";
}

// 4. Verificar conexión central
echo "\n🌐 4. VERIFICANDO CONEXIÓN CENTRAL:\n";
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

// 5. Verificar URLs actuales
echo "\n🌍 5. VERIFICANDO URLs ACTUALES:\n";
echo "   URL actual: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "\n";
echo "   Host: " . $_SERVER['HTTP_HOST'] . "\n";
echo "   Server Name: " . $_SERVER['SERVER_NAME'] . "\n";
echo "   Request URI: " . $_SERVER['REQUEST_URI'] . "\n";

echo "\n🎯 VERIFICACIÓN COMPLETADA\n";
echo "========================\n";

?>
