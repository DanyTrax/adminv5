<?php

require_once "conexion.php";

class ModeloReportes {
     
         /*===================================================================
    NUEVAS FUNCIONES PARA EL ARQUEO DE VENTAS (BRUTO, DESCUENTOS, NETO)
    ===================================================================*/

    /**
     * Calcula la Venta Bruta (neto + descuento) para un rango de fechas.
     */
    static public function mdlGetVentaBruta($fechaInicial, $fechaFinal) {
        $sql = "SELECT SUM(neto + descuento) as total FROM ventas";
        if ($fechaInicial != null) {
            $sql .= " WHERE fecha_venta BETWEEN :fechaInicial AND :fechaFinal";
        }
        $stmt = Conexion::conectar()->prepare($sql);
        if ($fechaInicial != null) {
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetch();
    }

    /*===================================================================
    NUEVA FUNCIÓN: OBTENER TOTAL DE DESCUENTOS
    ===================================================================*/
    static public function mdlGetTotalDescuentos($fechaInicial, $fechaFinal) {
        $sql = "SELECT SUM(descuento) as total FROM ventas";
        if ($fechaInicial != null) {
            $sql .= " WHERE fecha_venta BETWEEN :fechaInicial AND :fechaFinal";
        }
        $stmt = Conexion::conectar()->prepare($sql);
        if ($fechaInicial != null) {
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Calcula la Venta Neta (total final pagado) para un rango de fechas.
     */
    static public function mdlGetVentaNeta($fechaInicial, $fechaFinal) {
        $sql = "SELECT SUM(total) as total FROM ventas";
        if ($fechaInicial != null) {
            $sql .= " WHERE fecha_venta BETWEEN :fechaInicial AND :fechaFinal";
        }
        $stmt = Conexion::conectar()->prepare($sql);
        if ($fechaInicial != null) {
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetch();
    }

    /*===================================================================
    NUEVA FUNCIÓN: OBTENER VENTAS CON DESCUENTO (PARA EXCEL)
    ===================================================================*/
    static public function mdlGetVentasConDescuento($fechaInicial, $fechaFinal) {
        
        $sql = "SELECT v.codigo, c.nombre as cliente, u.nombre as vendedor, v.neto, v.descuento, v.total, v.fecha_venta 
                FROM ventas v
                INNER JOIN clientes c ON v.id_cliente = c.id
                INNER JOIN usuarios u ON v.id_vendedor = u.id
                WHERE v.descuento > 0";
        
        if ($fechaInicial != null) {
            $sql .= " AND v.fecha_venta BETWEEN :fechaInicial AND :fechaFinal";
        }
        
        $sql .= " ORDER BY v.fecha_venta DESC";

        $stmt = Conexion::conectar()->prepare($sql);

        if ($fechaInicial != null) {
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
     
     /*===================================================================
    NUEVAS FUNCIONES PARA EL TABLERO DE INICIO
    ===================================================================*/

    /**
     * Calcula la suma en la tabla 'contabilidad' por TIPO ('Entrada' o 'Gasto').
     */
    public static function mdlDashboardSumaContabilidadPorTipo($tipo, $fechaInicial, $fechaFinal) {
        $sql = "SELECT SUM(valor) as total FROM contabilidad WHERE tipo = :tipo AND fecha BETWEEN :fechaInicial AND :fechaFinal";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
        $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
        $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Calcula la suma en la tabla 'contabilidad' por TIPO y MEDIO DE PAGO.
     */
    public static function mdlDashboardSumaContabilidadPorTipoYMedio($tipo, $medioPago, $fechaInicial, $fechaFinal) {
        $sql = "SELECT SUM(valor) as total FROM contabilidad WHERE tipo = :tipo AND medio_pago = :medioPago AND fecha BETWEEN :fechaInicial AND :fechaFinal";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
        $stmt->bindParam(":medioPago", $medioPago, PDO::PARAM_STR);
        $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
        $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Calcula la suma total histórica de la tabla 'ventas'.
     */
    public static function mdlDashboardSumaTotalVentas() {
        $stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total_ventas FROM ventas");
        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    OBTENER VENTAS PARA DATATABLE (CORREGIDO CON ALIAS)
    =============================================*/
    static public function mdlObtenerVentasServerSide($params, $fechaInicial, $fechaFinal) {
        
        $db = Conexion::conectar();
        $columns = [
            0 => 'v.id', 1 => 'v.fecha_venta', 2 => 'v.codigo', 3 => 'vendedor.nombre',
            4 => 'cliente.nombre', 5 => 'vp.descripcion', 6 => 'vp.cantidad',
            7 => 'vp.total', 8 => 'v.medio_pago'
        ];

         $sql = "SELECT 
                v.fecha_venta, 
                v.codigo AS codigo_factura, 
                v.medio_pago,
                vendedor.nombre AS nombre_vendedor, 
                cliente.nombre AS nombre_cliente,
                vp.descripcion AS producto_descripcion,
                vp.cantidad AS producto_cantidad,
                vp.total AS producto_total
            FROM venta_productos AS vp
            JOIN ventas AS v ON vp.id_venta = v.id
            JOIN usuarios AS vendedor ON v.id_vendedor = vendedor.id
            JOIN clientes AS cliente ON v.id_cliente = cliente.id";

        $where = "";
        if (!empty($fechaInicial) && !empty($fechaFinal)) {
            $where = " WHERE v.fecha_venta BETWEEN :fechaInicial AND :fechaFinal";
        }

        if (!empty($params['search']['value'])) {
            $where .= ($where == "") ? " WHERE (" : " AND (";
            $where .= " v.codigo LIKE :search_value OR vendedor.nombre LIKE :search_value OR cliente.nombre LIKE :search_value OR vp.descripcion LIKE :search_value )";
        }
        $sql .= $where;

        $sql .= " ORDER BY " . $columns[$params['order'][0]['column']] . " " . $params['order'][0]['dir'];

        if ($params['length'] != -1) {
            $sql .= " LIMIT :start, :length";
        }
        
        $stmt = $db->prepare($sql);

        if (!empty($fechaInicial) && !empty($fechaFinal)) {
            // --- CORRECCIÓN AQUÍ ---
            // Añadimos la hora final para cubrir el día completo
            $fechaFinalConHora = $fechaFinal . ' 23:59:59';
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaFinalConHora, PDO::PARAM_STR);
        }
        if (!empty($params['search']['value'])) {
            $searchValue = "%" . $params['search']['value'] . "%";
            $stmt->bindParam(":search_value", $searchValue, PDO::PARAM_STR);
        }
        if ($params['length'] != -1) {
            $stmt->bindParam(":start", $params['start'], PDO::PARAM_INT);
            $stmt->bindParam(":length", $params['length'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar registros filtrados
     */
    static public function mdlContarVentasFiltradas($params, $fechaInicial, $fechaFinal) {
        $db = Conexion::conectar();
        $sql = "SELECT COUNT(v.id) as total FROM ventas AS v 
                INNER JOIN usuarios AS vendedor ON v.id_vendedor = vendedor.id
                INNER JOIN clientes AS cliente ON v.id_cliente = cliente.id
                INNER JOIN venta_productos AS vp ON v.id = vp.id_venta";

        $where = "";
        if (!empty($fechaInicial) && !empty($fechaFinal)) {
            $where = " WHERE v.fecha_venta BETWEEN :fechaInicial AND :fechaFinal";
        }
        if (!empty($params['search']['value'])) {
            $where .= ($where == "") ? " WHERE (" : " AND (";
            $where .= " v.codigo LIKE :search_value OR vendedor.nombre LIKE :search_value OR cliente.nombre LIKE :search_value OR vp.descripcion LIKE :search_value )";
        }
        $sql .= $where;

        $stmt = $db->prepare($sql);
        
        if (!empty($fechaInicial) && !empty($fechaFinal)) {
            // --- CORRECCIÓN AQUÍ ---
            $fechaFinalConHora = $fechaFinal . ' 23:59:59';
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaFinalConHora, PDO::PARAM_STR);
        }
        if (!empty($params['search']['value'])) {
            $searchValue = "%" . $params['search']['value'] . "%";
            $stmt->bindParam(":search_value", $searchValue, PDO::PARAM_STR);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Contar el total de registros sin filtros
     */
    static public function mdlContarTotalVentas($fechaInicial, $fechaFinal) {
        $db = Conexion::conectar();
        $sql = "SELECT COUNT(vp.id) as total FROM venta_productos vp
                INNER JOIN ventas v ON vp.id_venta = v.id";
        
        if (!empty($fechaInicial) && !empty($fechaFinal)) {
            $sql .= " WHERE v.fecha_venta BETWEEN :fechaInicial AND :fechaFinal";
        }
        
        $stmt = $db->prepare($sql);

        if (!empty($fechaInicial) && !empty($fechaFinal)) {
            // --- CORRECCIÓN AQUÍ ---
            $fechaFinalConHora = $fechaFinal . ' 23:59:59';
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaFinalConHora, PDO::PARAM_STR);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
    /*=============================================
OBTENER PRODUCTOS SOLO PARA REPORTE DETALLADO (venta_productos)
=============================================*/
static public function mdlObtenerProductosDetallado($params, $fechaInicial, $fechaFinal) {
    $db = Conexion::conectar();

    $columns = [
        0 => 'vp.id',
        1 => 'v.fecha_venta',
        2 => 'v.codigo',
        3 => 'u.nombre',
        4 => 'c.nombre',
        5 => 'vp.descripcion',
        6 => 'vp.cantidad',
        7 => 'vp.total',
        8 => 'v.medio_pago'
    ];

    $sql = "
        SELECT 
            vp.id AS contador,
            v.fecha_venta,
            v.codigo AS codigo_factura,
            u.nombre AS nombre_vendedor,
            c.nombre AS nombre_cliente,
            vp.descripcion AS producto_descripcion,
            vp.cantidad AS producto_cantidad,
            vp.total AS producto_total,
            v.medio_pago
        FROM venta_productos vp
        INNER JOIN ventas v ON vp.id_venta = v.codigo
        INNER JOIN usuarios u ON v.id_vendedor = u.id
        INNER JOIN clientes c ON v.id_cliente = c.id
    ";

    $where = "";
    if (!empty($fechaInicial) && !empty($fechaFinal)) {
        $where = " WHERE DATE(v.fecha_venta) BETWEEN :fechaInicial AND :fechaFinal";
    }

    if (!empty($params['search']['value'])) {
        $where .= ($where == "") ? " WHERE (" : " AND (";
        $where .= " v.codigo LIKE :search_value OR u.nombre LIKE :search_value OR c.nombre LIKE :search_value OR vp.descripcion LIKE :search_value )";
    }

    $sql .= $where;
    $sql .= " ORDER BY " . $columns[$params['order'][0]['column']] . " " . $params['order'][0]['dir'];
    $sql .= " LIMIT :start, :length";

    $stmt = $db->prepare($sql);

    if (!empty($fechaInicial) && !empty($fechaFinal)) {
        $fechaFinalConHora = $fechaFinal . ' 23:59:59';
        $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
        $stmt->bindParam(":fechaFinal", $fechaFinalConHora, PDO::PARAM_STR);
    }
    if (!empty($params['search']['value'])) {
        $searchValue = "%" . $params['search']['value'] . "%";
        $stmt->bindParam(":search_value", $searchValue, PDO::PARAM_STR);
    }
    $stmt->bindParam(":start", $params['start'], PDO::PARAM_INT);
    $stmt->bindParam(":length", $params['length'], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /*=============================================
    CONTAR PRODUCTOS PARA REPORTE DETALLADO (venta_productos)
    =============================================*/
    static public function mdlContarProductosDetallado($params, $fechaInicial, $fechaFinal) {
        $db = Conexion::conectar();
        $sql = "SELECT COUNT(vp.id) as total
                FROM venta_productos vp
                INNER JOIN ventas v ON vp.id_venta = v.codigo
                INNER JOIN usuarios u ON v.id_vendedor = u.id
                INNER JOIN clientes c ON v.id_cliente = c.id";
    
        $where = "";
        if (!empty($fechaInicial) && !empty($fechaFinal)) {
            $where = " WHERE DATE(v.fecha_venta) BETWEEN :fechaInicial AND :fechaFinal";
        }
        if (!empty($params['search']['value'])) {
            $where .= ($where == "") ? " WHERE (" : " AND (";
            $where .= " v.codigo LIKE :search_value OR u.nombre LIKE :search_value OR c.nombre LIKE :search_value OR vp.descripcion LIKE :search_value )";
        }
        $sql .= $where;
    
        $stmt = $db->prepare($sql);
        if (!empty($fechaInicial) && !empty($fechaFinal)) {
            $fechaFinalConHora = $fechaFinal . ' 23:59:59';
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaFinalConHora, PDO::PARAM_STR);
        }
        if (!empty($params['search']['value'])) {
            $searchValue = "%" . $params['search']['value'] . "%";
            $stmt->bindParam(":search_value", $searchValue, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetch()['total'];
    }
}