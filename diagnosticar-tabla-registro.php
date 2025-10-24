<?php
/*=============================================
DIAGNÓSTICO TABLA REGISTRO DESCARGAS
=============================================*/

// Incluir conexión
require_once "modelos/conexion.php";

echo "<h2>🔍 Diagnóstico Tabla Registro Descargas</h2>";

try {
    // 1. Verificar conexión
    echo "<h3>1. Verificar Conexión</h3>";
    $conexion = Conexion::conectar();
    echo "✅ Conexión exitosa<br>";
    
    // 2. Verificar tabla
    echo "<h3>2. Verificar Tabla</h3>";
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'registro_descargas_stock_transito'");
    $stmt->execute();
    $tabla = $stmt->fetch();
    
    if($tabla) {
        echo "✅ Tabla 'registro_descargas_stock_transito' existe<br>";
        
        // 3. Verificar estructura
        echo "<h3>3. Estructura de la Tabla</h3>";
        $stmt = $conexion->prepare("DESCRIBE registro_descargas_stock_transito");
        $stmt->execute();
        $columnas = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach($columnas as $columna) {
            echo "<tr>";
            echo "<td>" . $columna['Field'] . "</td>";
            echo "<td>" . $columna['Type'] . "</td>";
            echo "<td>" . $columna['Null'] . "</td>";
            echo "<td>" . $columna['Key'] . "</td>";
            echo "<td>" . $columna['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 4. Verificar datos
        echo "<h3>4. Datos en la Tabla</h3>";
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
        $stmt->execute();
        $total = $stmt->fetch()['total'];
        echo "📊 Total de registros: <strong>$total</strong><br>";
        
        if($total > 0) {
            echo "<h4>Últimos 5 registros:</h4>";
            $stmt = $conexion->prepare("
                SELECT 
                    id,
                    fecha_descarga,
                    codigo_producto,
                    descripcion_producto,
                    cantidad_descargada,
                    usuario_nombre,
                    transportador_nombre,
                    sucursal_nombre,
                    numero_despacho,
                    observaciones,
                    created_at
                FROM registro_descargas_stock_transito 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $stmt->execute();
            $registros = $stmt->fetchAll();
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Fecha Descarga</th><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Usuario</th><th>Transportador</th><th>Sucursal</th><th>Created At</th></tr>";
            foreach($registros as $registro) {
                echo "<tr>";
                echo "<td>" . $registro['id'] . "</td>";
                echo "<td>" . $registro['fecha_descarga'] . "</td>";
                echo "<td>" . $registro['codigo_producto'] . "</td>";
                echo "<td>" . substr($registro['descripcion_producto'], 0, 30) . "...</td>";
                echo "<td>" . $registro['cantidad_descargada'] . "</td>";
                echo "<td>" . $registro['usuario_nombre'] . "</td>";
                echo "<td>" . $registro['transportador_nombre'] . "</td>";
                echo "<td>" . $registro['sucursal_nombre'] . "</td>";
                echo "<td>" . $registro['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // 5. Probar consulta del AJAX
        echo "<h3>5. Probar Consulta del AJAX</h3>";
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
        $datos = $stmt->fetchAll();
        
        echo "✅ Consulta AJAX ejecutada correctamente<br>";
        echo "📊 Registros obtenidos: " . count($datos) . "<br>";
        
        if(count($datos) > 0) {
            echo "<h4>Datos formateados para DataTable:</h4>";
            echo "<pre>";
            print_r($datos);
            echo "</pre>";
        }
        
    } else {
        echo "❌ Tabla 'registro_descargas_stock_transito' NO existe<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "📍 Archivo: " . $e->getFile() . "<br>";
    echo "📍 Línea: " . $e->getLine() . "<br>";
}

echo "<h3>6. Verificar Archivos AJAX</h3>";
$archivos = [
    'ajax/datatable-registro-descargas-simple.ajax.php',
    'ajax/registro-descargas-simple.ajax.php',
    'ajax/obtener-usuario-actual.ajax.php'
];

foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        echo "✅ $archivo existe<br>";
    } else {
        echo "❌ $archivo NO existe<br>";
    }
}

echo "<h3>7. Verificar Permisos</h3>";
foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        $permisos = substr(sprintf('%o', fileperms($archivo)), -4);
        echo "📁 $archivo: permisos $permisos<br>";
    }
}
?>
