<?php

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que la sesión esté iniciada correctamente
if (!isset($_SESSION['perfil'])) {
    echo json_encode(["success" => false, "error" => "Sesión no iniciada"]);
    exit;
}

require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../api-transferencias/conexion-central.php";
require_once __DIR__ . "/../controladores/despachos.controlador.php";

/*=============================================
EXPORTAR DESPACHO A PDF
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "exportar_pdf" && isset($_POST["idDespacho"])) {
    
    try {
        $idDespacho = $_POST["idDespacho"];
        
        // Verificar que el despacho existe
        $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if (!$despacho) {
            echo json_encode([
                "success" => false,
                "error" => "Despacho no encontrado"
            ]);
            exit;
        }
        
        // Generar URL para descarga
        $urlPDF = "extensiones/tcpdf/pdf/despacho-detalle.php?id=" . $idDespacho;
        
        echo json_encode([
            "success" => true,
            "url" => $urlPDF,
            "nombreArchivo" => "Despacho_" . $despacho["numero_despacho"] . "_" . date('Y-m-d_H-i-s') . ".pdf",
            "message" => "PDF generado correctamente"
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "error" => "Error al generar PDF: " . $e->getMessage()
        ]);
    }
}

/*=============================================
EXPORTAR DESPACHO A EXCEL
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "exportar_excel" && isset($_POST["idDespacho"])) {
    
    try {
        $idDespacho = $_POST["idDespacho"];
        
        // Verificar que el despacho existe
        $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if (!$despacho) {
            echo json_encode([
                "success" => false,
                "error" => "Despacho no encontrado"
            ]);
            exit;
        }
        
        // Generar URL para descarga
        $urlExcel = "extensiones/excel/despacho-detalle.php?id=" . $idDespacho;
        
        echo json_encode([
            "success" => true,
            "url" => $urlExcel,
            "nombreArchivo" => "Despacho_" . $despacho["numero_despacho"] . "_" . date('Y-m-d_H-i-s') . ".xls",
            "message" => "Excel generado correctamente"
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "error" => "Error al generar Excel: " . $e->getMessage()
        ]);
    }
}

?>
