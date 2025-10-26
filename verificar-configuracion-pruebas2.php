<?php
/*=============================================
VERIFICAR CONFIGURACIÓN DE PRUEBAS2.ACPLASTICOS.COM
=============================================*/

echo "🔍 VERIFICANDO CONFIGURACIÓN DE PRUEBAS2.ACPLASTICOS.COM\n";
echo "=======================================================\n\n";

// 1. Verificar archivos necesarios
echo "📁 1. VERIFICANDO ARCHIVOS NECESARIOS:\n";
$archivos = [
    "controladores/registro-descargas-simple.controlador.php",
    "modelos/registro-descargas-simple.modelo.php",
    "ajax/registro-descargas-simple.ajax.php",
    "api-transferencias/conexion-central.php",
    "vistas/js/stock-transito-unificado.js"
];

foreach($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ $archivo existe\n";
    } else {
        echo "❌ $archivo NO existe\n";
    }
}

// 2. Verificar configuración de sucursal
echo "\n🏢 2. VERIFICANDO CONFIGURACIÓN DE SUCURSAL:\n";
try {
    require_once "modelos/conexion.php";
    $stmt = Conexion::conectar()->prepare("SELECT * FROM sucursal_local WHERE id = 1");
    $stmt->execute();
    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sucursal) {
        echo "✅ Configuración de sucursal encontrada:\n";
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
    } else {
        echo "❌ No se encontró configuración de sucursal\n";
    }
} catch (Exception $e) {
    echo "❌ Error obteniendo configuración: " . $e->getMessage() . "\n";
}

// 3. Verificar conexión central
echo "\n🌐 3. VERIFICANDO CONEXIÓN CENTRAL:\n";
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
            echo "✅ Tabla registro_descargas_stock_transito existe\n";
            
            // Verificar estructura de la tabla
            $stmt = $conexionCentral->prepare("DESCRIBE registro_descargas_stock_transito");
            $stmt->execute();
            $columnas = $stmt->fetchAll();
            
            echo "✅ Estructura de tabla:\n";
            foreach($columnas as $columna) {
                echo "   " . $columna['Field'] . " (" . $columna['Type'] . ")\n";
            }
            
        } else {
            echo "❌ Tabla registro_descargas_stock_transito NO existe\n";
        }
        
    } else {
        echo "❌ No se pudo conectar a BD central\n";
    }
} catch (Exception $e) {
    echo "❌ Error con conexión central: " . $e->getMessage() . "\n";
}

// 4. Verificar JavaScript
echo "\n📜 4. VERIFICANDO JAVASCRIPT:\n";
try {
    $contenidoJS = file_get_contents("vistas/js/stock-transito-unificado.js");
    
    if (strpos($contenidoJS, "registrarDescargaOptimizada") !== false) {
        echo "✅ Función registrarDescargaOptimizada encontrada\n";
    } else {
        echo "❌ Función registrarDescargaOptimizada NO encontrada\n";
    }
    
    if (strpos($contenidoJS, "ajax/registro-descargas-simple.ajax.php") !== false) {
        echo "✅ Endpoint AJAX encontrado en JavaScript\n";
    } else {
        echo "❌ Endpoint AJAX NO encontrado en JavaScript\n";
    }
    
    echo "📏 Tamaño del archivo JS: " . strlen($contenidoJS) . " caracteres\n";
    
} catch (Exception $e) {
    echo "❌ Error verificando JavaScript: " . $e->getMessage() . "\n";
}

// 5. Verificar permisos de archivos
echo "\n🔐 5. VERIFICANDO PERMISOS DE ARCHIVOS:\n";
foreach($archivos as $archivo) {
    if (file_exists($archivo)) {
        $permisos = fileperms($archivo);
        echo "✅ $archivo: " . substr(sprintf('%o', $permisos), -4) . "\n";
    }
}

// 6. Verificar logs de error
echo "\n📋 6. VERIFICANDO LOGS DE ERROR:\n";
$logFiles = [
    "error_log",
    "logs/error.log",
    "logs/registro-descargas.log"
];

foreach($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "✅ $logFile existe\n";
        $contenido = file_get_contents($logFile);
        $lineas = explode("\n", $contenido);
        $ultimasLineas = array_slice($lineas, -5);
        
        echo "   Últimas 5 líneas:\n";
        foreach($ultimasLineas as $linea) {
            if (!empty(trim($linea))) {
                echo "   " . $linea . "\n";
            }
        }
    } else {
        echo "❌ $logFile NO existe\n";
    }
}

echo "\n🎯 VERIFICACIÓN COMPLETADA\n";
echo "========================\n";

?>
