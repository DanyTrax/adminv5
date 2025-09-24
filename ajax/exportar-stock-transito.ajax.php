<?php

session_start();

// Verificar permisos
if($_SESSION["perfil"] != "Administrador" && $_SESSION["perfil"] != "Transportador") {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

require_once "../api-transferencias/conexion-central.php";

try {
    
    // Obtener filtros
    $transportador = $_POST['transportador'] ?? '';
    
    // Construir consulta
    $sql = "SELECT 
                st.codigo_producto,
                st.descripcion_producto,
                st.cantidad_disponible,
                st.nombre_transportador,
                st.sucursal_origen,
                st.fecha_carga,
                st.observaciones,
                COALESCE(SUM(sd.cantidad_solicitada), 0) as cantidad_solicitada_pendiente
            FROM stock_transito st
            LEFT JOIN solicitudes_descarga sd ON st.id = sd.id_stock_transito 
                AND sd.estado = 'pendiente'
            WHERE st.cantidad_disponible > 0";
    
    $parametros = [];
    
    // Si es transportador, solo su stock
    if($_SESSION["perfil"] == "Transportador") {
        $sql .= " AND st.transportador_id = :transportador_sesion";
        $parametros[':transportador_sesion'] = $_SESSION["id"];
    }
    // Si se especificó filtro de transportador
    else if(!empty($transportador)) {
        $sql .= " AND st.transportador_id = :transportador_filtro";
        $parametros[':transportador_filtro'] = $transportador;
    }
    
    $sql .= " GROUP BY st.id ORDER BY st.nombre_transportador ASC, st.codigo_producto ASC";
    
    $stmt = ConexionCentral::conectar()->prepare($sql);
    
    foreach($parametros as $key => $valor) {
        $stmt->bindValue($key, $valor);
    }
    
    $stmt->execute();
    $stockTransito = $stmt->fetchAll();
    
    // Crear datos para Excel
    $datosExcel = [];
    
    // ENCABEZADOS
    $datosExcel[] = ['REPORTE DE STOCK EN TRÁNSITO'];
    $datosExcel[] = ['Generado por: ' . $_SESSION["nombre"]];
    $datosExcel[] = ['Fecha: ' . date('d/m/Y H:i:s')];
    $datosExcel[] = ['Perfil: ' . $_SESSION["perfil"]];
    $datosExcel[] = []; // Línea vacía
    
    // HEADERS DE LA TABLA
    $datosExcel[] = [
        'CÓDIGO PRODUCTO',
        'DESCRIPCIÓN',
        'CANTIDAD TOTAL',
        'CANTIDAD SOLICITADA',
        'CANTIDAD DISPONIBLE',
        'TRANSPORTADOR',
        'SUCURSAL ORIGEN',
        'FECHA CARGUE',
        'OBSERVACIONES'
    ];
    
    // DATOS
    $totalUnidades = 0;
    $totalSolicitadas = 0;
    $totalDisponibles = 0;
    
    foreach($stockTransito as $stock) {
        $cantidadTotal = intval($stock["cantidad_disponible"]);
        $cantidadSolicitada = intval($stock["cantidad_solicitada_pendiente"]);
        $cantidadDisponible = $cantidadTotal - $cantidadSolicitada;
        
        $totalUnidades += $cantidadTotal;
        $totalSolicitadas += $cantidadSolicitada;
        $totalDisponibles += $cantidadDisponible;
        
        $datosExcel[] = [
            $stock["codigo_producto"],
            $stock["descripcion_producto"],
            $cantidadTotal,
            $cantidadSolicitada,
            $cantidadDisponible,
            $stock["nombre_transportador"],
            $stock["sucursal_origen"],
            date('d/m/Y H:i', strtotime($stock["fecha_carga"])),
            $stock["observaciones"] ?: 'Sin observaciones'
        ];
    }
    
    // TOTALES
    $datosExcel[] = []; // Línea vacía
    $datosExcel[] = [
        'TOTALES:',
        '',
        $totalUnidades,
        $totalSolicitadas,
        $totalDisponibles,
        '',
        '',
        '',
        'Total productos: ' . count($stockTransito)
    ];
    
    // ESTADÍSTICAS POR TRANSPORTADOR
    $datosExcel[] = []; // Línea vacía
    $datosExcel[] = ['ESTADÍSTICAS POR TRANSPORTADOR:'];
    
    $estadisticas = [];
    foreach($stockTransito as $stock) {
        $transportador = $stock["nombre_transportador"];
        if(!isset($estadisticas[$transportador])) {
            $estadisticas[$transportador] = [
                'productos' => 0,
                'unidades' => 0
            ];
        }
        $estadisticas[$transportador]['productos']++;
        $estadisticas[$transportador]['unidades'] += $stock["cantidad_disponible"];
    }
    
    foreach($estadisticas as $transportador => $datos) {
        $datosExcel[] = [
            $transportador,
            $datos['productos'] . ' productos',
            $datos['unidades'] . ' unidades'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $datosExcel,
        'filename' => 'Stock_Transito_' . date('Y-m-d_H-i-s') . '.xlsx'
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar reporte: ' . $e->getMessage()
    ]);
}

?>