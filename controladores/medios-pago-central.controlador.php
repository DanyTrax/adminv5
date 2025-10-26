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
<<<<<<< HEAD
    OBTENER SUCURSALES PARA ESTADO
    =============================================*/
    static public function ctrObtenerSucursalesEstado() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesEstado();
=======
    OBTENER SUCURSALES PARA ASIGNACIÓN
    =============================================*/
    static public function ctrObtenerSucursalesAsignacion() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesAsignacion();
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return ['success' => true, 'data' => $sucursales];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
    OBTENER ESTADO DE MEDIOS POR SUCURSAL
    =============================================*/
    static public function ctrObtenerEstadoMediosSucursal($sucursalId) {
        try {
            $medios = ModeloMediosPagoCentral::mdlObtenerEstadoMediosSucursal($sucursalId);
=======
    OBTENER MEDIOS ASIGNADOS POR SUCURSAL
    =============================================*/
    static public function ctrObtenerMediosAsignadosSucursal($sucursalId) {
        try {
            $medios = ModeloMediosPagoCentral::mdlObtenerMediosAsignadosSucursal($sucursalId);
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return ['success' => true, 'data' => $medios];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
    OBTENER SUCURSALES DESTINO PARA ACTIVACIÓN
    =============================================*/
    static public function ctrObtenerSucursalesDestinoActivar() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesDestinoActivar();
=======
    OBTENER SUCURSALES DISPONIBLES PARA ASIGNACIÓN
    =============================================*/
    static public function ctrObtenerSucursalesDisponiblesAsignacion() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesDisponiblesAsignacion();
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return ['success' => true, 'data' => $sucursales];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
    OBTENER SUCURSALES DESTINO PARA DESACTIVACIÓN
    =============================================*/
    static public function ctrObtenerSucursalesDestinoDesactivar() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesDestinoDesactivar();
=======
    OBTENER SUCURSALES DESTINO PARA COPIA MASIVA
    =============================================*/
    static public function ctrObtenerSucursalesDestinoCopia() {
        try {
            $sucursales = ModeloMediosPagoCentral::mdlObtenerSucursalesDestinoCopia();
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return ['success' => true, 'data' => $sucursales];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
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
=======
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
    ACTIVAR MEDIOS EN SUCURSALES
    =============================================*/
    static public function ctrActivarMediosSucursales($mediosPago, $sucursales) {
=======
    ASIGNAR MEDIOS A SUCURSALES
    =============================================*/
    static public function ctrAsignarMediosSucursales($mediosPago, $sucursales) {
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
        try {
            if (empty($mediosPago) || empty($sucursales)) {
                return ['success' => false, 'error' => 'Debes seleccionar medios y sucursales'];
            }
            
<<<<<<< HEAD
            $resultado = ModeloMediosPagoCentral::mdlActivarMediosSucursales($mediosPago, $sucursales);
=======
            $resultado = ModeloMediosPagoCentral::mdlAsignarMediosSucursales($mediosPago, $sucursales);
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
    DESACTIVAR MEDIOS EN SUCURSALES
    =============================================*/
    static public function ctrDesactivarMediosSucursales($mediosPago, $sucursales) {
        try {
            if (empty($mediosPago) || empty($sucursales)) {
                return ['success' => false, 'error' => 'Debes seleccionar medios y sucursales'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlDesactivarMediosSucursales($mediosPago, $sucursales);
=======
    COPIAR MEDIOS MASIVO
    =============================================*/
    static public function ctrCopiarMediosMasivo($mediosPago, $sucursales) {
        try {
            if (empty($mediosPago) || empty($sucursales)) {
                return ['success' => false, 'error' => 'Debes seleccionar medios y sucursales destino'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlCopiarMediosMasivo($mediosPago, $sucursales);
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
<<<<<<< HEAD
    TOGGLE ESTADO DE MEDIO EN SUCURSAL
    =============================================*/
    static public function ctrToggleEstadoMedioSucursal($medioId, $sucursalId, $nuevoEstado) {
        try {
            if (empty($medioId) || empty($sucursalId)) {
                return ['success' => false, 'error' => 'ID de medio y sucursal requeridos'];
            }
            
            $resultado = ModeloMediosPagoCentral::mdlToggleEstadoMedioSucursal($medioId, $sucursalId, $nuevoEstado);
=======
    SINCRONIZAR TODOS LOS MEDIOS
    =============================================*/
    static public function ctrSincronizarTodosMedios() {
        try {
            $resultado = ModeloMediosPagoCentral::mdlSincronizarTodosMedios();
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
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
<<<<<<< HEAD
=======
    
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
>>>>>>> d87c20f53cc1d45f1dc53f773d4e1987fddd46a7
}
?>
