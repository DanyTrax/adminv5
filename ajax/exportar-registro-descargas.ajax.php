<?php
/*=============================================
EXPORTAR REGISTRO DE DESCARGAS A EXCEL
=============================================*/

session_start();

// Verificar permisos
if($_SESSION["perfil"] != "Administrador" && $_SESSION["perfil"] != "Vendedor" && $_SESSION["perfil"] != "Contador") {
    echo json_encode(['success' => false, 'message' => 'Sin permisos para exportar']);
    exit;
}

require_once "../modelos/conexion.php";

try {
    // Obtener parámetros de fecha
    $fechaInicial = isset($_POST['fechaInicial']) ? $_POST['fechaInicial'] : null;
    $fechaFinal = isset($_POST['fechaFinal']) ? $_POST['fechaFinal'] : null;
    
    // Construir consulta con filtros de fecha
    $whereClause = "";
    $params = [];
    
    if ($fechaInicial && $fechaFinal) {
        $whereClause = "WHERE DATE(fecha_descarga) BETWEEN :fechaInicial AND :fechaFinal";
        $params[":fechaInicial"] = $fechaInicial;
        $params[":fechaFinal"] = $fechaFinal;
    }
    
    // Consulta SQL
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
        ORDER BY fecha_descarga DESC
    ";
    
    $stmt = Conexion::conectar()->prepare($sql);
    
    // Bindear parámetros si existen
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Crear datos para Excel
    $datosExcel = [];
    
    // ENCABEZADOS
    $datosExcel[] = ['REPORTE DE REGISTRO DE DESCARGAS'];
    $datosExcel[] = ['Generado por: ' . $_SESSION["nombre"]];
    $datosExcel[] = ['Fecha: ' . date('d/m/Y H:i:s')];
    $datosExcel[] = ['Perfil: ' . $_SESSION["perfil"]];
    
    // Información del filtro
    if ($fechaInicial && $fechaFinal) {
        $datosExcel[] = ['Período: ' . $fechaInicial . ' a ' . $fechaFinal];
    } else {
        $datosExcel[] = ['Período: Todos los registros'];
    }
    
    $datosExcel[] = []; // Línea vacía
    
    // HEADERS DE LA TABLA
    $datosExcel[] = [
        'ID',
        'FECHA Y HORA',
        'CÓDIGO PRODUCTO',
        'DESCRIPCIÓN',
        'CANTIDAD DESCARGADA',
        'USUARIO',
        'TRANSPORTADOR',
        'SUCURSAL',
        'NÚMERO DESPACHO',
        'OBSERVACIONES'
    ];
    
    // DATOS
    $totalCantidad = 0;
    $usuariosUnicos = [];
    $transportadoresUnicos = [];
    $sucursalesUnicas = [];
    
    foreach($registros as $registro) {
        $cantidad = intval($registro["cantidad_descargada"]);
        $totalCantidad += $cantidad;
        
        // Estadísticas
        if (!in_array($registro["usuario_nombre"], $usuariosUnicos)) {
            $usuariosUnicos[] = $registro["usuario_nombre"];
        }
        if (!in_array($registro["transportador_nombre"], $transportadoresUnicos)) {
            $transportadoresUnicos[] = $registro["transportador_nombre"];
        }
        if (!in_array($registro["sucursal_nombre"], $sucursalesUnicas)) {
            $sucursalesUnicas[] = $registro["sucursal_nombre"];
        }
        
        $datosExcel[] = [
            $registro["id"],
            $registro["fecha_hora"],
            $registro["codigo_producto"],
            $registro["descripcion_producto"],
            $cantidad,
            $registro["usuario_nombre"],
            $registro["transportador_nombre"],
            $registro["sucursal_nombre"],
            $registro["numero_despacho"],
            $registro["observaciones"] ?: 'Sin observaciones'
        ];
    }
    
    // TOTALES
    $datosExcel[] = []; // Línea vacía
    $datosExcel[] = [
        'TOTALES:',
        '',
        '',
        '',
        $totalCantidad,
        '',
        '',
        '',
        '',
        'Total registros: ' . count($registros)
    ];
    
    // ESTADÍSTICAS
    $datosExcel[] = []; // Línea vacía
    $datosExcel[] = ['ESTADÍSTICAS:'];
    $datosExcel[] = ['Usuarios únicos: ' . count($usuariosUnicos)];
    $datosExcel[] = ['Transportadores únicos: ' . count($transportadoresUnicos)];
    $datosExcel[] = ['Sucursales únicas: ' . count($sucursalesUnicas)];
    
    // Generar nombre de archivo
    $fechaArchivo = date('Y-m-d_H-i-s');
    $nombreArchivo = 'Registro_Descargas_' . $fechaArchivo . '.xlsx';
    
    if ($fechaInicial && $fechaFinal) {
        $nombreArchivo = 'Registro_Descargas_' . $fechaInicial . '_a_' . $fechaFinal . '_' . $fechaArchivo . '.xlsx';
    }
    
    echo json_encode([
        'success' => true,
        'data' => $datosExcel,
        'filename' => $nombreArchivo,
        'total_registros' => count($registros),
        'total_cantidad' => $totalCantidad
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar reporte: ' . $e->getMessage()
    ]);
}

?>
