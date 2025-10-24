<?php
/*=============================================
VERIFICAR REGISTROS EN BASE DE DATOS
=============================================*/

// Incluir conexión
require_once "modelos/conexion.php";

echo "<h2>🔍 Verificar Registros en Base de Datos</h2>";

try {
    $conexion = Conexion::conectar();
    
    // 1. Verificar información de la conexión
    echo "<h3>1. Información de Conexión</h3>";
    $stmt = $conexion->prepare("SELECT DATABASE() as db_name, USER() as user_name, CONNECTION_ID() as connection_id");
    $stmt->execute();
    $info = $stmt->fetch();
    echo "📊 Base de datos: <strong>" . $info['db_name'] . "</strong><br>";
    echo "👤 Usuario: <strong>" . $info['user_name'] . "</strong><br>";
    echo "🔗 ID Conexión: <strong>" . $info['connection_id'] . "</strong><br>";
    
    // 2. Verificar todas las tablas que contengan 'registro' o 'descarga'
    echo "<h3>2. Tablas Relacionadas</h3>";
    $stmt = $conexion->prepare("SHOW TABLES LIKE '%registro%'");
    $stmt->execute();
    $tablas = $stmt->fetchAll();
    
    echo "📋 Tablas con 'registro':<br>";
    foreach($tablas as $tabla) {
        $nombreTabla = array_values($tabla)[0];
        echo "- $nombreTabla<br>";
        
        // Contar registros en cada tabla
        $stmt2 = $conexion->prepare("SELECT COUNT(*) as total FROM `$nombreTabla`");
        $stmt2->execute();
        $total = $stmt2->fetch()['total'];
        echo "  📊 Registros: $total<br>";
    }
    
    // 3. Verificar tabla específica
    echo "<h3>3. Verificar Tabla Específica</h3>";
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    echo "📊 Total en 'registro_descargas_stock_transito': <strong>$total</strong><br>";
    
    if($total > 0) {
        echo "<h4>Últimos registros:</h4>";
        $stmt = $conexion->prepare("
            SELECT 
                id,
                fecha_descarga,
                codigo_producto,
                usuario_nombre,
                created_at
            FROM registro_descargas_stock_transito 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $registros = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Fecha Descarga</th><th>Código</th><th>Usuario</th><th>Created At</th></tr>";
        foreach($registros as $registro) {
            echo "<tr>";
            echo "<td>" . $registro['id'] . "</td>";
            echo "<td>" . $registro['fecha_descarga'] . "</td>";
            echo "<td>" . $registro['codigo_producto'] . "</td>";
            echo "<td>" . $registro['usuario_nombre'] . "</td>";
            echo "<td>" . $registro['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Verificar si hay registros en otras bases de datos
    echo "<h3>4. Verificar Otras Bases de Datos</h3>";
    $stmt = $conexion->prepare("SHOW DATABASES");
    $stmt->execute();
    $bases = $stmt->fetchAll();
    
    echo "📋 Bases de datos disponibles:<br>";
    foreach($bases as $base) {
        $nombreBase = array_values($base)[0];
        echo "- $nombreBase<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "📍 Archivo: " . $e->getFile() . "<br>";
    echo "📍 Línea: " . $e->getLine() . "<br>";
}
?>
