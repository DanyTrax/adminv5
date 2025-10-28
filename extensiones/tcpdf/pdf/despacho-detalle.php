<?php

session_start();

// Verificar que el usuario esté logueado
if(!isset($_SESSION['id'])) {
    die('Acceso denegado');
}

// Incluir conexiones y modelos necesarios
require_once __DIR__ . "/../../../modelos/conexion.php";
require_once __DIR__ . "/../../../api-transferencias/conexion-central.php";
require_once __DIR__ . "/../../../controladores/despachos.controlador.php";

// Obtener ID del despacho
$idDespacho = isset($_GET['id']) ? $_GET['id'] : null;

if (!$idDespacho || !is_numeric($idDespacho)) {
    die('ID de despacho inválido');
}

try {
    // Obtener datos del despacho
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
    
    if (!$despacho) {
        die('Despacho no encontrado');
    }
    
    // Decodificar productos
    $productos = json_decode($despacho["productos_despacho"], true);
    
    if (!$productos || !is_array($productos)) {
        die('No se pudieron obtener los productos del despacho');
    }
    
    // Obtener información del usuario actual
    $stmtUsuario = Conexion::conectar()->prepare("
        SELECT nombre, perfil FROM usuarios 
        WHERE id = :id
    ");
    $stmtUsuario->bindParam(":id", $_SESSION['id'], PDO::PARAM_INT);
    $stmtUsuario->execute();
    $usuarioActual = $stmtUsuario->fetch();
    
    // Incluir TCPDF
    require_once('tcpdf_include.php');
    
    // Crear instancia PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Configuración del PDF
    $pdf->SetCreator('Sistema de Gestión');
    $pdf->SetAuthor($usuarioActual['nombre']);
    $pdf->SetTitle('Detalle del Despacho ' . $despacho["numero_despacho"]);
    $pdf->SetSubject('Detalle del Despacho');
    
    // Quitar header y footer por defecto
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Configurar márgenes
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Agregar página
    $pdf->AddPage();
    
    // Definir colores
    $colorPrimario = array(60, 141, 188); // Azul
    $colorSecundario = array(243, 156, 18); // Naranja
    $colorExito = array(0, 166, 90); // Verde
    $colorPeligro = array(221, 75, 57); // Rojo
    
    // Color según estado
    $colorEstado = $colorSecundario; // Por defecto amarillo
    switch($despacho["estado"]) {
        case 'aceptado':
        case 'en_transito':
            $colorEstado = $colorExito;
            break;
        case 'cancelado':
            $colorEstado = $colorPeligro;
            break;
    }
    
    // ENCABEZADO PRINCIPAL
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
    $pdf->Cell(0, 15, 'DETALLE DEL DESPACHO', 0, 1, 'C');
    
    // NÚMERO DE DESPACHO
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor($colorEstado[0], $colorEstado[1], $colorEstado[2]);
    $pdf->Cell(0, 10, $despacho["numero_despacho"], 0, 1, 'C');
    
    $pdf->Ln(5);
    
    // INFORMACIÓN GENERAL
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'INFORMACIÓN GENERAL', 0, 1, 'L');
    
    // Línea debajo del título
    $pdf->SetDrawColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(3);
    
    // Tabla de información general
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(240, 240, 240);
    
    $info = [
        'Sucursal Origen' => $despacho["sucursal_origen"] ?? 'Sin especificar',
        'Creado por' => $despacho["nombre_usuario_creador"] ?? 'Sin especificar',
        'Fecha de Creación' => date('d/m/Y H:i', strtotime($despacho["fecha_creacion"])),
        'Estado' => strtoupper($despacho["estado"]),
        'Transportador' => $despacho["nombre_transportador"] ?? 'Sin asignar',
        'Total Productos' => $despacho["total_productos"] . ' productos',
        'Total Cantidad' => number_format($despacho["total_cantidad"]) . ' unidades'
    ];
    
    foreach ($info as $label => $value) {
        $pdf->Cell(60, 6, $label . ':', 1, 0, 'L', true);
        $pdf->Cell(120, 6, $value, 1, 1, 'L');
    }
    
    $pdf->Ln(5);
    
    // PRODUCTOS A DESPACHAR
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'PRODUCTOS A DESPACHAR', 0, 1, 'L');
    
    // Línea debajo del título
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(3);
    
    // Headers de la tabla
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
    $pdf->SetTextColor(255, 255, 255);
    
    $pdf->Cell(15, 8, '#', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Código', 1, 0, 'C', true);
    $pdf->Cell(80, 8, 'Descripción', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Cantidad', 1, 0, 'C', true);
    $pdf->Cell(45, 8, 'Observaciones', 1, 1, 'C', true);
    
    // Restaurar color de texto
    $pdf->SetTextColor(0, 0, 0);
    
    // Datos de productos
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetFillColor(250, 250, 250);
    
    $contador = 1;
    $totalCantidad = 0;
    
    foreach ($productos as $producto) {
        $fill = ($contador % 2 == 0);
        
        $pdf->Cell(15, 6, $contador, 1, 0, 'C', $fill);
        $pdf->Cell(30, 6, $producto['codigo'], 1, 0, 'C', $fill);
        $pdf->Cell(80, 6, substr($producto['descripcion'], 0, 50) . (strlen($producto['descripcion']) > 50 ? '...' : ''), 1, 0, 'L', $fill);
        $pdf->Cell(25, 6, number_format($producto['cantidad']), 1, 0, 'C', $fill);
        $pdf->Cell(45, 6, substr($producto['observaciones'] ?? '', 0, 30) . (strlen($producto['observaciones'] ?? '') > 30 ? '...' : ''), 1, 1, 'L', $fill);
        
        $totalCantidad += $producto['cantidad'];
        $contador++;
    }
    
    // Fila de totales
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(220, 220, 220);
    
    $pdf->Cell(125, 6, 'TOTAL:', 1, 0, 'R', true);
    $pdf->Cell(25, 6, number_format($totalCantidad), 1, 0, 'C', true);
    $pdf->Cell(45, 6, '', 1, 1, 'C', true);
    
    $pdf->Ln(5);
    
    // DETALLE ADICIONAL (si existe)
    if (!empty($despacho["detalle_adicional"])) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'DETALLE ADICIONAL', 0, 1, 'L');
        
        // Línea debajo del título
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(3);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 6, $despacho["detalle_adicional"], 1, 'L', false);
        
        $pdf->Ln(5);
    }
    
    // FOOTER
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(5);
    
    // Información del documento
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 5, 'Documento generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    $pdf->Cell(0, 5, 'Generado por: ' . $usuarioActual['nombre'] . ' (' . $usuarioActual['perfil'] . ')', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Sistema de Gestión - Despacho ' . $despacho["numero_despacho"], 0, 1, 'C');
    
    // Generar y mostrar PDF
    ob_end_clean();
    
    $nombreArchivo = 'Despacho_' . $despacho["numero_despacho"] . '_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($nombreArchivo, 'I'); // 'I' para mostrar en navegador, 'D' para descargar
    
} catch (Exception $e) {
    die('Error generando PDF: ' . $e->getMessage());
}

?>
