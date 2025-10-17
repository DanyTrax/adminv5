<?php

require_once "conexion.php";

class ModeloHistoricoTransito {

    /*=============================================
    MOSTRAR HISTÓRICO DE MOVIMIENTOS
    =============================================*/
    static public function mdlMostrarHistoricoTransito($tabla, $item, $valor, $filtros = []) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            if($item != null && $valor != null) {
                // Consulta específica por item y valor
                $stmt = ConexionCentral::conectar()->prepare("
                    SELECT * FROM $tabla 
                    WHERE $item = :$item 
                    ORDER BY fecha_movimiento DESC
                ");
                $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetch();
                
            } else {
                // Consulta con filtros
                $sql = "SELECT * FROM $tabla WHERE 1=1";
                $parametros = [];
                
                // Filtro por perfil de usuario
                if($_SESSION["perfil"] == "Transportador") {
                    $sql .= " AND transportador_id = :transportador_sesion";
                    $parametros[':transportador_sesion'] = $_SESSION["id"];
                }
                
                // Aplicar filtros adicionales
                if(!empty($filtros['fecha_desde'])) {
                    $sql .= " AND DATE(fecha_movimiento) >= :fecha_desde";
                    $parametros[':fecha_desde'] = $filtros['fecha_desde'];
                }
                
                if(!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND DATE(fecha_movimiento) <= :fecha_hasta";
                    $parametros[':fecha_hasta'] = $filtros['fecha_hasta'];
                }
                
                if(!empty($filtros['transportador'])) {
                    $sql .= " AND transportador_id = :transportador_filtro";
                    $parametros[':transportador_filtro'] = $filtros['transportador'];
                }
                
                if(!empty($filtros['tipo_movimiento'])) {
                    $sql .= " AND tipo_movimiento = :tipo_movimiento";
                    $parametros[':tipo_movimiento'] = $filtros['tipo_movimiento'];
                }
                
                if(!empty($filtros['codigo_producto'])) {
                    $sql .= " AND codigo_producto LIKE :codigo_producto";
                    $parametros[':codigo_producto'] = '%' . $filtros['codigo_producto'] . '%';
                }
                
                if(!empty($filtros['sucursal'])) {
                    $sql .= " AND (sucursal_origen LIKE :sucursal OR sucursal_destino LIKE :sucursal)";
                    $parametros[':sucursal'] = '%' . $filtros['sucursal'] . '%';
                }
                
                if(!empty($filtros['usuario'])) {
                    $sql .= " AND (nombre_usuario_origen LIKE :usuario OR nombre_usuario_destino LIKE :usuario)";
                    $parametros[':usuario'] = '%' . $filtros['usuario'] . '%';
                }
                
                $sql .= " ORDER BY fecha_movimiento DESC";
                
                // Limitar resultados si no hay filtros específicos
                if(empty($filtros) || count($filtros) <= 2) {
                    $sql .= " LIMIT 500";
                }
                
                $stmt = ConexionCentral::conectar()->prepare($sql);
                
                foreach($parametros as $key => $valor) {
                    $stmt->bindValue($key, $valor);
                }
                
                $stmt->execute();
                return $stmt->fetchAll();
            }

        } catch(Exception $e) {
            error_log("Error en mdlMostrarHistoricoTransito: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    REGISTRAR MOVIMIENTO EN HISTÓRICO
    =============================================*/
    static public function mdlRegistrarMovimiento($datos) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                INSERT INTO historico_transito 
                (codigo_producto, descripcion_producto, cantidad, tipo_movimiento, 
                transportador_id, nombre_transportador, sucursal_origen, sucursal_destino,
                usuario_origen, nombre_usuario_origen, usuario_destino, nombre_usuario_destino,
                id_despacho, numero_despacho, id_solicitud_descarga, observaciones) 
                VALUES 
                (:codigo_producto, :descripcion_producto, :cantidad, :tipo_movimiento,
                :transportador_id, :nombre_transportador, :sucursal_origen, :sucursal_destino,
                :usuario_origen, :nombre_usuario_origen, :usuario_destino, :nombre_usuario_destino,
                :id_despacho, :numero_despacho, :id_solicitud_descarga, :observaciones)
            ");
            
            $stmt->bindParam(":codigo_producto", $datos["codigo_producto"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion_producto", $datos["descripcion_producto"], PDO::PARAM_STR);
            $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
            $stmt->bindParam(":tipo_movimiento", $datos["tipo_movimiento"], PDO::PARAM_STR);
            $stmt->bindParam(":transportador_id", $datos["transportador_id"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre_transportador", $datos["nombre_transportador"], PDO::PARAM_STR);
            $stmt->bindParam(":sucursal_origen", $datos["sucursal_origen"], PDO::PARAM_STR);
            $stmt->bindParam(":sucursal_destino", $datos["sucursal_destino"], PDO::PARAM_STR);
            $stmt->bindParam(":usuario_origen", $datos["usuario_origen"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre_usuario_origen", $datos["nombre_usuario_origen"], PDO::PARAM_STR);
            $stmt->bindParam(":usuario_destino", $datos["usuario_destino"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre_usuario_destino", $datos["nombre_usuario_destino"], PDO::PARAM_STR);
            $stmt->bindParam(":id_despacho", $datos["id_despacho"], PDO::PARAM_INT);
            $stmt->bindParam(":numero_despacho", $datos["numero_despacho"], PDO::PARAM_STR);
            $stmt->bindParam(":id_solicitud_descarga", $datos["id_solicitud_descarga"], PDO::PARAM_INT);
            $stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);
            
            if($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }

        } catch(Exception $e) {
            error_log("Error registrando movimiento histórico: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }

    /*=============================================
    OBTENER ESTADÍSTICAS POR FECHAS
    =============================================*/
    static public function mdlObtenerEstadisticasPorFechas($fechaDesde, $fechaHasta, $transportadorId = null) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $sql = "SELECT 
                        DATE(fecha_movimiento) as fecha,
                        tipo_movimiento,
                        COUNT(*) as total_movimientos,
                        SUM(cantidad) as total_cantidad
                    FROM historico_transito 
                    WHERE DATE(fecha_movimiento) BETWEEN :fecha_desde AND :fecha_hasta";
            
            $parametros = [
                ':fecha_desde' => $fechaDesde,
                ':fecha_hasta' => $fechaHasta
            ];
            
            // Filtrar por transportador si es necesario
            if($transportadorId) {
                $sql .= " AND transportador_id = :transportador_id";
                $parametros[':transportador_id'] = $transportadorId;
            }
            
            $sql .= " GROUP BY DATE(fecha_movimiento), tipo_movimiento
                     ORDER BY fecha ASC, tipo_movimiento ASC";
            
            $stmt = ConexionCentral::conectar()->prepare($sql);
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();

        } catch(Exception $e) {
            error_log("Error obteniendo estadísticas por fechas: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    OBTENER TOP PRODUCTOS MÁS MOVIDOS
    =============================================*/
    static public function mdlObtenerTopProductos($limite = 10, $filtros = []) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $sql = "SELECT 
                        codigo_producto,
                        descripcion_producto,
                        COUNT(*) as total_movimientos,
                        SUM(cantidad) as total_cantidad,
                        MAX(fecha_movimiento) as ultimo_movimiento
                    FROM historico_transito 
                    WHERE 1=1";
            
            $parametros = [];
            
            // Aplicar filtros
            if($_SESSION["perfil"] == "Transportador") {
                $sql .= " AND transportador_id = :transportador_sesion";
                $parametros[':transportador_sesion'] = $_SESSION["id"];
            }
            
            if(!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(fecha_movimiento) >= :fecha_desde";
                $parametros[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if(!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(fecha_movimiento) <= :fecha_hasta";
                $parametros[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            $sql .= " GROUP BY codigo_producto, descripcion_producto
                     ORDER BY total_cantidad DESC
                     LIMIT :limite";
            
            $stmt = ConexionCentral::conectar()->prepare($sql);
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();

        } catch(Exception $e) {
            error_log("Error obteniendo top productos: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    OBTENER EFICIENCIA POR TRANSPORTADOR
    =============================================*/
    static public function mdlObtenerEficienciaPorTransportador($filtros = []) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $sql = "SELECT 
                        transportador_id,
                        nombre_transportador,
                        COUNT(*) as total_movimientos,
                        SUM(CASE WHEN tipo_movimiento = 'cargue' THEN cantidad ELSE 0 END) as productos_cargados,
                        SUM(CASE WHEN tipo_movimiento = 'descarga' THEN cantidad ELSE 0 END) as productos_descargados,
                        COUNT(CASE WHEN tipo_movimiento = 'solicitud_descarga' THEN 1 END) as solicitudes_recibidas,
                        COUNT(CASE WHEN tipo_movimiento = 'rechazo_descarga' THEN 1 END) as solicitudes_rechazadas,
                        COUNT(CASE WHEN tipo_movimiento = 'descarga_forzada' THEN 1 END) as descargas_forzadas,
                        ROUND(
                            (COUNT(CASE WHEN tipo_movimiento = 'descarga' THEN 1 END) * 100.0) / 
                            NULLIF(COUNT(CASE WHEN tipo_movimiento = 'solicitud_descarga' THEN 1 END), 0), 
                            2
                        ) as tasa_aprobacion
                    FROM historico_transito 
                    WHERE 1=1";
            
            $parametros = [];
            
            // Solo para administradores
            if($_SESSION["perfil"] != "Administrador") {
                return [];
            }
            
            if(!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(fecha_movimiento) >= :fecha_desde";
                $parametros[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if(!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(fecha_movimiento) <= :fecha_hasta";
                $parametros[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            $sql .= " GROUP BY transportador_id, nombre_transportador
                     ORDER BY total_movimientos DESC";
            
            $stmt = ConexionCentral::conectar()->prepare($sql);
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();

        } catch(Exception $e) {
            error_log("Error obteniendo eficiencia por transportador: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    OBTENER MOVIMIENTOS RECIENTES
    =============================================*/
    static public function mdlObtenerMovimientosRecientes($limite = 20) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $sql = "SELECT * FROM historico_transito WHERE 1=1";
            $parametros = [];
            
            // Filtrar por transportador si es necesario
            if($_SESSION["perfil"] == "Transportador") {
                $sql .= " AND transportador_id = :transportador_sesion";
                $parametros[':transportador_sesion'] = $_SESSION["id"];
            }
            
            $sql .= " ORDER BY fecha_movimiento DESC LIMIT :limite";
            
            $stmt = ConexionCentral::conectar()->prepare($sql);
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();

        } catch(Exception $e) {
            error_log("Error obteniendo movimientos recientes: " . $e->getMessage());
            return [];
        }
    }

    /*=============================================
    LIMPIAR HISTÓRICO ANTIGUO (TAREA DE MANTENIMIENTO)
    =============================================*/
    static public function mdlLimpiarHistoricoAntiguo($diasAntiguedad = 365) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            // Solo administradores pueden ejecutar esta función
            if($_SESSION["perfil"] != "Administrador") {
                return false;
            }
            
            $fechaLimite = date('Y-m-d', strtotime("-{$diasAntiguedad} days"));
            
            $stmt = ConexionCentral::conectar()->prepare("
                DELETE FROM historico_transito 
                WHERE DATE(fecha_movimiento) < :fecha_limite
                AND tipo_movimiento NOT IN ('cargue', 'descarga')
            ");
            
            $stmt->bindParam(":fecha_limite", $fechaLimite, PDO::PARAM_STR);
            
            if($stmt->execute()) {
                $registrosEliminados = $stmt->rowCount();
                
                // Registrar la operación de limpieza
                error_log("Limpieza de histórico ejecutada. Registros eliminados: " . $registrosEliminados);
                
                return [
                    "success" => true,
                    "registros_eliminados" => $registrosEliminados,
                    "fecha_limite" => $fechaLimite
                ];
            } else {
                return [
                    "success" => false,
                    "error" => "Error ejecutando la limpieza"
                ];
            }

        } catch(Exception $e) {
            error_log("Error en limpieza de histórico: " . $e->getMessage());
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }

    /*=============================================
    OBTENER RESUMEN EJECUTIVO
    =============================================*/
    static public function mdlObtenerResumenEjecutivo($filtros = []) {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $parametros = [];
            $filtroFechas = "";
            
            if(!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                $filtroFechas = "WHERE DATE(fecha_movimiento) BETWEEN :fecha_desde AND :fecha_hasta";
                $parametros[':fecha_desde'] = $filtros['fecha_desde'];
                $parametros[':fecha_hasta'] = $filtros['fecha_hasta'];
            } else {
                // Por defecto, últimos 30 días
                $filtroFechas = "WHERE DATE(fecha_movimiento) >= :fecha_default";
                $parametros[':fecha_default'] = date('Y-m-d', strtotime('-30 days'));
            }
            
            // Resumen general
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_movimientos,
                    COUNT(DISTINCT codigo_producto) as productos_distintos,
                    COUNT(DISTINCT transportador_id) as transportadores_activos,
                    SUM(cantidad) as unidades_totales,
                    AVG(cantidad) as promedio_por_movimiento,
                    MIN(fecha_movimiento) as primer_movimiento,
                    MAX(fecha_movimiento) as ultimo_movimiento
                FROM historico_transito 
                {$filtroFechas}
            ");
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            $resumenGeneral = $stmt->fetch();
            
            // Distribución por tipo de movimiento
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    tipo_movimiento,
                    COUNT(*) as cantidad,
                    SUM(cantidad) as unidades,
                    ROUND((COUNT(*) * 100.0) / (
                        SELECT COUNT(*) FROM historico_transito {$filtroFechas}
                    ), 2) as porcentaje
                FROM historico_transito 
                {$filtroFechas}
                GROUP BY tipo_movimiento
                ORDER BY cantidad DESC
            ");
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            $distribucionTipos = $stmt->fetchAll();
            
            // Top 10 productos más activos
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT 
                    codigo_producto,
                    descripcion_producto,
                    COUNT(*) as movimientos,
                    SUM(cantidad) as unidades_totales
                FROM historico_transito 
                {$filtroFechas}
                GROUP BY codigo_producto, descripcion_producto
                ORDER BY movimientos DESC
                LIMIT 10
            ");
            
            foreach($parametros as $key => $valor) {
                $stmt->bindValue($key, $valor);
            }
            
            $stmt->execute();
            $topProductos = $stmt->fetchAll();
            
            // Rendimiento por transportador (solo para administradores)
            $rendimientoTransportadores = [];
            if($_SESSION["perfil"] == "Administrador") {
                $stmt = ConexionCentral::conectar()->prepare("
                    SELECT 
                        nombre_transportador,
                        COUNT(*) as movimientos,
                        SUM(cantidad) as unidades,
                        COUNT(CASE WHEN tipo_movimiento = 'descarga' THEN 1 END) as descargas,
                        COUNT(CASE WHEN tipo_movimiento = 'rechazo_descarga' THEN 1 END) as rechazos
                    FROM historico_transito 
                    {$filtroFechas}
                    GROUP BY transportador_id, nombre_transportador
                    ORDER BY movimientos DESC
                    LIMIT 10
                ");
                
                foreach($parametros as $key => $valor) {
                    $stmt->bindValue($key, $valor);
                }
                
                $stmt->execute();
                $rendimientoTransportadores = $stmt->fetchAll();
            }
            
            return [
                "resumen_general" => $resumenGeneral,
                "distribucion_tipos" => $distribucionTipos,
                "top_productos" => $topProductos,
                "rendimiento_transportadores" => $rendimientoTransportadores
            ];

        } catch(Exception $e) {
            error_log("Error obteniendo resumen ejecutivo: " . $e->getMessage());
            return [
                "resumen_general" => [],
                "distribucion_tipos" => [],
                "top_productos" => [],
                "rendimiento_transportadores" => []
            ];
        }
    }
}