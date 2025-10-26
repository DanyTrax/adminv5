<?php
/*=============================================
CONTROLADOR MEDIOS DE PAGO CENTRAL
=============================================*/

require_once __DIR__ . "/../modelos/medios-pago-central.modelo.php";

class ControladorMediosPagoCentral {
    
    /*=============================================
    OBTENER MEDIOS DE PAGO CENTRALES
    =============================================*/
    static public function ctrObtenerMediosPagoCentral() {
        try {
            $medios = ModeloMediosPagoCentral::mdlObtenerMediosPagoCentral();
            return ['success' => true, 'data' => $medios];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES PARA ESTADO
    =============================================*/
    static public function ctrObtenerSucursalesEstado() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesEstado();
            return ['success' => true, 'data' => $sucursales];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    OBTENER ESTADO DE MEDIOS POR SUCURSAL
    =============================================*/
    static public function ctrObtenerEstadoMediosSucursal($sucursalId) {
        try {
            $medios = ModeloMediosPagoCentral::mdlObtenerEstadoMediosSucursal($sucursalId);
            return ['success' => true, 'data' => $medios];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES DESTINO PARA ACTIVACIÓN
    =============================================*/
    static public function ctrObtenerSucursalesDestinoActivar() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesDestinoActivar();
            return ['success' => true, 'data' => $sucursales];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES DESTINO PARA DESACTIVACIÓN
    =============================================*/
    static public function ctrObtenerSucursalesDestinoDesactivar() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesDestinoDesactivar();
            return ['success' => true, 'data' => $sucursales];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    OBTENER ESTADO COMPLETO
    =============================================*/
    static public function ctrObtenerEstadoCompleto() {
        try {
            $estado = ModeloMediosPagoCentral::mdlObtenerEstadoCompleto();
            return ['success' => true, 'data' => $estado];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    OBTENER ESTADO DE MEDIO ESPECÍFICO
    =============================================*/
    static public function ctrObtenerEstadoMedio($medioId) {
        try {
            $estado = ModeloMediosPagoCentral::mdlObtenerEstadoMedio($medioId);
            return ['success' => true, 'medio' => $estado['medio'], 'sucursales' => $estado['sucursales']];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    CREAR MEDIO DE PAGO
    =============================================*/
    static public function ctrCrearMedioPago($datos) {
        try {
            // Validar datos
            if (empty($datos['codigo']) || empty($datos['nombre']) || empty($datos['tipo'])) {
                return ['success' => false, 'error' => 'Todos los campos son obligatorios'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlCrearMedioPago($datos);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    ACTIVAR MEDIOS EN SUCURSALES
    =============================================*/
    static public function ctrActivarMediosSucursales($mediosPago, $sucursales) {
        try {
            if (empty($mediosPago) || empty($sucursales)) {
                return ['success' => false, 'error' => 'Debes seleccionar medios y sucursales'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlActivarMediosSucursales($mediosPago, $sucursales);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    DESACTIVAR MEDIOS EN SUCURSALES
    =============================================*/
    static public function ctrDesactivarMediosSucursales($mediosPago, $sucursales) {
        try {
            if (empty($mediosPago) || empty($sucursales)) {
                return ['success' => false, 'error' => 'Debes seleccionar medios y sucursales'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlDesactivarMediosSucursales($mediosPago, $sucursales);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    TOGGLE ESTADO DE MEDIO EN SUCURSAL
    =============================================*/
    static public function ctrToggleEstadoMedioSucursal($medioId, $sucursalId, $nuevoEstado) {
        try {
            if (empty($medioId) || empty($sucursalId)) {
                return ['success' => false, 'error' => 'ID de medio y sucursal requeridos'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlToggleEstadoMedioSucursal($medioId, $sucursalId, $nuevoEstado);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    ELIMINAR MEDIO DE PAGO
    =============================================*/
    static public function ctrEliminarMedioPago($id) {
        try {
            if (empty($id)) {
                return ['success' => false, 'error' => 'ID de medio de pago requerido'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlEliminarMedioPago($id);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
