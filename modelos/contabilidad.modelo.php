<?php

require_once "conexion.php";

class ModeloContabilidad
    {
        private const TABLA = 'contabilidad';
    
        /*=============================================
    GUARDAR MOVIMIENTO (GASTO O ENTRADA) - FUNCIÓN CORREGIDA
    =============================================*/
    public static function save($datos) {
    
        // Se construye la lista de columnas y placeholders dinámicamente
        // Esto hace que la función sea flexible para guardar tanto gastos como entradas
        $columnas = implode(", ", array_keys($datos));
        $placeholders = ":" . implode(", :", array_keys($datos));
    
        $sql = "INSERT INTO " . self::TABLA . " ($columnas) VALUES ($placeholders)";
        
        $stmt = Conexion::conectar()->prepare($sql);
    
        // Se vinculan todos los datos que llegaron desde el controlador
        foreach ($datos as $key => &$val) {
            $stmt->bindParam(":" . $key, $val);
        }
    
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    
        public static function filterBy($fechaInicial, $fechaFinal, $medioPago, $tipo)
    {
        // 1. La consulta base ahora une las tablas para obtener el nombre del vendedor
        $sql = "SELECT cont.*, u.nombre AS nombre_vendedor
                FROM " . self::TABLA . " cont
                INNER JOIN usuarios u ON cont.id_vendedor = u.id";
    
        // 2. Se usan arrays para construir la consulta de forma segura
        $conditions = [];
        $params = [];
    
        // Siempre se filtra por tipo (Gasto, Entrada, etc.)
        $conditions[] = "cont.tipo = :tipo";
        $params[':tipo'] = $tipo;
    
        // Se añaden los filtros opcionales de forma segura
        if ($fechaInicial != null && $fechaFinal != null) {
            $conditions[] = "cont.fecha BETWEEN :fechaInicial AND :fechaFinal";
            $params[':fechaInicial'] = $fechaInicial . " 00:00:00";
            $params[':fechaFinal'] = $fechaFinal . " 23:59:59";
        }
    
        if ($medioPago != null) {
            $conditions[] = "cont.medio_pago = :medioPago";
            $params[':medioPago'] = $medioPago;
        }
    
        // 3. Se añaden las condiciones a la consulta
        $sql .= " WHERE " . implode(" AND ", $conditions);
        $sql .= " ORDER BY cont.id DESC";
    
        $stmt = Conexion::conectar()->prepare($sql);
    
        // 4. Se ejecuta la consulta con los parámetros, previniendo inyección SQL
        $stmt->execute($params);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
        public static function findById($id)
        {
            $sql = "SELECT * FROM " . self::TABLA . " WHERE id = '$id'";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
        }
    
        public static function update($id, $datos)
        {
            $columns = '';
            foreach ($datos as $key => $value) {
                $columns .= $key . "='" . $value . "',";
            }
            $columns = substr($columns, 0, -1);
            $sql = "UPDATE " . self::TABLA . " SET $columns WHERE id = :id";
    
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        }
    
        public static function delete($id)
        {
            $sql = "DELETE FROM " . self::TABLA . " WHERE id = :id";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        }
    
        public static function sumByTipo($tipo)
        {
            $dataInicial = date('Y-m-d 00:00:00');
            $dataFinal = date('Y-m-d 23:59:59');
    
            $sql = "SELECT SUM(valor) AS total FROM " . self::TABLA . " WHERE tipo = '$tipo' AND fecha BETWEEN '$dataInicial' AND '$dataFinal'";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
        }
    
        public static function sumByTipoAndMedio($tipo, $medioPago)
        {
            $dataInicial = date('Y-m-d 00:00:00');
            $dataFinal = date('Y-m-d 23:59:59');
    
            $sql = "SELECT SUM(valor) AS total FROM " . self::TABLA . " WHERE tipo = '$tipo' AND medio_pago = '$medioPago' AND fecha BETWEEN '$dataInicial' AND '$dataFinal'";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
        }
        /*=============================================
        SUMA ENTRADAS POR MEDIO DE PAGO (CORREGIDO)
        =============================================*/
        static public function mdlSumaEntradasPorMedioPago($tabla, $fechaInicial, $fechaFinal){
        
        	if($fechaInicial == null){
        
        		$stmt = Conexion::conectar()->prepare("SELECT medio_pago, SUM(valor) as total_entradas FROM $tabla WHERE tipo = 'Entrada' GROUP BY medio_pago");
        	
        	}else{
        
        		$fechaFinalConHora = $fechaFinal . ' 23:59:59';
        		$stmt = Conexion::conectar()->prepare("SELECT medio_pago, SUM(valor) as total_entradas FROM $tabla WHERE tipo = 'Entrada' AND fecha BETWEEN :fechaInicial AND :fechaFinal GROUP BY medio_pago");
        		$stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
        		$stmt->bindParam(":fechaFinal", $fechaFinalConHora, PDO::PARAM_STR);
        	
        	}
        	
        	$stmt -> execute();
        	return $stmt -> fetchAll();
        
        }
    /*=============================================
    SUMAR TOTAL DE GASTOS (CORREGIDO)
    =============================================*/
    static public function mdlSumaTotalGastos($tabla, $fechaInicial, $fechaFinal){
    
        $sql_base = "SELECT SUM(valor) as total FROM $tabla WHERE tipo = 'Gasto'";
        
        if($fechaInicial == null){
            $stmt = Conexion::conectar()->prepare($sql_base);
        } else {
            // Se añade el filtro de fecha a la consulta de suma
            $stmt = Conexion::conectar()->prepare($sql_base . " AND DATE(fecha) BETWEEN :fechaInicial AND :fechaFinal");
            $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
            $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
        }
    
        $stmt -> execute();
        return $stmt -> fetch();
    }
    
    	/*=============================================
    	SUMAR TOTAL POR TIPO Y MEDIO DE PAGO (CORREGIDO)
    	=============================================*/
    	static public function mdlSumaTotalPorTipoYMedio($tabla, $tipo, $medioPago, $fechaInicial, $fechaFinal){
    		$sql_base = "SELECT SUM(valor) as total FROM $tabla WHERE tipo = :tipo AND medio_pago = :medio_pago";
    		if($fechaInicial == null){
    			$stmt = Conexion::conectar()->prepare($sql_base);
    			$stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
    			$stmt->bindParam(":medio_pago", $medioPago, PDO::PARAM_STR);
    		}else{
    			$stmt = Conexion::conectar()->prepare($sql_base . " AND DATE(fecha) BETWEEN :fechaInicial AND :fechaFinal");
    			$stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
    			$stmt->bindParam(":medio_pago", $medioPago, PDO::PARAM_STR);
    			$stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
    			$stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
    		}
    		$stmt -> execute();
    		return $stmt -> fetch();
    	}
    
    
    	/*=============================================
    	SUMAR GASTOS POR MEDIO DE PAGO (CORREGIDO)
    	=============================================*/
    	static public function mdlSumaGastosPorMedioPago($tabla, $fechaInicial, $fechaFinal){
    		$sql_base = "SELECT medio_pago, SUM(valor) as total_gastos FROM $tabla WHERE tipo = 'Gasto' GROUP BY medio_pago";
    		if($fechaInicial == null){
    			$stmt = Conexion::conectar()->prepare($sql_base);
    		}else{
    			$stmt = Conexion::conectar()->prepare("SELECT medio_pago, SUM(valor) as total_gastos FROM $tabla WHERE tipo = 'Gasto' AND DATE(fecha) BETWEEN :fechaInicial AND :fechaFinal GROUP BY medio_pago");
    			$stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
    			$stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
    		}
    		$stmt -> execute();
    		return $stmt -> fetchAll();
    	}
        /*=============================================
        SUMA TOTAL DE ENTRADAS (CORREGIDO)
        =============================================*/
        static public function mdlSumaTotalEntradas($tabla, $fechaInicial, $fechaFinal){
        
        	// El nombre de la columna de fecha puede ser 'fecha' o 'fecha_creacion'
        	// Ajusta 'fecha' si tu columna se llama diferente.
        	if($fechaInicial == null){
        
        		$stmt = Conexion::conectar()->prepare("SELECT SUM(valor) as total FROM $tabla WHERE tipo = 'Entrada'");
        
        	}else{
                // Añadimos la hora final para cubrir el día completo
        		$fechaFinalConHora = $fechaFinal . ' 23:59:59';
        		$stmt = Conexion::conectar()->prepare("SELECT SUM(valor) as total FROM $tabla WHERE tipo = 'Entrada' AND fecha BETWEEN :fechaInicial AND :fechaFinal");
        		$stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
        		$stmt->bindParam(":fechaFinal", $fechaFinalConHora, PDO::PARAM_STR);
        
        	}
        
        	$stmt -> execute();
        	return $stmt -> fetch();
        
        }
        /*=============================================
        FILTRAR ENTRADAS/GASTOS (FUNCIÓN CORREGIDA)
        =============================================*/
        // Asegúrate de que esta es la función que usa tu tabla de Entradas.
        // Si tiene otro nombre, como mdlMostrarEntradas, reemplaza esa.
        static public function mdlFilterContabilidad($tabla, $fechaInicial, $fechaFinal, $medioPago, $tipo) {
            
            $sql = "SELECT * FROM $tabla WHERE 1=1";
            $params = [];
        
            if ($tipo != null) {
                $sql .= " AND tipo = :tipo";
                $params[":tipo"] = $tipo;
            }
        
            if ($fechaInicial != null && $fechaFinal != null) {
                // CAMBIO CLAVE: Se usa DATE() para comparar solo la fecha
                $sql .= " AND DATE(fecha) BETWEEN :fechaInicial AND :fechaFinal";
                $params[":fechaInicial"] = $fechaInicial;
                $params[":fechaFinal"] = $fechaFinal;
            }
        
            if ($medioPago != null) {
                $sql .= " AND medio_pago = :medioPago";
                $params[":medioPago"] = $medioPago;
            }
        
            $stmt = Conexion::conectar()->prepare("$sql ORDER BY id DESC");
        
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        
            $stmt->execute();
            return $stmt->fetchAll();
        }
    /*=============================================
    FILTRAR MOVIMIENTOS (VERSIÓN FINAL)
    =============================================*/
    public static function mdlFilterBy($tabla, $fechaInicial, $fechaFinal, $medioPago, $tipo) {
        
        $sql = "SELECT * FROM $tabla WHERE 1=1";
        $params = [];
    
        if ($tipo != null) {
            $sql .= " AND tipo = :tipo";
            $params[":tipo"] = $tipo;
        }
    
        // LÓGICA DE FECHA CORREGIDA
        if ($fechaInicial != null && $fechaFinal != null) {
            // Se usa DATE() para ignorar la hora
            $sql .= " AND DATE(fecha) BETWEEN :fechaInicial AND :fechaFinal";
            $params[":fechaInicial"] = $fechaInicial;
            $params[":fechaFinal"] = $fechaFinal;
        }
    
        if ($medioPago != null) {
            $sql .= " AND medio_pago = :medioPago";
            $params[":medioPago"] = $medioPago;
        }
    
        $stmt = Conexion::conectar()->prepare("$sql ORDER BY id DESC");
    
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
    
        $stmt->execute();
        return $stmt->fetchAll();
    }
        /*=============================================
    NUEVA FUNCIÓN: BORRAR ENTRADA POR NÚMERO DE FACTURA
    =============================================*/
    public static function deleteByFactura($factura)
    {
        $sql = "DELETE FROM " . self::TABLA . " WHERE factura = :factura AND tipo = 'Entrada'";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":factura", $factura, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    /*=============================================
    MOSTRAR ENTRADAS SERVER-SIDE (VERSIÓN FINAL CON FILTRO DE FECHA CORREGIDO)
    =============================================*/
    static public function mdlMostrarEntradasServerSide($tabla, $inicio, $longitud, $busqueda, $fechaInicial, $fechaFinal, $medioPago){
        $sql = "SELECT t1.id, t1.factura, t1.fecha, t1.detalle, t1.valor, t1.medio_pago, t1.forma_pago, t2.empresa 
                FROM $tabla AS t1
                INNER JOIN usuarios AS t2 ON t1.id_vendedor = t2.id";
        $condiciones = ["TRIM(t1.tipo) = 'Entrada'"];
        $parametros = [];
    
        if($busqueda != null && $busqueda != ""){
            $condiciones[] = "(t1.factura LIKE :busqueda OR t1.detalle LIKE :busqueda OR t2.empresa LIKE :busqueda)";
            $parametros[':busqueda'] = "%".$busqueda."%";
        }
        if($fechaInicial != null && $fechaInicial != "" && $fechaFinal != null && $fechaFinal != ""){
            // CAMBIO: Añadimos DATE() para comparar solo la porción de la fecha
            $condiciones[] = "DATE(t1.fecha) BETWEEN :fechaInicial AND :fechaFinal";
            $parametros[':fechaInicial'] = $fechaInicial;
            $parametros[':fechaFinal'] = $fechaFinal;
        }
        if($medioPago != null && $medioPago != ""){
            $condiciones[] = "t1.medio_pago = :medioPago";
            $parametros[':medioPago'] = $medioPago;
        }
        $sql .= " WHERE " . implode(" AND ", $condiciones);
        $sql .= " ORDER BY t1.id DESC LIMIT :inicio, :longitud";
        $stmt = Conexion::conectar()->prepare($sql);
        foreach ($parametros as $key => &$val) { $stmt->bindParam($key, $val); }
        $stmt->bindParam(":inicio", $inicio, PDO::PARAM_INT);
        $stmt->bindParam(":longitud", $longitud, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /*=============================================
    CONTAR ENTRADAS SERVER-SIDE (VERSIÓN FINAL CON FILTRO DE FECHA CORREGIDO)
    =============================================*/
    static public function mdlContarEntradasServerSide($tabla, $busqueda, $fechaInicial, $fechaFinal, $medioPago){
        $sql = "SELECT COUNT(t1.id)
                FROM $tabla AS t1
                INNER JOIN usuarios AS t2 ON t1.id_vendedor = t2.id";
        $condiciones = ["TRIM(t1.tipo) = 'Entrada'"];
        $parametros = [];
        if($busqueda != null && $busqueda != ""){
            $condiciones[] = "(t1.factura LIKE :busqueda OR t1.detalle LIKE :busqueda OR t2.empresa LIKE :busqueda)";
            $parametros[':busqueda'] = "%".$busqueda."%";
        }
        if($fechaInicial != null && $fechaInicial != "" && $fechaFinal != null && $fechaFinal != ""){
            // CAMBIO: Añadimos DATE() también aquí para que el conteo sea correcto
            $condiciones[] = "DATE(t1.fecha) BETWEEN :fechaInicial AND :fechaFinal";
            $parametros[':fechaInicial'] = $fechaInicial;
            $parametros[':fechaFinal'] = $fechaFinal;
        }
        if($medioPago != null && $medioPago != ""){
            $condiciones[] = "t1.medio_pago = :medioPago";
            $parametros[':medioPago'] = $medioPago;
        }
        $sql .= " WHERE " . implode(" AND ", $condiciones);
        $stmt = Conexion::conectar()->prepare($sql);
        foreach ($parametros as $key => &$val) { $stmt->bindParam($key, $val); }
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
/*=============================================
MOSTRAR VENTAS DE CONTABILIDAD (VERSIÓN FINAL Y COMPLETA)
=============================================*/
static public function mdlMostrarVentasContabilidad($inicio, $longitud, $busqueda, $fechaInicial, $fechaFinal, $medioPago, $formaPago){
    $sql = "SELECT v.id, v.codigo, c.nombre AS cliente, u.nombre AS vendedor, v.metodo_pago, v.neto, v.total, v.fecha_venta AS fecha, v.abono, v.Ult_abono AS ultimo_abono, v.pago, v.medio_pago,
            u_abono.empresa AS empresa_abono, u_abono.nombre AS vendedor_abono
            FROM ventas AS v
            INNER JOIN clientes AS c ON v.id_cliente = c.id
            INNER JOIN usuarios AS u ON v.id_vendedor = u.id
            LEFT JOIN usuarios AS u_abono ON v.id_vend_abono = u_abono.id";
    
    $condiciones = [];
    $parametros = [];

    if($busqueda != null && $busqueda != ""){
        $condiciones[] = "(v.codigo LIKE :busqueda OR c.nombre LIKE :busqueda OR u.nombre LIKE :busqueda)";
        $parametros[':busqueda'] = "%".$busqueda."%";
    }
    if($fechaInicial != null && $fechaInicial != "" && $fechaFinal != null && $fechaFinal != ""){
        $condiciones[] = "DATE(v.fecha_venta) BETWEEN :fechaInicial AND :fechaFinal";
        $parametros[':fechaInicial'] = $fechaInicial;
        $parametros[':fechaFinal'] = $fechaFinal;
    }
    if($medioPago != null && $medioPago != ""){
        $condiciones[] = "v.medio_pago = :medioPago";
        $parametros[':medioPago'] = $medioPago;
    }
    if($formaPago != null && $formaPago != ""){
        $condiciones[] = "v.metodo_pago = :formaPago";
        $parametros[':formaPago'] = $formaPago;
    }

    if(count($condiciones) > 0){
        $sql .= " WHERE " . implode(" AND ", $condiciones);
    }

    $sql .= " ORDER BY v.id DESC LIMIT :inicio, :longitud";
    $stmt = Conexion::conectar()->prepare($sql);
    foreach ($parametros as $key => &$val) { $stmt->bindParam($key, $val); }
    $stmt->bindParam(":inicio", $inicio, PDO::PARAM_INT);
    $stmt->bindParam(":longitud", $longitud, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/*=============================================
CONTAR VENTAS DE CONTABILIDAD (VERSIÓN FINAL Y COMPLETA)
=============================================*/
static public function mdlContarVentasContabilidad($busqueda, $fechaInicial, $fechaFinal, $medioPago, $formaPago){
    $sql = "SELECT COUNT(v.id)
            FROM ventas AS v
            INNER JOIN clientes AS c ON v.id_cliente = c.id
            INNER JOIN usuarios AS u ON v.id_vendedor = u.id";
            
    $condiciones = [];
    $parametros = [];

    if($busqueda != null && $busqueda != ""){
        $condiciones[] = "(v.codigo LIKE :busqueda OR c.nombre LIKE :busqueda OR u.nombre LIKE :busqueda)";
        $parametros[':busqueda'] = "%".$busqueda."%";
    }
    if($fechaInicial != null && $fechaInicial != "" && $fechaFinal != null && $fechaFinal != ""){
        $condiciones[] = "DATE(v.fecha_venta) BETWEEN :fechaInicial AND :fechaFinal";
        $parametros[':fechaInicial'] = $fechaInicial;
        $parametros[':fechaFinal'] = $fechaFinal;
    }
    if($medioPago != null && $medioPago != ""){
        $condiciones[] = "v.medio_pago = :medioPago";
        $parametros[':medioPago'] = $medioPago;
    }
    if($formaPago != null && $formaPago != ""){
        $condiciones[] = "v.metodo_pago = :formaPago";
        $parametros[':formaPago'] = $formaPago;
    }

    if(count($condiciones) > 0){
        $sql .= " WHERE " . implode(" AND ", $condiciones);
    }
    
    $stmt = Conexion::conectar()->prepare($sql);
    foreach ($parametros as $key => &$val) { $stmt->bindParam($key, $val); }
    $stmt->execute();
    return $stmt->fetchColumn();
}
    
}

