<?php
/**
 * Este archivo unifica la lógica de todos los reportes.
 * Reemplaza el contenido de 'controladores/reportes.controlador.php' con este código
 * y elimina el archivo 'controladores/reporte-detallado.controlador.php'.
 */
class ControladorReportes {
    /*===================================================================
    FUNCIONES PARA EL ARQUEO DE VENTAS
    ===================================================================*/

    static public function ctrGetVentaBruta($fechaInicial, $fechaFinal) {
        $respuesta = ModeloReportes::mdlGetVentaBruta($fechaInicial, $fechaFinal);
        return $respuesta['total'] ?? 0;
    }

    /*===================================================================
    NUEVA FUNCIÓN: OBTENER TOTAL DE DESCUENTOS
    ===================================================================*/
    static public function ctrGetTotalDescuentos($fechaInicial, $fechaFinal) {
        $respuesta = ModeloReportes::mdlGetTotalDescuentos($fechaInicial, $fechaFinal);
        return $respuesta['total'] ?? 0;
    }

    static public function ctrGetVentaNeta($fechaInicial, $fechaFinal) {
        $respuesta = ModeloReportes::mdlGetVentaNeta($fechaInicial, $fechaFinal);
        return $respuesta['total'] ?? 0;
    }

    /*===================================================================
    NUEVA FUNCIÓN: OBTENER VENTAS CON DESCUENTO (PARA EXCEL)
    ===================================================================*/
    static public function ctrGetVentasConDescuento($fechaInicial, $fechaFinal) {
        return ModeloReportes::mdlGetVentasConDescuento($fechaInicial, $fechaFinal);
    }
    
       /*===================================================================
    NUEVAS FUNCIONES PARA EL TABLERO DE INICIO
    ===================================================================*/

    static public function ctrDashboardTotalEntradasHoy() {
        $fechaInicial = date("Y-m-d") . " 00:00:00";
        $fechaFinal   = date("Y-m-d") . " 23:59:59";
        $respuesta = ModeloReportes::mdlDashboardSumaContabilidadPorTipo('Entrada', $fechaInicial, $fechaFinal);
        return $respuesta['total'] ?? 0;
    }

    static public function ctrDashboardTotalEntradasEfectivoHoy() {
        $fechaInicial = date("Y-m-d") . " 00:00:00";
        $fechaFinal   = date("Y-m-d") . " 23:59:59";
        $respuesta = ModeloReportes::mdlDashboardSumaContabilidadPorTipoYMedio('Entrada', 'Efectivo', $fechaInicial, $fechaFinal);
        return $respuesta['total'] ?? 0;
    }

    static public function ctrDashboardTotalGastosHoy() {
        $fechaInicial = date("Y-m-d") . " 00:00:00";
        $fechaFinal   = date("Y-m-d") . " 23:59:59";
        $respuesta = ModeloReportes::mdlDashboardSumaContabilidadPorTipo('Gasto', $fechaInicial, $fechaFinal);
        return $respuesta['total'] ?? 0;
    }

    static public function ctrDashboardTotalGastosEfectivoHoy() {
        $fechaInicial = date("Y-m-d") . " 00:00:00";
        $fechaFinal   = date("Y-m-d") . " 23:59:59";
        $respuesta = ModeloReportes::mdlDashboardSumaContabilidadPorTipoYMedio('Gasto', 'Efectivo', $fechaInicial, $fechaFinal);
        return $respuesta['total'] ?? 0;
    }

    static public function ctrDashboardSumaTotalVentasGeneral() {
        $respuesta = ModeloReportes::mdlDashboardSumaTotalVentas();
        return $respuesta['total_ventas'] ?? 0;
    }

    /*===================================================================
    OBTENER DATOS PARA LA TABLA DE REPORTES (PROCESAMIENTO SERVERSIDE)
    Esta versión es más robusta para trabajar con el plugin DataTables.
    ===================================================================*/
    static public function ctrObtenerVentasServerSide($params, $fechaInicial, $fechaFinal) {
        
        // Llama al modelo para obtener los datos paginados y filtrados
        $respuesta = ModeloReportes::mdlObtenerVentasServerSide($params, $fechaInicial, $fechaFinal);
        
        $data = [];
        // El contador se inicializa con el valor 'start' de DataTables para mantener la numeración correcta en la paginación
        $contador = intval($params['start']) + 1;
        
        foreach ($respuesta as $row) {
            // Se usan operadores de fusión de null (??) para asignar un valor por defecto
            // y evitar errores si una columna en la base de datos es nula.
            $data[] = [
                "contador"               => $contador++,
                "fecha_venta"            => $row['fecha_venta'] ?? '',
                "codigo_factura"         => $row['codigo_factura'] ?? '',
                "vendedor"               => $row['nombre_vendedor'] ?? '',
                "cliente"                => $row['nombre_cliente'] ?? '',
                "producto_descripcion"   => $row['producto_descripcion'] ?? '',
                "producto_cantidad"      => $row['producto_cantidad'] ?? 0,
                "producto_total"         => $row['producto_total'] ?? 0,
                "medio_pago"             => $row['medio_pago'] ?? ''
            ];
        }
        return $data;
    }

    /*=============================================
    CONTAR REGISTROS FILTRADOS (PARA DATATABLES)
    =============================================*/
    static public function ctrContarVentasFiltradas($params, $fechaInicial, $fechaFinal) {
        return ModeloReportes::mdlContarVentasFiltradas($params, $fechaInicial, $fechaFinal);
    }

    /*=============================================
    CONTAR TOTAL DE REGISTROS (PARA DATATABLES)
    =============================================*/
    static public function ctrContarTotalVentas($fechaInicial, $fechaFinal) {
        return ModeloReportes::mdlContarTotalVentas($fechaInicial, $fechaFinal);
    }

    /*======================================================================
    OBTENER PRODUCTOS VENDIDOS POR RANGO DE FECHAS (REPORTE DETALLADO)
    Este método estaba antes en la clase 'ControladorReporteDetallado'.
    Se ha movido aquí para mantener toda la lógica de reportes en un solo lugar.
    ======================================================================*/
    static public function ctrObtenerProductosPorFecha($fechaInicial, $fechaFinal) {

        $db = Conexion::conectar();

        // Consulta base para obtener el detalle de productos en ventas
        $sql = "
            SELECT 
                vp.id AS contador,
                v.fecha_venta,
                v.codigo AS codigo_factura,
                u.nombre AS vendedor,
                c.nombre AS cliente,
                vp.descripcion AS producto_descripcion,
                vp.cantidad AS producto_cantidad,
                vp.total AS producto_total,
                v.medio_pago
            FROM venta_productos vp
            JOIN ventas v ON vp.id_venta = v.codigo
            JOIN usuarios u ON v.id_vendedor = u.id
            JOIN clientes c ON v.id_cliente = c.id
        ";

        // Si se proporcionan fechas, se añade el filtro WHERE a la consulta
        if ($fechaInicial && $fechaFinal) {
            // Se usa DATE() para comparar solo la parte de la fecha, ignorando la hora
            $sql .= " WHERE DATE(v.fecha_venta) BETWEEN :fechaInicial AND :fechaFinal";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
        } else {
            $stmt = $db->prepare($sql);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
