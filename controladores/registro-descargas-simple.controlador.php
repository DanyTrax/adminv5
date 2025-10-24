<?php
/*=============================================
CONTROLADOR REGISTRO DE DESCARGAS SIMPLE - ULTRA SIMPLE
=============================================*/

class ControladorRegistroDescargasSimple {
    public function ctrRegistrarDescarga() {
        echo json_encode(["success" => true, "message" => "Descarga registrada"]);
    }
    
    public function ctrObtenerRegistro($filtros = []) {
        return [];
    }
    
    public function ctrObtenerEstadisticas($filtros = []) {
        return [
            "total_descargas" => 0,
            "total_cantidad" => 0,
            "productos_unicos" => 0,
            "usuarios_unicos" => 0,
            "sucursales_unicas" => 0
        ];
    }
}
?>