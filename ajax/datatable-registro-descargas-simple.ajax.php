<?php
/*=============================================
DATATABLE REGISTRO DE DESCARGAS SIMPLE - FUNCIONAL
=============================================*/

// Incluir controlador si no existe
if (!class_exists('ControladorRegistroDescargasSimple')) {
    require_once __DIR__ . "/../controladores/registro-descargas-simple.controlador.php";
}

// Incluir modelo si no existe
if (!class_exists('ModeloRegistroDescargasSimple')) {
    require_once __DIR__ . "/../modelos/registro-descargas-simple.modelo.php";
}

// Incluir conexión
require_once __DIR__ . "/../modelos/conexion.php";

try {
    // Parámetros de DataTable
    $draw = intval($_POST["draw"] ?? 1);
    $start = intval($_POST["start"] ?? 0);
    $length = intval($_POST["length"] ?? 10);
    $searchValue = $_POST["search"]["value"] ?? "";
    
    // Filtros adicionales
    $filtros = [
        'producto' => $_POST['producto'] ?? '',
        'usuario' => $_POST['usuario'] ?? '',
        'fecha_desde' => $_POST['fecha_desde'] ?? '',
        'fecha_hasta' => $_POST['fecha_hasta'] ?? ''
    ];
    
    // Construir WHERE clause
    $where = "1=1";
    $params = [];
    
    if(!empty($filtros['producto'])) {
        $where .= " AND codigo_producto LIKE :producto";
        $params[':producto'] = '%' . $filtros['producto'] . '%';
    }
    
    if(!empty($filtros['usuario'])) {
        $where .= " AND usuario_nombre LIKE :usuario";
        $params[':usuario'] = '%' . $filtros['usuario'] . '%';
    }
    
    if(!empty($filtros['fecha_desde'])) {
        $where .= " AND fecha_descarga >= :fecha_desde";
        $params[':fecha_desde'] = $filtros['fecha_desde'];
    }
    
    if(!empty($filtros['fecha_hasta'])) {
        $where .= " AND fecha_descarga <= :fecha_hasta";
        $params[':fecha_hasta'] = $filtros['fecha_hasta'];
    }
    
    // Búsqueda general
    if(!empty($searchValue)) {
        $where .= " AND (codigo_producto LIKE :search OR descripcion_producto LIKE :search OR usuario_nombre LIKE :search OR transportador_nombre LIKE :search OR sucursal_nombre LIKE :search OR numero_despacho LIKE :search)";
        $params[':search'] = '%' . $searchValue . '%';
    }
    
    // Contar total de registros
    $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito WHERE $where");
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetch()['total'];
    
    // Obtener datos paginados
    $stmt = Conexion::conectar()->prepare("
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
        WHERE $where 
        ORDER BY created_at DESC 
        LIMIT $start, $length
    ");
    
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    // Respuesta para DataTable
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $totalRecords,
        "data" => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "draw" => intval($_POST["draw"] ?? 1),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => $e->getMessage()
    ]);
}
?>