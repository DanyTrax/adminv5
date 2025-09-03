<?php

require_once "controladores/plantilla.controlador.php";
require_once "controladores/catalogo-maestro.controlador.php";
require_once "modelos/catalogo-maestro.modelo.php";

$plantilla = new ControladorPlantilla();
$plantilla -> ctrPlantilla();

class ExportadorCatalogo {
    
    public static function exportarCSV() {
        
        try {
            
            // ✅ CONECTAR A BD CENTRAL
            require_once "api-transferencias/conexion-central.php";
            $db = ConexionCentral::conectar();
            
            // ✅ OBTENER PRODUCTOS DEL CATÁLOGO MAESTRO
            $stmt = $db->prepare("
                SELECT cm.id, cm.codigo, cm.descripcion, cm.id_categoria,
                       c.categoria as nombre_categoria,
                       cm.precio_venta, cm.imagen,
                       cm.es_divisible,
                       cm.codigo_hijo_mitad, cm.codigo_hijo_tercio, cm.codigo_hijo_cuarto,
                       cm.fecha_actualizacion
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
            
            // ✅ CONFIGURAR HEADERS PARA CSV
            $fecha = date('d_m_Y');
            $nombreArchivo = "Catalogo_Maestro_{$fecha}.csv";
            
            header('Content-Type: text/csv; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"{$nombreArchivo}\"");
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
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
                    $producto['nombre_categoria'] ?? '',
                    $producto['precio_venta'],
                    $producto['es_divisible'] ? 'SI' : 'NO',
                    $producto['codigo_hijo_mitad'] ?? '',
                    $producto['codigo_hijo_tercio'] ?? '',
                    $producto['codigo_hijo_cuarto'] ?? '',
                    $producto['fecha_actualizacion'] ?? ''
                );
                
                fputcsv($output, $fila, ',', '"');
            }
            
            fclose($output);
            exit();
            
        } catch(Exception $e) {
            
            error_log("Error exportando catálogo CSV: " . $e->getMessage());
            die("Error al exportar el catálogo. Contacte al administrador.");
        }
    }
}

// ✅ SI SE SOLICITA LA DESCARGA
if(isset($_GET['descargar']) && $_GET['descargar'] == 'csv') {
    ExportadorCatalogo::exportarCSV();
}

?>