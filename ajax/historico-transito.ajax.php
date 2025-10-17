<?php

session_start();

require_once "../controladores/historico-transito.controlador.php";
require_once "../modelos/historico-transito.modelo.php";

class AjaxHistoricoTransito {

    /*=============================================
    OBTENER TRAZABILIDAD COMPLETA DE UN PRODUCTO
    =============================================*/
    public function ajaxObtenerTrazabilidadCompleta() {
        
        if(isset($_POST["codigoProducto"]) && isset($_POST["obtenerTrazabilidad"])) {
            
            $codigoProducto = $_POST["codigoProducto"];
            $transportadorId = $_POST["transportadorId"] ?? null;
            
            try {
                $trazabilidad = ControladorHistoricoTransito::ctrObtenerTrazabilidadProducto($codigoProducto, $transportadorId);
                
                echo json_encode([
                    "success" => true,
                    "trazabilidad" => $trazabilidad,
                    "codigo_producto" => $codigoProducto,
                    "total_movimientos" => count($trazabilidad)
                ]);
                
            } catch(Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error obteniendo trazabilidad: " . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    OBTENER DETALLES DE UN MOVIMIENTO ESPECÍFICO
    =============================================*/
    public function ajaxObtenerDetalleMovimiento() {
        
        if(isset($_POST["idMovimiento"])) {
            
            $idMovimiento = intval($_POST["idMovimiento"]);
            
            try {
                $movimiento = ControladorHistoricoTransito::ctrMostrarHistoricoTransito("historico_transito", "id", $idMovimiento);
                
                if($movimiento) {
                    echo json_encode([
                        "success" => true,
                        "movimiento" => $movimiento
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "error" => "Movimiento no encontrado"
                    ]);
                }
                
            } catch(Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error obteniendo detalle: " . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    OBTENER RESUMEN EJECUTIVO
    =============================================*/
    public function ajaxObtenerResumenEjecutivo() {
        
        if(isset($_POST["obtenerResumen"])) {
            
            $filtros = [];
            
            if(isset($_POST["fecha_desde"])) $filtros["fecha_desde"] = $_POST["fecha_desde"];
            if(isset($_POST["fecha_hasta"])) $filtros["fecha_hasta"] = $_POST["fecha_hasta"];
            
            try {
                require_once "../modelos/historico-transito.modelo.php";
                $resumen = ModeloHistoricoTransito::mdlObtenerResumenEjecutivo($filtros);
                
                echo json_encode([
                    "success" => true,
                    "resumen" => $resumen
                ]);
                
            } catch(Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error obteniendo resumen: " . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    LIMPIAR HISTÓRICO ANTIGUO (SOLO ADMIN)
    =============================================*/
    public function ajaxLimpiarHistoricoAntiguo() {
        
        if(isset($_POST["limpiarHistorico"]) && $_SESSION["perfil"] == "Administrador") {
            
            $diasAntiguedad = intval($_POST["diasAntiguedad"]) ?: 365;
            
            try {
                require_once "../modelos/historico-transito.modelo.php";
                $resultado = ModeloHistoricoTransito::mdlLimpiarHistoricoAntiguo($diasAntiguedad);
                
                echo json_encode($resultado);
                
            } catch(Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error en limpieza: " . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    OBTENER TOP PRODUCTOS MÁS MOVIDOS
    =============================================*/
    public function ajaxObtenerTopProductos() {
        
        if(isset($_POST["obtenerTopProductos"])) {
            
            $limite = intval($_POST["limite"]) ?: 10;
            $filtros = [];
            
            if(isset($_POST["fecha_desde"])) $filtros["fecha_desde"] = $_POST["fecha_desde"];
            if(isset($_POST["fecha_hasta"])) $filtros["fecha_hasta"] = $_POST["fecha_hasta"];
            
            try {
                require_once "../modelos/historico-transito.modelo.php";
                $topProductos = ModeloHistoricoTransito::mdlObtenerTopProductos($limite, $filtros);
                
                echo json_encode([
                    "success" => true,
                    "productos" => $topProductos,
                    "limite" => $limite
                ]);
                
            } catch(Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error obteniendo top productos: " . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    OBTENER EFICIENCIA POR TRANSPORTADOR
    =============================================*/
    public function ajaxObtenerEficienciaTransportadores() {
        
        if(isset($_POST["obtenerEficiencia"]) && $_SESSION["perfil"] == "Administrador") {
            
            $filtros = [];
            if(isset($_POST["fecha_desde"])) $filtros["fecha_desde"] = $_POST["fecha_desde"];
            if(isset($_POST["fecha_hasta"])) $filtros["fecha_hasta"] = $_POST["fecha_hasta"];
            
            try {
                require_once "../modelos/historico-transito.modelo.php";
                $eficiencia = ModeloHistoricoTransito::mdlObtenerEficienciaPorTransportador($filtros);
                
                echo json_encode([
                    "success" => true,
                    "eficiencia" => $eficiencia
                ]);
                
            } catch(Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error obteniendo eficiencia: " . $e->getMessage()
                ]);
            }
        }
    }
}

/*=============================================
PROCESAR PETICIONES AJAX
=============================================*/
if(isset($_POST["codigoProducto"]) && isset($_POST["obtenerTrazabilidad"])) {
    $trazabilidad = new AjaxHistoricoTransito();
    $trazabilidad->ajaxObtenerTrazabilidadCompleta();
}

if(isset($_POST["idMovimiento"])) {
    $detalle = new AjaxHistoricoTransito();
    $detalle->ajaxObtenerDetalleMovimiento();
}

if(isset($_POST["obtenerResumen"])) {
    $resumen = new AjaxHistoricoTransito();
    $resumen->ajaxObtenerResumenEjecutivo();
}

if(isset($_POST["limpiarHistorico"])) {
    $limpiar = new AjaxHistoricoTransito();
    $limpiar->ajaxLimpiarHistoricoAntiguo();
}

if(isset($_POST["obtenerTopProductos"])) {
    $topProductos = new AjaxHistoricoTransito();
    $topProductos->ajaxObtenerTopProductos();
}

if(isset($_POST["obtenerEficiencia"])) {
    $eficiencia = new AjaxHistoricoTransito();
    $eficiencia->ajaxObtenerEficienciaTransportadores();
}

?>