<?php
/*=============================================
VERIFICAR REGISTRO CREADO DESDE JAVASCRIPT
=============================================*/

echo "🔍 VERIFICANDO REGISTRO CREADO DESDE JAVASCRIPT\n";
echo "==============================================\n\n";

// Verificar conexión central
echo "🌐 1. CONEXIÓN CENTRAL:\n";
try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    
    if ($conexionCentral) {
        echo "✅ Conexión central exitosa\n";
        
        // Verificar registros recientes
        $stmt = $conexionCentral->prepare("
            SELECT * FROM registro_descargas_stock_transito 
            ORDER BY id DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $registros = $stmt->fetchAll();
        
        echo "📊 Últimos 5 registros:\n";
        foreach($registros as $registro) {
            echo "   ID: " . $registro['id'] . "\n";
            echo "   Código: " . $registro['codigo_producto'] . "\n";
            echo "   Sucursal: " . $registro['sucursal_nombre'] . "\n";
            echo "   Usuario: " . $registro['usuario_nombre'] . "\n";
            echo "   Cantidad: " . $registro['cantidad_descargada'] . "\n";
            echo "   Fecha: " . $registro['fecha_descarga'] . "\n";
            echo "   ---\n";
        }
        
        // Verificar registro específico del producto 373
        $stmt = $conexionCentral->prepare("
            SELECT * FROM registro_descargas_stock_transito 
            WHERE codigo_producto = '373' 
            ORDER BY fecha_descarga DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $registro373 = $stmt->fetch();
        
        if ($registro373) {
            echo "✅ Registro del producto 373 encontrado:\n";
            echo "   ID: " . $registro373['id'] . "\n";
            echo "   Código: " . $registro373['codigo_producto'] . "\n";
            echo "   Sucursal: " . $registro373['sucursal_nombre'] . "\n";
            echo "   Usuario: " . $registro373['usuario_nombre'] . "\n";
            echo "   Cantidad: " . $registro373['cantidad_descargada'] . "\n";
            echo "   Fecha: " . $registro373['fecha_descarga'] . "\n";
        } else {
            echo "❌ No se encontró registro del producto 373\n";
        }
        
    } else {
        echo "❌ No se pudo conectar a BD central\n";
    }
} catch (Exception $e) {
    echo "❌ Error con conexión central: " . $e->getMessage() . "\n";
}

// Verificar estadísticas
echo "\n📊 2. ESTADÍSTICAS:\n";
try {
    $stmt = $conexionCentral->prepare("
        SELECT 
            COUNT(*) as total_descargas,
            SUM(cantidad_descargada) as total_cantidad,
            COUNT(DISTINCT codigo_producto) as productos_unicos,
            COUNT(DISTINCT usuario_id) as usuarios_unicos,
            COUNT(DISTINCT sucursal_id) as sucursales_unicas
        FROM registro_descargas_stock_transito
    ");
    $stmt->execute();
    $stats = $stmt->fetch();
    
    echo "✅ Estadísticas generales:\n";
    echo "   Total descargas: " . $stats['total_descargas'] . "\n";
    echo "   Total cantidad: " . $stats['total_cantidad'] . "\n";
    echo "   Productos únicos: " . $stats['productos_unicos'] . "\n";
    echo "   Usuarios únicos: " . $stats['usuarios_unicos'] . "\n";
    echo "   Sucursales únicas: " . $stats['sucursales_unicas'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error obteniendo estadísticas: " . $e->getMessage() . "\n";
}

echo "\n🎯 VERIFICACIÓN COMPLETADA\n";
echo "========================\n";

?>
