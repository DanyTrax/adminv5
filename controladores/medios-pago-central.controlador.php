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
    OBTENER SUCURSALES PARA ASIGNACIÓN
    =============================================*/
    static public function ctrObtenerSucursalesAsignacion() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesAsignacion();
            return ['success' => true, 'data' => $sucursales];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    OBTENER MEDIOS ASIGNADOS POR SUCURSAL
    =============================================*/
    static public function ctrObtenerMediosAsignadosSucursal($sucursalId) {
        try {
            $medios = ModeloMediosPagoCentral::mdlObtenerMediosAsignadosSucursal($sucursalId);
            return ['success' => true, 'data' => $medios];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES DISPONIBLES PARA ASIGNACIÓN
    =============================================*/
    static public function ctrObtenerSucursalesDisponiblesAsignacion() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesDisponiblesAsignacion();
            return ['success' => true, 'data' => $sucursales];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    OBTENER SUCURSALES DESTINO PARA COPIA MASIVA
    =============================================*/
    static public function ctrObtenerSucursalesDestinoCopia() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesDestinoCopia();
            return ['success' => true, 'data' => $sucursales];
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
    ASIGNAR MEDIOS A SUCURSALES
    =============================================*/
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
    
    /*=============================================
    COPIAR MEDIOS MASIVO
    =============================================*/
    static public function ctrCopiarMediosMasivo($mediosPago, $sucursales) {
        try {
            if (empty($mediosPago) || empty($sucursales)) {
                return ['success' => false, 'error' => 'Debes seleccionar medios y sucursales destino'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlCopiarMediosMasivo($mediosPago, $sucursales);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    SINCRONIZAR TODOS LOS MEDIOS
    =============================================*/
    static public function ctrSincronizarTodosMedios() {
        try {
            $resultado = ModeloMediosPagoCentral::mdlSincronizarTodosMedios();
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
    
    /*=============================================
    DESASIGNAR MEDIO DE SUCURSAL
    =============================================*/
    static public function ctrDesasignarMedioSucursal($medioId, $sucursalId) {
        try {
            if (empty($medioId) || empty($sucursalId)) {
                return ['success' => false, 'error' => 'ID de medio y sucursal requeridos'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlDesasignarMedioSucursal($medioId, $sucursalId);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
