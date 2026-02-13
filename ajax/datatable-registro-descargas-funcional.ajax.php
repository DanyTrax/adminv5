<?php
/*=============================================
DATATABLE REGISTRO DE DESCARGAS - FUNCIONAL
=============================================*/

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir conexión
require_once "../modelos/conexion.php";
require_once "../api-transferencias/conexion-central.php";

try {
    // Verificar conexión
    $conexion = ConexionCentral::conectar();
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

    // Obtener parámetros de fecha desde la URL actual
    $fechaInicial = null;
    $fechaFinal = null;
    
    // Verificar si hay parámetros de fecha en la URL de referencia
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer = $_SERVER['HTTP_REFERER'];
        if (preg_match('/fechaInicial=([^&]+)/', $referer, $matches)) {
            $fechaInicial = $matches[1];
        }
        if (preg_match('/fechaFinal=([^&]+)/', $referer, $matches)) {
            $fechaFinal = $matches[1];
        }
    }
    
    // También verificar parámetros directos
    if (!$fechaInicial && isset($_GET["fechaInicial"])) {
        $fechaInicial = $_GET["fechaInicial"];
    }
    if (!$fechaFinal && isset($_GET["fechaFinal"])) {
        $fechaFinal = $_GET["fechaFinal"];
    }
    
    // Construir consulta con filtros de fecha
    $whereClause = "";
    $params = [];
    
    if ($fechaInicial && $fechaFinal) {
        $whereClause = "WHERE DATE(fecha_descarga) BETWEEN :fechaInicial AND :fechaFinal";
        $params[":fechaInicial"] = $fechaInicial;
        $params[":fechaFinal"] = $fechaFinal;
        
        // Debug: Log de parámetros
        error_log("Filtro de fechas aplicado: $fechaInicial a $fechaFinal");
    } else {
        // Debug: Log cuando no hay filtro
        error_log("Sin filtro de fechas - mostrando todos los registros");
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

    // Columna Acción solo para Administrador
    $esAdmin = isset($_SESSION["perfil"]) && $_SESSION["perfil"] == "Administrador";

    // Formatear datos para DataTable
    $data = [];
    foreach($datos as $dato) {
        $fila = [
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
        if ($esAdmin) {
            $fila[] = '<button type="button" class="btn btn-danger btn-xs btnEliminarRegistroDescarga" data-id="' . intval($dato['id']) . '" title="Eliminar registro"><i class="fa fa-trash"></i></button>';
        }
        $data[] = $fila;
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
