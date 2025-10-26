<?php
/*=============================================
CONTROLADOR MEDIOS DE PAGO (MÓDULO ORIGINAL)
=============================================*/

require_once __DIR__ . "/../modelos/medios-pago.modelo.php";

class ControladorMediosPago {
    
    /*=============================================
    MOSTRAR MEDIOS DE PAGO
    =============================================*/
    static public function ctrMostrarMediosPago() {
        try {
            $medios = ModeloMediosPago::mdlMostrarMediosPago();
            return $medios;
        } catch (Exception $e) {
            error_log("Error en ctrMostrarMediosPago: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    CREAR MEDIO DE PAGO
    =============================================*/
    static public function ctrCrearMedioPago($datos) {
        try {
            $resultado = ModeloMediosPago::mdlCrearMedioPago($datos);
            return $resultado;
        } catch (Exception $e) {
            error_log("Error en ctrCrearMedioPago: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    EDITAR MEDIO DE PAGO
    =============================================*/
    static public function ctrEditarMedioPago($datos) {
        try {
            $resultado = ModeloMediosPago::mdlEditarMedioPago($datos);
            return $resultado;
        } catch (Exception $e) {
            error_log("Error en ctrEditarMedioPago: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /*=============================================
    ELIMINAR MEDIO DE PAGO
    =============================================*/
    static public function ctrEliminarMedioPago($id) {
        try {
            $resultado = ModeloMediosPago::mdlEliminarMedioPago($id);
            return $resultado;
        } catch (Exception $e) {
            error_log("Error en ctrEliminarMedioPago: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>