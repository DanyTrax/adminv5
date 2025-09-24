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
    $fechaDesde = $_POST['fechaDesde'] ?? date('Y-m-d', strtotime('-30 days'));
    $fechaHasta = $_POST['fechaHasta'] ?? date('Y-m-d');
    $estado = $_POST['estado'] ?? '';
    $transportador = $_POST['transportador'] ?? '';
    
    // Construir consulta
    $sql = "SELECT * FROM despachos WHERE DATE(fecha_creacion) BETWEEN :fecha_desde AND :fecha_hasta";
    $parametros = [':fecha_desde' => $fechaDesde, ':fecha_hasta' => $fechaHasta];
    
    if(!empty($estado)) {
        $sql .= " AND estado = :estado";
        $parametros[':estado'] = $estado;
    }
    
    if(!empty($transportador)) {
        $sql .= " AND id_transportador_asignado = :transportador";
        $parametros[':transportador'] = $transportador;
    }
    
    $sql .= " ORDER BY fecha_creacion DESC";
    
    $stmt = ConexionCentral::conectar()->prepare($sql);
    
    foreach($parametros as $key => $valor) {
        $stmt->bindValue($key, $valor);
    }
    
    $stmt->execute();
    $despachos = $stmt->fetchAll();
    
    // Crear datos para Excel
    $datosExcel = [];
    
    // ENCABEZADOS
    $datosExcel[] = ['REPORTE DE DESPACHOS'];
    $datosExcel[] = ['Generado por: ' . $_SESSION["nombre"]];
    $datosExcel[] = ['Fecha: ' . date('d/m/Y H:i:s')];
    $datosExcel[] = ['Período: ' . date('d/m/Y', strtotime($fechaDesde)) . ' - ' . date('d/m/Y', strtotime($fechaHasta))];
    $datosExcel[] = []; // Línea vacía
    
    // HEADERS DE LA TABLA
    $datosExcel[] = [
        'N° DESPACHO',
        'SUCURSAL ORIGEN',
        'USUARIO CREADOR',
        'ESTADO',
        'TOTAL PRODUCTOS',
        'TOTAL CANTIDAD',
        'TRANSPORTADOR ASIGNADO',
        'FECHA CREACIÓN',
        'FECHA ACEPTACIÓN',
        'OBSERVACIONES'
    ];
    
    // DATOS
    $totalProductos = 0;
    $totalCantidad = 0;
    
    foreach($despachos as $despacho) {
        $totalProductos += $despacho["total_productos"];
        $totalCantidad += $despacho["total_cantidad"];
        
        $datosExcel[] = [
            $despacho["numero_despacho"],
            $despacho["nombre_sucursal_origen"],
            $despacho["nombre_usuario_creador"],
            strtoupper($despacho["estado"]),
            $despacho["total_productos"],
            $despacho["total_cantidad"],
            $despacho["nombre_transportador_asignado"] ?: 'Sin asignar',
            date('d/m/Y H:i', strtotime($despacho["fecha_creacion"])),
            $despacho["fecha_aceptacion"] ? date('d/m/Y H:i', strtotime($despacho["fecha_aceptacion"])) : 'Sin aceptar',
            $despacho["detalle_adicional"] ?: 'Sin observaciones'
        ];
    }
    
    // TOTALES
    $datosExcel[] = []; // Línea vacía
    $datosExcel[] = [
        'TOTALES:',
        '',
        '',
        '',
        $totalProductos,
        $totalCantidad,
        '',
        '',
        '',
        'Total registros: ' . count($despachos)
    ];
    
    // ESTADÍSTICAS POR ESTADO
    $datosExcel[] = []; // Línea vacía
    $datosExcel[] = ['ESTADÍSTICAS POR ESTADO:'];
    
    $estadisticas = [];
    foreach($despachos as $despacho) {
        $estado = $despacho["estado"];
        if(!isset($estadisticas[$estado])) {
            $estadisticas[$estado] = 0;
        }
        $estadisticas[$estado]++;
    }
    
    foreach($estadisticas as $estado => $cantidad) {
        $datosExcel[] = [strtoupper($estado), $cantidad];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $datosExcel,
        'filename' => 'Reporte_Despachos_' . date('Y-m-d_H-i-s') . '.xlsx'
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar reporte: ' . $e->getMessage()
    ]);
}
/*=============================================
CARGAR PRODUCTOS DEL INVENTARIO LOCAL
=============================================*/
public function ajaxCargarInventario() {
    
    if(isset($_POST["cargarInventario"])) {
        
        try {
            require_once "../modelos/conexion.php";
            
            $stmt = Conexion::conectar()->prepare("
                SELECT codigo, descripcion, stock, precio_venta 
                FROM productos 
                WHERE estado = 1 
                ORDER BY descripcion ASC
            ");
            
            $stmt->execute();
            $productos = $stmt->fetchAll();
            
            echo json_encode($productos);
            
        } catch(Exception $e) {
            echo json_encode([]);
        }
    }
}

/*=============================================
OBTENER DESPACHO PARA EDITAR
=============================================*/
public function ajaxObtenerDespachoEditar() {
    
    if(isset($_POST["idDespachoEditar"])) {
        
        $item = "id";
        $valor = $_POST["idDespachoEditar"];
        
        $respuesta = ControladorDespachos::ctrMostrarDespachos($item, $valor);
        
        // Solo permitir edición de despachos pendientes
        if($respuesta && $respuesta["estado"] == "pendiente") {
            echo json_encode($respuesta);
        } else {
            echo json_encode(["error" => "No se puede editar este despacho"]);
        }
    }
}

// AGREGAR AL FINAL DEL ARCHIVO:
if(isset($_POST["cargarInventario"])) {
    $cargarInventario = new AjaxDespachos();
    $cargarInventario->ajaxCargarInventario();
}

if(isset($_POST["idDespachoEditar"])) {
    $obtenerEditar = new AjaxDespachos();
    $obtenerEditar->ajaxObtenerDespachoEditar();
}