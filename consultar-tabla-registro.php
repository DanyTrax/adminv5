<?php
/*=============================================
CONSULTAR TABLA REGISTRO DESCARGAS
=============================================*/

// Incluir conexión
require_once "modelos/conexion.php";

echo "<h2>🔍 Consultar Tabla Registro Descargas</h2>";

try {
    $conexion = Conexion::conectar();
    
    // 1. Información de conexión
    echo "<h3>1. Información de Conexión</h3>";
    $stmt = $conexion->prepare("SELECT DATABASE() as db_name, USER() as user_name");
    $stmt->execute();
    $info = $stmt->fetch();
    echo "📊 Base de datos: <strong>" . $info['db_name'] . "</strong><br>";
    echo "👤 Usuario: <strong>" . $info['user_name'] . "</strong><br>";
    
    // 2. Estructura de la tabla
    echo "<h3>2. Estructura de la Tabla</h3>";
    $stmt = $conexion->prepare("DESCRIBE registro_descargas_stock_transito");
    $stmt->execute();
    $columnas = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach($columnas as $columna) {
        echo "<tr>";
        echo "<td><strong>" . $columna['Field'] . "</strong></td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "<td>" . $columna['Default'] . "</td>";
        echo "<td>" . $columna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Contar registros
    echo "<h3>3. Conteo de Registros</h3>";
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    echo "📊 Total de registros: <strong>$total</strong><br>";
    
    if($total > 0) {
        // 4. Mostrar todos los registros
        echo "<h3>4. Todos los Registros</h3>";
        $stmt = $conexion->prepare("SELECT * FROM registro_descargas_stock_transito ORDER BY created_at DESC");
        $stmt->execute();
        $registros = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        foreach($columnas as $columna) {
            echo "<th>" . $columna['Field'] . "</th>";
        }
        echo "</tr>";
        
        foreach($registros as $registro) {
            echo "<tr>";
            foreach($columnas as $columna) {
                $valor = $registro[$columna['Field']];
                if($valor === null) {
                    $valor = "<em>NULL</em>";
                } elseif($valor === '') {
                    $valor = "<em>VACÍO</em>";
                }
                echo "<td>" . htmlspecialchars($valor) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // 5. Probar consulta específica del AJAX
        echo "<h3>5. Consulta del AJAX (con DATE_FORMAT)</h3>";
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
            LIMIT 10
        ");
        $stmt->execute();
        $datosFormateados = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #e0f0ff;'>";
        echo "<th>ID</th><th>Fecha y Hora</th><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Usuario</th><th>Transportador</th><th>Sucursal</th><th>Despacho</th><th>Observaciones</th>";
        echo "</tr>";
        
        foreach($datosFormateados as $dato) {
            echo "<tr>";
            echo "<td>" . $dato['id'] . "</td>";
            echo "<td>" . $dato['fecha_hora'] . "</td>";
            echo "<td>" . $dato['codigo_producto'] . "</td>";
            echo "<td>" . substr($dato['descripcion_producto'], 0, 30) . "...</td>";
            echo "<td>" . $dato['cantidad_descargada'] . "</td>";
            echo "<td>" . $dato['usuario_nombre'] . "</td>";
            echo "<td>" . $dato['transportador_nombre'] . "</td>";
            echo "<td>" . $dato['sucursal_nombre'] . "</td>";
            echo "<td>" . $dato['numero_despacho'] . "</td>";
            echo "<td>" . $dato['observaciones'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 6. Simular respuesta JSON del AJAX
        echo "<h3>6. Respuesta JSON del AJAX</h3>";
        $respuesta = [
            "draw" => 1,
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $datosFormateados
        ];
        
        echo "<pre style='background-color: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>";
        echo htmlspecialchars(json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "</pre>";
        
    } else {
        echo "<h3>4. No hay registros en la tabla</h3>";
        echo "❌ La tabla está vacía. No se han registrado descargas.<br>";
    }
    
    // 7. Verificar índices
    echo "<h3>7. Índices de la Tabla</h3>";
    $stmt = $conexion->prepare("SHOW INDEX FROM registro_descargas_stock_transito");
    $stmt->execute();
    $indices = $stmt->fetchAll();
    
    if(count($indices) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>Tabla</th><th>No Único</th><th>Nombre</th><th>Secuencia</th><th>Columna</th><th>Orden</th><th>Cardinalidad</th><th>Subparte</th><th>Empaquetado</th><th>Null</th><th>Tipo</th><th>Comentario</th></tr>";
        foreach($indices as $indice) {
            echo "<tr>";
            echo "<td>" . $indice['Table'] . "</td>";
            echo "<td>" . $indice['Non_unique'] . "</td>";
            echo "<td>" . $indice['Key_name'] . "</td>";
            echo "<td>" . $indice['Seq_in_index'] . "</td>";
            echo "<td>" . $indice['Column_name'] . "</td>";
            echo "<td>" . $indice['Collation'] . "</td>";
            echo "<td>" . $indice['Cardinality'] . "</td>";
            echo "<td>" . $indice['Sub_part'] . "</td>";
            echo "<td>" . $indice['Packed'] . "</td>";
            echo "<td>" . $indice['Null'] . "</td>";
            echo "<td>" . $indice['Index_type'] . "</td>";
            echo "<td>" . $indice['Comment'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No hay índices en la tabla<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "📍 Archivo: " . $e->getFile() . "<br>";
    echo "📍 Línea: " . $e->getLine() . "<br>";
    echo "📍 Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>Instrucciones:</strong></p>";
echo "<ol>";
echo "<li>Ejecuta este script en tu navegador: <code>consultar-tabla-registro.php</code></li>";
echo "<li>Copia y pega aquí toda la salida que genere</li>";
echo "<li>Con esta información podremos identificar exactamente qué está pasando</li>";
echo "</ol>";
?>
