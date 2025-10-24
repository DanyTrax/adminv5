<?php
/*=============================================
MODELO REGISTRO DE DESCARGAS SIMPLE - FUNCIONAL
=============================================*/

require_once "conexion.php";

class ModeloRegistroDescargasSimple {
    
    /*=============================================
    REGISTRAR DESCARGA
    =============================================*/
    static public function mdlRegistrarDescarga($datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO registro_descargas_stock_transito (
            codigo_producto, 
            descripcion_producto, 
            cantidad_descargada, 
            usuario_id, 
            usuario_nombre, 
            sucursal_id, 
            sucursal_nombre, 
            transportador_id, 
            transportador_nombre, 
            numero_despacho, 
            observaciones, 
            fecha_descarga, 
            ip_usuario, 
            user_agent, 
            created_at
        ) VALUES (
            :codigo_producto, 
            :descripcion_producto, 
            :cantidad_descargada, 
            :usuario_id, 
            :usuario_nombre, 
            :sucursal_id, 
            :sucursal_nombre, 
            :transportador_id, 
            :transportador_nombre, 
            :numero_despacho, 
            :observaciones, 
            :fecha_descarga, 
            :ip_usuario, 
            :user_agent, 
            :created_at
        )");
        
        $stmt->bindParam(":codigo_producto", $datos["codigo_producto"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion_producto", $datos["descripcion_producto"], PDO::PARAM_STR);
        $stmt->bindParam(":cantidad_descargada", $datos["cantidad_descargada"], PDO::PARAM_INT);
        $stmt->bindParam(":usuario_id", $datos["usuario_id"], PDO::PARAM_INT);
        $stmt->bindParam(":usuario_nombre", $datos["usuario_nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":sucursal_id", $datos["sucursal_id"], PDO::PARAM_INT);
        $stmt->bindParam(":sucursal_nombre", $datos["sucursal_nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":transportador_id", $datos["transportador_id"], PDO::PARAM_INT);
        $stmt->bindParam(":transportador_nombre", $datos["transportador_nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":numero_despacho", $datos["numero_despacho"], PDO::PARAM_STR);
        $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_descarga", date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindParam(":ip_usuario", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $stmt->bindParam(":user_agent", $_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR);
        $stmt->bindParam(":created_at", date('Y-m-d H:i:s'), PDO::PARAM_STR);
        
        if($stmt->execute()){
            return "ok";
        } else {
            return "error";
        }
        
        $stmt->close();
        $stmt = null;
    }
    
    /*=============================================
    OBTENER REGISTRO
    =============================================*/
    static public function mdlObtenerRegistro($filtros = []) {
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
        
        $stmt = Conexion::conectar()->prepare("SELECT * FROM registro_descargas_stock_transito WHERE $where ORDER BY created_at DESC");
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /*=============================================
    OBTENER ESTADÍSTICAS
    =============================================*/
    static public function mdlObtenerEstadisticas($filtros = []) {
        $where = "1=1";
        $params = [];
        
        if(!empty($filtros['fecha_desde'])) {
            $where .= " AND fecha_descarga >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }
        
        if(!empty($filtros['fecha_hasta'])) {
            $where .= " AND fecha_descarga <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                COUNT(*) as total_descargas,
                SUM(cantidad_descargada) as total_cantidad,
                COUNT(DISTINCT codigo_producto) as productos_unicos,
                COUNT(DISTINCT usuario_id) as usuarios_unicos,
                COUNT(DISTINCT sucursal_id) as sucursales_unicas
            FROM registro_descargas_stock_transito 
            WHERE $where
        ");
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        return [
            "total_descargas" => $resultado['total_descargas'] ?? 0,
            "total_cantidad" => $resultado['total_cantidad'] ?? 0,
            "productos_unicos" => $resultado['productos_unicos'] ?? 0,
            "usuarios_unicos" => $resultado['usuarios_unicos'] ?? 0,
            "sucursales_unicas" => $resultado['sucursales_unicas'] ?? 0
        ];
    }
}
?>