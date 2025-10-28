<?php
/*=============================================
DESCARGAR REGISTRO DE DESCARGAS - FUNCIONAL
=============================================*/

session_start();

// Verificar sesión
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
    header("Location: ../login");
    exit;
}

// Incluir conexiones
require_once "../modelos/conexion.php";
require_once "../api-transferencias/conexion-central.php";

try {
    // Conectar a la base de datos central
    $conexion = ConexionCentral::conectar();
    if (!$conexion) {
        die("Error de conexión a la base de datos");
    }

    // Verificar tabla
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'registro_descargas_stock_transito'");
    $stmt->execute();
    $tabla = $stmt->fetch();
    
    if (!$tabla) {
        die("Tabla registro_descargas_stock_transito no existe");
    }

    // Obtener parámetros de fecha
    $fechaInicial = $_GET["fechaInicial"] ?? null;
    $fechaFinal = $_GET["fechaFinal"] ?? null;
    
    // Construir consulta con filtros de fecha
    $whereClause = "";
    $params = [];
    
    if ($fechaInicial && $fechaFinal) {
        $whereClause = "WHERE DATE(fecha_descarga) BETWEEN :fechaInicial AND :fechaFinal";
        $params[":fechaInicial"] = $fechaInicial;
        $params[":fechaFinal"] = $fechaFinal;
    }
    
    // Obtener datos
    $sql = "
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
        $whereClause
        ORDER BY created_at DESC
    ";
    
    $stmt = $conexion->prepare($sql);
    
    // Bindear parámetros si existen
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Configurar headers para descarga Excel
    $nombreArchivo = "registro_descargas_" . date('Y-m-d_H-i-s') . ".xls";
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    // Generar contenido Excel
    echo '<html>';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<style>';
    echo 'table { border-collapse: collapse; width: 100%; }';
    echo 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
    echo 'th { background-color: #f2f2f2; font-weight: bold; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    
    echo '<h2>Registro de Descargas - Stock en Tránsito</h2>';
    
    if ($fechaInicial && $fechaFinal) {
        echo '<p><strong>Período:</strong> ' . $fechaInicial . ' - ' . $fechaFinal . '</p>';
    } else {
        echo '<p><strong>Período:</strong> Todos los registros</p>';
    }
    
    echo '<p><strong>Generado:</strong> ' . date('d/m/Y H:i:s') . '</p>';
    echo '<p><strong>Total registros:</strong> ' . count($datos) . '</p>';
    
    echo '<br>';
    
    // Tabla de datos
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Fecha y Hora</th>';
    echo '<th>Código Producto</th>';
    echo '<th>Descripción</th>';
    echo '<th>Cantidad</th>';
    echo '<th>Usuario</th>';
    echo '<th>Transportador</th>';
    echo '<th>Sucursal</th>';
    echo '<th>Despacho</th>';
    echo '<th>Observaciones</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach($datos as $dato) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($dato['id']) . '</td>';
        echo '<td>' . htmlspecialchars($dato['fecha_hora']) . '</td>';
        echo '<td>' . htmlspecialchars($dato['codigo_producto']) . '</td>';
        echo '<td>' . htmlspecialchars($dato['descripcion_producto']) . '</td>';
        echo '<td>' . htmlspecialchars($dato['cantidad_descargada']) . '</td>';
        echo '<td>' . htmlspecialchars($dato['usuario_nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($dato['transportador_nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($dato['sucursal_nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($dato['numero_despacho']) . '</td>';
        echo '<td>' . htmlspecialchars($dato['observaciones']) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    echo '</body>';
    echo '</html>';

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
