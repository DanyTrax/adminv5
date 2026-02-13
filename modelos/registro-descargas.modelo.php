<?php

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

class ModeloRegistroDescargas {

    /*=============================================
    BORRAR TODOS LOS REGISTROS DE DESCARGAS
    =============================================*/
    static public function mdlBorrarTodosRegistrosDescargas() {
        try {
            $conexionCentral = ConexionCentral::conectar();
            
            if (!$conexionCentral) {
                return "error: No hay conexión a la base de datos central";
            }

            $stmt = $conexionCentral->prepare("DELETE FROM registro_descargas_stock_transito");
            $stmt->execute();
            
            return "ok";
            
        } catch (Exception $e) {
            error_log("Error en mdlBorrarTodosRegistrosDescargas: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }

    /*=============================================
    BORRAR UN REGISTRO DE DESCARGA POR ID
    =============================================*/
    static public function mdlBorrarRegistroDescarga($id) {
        try {
            $conexionCentral = ConexionCentral::conectar();
            
            if (!$conexionCentral) {
                return "error: No hay conexión a la base de datos central";
            }

            $stmt = $conexionCentral->prepare("DELETE FROM registro_descargas_stock_transito WHERE id = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return ($stmt->rowCount() > 0) ? "ok" : "error: Registro no encontrado";
            
        } catch (Exception $e) {
            error_log("Error en mdlBorrarRegistroDescarga: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }
}

?>
