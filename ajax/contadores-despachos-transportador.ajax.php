<?php
session_start();

require_once "../controladores/despachos.controlador.php";

try {
    // Obtener despachos del transportador
    $despachos = ControladorDespachos::ctrMostrarDespachosTransportador($_SESSION["id"]);
    
    $contadores = [
        "pendientes" => 0,
        "en_transito" => 0,
        "entregados" => 0,
        "cancelados" => 0
    ];
    
    foreach($despachos as $despacho) {
        switch($despacho["estado"]) {
            case "pendiente":
                $contadores["pendientes"]++;
                break;
            case "en_transito":
                if($despacho["transportador_id"] == $_SESSION["id"]) {
                    $contadores["en_transito"]++;
                }
                break;
            case "entregado":
                $contadores["entregados"]++;
                break;
            case "cancelado":
                $contadores["cancelados"]++;
                break;
        }
    }
    
    echo json_encode([
        "success" => true,
        "pendientes" => $contadores["pendientes"],
        "en_transito" => $contadores["en_transito"],
        "entregados" => $contadores["entregados"],
        "cancelados" => $contadores["cancelados"]
    ]);
    
} catch(Exception $e) {
    error_log("Error en contadores-despachos-transportador.ajax.php: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => "Error obteniendo contadores"
    ]);
}
?>
