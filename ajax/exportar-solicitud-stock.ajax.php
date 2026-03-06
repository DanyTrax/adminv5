<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['perfil'])) {
    echo json_encode(["success" => false, "error" => "Sesión no iniciada"]);
    exit;
}

require_once __DIR__ . "/../api-transferencias/conexion-central.php";

/*=============================================
EXPORTAR SOLICITUD A PDF
=============================================*/
if (isset($_POST["accion"]) && $_POST["accion"] == "exportar_pdf") {
    
    try {
        $conexion = ConexionCentral::conectar();
        $solicitud = null;
        
        // Buscar por id (numérico) o por numero_solicitud (ej: SOL000057)
        if (!empty($_POST["idSolicitud"])) {
            $idSolicitud = intval($_POST["idSolicitud"]);
            if ($idSolicitud > 0) {
                $stmt = $conexion->prepare("SELECT id, numero_solicitud FROM solicitudes_stock WHERE id = :id");
                $stmt->bindParam(":id", $idSolicitud, PDO::PARAM_INT);
                $stmt->execute();
                $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        if (!$solicitud && !empty($_POST["numeroSolicitud"])) {
            $numeroSolicitud = trim($_POST["numeroSolicitud"]);
            $stmt = $conexion->prepare("SELECT id, numero_solicitud FROM solicitudes_stock WHERE numero_solicitud = :numero");
            $stmt->bindParam(":numero", $numeroSolicitud, PDO::PARAM_STR);
            $stmt->execute();
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if (!$solicitud) {
            echo json_encode(["success" => false, "error" => "Solicitud no encontrada"]);
            exit;
        }
        
        $idSolicitud = (int) $solicitud["id"];
        $urlPDF = "extensiones/tcpdf/pdf/solicitud-stock.php?id=" . $idSolicitud;
        $nombreArchivo = "Solicitud_" . $solicitud["numero_solicitud"] . "_" . date('Y-m-d_H-i-s') . ".pdf";
        
        echo json_encode([
            "success" => true,
            "url" => $urlPDF,
            "nombreArchivo" => $nombreArchivo,
            "message" => "PDF generado correctamente"
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "error" => "Error al generar PDF: " . $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode(["success" => false, "error" => "Acción no válida"]);
