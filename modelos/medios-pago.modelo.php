<?php
/*=============================================
MODELO MEDIOS DE PAGO (MÓDULO ORIGINAL)
=============================================*/

require_once __DIR__ . "/../modelos/conexion.php";

class ModeloMediosPago {
    
    /*=============================================
    MOSTRAR MEDIOS DE PAGO
    =============================================*/
    static public function mdlMostrarMediosPago() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    id,
                    nombre,
                    activo,
                    fecha_creacion
                FROM medios_pago 
                WHERE activo = 1
                ORDER BY nombre ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlMostrarMediosPago: " . $e->getMessage());
            return [];
        }
    }
    
    /*=============================================
    CREAR MEDIO DE PAGO
    =============================================*/
    static public function mdlCrearMedioPago($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO medios_pago (nombre) 
                VALUES (:nombre)
            ");
            
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Medio de pago creado correctamente'];
            } else {
                return ['success' => false, 'error' => 'Error al crear medio de pago'];
            }
        } catch (Exception $e) {
            error_log("Error en mdlCrearMedioPago: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /*=============================================
    EDITAR MEDIO DE PAGO
    =============================================*/
    static public function mdlEditarMedioPago($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE medios_pago 
                SET nombre = :nombre
                WHERE id = :id
            ");
            
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Medio de pago actualizado correctamente'];
            } else {
                return ['success' => false, 'error' => 'Error al actualizar medio de pago'];
            }
        } catch (Exception $e) {
            error_log("Error en mdlEditarMedioPago: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /*=============================================
    ELIMINAR MEDIO DE PAGO
    =============================================*/
    static public function mdlEliminarMedioPago($id) {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE medios_pago 
                SET activo = 0
                WHERE id = :id
            ");
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Medio de pago eliminado correctamente'];
            } else {
                return ['success' => false, 'error' => 'Error al eliminar medio de pago'];
            }
        } catch (Exception $e) {
            error_log("Error en mdlEliminarMedioPago: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>