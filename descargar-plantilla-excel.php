<?php
// ✅ SOLO CARGAR LO NECESARIO - SIN PLANTILLA HTML
require_once "modelos/conexion.php";

// ✅ VERIFICAR PARÁMETRO DE DESCARGA
if(!isset($_GET['descargar']) || $_GET['descargar'] !== 'csv') {
    // Si no es descarga, redireccionar al catálogo maestro
    header('Location: catalogo-maestro');
    exit();
}

try {
    
    // ✅ CONECTAR A BD CENTRAL DIRECTAMENTE
    require_once "api-transferencias/conexion-central.php";
    $db = ConexionCentral::conectar();
    
    // ✅ OBTENER PRODUCTOS DEL CATÁLOGO MAESTRO
    $stmt = $db->prepare("
        SELECT cm.id, cm.codigo, cm.descripcion, cm.id_categoria,
               COALESCE(c.categoria, 'Sin categoría') as nombre_categoria,
               cm.precio_venta, 
               CASE WHEN cm.es_divisible = 1 THEN 'SI' ELSE 'NO' END as es_divisible,
               COALESCE(cm.codigo_hijo_mitad, '') as codigo_hijo_mitad,
               COALESCE(cm.codigo_hijo_tercio, '') as codigo_hijo_tercio, 
               COALESCE(cm.codigo_hijo_cuarto, '') as codigo_hijo_cuarto,
               DATE_FORMAT(cm.fecha_actualizacion, '%Y-%m-%d %H:%i') as fecha_actualizacion
        FROM catalogo_maestro cm
        LEFT JOIN categorias c ON cm.id_categoria = c.id
        WHERE cm.activo = 1
        ORDER BY cm.codigo ASC
    ");
    
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(empty($productos)) {
        die("No hay productos en el catálogo maestro para exportar");
    }
    
    // ✅ CONFIGURAR HEADERS PARA DESCARGA CSV
    $fecha = date('d_m_Y_His');
    $nombreArchivo = "Catalogo_Maestro_{$fecha}.csv";
    
    // ✅ LIMPIAR CUALQUIER OUTPUT ANTERIOR
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"{$nombreArchivo}\"");
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Expires: 0');
    
    // ✅ CREAR OUTPUT STREAM
    $output = fopen('php://output', 'w');
    
    // ✅ BOM PARA UTF-8 (Para que Excel lo abra correctamente)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // ✅ ESCRIBIR ENCABEZADOS
    $encabezados = array(
        'ID',
        'CODIGO', 
        'DESCRIPCION',
        'ID_CATEGORIA',
        'NOMBRE_CATEGORIA',
        'PRECIO_VENTA',
        'ES_DIVISIBLE',
        'CODIGO_HIJO_MITAD',
        'CODIGO_HIJO_TERCIO', 
        'CODIGO_HIJO_CUARTO',
        'FECHA_ACTUALIZACION'
    );
    
    fputcsv($output, $encabezados, ',', '"');
    
    // ✅ ESCRIBIR DATOS DE PRODUCTOS
    foreach($productos as $producto) {
        
        $fila = array(
            $producto['id'],
            $producto['codigo'],
            $producto['descripcion'],
            $producto['id_categoria'],
            $producto['nombre_categoria'],
            $producto['precio_venta'],
            $producto['es_divisible'],
            $producto['codigo_hijo_mitad'],
            $producto['codigo_hijo_tercio'],
            $producto['codigo_hijo_cuarto'],
            $producto['fecha_actualizacion']
        );
        
        fputcsv($output, $fila, ',', '"');
    }
    
    fclose($output);
    
    // ✅ FINALIZAR SCRIPT SIN OUTPUT ADICIONAL
    exit();
    
} catch(Exception $e) {
    
    // ✅ LOG ERROR Y MOSTRAR MENSAJE SIMPLE
    error_log("Error exportando catálogo CSV: " . $e->getMessage());
    
    // Limpiar headers si hay error
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Error</title></head><body>";
    echo "<h1>Error al exportar</h1>";
    echo "<p>No se pudo generar el archivo CSV. Contacte al administrador.</p>";
    echo "<p><a href='catalogo-maestro'>Volver al catálogo</a></p>";
    echo "</body></html>";
    exit();
}
?>