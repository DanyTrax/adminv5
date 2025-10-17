<?php

session_start();

require_once "../api-transferencias/conexion-central.php";

class TablaHistoricoTransito {

    /*=============================================
    MOSTRAR LA TABLA DE HISTÓRICO DE MOVIMIENTOS
    =============================================*/
    public function mostrarTablaHistoricoTransito() {

        try {
            // Obtener filtros de la petición POST
            $filtros = [];
            
            if(isset($_POST['fecha_desde']) && !empty($_POST['fecha_desde'])) {
                $filtros['fecha_desde'] = $_POST['fecha_desde'];
            }
            if(isset($_POST['fecha_hasta']) && !empty($_POST['fecha_hasta'])) {
                $filtros['fecha_hasta'] = $_POST['fecha_hasta'];
            }
            if(isset($_POST['tipo_movimiento']) && !empty($_POST['tipo_movimiento'])) {
                $filtros['tipo_movimiento'] = $_POST['tipo_movimiento'];
            }
            if(isset($_POST['transportador']) && !empty($_POST['transportador'])) {
                $filtros['transportador'] = $_POST['transportador'];
            }
            if(isset($_POST['codigo_producto']) && !empty($_POST['codigo_producto'])) {
                $filtros['codigo_producto'] = $_POST['codigo_producto'];
            }
            if(isset($_POST['sucursal']) && !empty($_POST['sucursal'])) {
                $filtros['sucursal'] = $_POST['sucursal'];
            }
            if(isset($_POST['usuario']) && !empty($_POST['usuario'])) {
                $filtros['usuario'] = $_POST['usuario'];
            }

            // Si no hay filtros, mostrar últimos 7 días por defecto
            if(empty($filtros)) {
                $filtros['fecha_desde'] = date('Y-m-d', strtotime('-7 days'));
                $filtros['fecha_hasta'] = date('Y-m-d');
            }

            $historico = $this->obtenerHistoricoConFiltros($filtros);

            if(count($historico) == 0) {
                echo '{"data": [], "recordsTotal": 0, "recordsFiltered": 0}';
                return;
            }

            $datosJson = '{
                "data": [';

            foreach($historico as $key => $value) {

                /*=============================================
                FORMATEAR FECHA Y HORA
                =============================================*/
                $fechaHora = $this->formatearFechaHora($value["fecha_movimiento"]);

                /*=============================================
                TIPO DE MOVIMIENTO CON BADGE
                =============================================*/
                $tipoMovimiento = $this->formatearTipoMovimiento($value["tipo_movimiento"]);

                /*=============================================
                CANTIDAD CON FORMATO
                =============================================*/
                $cantidad = '<span class="text-bold text-blue">' . number_format($value["cantidad"]) . '</span>';

                /*=============================================
                ORIGEN Y DESTINO
                =============================================*/
                $origenDestino = $this->formatearOrigenDestino($value);

                /*=============================================
                USUARIO CON INFORMACIÓN ADICIONAL
                =============================================*/
                $usuario = $this->formatearUsuario($value);

                /*=============================================
                BOTONES DE ACCIONES
                =============================================*/
                $botones = $this->generarBotonesAccion($value);

                /*=============================================
                DESCRIPCIÓN TRUNCADA
                =============================================*/
                $descripcion = $this->truncarTexto($value["descripcion_producto"], 35);

                /*=============================================
                CONSTRUIR FILA JSON
                =============================================*/
                $datosJson .= '[
                    "' . ($key + 1) . '",
                    "' . $fechaHora . '",
                    "' . $tipoMovimiento . '",
                    "<code>' . $value["codigo_producto"] . '</code>",
                    "' . addslashes($descripcion) . '",
                    "' . $cantidad . '",
                    "' . addslashes($value["nombre_transportador"]) . '",
                    "' . $origenDestino . '",
                    "' . $usuario . '",
                    "' . $botones . '"
                ],';
            }

            $datosJson = substr($datosJson, 0, -1);
            $datosJson .= '], 
                "recordsTotal": ' . count($historico) . ',
                "recordsFiltered": ' . count($historico) . '
            }';

            echo $datosJson;

        } catch(Exception $e) {
            echo '{"data": [], "error": "' . addslashes($e->getMessage()) . '"}';
        }
    }

    /*=============================================
    OBTENER HISTÓRICO CON FILTROS
    =============================================*/
    private function obtenerHistoricoConFiltros($filtros) {
        
        $sql = "SELECT * FROM historico_transito WHERE 1=1";
        $parametros = [];
        
        // Filtro por perfil de usuario
        if($_SESSION["perfil"] == "Transportador") {
            $sql .= " AND transportador_id = :transportador_sesion";
            $parametros[':transportador_sesion'] = $_SESSION["id"];
        }
        
        // Aplicar filtros
        foreach($filtros as $campo => $valor) {
            switch($campo) {
                case 'fecha_desde':
                    $sql .= " AND DATE(fecha_movimiento) >= :fecha_desde";
                    $parametros[':fecha_desde'] = $valor;
                    break;
                case 'fecha_hasta':
                    $sql .= " AND DATE(fecha_movimiento) <= :fecha_hasta";
                    $parametros[':fecha_hasta'] = $valor;
                    break;
                case 'tipo_movimiento':
                    $sql .= " AND tipo_movimiento = :tipo_movimiento";
                    $parametros[':tipo_movimiento'] = $valor;
                    break;
                case 'transportador':
                    $sql .= " AND transportador_id = :transportador_filtro";
                    $parametros[':transportador_filtro'] = $valor;
                    break;
                case 'codigo_producto':
                    $sql .= " AND codigo_producto LIKE :codigo_producto";
                    $parametros[':codigo_producto'] = '%' . $valor . '%';
                    break;
                case 'sucursal':
                    $sql .= " AND (sucursal_origen LIKE :sucursal OR sucursal_destino LIKE :sucursal)";
                    $parametros[':sucursal'] = '%' . $valor . '%';
                    break;
                case 'usuario':
                    $sql .= " AND (nombre_usuario_origen LIKE :usuario OR nombre_usuario_destino LIKE :usuario)";
                    $parametros[':usuario'] = '%' . $valor . '%';
                    break;
            }
        }
        
        $sql .= " ORDER BY fecha_movimiento DESC LIMIT 1000";
        
        $stmt = ConexionCentral::conectar()->prepare($sql);
        
        foreach($parametros as $key => $valor) {
            $stmt->bindValue($key, $valor);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /*=============================================
    FORMATEAR FECHA Y HORA
    =============================================*/
    private function formatearFechaHora($fechaMovimiento) {
        
        $fecha = date('d/m/Y', strtotime($fechaMovimiento));
        $hora = date('H:i:s', strtotime($fechaMovimiento));
        
        return '<div class="text-center">
                    <div class="text-bold text-primary">' . $fecha . '</div>
                    <small class="text-muted">' . $hora . '</small>
                </div>';
    }

    /*=============================================
    FORMATEAR TIPO DE MOVIMIENTO
    =============================================*/
    private function formatearTipoMovimiento($tipo) {
        
        $configuraciones = [
            'cargue' => ['texto' => 'CARGUE', 'clase' => 'badge-cargue'],
            'descarga' => ['texto' => 'DESCARGA', 'clase' => 'badge-descarga'],
            'solicitud_descarga' => ['texto' => 'SOLICITUD', 'clase' => 'badge-solicitud'],
            'rechazo_descarga' => ['texto' => 'RECHAZO', 'clase' => 'badge-rechazo'],
            'descarga_forzada' => ['texto' => 'FORZADA', 'clase' => 'badge-forzada'],
            'transferencia' => ['texto' => 'TRANSFER', 'clase' => 'badge-transferencia']
        ];
        
        $config = $configuraciones[$tipo] ?? ['texto' => strtoupper($tipo), 'clase' => 'badge-secondary'];
        
        return '<span class="badge-tipo-movimiento ' . $config['clase'] . '">' . $config['texto'] . '</span>';
    }

    /*=============================================
    FORMATEAR ORIGEN Y DESTINO
    =============================================*/
    private function formatearOrigenDestino($movimiento) {
        
        $origen = $movimiento["sucursal_origen"] ?? '';
        $destino = $movimiento["sucursal_destino"] ?? '';
        
        if(empty($origen) && empty($destino)) {
            return '<span class="text-muted">-</span>';
        }
        
        $html = '<div class="text-center">';
        
        if(!empty($origen)) {
            $html .= '<div class="text-primary"><i class="fa fa-map-marker"></i> ' . $this->truncarTexto($origen, 15) . '</div>';
        }
        
        if(!empty($origen) && !empty($destino)) {
            $html .= '<div class="text-muted"><i class="fa fa-arrow-down"></i></div>';
        }
        
        if(!empty($destino)) {
            $html .= '<div class="text-success"><i class="fa fa-map-marker"></i> ' . $this->truncarTexto($destino, 15) . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /*=============================================
    FORMATEAR USUARIO
    =============================================*/
    private function formatearUsuario($movimiento) {
        
        $usuarioOrigen = $movimiento["nombre_usuario_origen"] ?? '';
        $usuarioDestino = $movimiento["nombre_usuario_destino"] ?? '';
        
        if(!empty($usuarioOrigen) && !empty($usuarioDestino) && $usuarioOrigen != $usuarioDestino) {
            return '<div class="text-center">
                        <div class="text-primary"><small>' . $this->truncarTexto($usuarioOrigen, 12) . '</small></div>
                        <div class="text-muted"><i class="fa fa-arrow-right"></i></div>
                        <div class="text-success"><small>' . $this->truncarTexto($usuarioDestino, 12) . '</small></div>
                    </div>';
        } elseif(!empty($usuarioOrigen)) {
            return '<div class="text-center">
                        <div class="text-primary">' . $this->truncarTexto($usuarioOrigen, 15) . '</div>
                    </div>';
        } elseif(!empty($usuarioDestino)) {
            return '<div class="text-center">
                        <div class="text-success">' . $this->truncarTexto($usuarioDestino, 15) . '</div>
                    </div>';
        }
        
        return '<span class="text-muted">-</span>';
    }

    /*=============================================
    GENERAR BOTONES DE ACCIÓN
    =============================================*/
    private function generarBotonesAccion($movimiento) {
        
        $botones = '<div class="btn-group">';
        
        // BOTÓN VER DETALLES - TODOS LOS PERFILES
        $botones .= '<button class="btn btn-info btn-xs btnVerDetallesMovimiento" 
                            data-toggle="tooltip" 
                            title="Ver detalles completos" 
                            data-movimiento=\'' . json_encode($movimiento) . '\'>
                        <i class="fa fa-eye"></i>
                    </button>';
        
        // BOTÓN VER TRAZABILIDAD DEL PRODUCTO
        $botones .= '<button class="btn btn-success btn-xs btnVerTrazabilidadProducto" 
                            data-toggle="tooltip" 
                            title="Ver trazabilidad del producto" 
                            codigoProducto="' . $movimiento["codigo_producto"] . '"
                            transportadorId="' . $movimiento["transportador_id"] . '">
                        <i class="fa fa-route"></i>
                    </button>';
        
        // BOTÓN GENERAR REPORTE INDIVIDUAL (SOLO ADMINISTRADOR)
        if($_SESSION["perfil"] == "Administrador") {
            $botones .= '<button class="btn btn-warning btn-xs btnReporteIndividual" 
                                data-toggle="tooltip" 
                                title="Generar reporte de este movimiento" 
                                idMovimiento="' . $movimiento["id"] . '">
                            <i class="fa fa-file-pdf-o"></i>
                        </button>';
        }
        
        $botones .= '</div>';
        
        return $botones;
    }

    /*=============================================
    TRUNCAR TEXTO
    =============================================*/
    private function truncarTexto($texto, $limite) {
        if(strlen($texto) > $limite) {
            return substr($texto, 0, $limite) . '...';
        }
        return $texto;
    }

    /*=============================================
    OBTENER ESTADÍSTICAS PARA EL DASHBOARD
    =============================================*/
    public function obtenerEstadisticasHistorico() {
        
        try {
            $filtros = [];
            
            // Obtener filtros de la petición
            if(isset($_POST['fecha_desde'])) $filtros['fecha_desde'] = $_POST['fecha_desde'];
            if(isset($_POST['fecha_hasta'])) $filtros['fecha_hasta'] = $_POST['fecha_hasta'];
            if(isset($_POST['transportador'])) $filtros['transportador'] = $_POST['transportador'];
            if(isset($_POST['tipo_movimiento'])) $filtros['tipo_movimiento'] = $_POST['tipo_movimiento'];
            
            require_once "../controladores/historico-transito.controlador.php";
            $estadisticas = ControladorHistoricoTransito::ctrObtenerEstadisticasHistorico($filtros);
            
            echo json_encode($estadisticas);

        } catch(Exception $e) {
            echo json_encode([
                "error" => $e->getMessage(),
                "estadisticas_generales" => [
                    "total_movimientos" => 0,
                    "descargas_exitosas" => 0,
                    "productos_cargados" => 0,
                    "solicitudes_rechazadas" => 0
                ],
                "tipos_movimiento" => [],
                "transportadores" => []
            ]);
        }
    }

    /*=============================================
    OBTENER DATOS PARA GRÁFICOS
    =============================================*/
    public function obtenerDatosGraficos() {
        
        try {
            $filtros = [];
            
            if(isset($_POST['fecha_desde'])) $filtros['fecha_desde'] = $_POST['fecha_desde'];
            if(isset($_POST['fecha_hasta'])) $filtros['fecha_hasta'] = $_POST['fecha_hasta'];
            
            require_once "../controladores/historico-transito.controlador.php";
            $datosLineaTiempo = ControladorHistoricoTransito::ctrObtenerDatosLineaTiempo($filtros);
            
            echo json_encode($datosLineaTiempo);

        } catch(Exception $e) {
            echo json_encode([
                "error" => $e->getMessage(),
                "fechas" => [],
                "series" => []
            ]);
        }
    }
}

/*=============================================
PROCESAR PETICIONES
=============================================*/
if(isset($_POST["tabla"]) && $_POST["tabla"] == "historico-transito") {
    $activarHistorico = new TablaHistoricoTransito();
    $activarHistorico->mostrarTablaHistoricoTransito();
}

if(isset($_POST["estadisticas"]) && $_POST["estadisticas"] == "historico") {
    $estadisticas = new TablaHistoricoTransito();
    $estadisticas->obtenerEstadisticasHistorico();
}

if(isset($_POST["graficos"]) && $_POST["graficos"] == "historico") {
    $graficos = new TablaHistoricoTransito();
    $graficos->obtenerDatosGraficos();
}

?>