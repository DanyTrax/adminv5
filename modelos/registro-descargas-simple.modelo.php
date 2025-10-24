<?php
/*=============================================
MODELO REGISTRO DE DESCARGAS SIMPLE
=============================================*/

class ModeloRegistroDescargasSimple {

    /*=============================================
    REGISTRAR DESCARGA
    =============================================*/
    static public function mdlRegistrarDescarga($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO registro_descargas_stock_transito (
                    codigo_producto, descripcion_producto, cantidad_descargada,
                    usuario_id, usuario_nombre, sucursal_id, sucursal_nombre,
                    transportador_id, transportador_nombre, numero_despacho,
                    observaciones, ip_usuario, user_agent
                ) VALUES (
                    :codigo_producto, :descripcion_producto, :cantidad_descargada,
                    :usuario_id, :usuario_nombre, :sucursal_id, :sucursal_nombre,
                    :transportador_id, :transportador_nombre, :numero_despacho,
                    :observaciones, :ip_usuario, :user_agent
                )
            ");

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
            $stmt->bindParam(":ip_usuario", $datos["ip_usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":user_agent", $datos["user_agent"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }

            $stmt->close();
            $stmt = null;

        } catch (Exception $e) {
            error_log("Error en mdlRegistrarDescarga: " . $e->getMessage());
            return "error";
        }
    }

    /*=============================================
    OBTENER TODAS LAS DESCARGAS
    =============================================*/
    static public function mdlObtenerTodasDescargas($filtros = []) {
        try {
            $where = "1=1";
            $params = [];

            // Filtro por fecha
            if (!empty($filtros["fecha_desde"])) {
                $where .= " AND DATE(fecha_descarga) >= :fecha_desde";
                $params[":fecha_desde"] = $filtros["fecha_desde"];
            }
            if (!empty($filtros["fecha_hasta"])) {
                $where .= " AND DATE(fecha_descarga) <= :fecha_hasta";
                $params[":fecha_hasta"] = $filtros["fecha_hasta"];
            }

            // Filtro por usuario
            if (!empty($filtros["usuario_id"])) {
                $where .= " AND usuario_id = :usuario_id";
                $params[":usuario_id"] = $filtros["usuario_id"];
            }

            // Filtro por sucursal
            if (!empty($filtros["sucursal_id"])) {
                $where .= " AND sucursal_id = :sucursal_id";
                $params[":sucursal_id"] = $filtros["sucursal_id"];
            }

            // Filtro por código de producto
            if (!empty($filtros["codigo_producto"])) {
                $where .= " AND codigo_producto LIKE :codigo_producto";
                $params[":codigo_producto"] = "%" . $filtros["codigo_producto"] . "%";
            }

            // Búsqueda general
            if (!empty($filtros["busqueda_general"])) {
                $busqueda = "%" . $filtros["busqueda_general"] . "%";
                $where .= " AND (
                    codigo_producto LIKE :busqueda1 OR 
                    descripcion_producto LIKE :busqueda2 OR 
                    usuario_nombre LIKE :busqueda3 OR 
                    sucursal_nombre LIKE :busqueda4 OR 
                    transportador_nombre LIKE :busqueda5 OR 
                    numero_despacho LIKE :busqueda6
                )";
                $params[":busqueda1"] = $busqueda;
                $params[":busqueda2"] = $busqueda;
                $params[":busqueda3"] = $busqueda;
                $params[":busqueda4"] = $busqueda;
                $params[":busqueda5"] = $busqueda;
                $params[":busqueda6"] = $busqueda;
            }

            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM registro_descargas_stock_transito 
                WHERE $where 
                ORDER BY fecha_descarga DESC
            ");

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error en mdlObtenerTodasDescargas: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    OBTENER ESTADÍSTICAS DE DESCARGAS
    =============================================*/
    static public function mdlObtenerEstadisticasDescargas($filtros = []) {
        try {
            $where = "1=1";
            $params = [];

            // Aplicar mismos filtros que en mdlObtenerTodasDescargas
            if (!empty($filtros["fecha_desde"])) {
                $where .= " AND DATE(fecha_descarga) >= :fecha_desde";
                $params[":fecha_desde"] = $filtros["fecha_desde"];
            }
            if (!empty($filtros["fecha_hasta"])) {
                $where .= " AND DATE(fecha_descarga) <= :fecha_hasta";
                $params[":fecha_hasta"] = $filtros["fecha_hasta"];
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

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetch();

        } catch (Exception $e) {
            error_log("Error en mdlObtenerEstadisticasDescargas: " . $e->getMessage());
            return [
                "total_descargas" => 0,
                "total_cantidad" => 0,
                "productos_unicos" => 0,
                "usuarios_unicos" => 0,
                "sucursales_unicas" => 0
            ];
        }
    }
}
?>