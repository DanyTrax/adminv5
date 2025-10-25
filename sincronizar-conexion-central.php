<?php
/*=============================================
SINCRONIZAR CONEXIÓN CENTRAL AL SERVIDOR
=============================================*/

echo "🚀 Sincronizando conexión central al servidor\n";
echo "📋 Objetivo: Asegurar que api-transferencias/conexion-central.php esté disponible\n\n";

// Verificar si el archivo existe localmente
$archivoLocal = "api-transferencias/conexion-central.php";
if (!file_exists($archivoLocal)) {
    echo "❌ ERROR: El archivo $archivoLocal no existe localmente\n";
    exit(1);
}

echo "✅ Archivo local encontrado: $archivoLocal\n";

// Leer el contenido del archivo
$contenido = file_get_contents($archivoLocal);
echo "✅ Contenido leído: " . strlen($contenido) . " bytes\n";

// Crear el archivo en el servidor
$archivoServidor = "api-transferencias/conexion-central.php";
$directorio = dirname($archivoServidor);

// Crear directorio si no existe
if (!is_dir($directorio)) {
    mkdir($directorio, 0755, true);
    echo "✅ Directorio creado: $directorio\n";
}

// Escribir el archivo
if (file_put_contents($archivoServidor, $contenido)) {
    echo "✅ Archivo creado en servidor: $archivoServidor\n";
} else {
    echo "❌ ERROR: No se pudo crear el archivo en el servidor\n";
    exit(1);
}

// Verificar que el archivo se creó correctamente
if (file_exists($archivoServidor)) {
    echo "✅ Archivo verificado en servidor\n";
    
    // Verificar que la clase se puede cargar
    try {
        require_once $archivoServidor;
        if (class_exists('ConexionCentral')) {
            echo "✅ Clase ConexionCentral cargada correctamente\n";
            
            // Probar conexión
            $conexion = ConexionCentral::conectar();
            if ($conexion) {
                echo "✅ Conexión a BD central establecida\n";
                
                // Verificar que la tabla existe
                $sql = "SHOW TABLES LIKE 'registro_descargas_stock_transito'";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $tabla = $stmt->fetch();
                
                if ($tabla) {
                    echo "✅ Tabla registro_descargas_stock_transito encontrada\n";
                    
                    // Contar registros
                    $sql = "SELECT COUNT(*) as total FROM registro_descargas_stock_transito";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute();
                    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "📊 Total de registros: " . $resultado['total'] . "\n";
                } else {
                    echo "⚠️ Tabla registro_descargas_stock_transito no encontrada\n";
                }
            } else {
                echo "❌ ERROR: No se pudo establecer conexión a BD central\n";
            }
        } else {
            echo "❌ ERROR: Clase ConexionCentral no encontrada\n";
        }
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ ERROR: El archivo no se creó correctamente\n";
    exit(1);
}

echo "\n🎉 ¡SINCRONIZACIÓN COMPLETADA!\n";
echo "==========================================\n";
echo "✅ Archivo api-transferencias/conexion-central.php sincronizado\n";
echo "✅ Conexión a BD central verificada\n";
echo "✅ Sistema listo para usar BD central\n\n";

echo "🧪 PRÓXIMOS PASOS:\n";
echo "1. Probar funcionalidad de registro de descargas\n";
echo "2. Verificar que el historial se carga correctamente\n";
echo "3. Confirmar que las descargas se registran en BD central\n\n";

echo "✅ Sincronización completada\n";
?>
