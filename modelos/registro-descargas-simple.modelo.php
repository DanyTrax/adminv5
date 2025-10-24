<?php
/*=============================================
MODELO REGISTRO DE DESCARGAS SIMPLE - ULTRA SIMPLE
=============================================*/

class ModeloRegistroDescargasSimple {
    static public function mdlRegistrarDescarga($datos) {
        return "ok";
    }
    
    static public function mdlObtenerTodasDescargas($filtros = []) {
        return [];
    }
    
    static public function mdlObtenerEstadisticasDescargas($filtros = []) {
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