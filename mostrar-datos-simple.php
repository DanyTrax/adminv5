<?php
/*=============================================
MOSTRAR DATOS SIMPLE - SIN DATATABLE
=============================================*/

// Incluir conexión
require_once "modelos/conexion.php";

try {
    $conexion = Conexion::conectar();
    
    // Consulta simple para obtener los datos
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
    ");
    $stmt->execute();
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>📋 Registro de Descargas - Datos Simples</h2>";
    echo "<p>Total de registros: <strong>" . count($datos) . "</strong></p>";
    
    if(count($datos) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th>";
        echo "<th>Fecha y Hora</th>";
        echo "<th>Código</th>";
        echo "<th>Descripción</th>";
        echo "<th>Cantidad</th>";
        echo "<th>Usuario</th>";
        echo "<th>Transportador</th>";
        echo "<th>Sucursal</th>";
        echo "<th>Despacho</th>";
        echo "<th>Observaciones</th>";
        echo "</tr>";
        
        foreach($datos as $dato) {
            echo "<tr>";
            echo "<td>" . $dato['id'] . "</td>";
            echo "<td>" . $dato['fecha_hora'] . "</td>";
            echo "<td>" . $dato['codigo_producto'] . "</td>";
            echo "<td>" . substr($dato['descripcion_producto'], 0, 50) . "...</td>";
            echo "<td>" . $dato['cantidad_descargada'] . "</td>";
            echo "<td>" . $dato['usuario_nombre'] . "</td>";
            echo "<td>" . $dato['transportador_nombre'] . "</td>";
            echo "<td>" . $dato['sucursal_nombre'] . "</td>";
            echo "<td>" . $dato['numero_despacho'] . "</td>";
            echo "<td>" . $dato['observaciones'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay registros en la tabla.</p>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
