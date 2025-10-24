<?php
/*=============================================
DATATABLE REGISTRO DE DESCARGAS - FUNCIONAL
=============================================*/

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir conexión
require_once "../modelos/conexion.php";

try {
    // Verificar conexión
    $conexion = Conexion::conectar();
    if (!$conexion) {
        echo json_encode(["error" => "No hay conexión a la base de datos"]);
        exit;
    }

    // Verificar tabla
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'registro_descargas_stock_transito'");
    $stmt->execute();
    $tabla = $stmt->fetch();
    
    if (!$tabla) {
        echo json_encode(["error" => "Tabla registro_descargas_stock_transito no existe"]);
        exit;
    }

    // Obtener parámetros de fecha
    $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
    $fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;
    
    // Construir consulta con filtros de fecha
    $whereClause = "";
    $params = [];
    
    if ($fechaInicial && $fechaFinal) {
        $whereClause = "WHERE DATE(fecha_descarga) BETWEEN :fechaInicial AND :fechaFinal";
        $params[":fechaInicial"] = $fechaInicial;
        $params[":fechaFinal"] = $fechaFinal;
    }
    
    // Obtener datos con formato correcto
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

    // Formatear datos para DataTable
    $data = [];
    foreach($datos as $dato) {
        $data[] = [
            $dato['id'],
            $dato['fecha_hora'],
            $dato['codigo_producto'],
            $dato['descripcion_producto'],
            $dato['cantidad_descargada'],
            $dato['usuario_nombre'],
            $dato['transportador_nombre'],
            $dato['sucursal_nombre'],
            $dato['numero_despacho'],
            $dato['observaciones']
        ];
    }

    // Respuesta para DataTable
    echo json_encode([
        "data" => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}
?>
