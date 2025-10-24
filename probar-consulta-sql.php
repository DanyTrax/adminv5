<?php
/*=============================================
PROBAR CONSULTA SQL
=============================================*/

// Incluir conexión
require_once "modelos/conexion.php";

echo "<h2>🔍 Probar Consulta SQL</h2>";

try {
    $conexion = Conexion::conectar();
    
    // 1. Verificar que hay datos
    echo "<h3>1. Verificar datos en la tabla</h3>";
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    echo "📊 Total de registros: <strong>$total</strong><br>";
    
    if($total > 0) {
        // 2. Probar consulta simple
        echo "<h3>2. Consulta simple</h3>";
        $stmt = $conexion->prepare("SELECT * FROM registro_descargas_stock_transito ORDER BY created_at DESC LIMIT 5");
        $stmt->execute();
        $datos = $stmt->fetchAll();
        
        echo "📊 Registros obtenidos: " . count($datos) . "<br>";
        echo "<pre>";
        print_r($datos);
        echo "</pre>";
        
        // 3. Probar consulta con DATE_FORMAT
        echo "<h3>3. Consulta con DATE_FORMAT</h3>";
        $stmt = $conexion->prepare("
            SELECT 
                id,
                DATE_FORMAT(fecha_descarga, '%d/%m/%Y %H:%i:%s') as fecha_hora,
                codigo_producto,
                descripcion_producto,
                cantidad_descargada,
                usuario_nombre,
                transportador_nombre,
                sucursal_nombre,
                numero_despacho,
                observaciones
            FROM registro_descargas_stock_transito 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $datosFormateados = $stmt->fetchAll();
        
        echo "📊 Registros formateados: " . count($datosFormateados) . "<br>";
        echo "<pre>";
        print_r($datosFormateados);
        echo "</pre>";
        
        // 4. Verificar estructura de fecha_descarga
        echo "<h3>4. Verificar estructura de fecha_descarga</h3>";
        $stmt = $conexion->prepare("
            SELECT 
                id,
                fecha_descarga,
                created_at,
                DATE_FORMAT(fecha_descarga, '%d/%m/%Y %H:%i:%s') as fecha_formateada
            FROM registro_descargas_stock_transito 
            ORDER BY created_at DESC 
            LIMIT 3
        ");
        $stmt->execute();
        $fechas = $stmt->fetchAll();
        
        echo "<pre>";
        print_r($fechas);
        echo "</pre>";
        
    } else {
        echo "❌ No hay registros en la tabla<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "📍 Archivo: " . $e->getFile() . "<br>";
    echo "📍 Línea: " . $e->getLine() . "<br>";
}
?>
