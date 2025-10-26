<?php
require_once __DIR__ . "/../modelos/medios-pago-central.modelo.php";

class ControladorMediosPagoCentral {
    
    static public function ctrObtenerMediosPagoCentral() {
        try {
            $medios = ModeloMediosPagoCentral::mdlObtenerMediosPagoCentral();
            return ['success' => true, 'data' => $medios];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    static public function ctrObtenerMedioPagoCentral($id) {
        try {
            $medio = ModeloMediosPagoCentral::mdlObtenerMedioPagoCentral($id);
            return ['success' => true, 'data' => $medio];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrObtenerSucursalesEstado() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesEstado();
            return ['success' => true, 'data' => $sucursales];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrObtenerEstadoMediosSucursal($sucursalId) {
        try {
            $medios = ModeloMediosPagoCentral::mdlObtenerEstadoMediosSucursal($sucursalId);
            return ['success' => true, 'data' => $medios];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrObtenerSucursalesDestinoAsignar() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesDestinoAsignar();
            return ['success' => true, 'data' => $sucursales];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrObtenerEstadoCompleto() {
        try {
            $estadoCompleto = ModeloMediosPagoCentral::mdlObtenerEstadoCompleto();
            return ['success' => true, 'data' => $estadoCompleto];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrObtenerEstadoMedioEspecifico($medioId) {
        try {
            $estado = ModeloMediosPagoCentral::mdlObtenerEstadoMedioEspecifico($medioId);
            return ['success' => true, 'data' => $estado['data'], 'medio_nombre' => $estado['medio_nombre']];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    static public function ctrCrearMedioPago($datos) {
        try {
            $resultado = ModeloMediosPagoCentral::mdlCrearMedioPago($datos);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrEditarMedioPago($datos) {
        try {
            $resultado = ModeloMediosPagoCentral::mdlEditarMedioPago($datos);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrEliminarMedioPago($id) {
        try {
            $resultado = ModeloMediosPagoCentral::mdlEliminarMedioPago($id);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrAsignarMediosSucursales($mediosPago, $sucursales) {
        try {
            if (empty($mediosPago) || empty($sucursales)) {
                return ['success' => false, 'error' => 'Debes seleccionar medios y sucursales'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlAsignarMediosSucursales($mediosPago, $sucursales);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrSincronizarSucursalesActivas() {
        try {
            $resultado = ModeloMediosPagoCentral::mdlSincronizarSucursalesActivas();
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrDesactivarTodasSucursales($mediosPago) {
        try {
            if (empty($mediosPago)) {
                return ['success' => false, 'error' => 'Debes seleccionar medios de pago'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlDesactivarTodasSucursales($mediosPago);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrEliminarTodasAsignaciones($mediosPago) {
        try {
            if (empty($mediosPago)) {
                return ['success' => false, 'error' => 'Debes seleccionar medios de pago'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlEliminarTodasAsignaciones($mediosPago);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    static public function ctrToggleEstadoMedioSucursal($medioId, $sucursalId, $nuevoEstado) {
        try {
            if (empty($medioId) || empty($sucursalId) || !isset($nuevoEstado)) {
                return ['success' => false, 'error' => 'ID de medio, sucursal y nuevo estado requeridos'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlToggleEstadoMedioSucursal($medioId, $sucursalId, $nuevoEstado);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>