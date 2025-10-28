<?php

require_once __DIR__ . "/../../tcpdf/tcpdf.php";
require_once __DIR__ . "/../../../modelos/conexion.php";
require_once __DIR__ . "/../../../api-transferencias/conexion-central.php";
require_once __DIR__ . "/../../../controladores/despachos.controlador.php";

class PDFDespachoDetalle extends TCPDF {
    
    private $despacho;
    private $productos;
    
    public function __construct($idDespacho) {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Obtener datos del despacho
        $this->despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if (!$this->despacho) {
            throw new Exception("Despacho no encontrado");
        }
        
        // Decodificar productos
        $this->productos = json_decode($this->despacho["productos_despacho"], true);
        
        // Configurar documento
        $this->SetCreator('Sistema de Gestión');
        $this->SetAuthor('Sistema de Gestión');
        $this->SetTitle('Detalle del Despacho ' . $this->despacho["numero_despacho"]);
        $this->SetSubject('Detalle del Despacho');
        
        // Configurar márgenes
        $this->SetMargins(15, 20, 15);
        $this->SetHeaderMargin(10);
        $this->SetFooterMargin(10);
        
        // Configurar auto page break
        $this->SetAutoPageBreak(TRUE, 20);
        
        // Agregar página
        $this->AddPage();
        
        // Generar contenido
        $this->generarContenido();
    }
    
    private function generarContenido() {
        
        // HEADER DEL DOCUMENTO
        $this->generarHeader();
        
        // INFORMACIÓN GENERAL
        $this->generarInformacionGeneral();
        
        // TABLA DE PRODUCTOS
        $this->generarTablaProductos();
        
        // DETALLE ADICIONAL
        if (!empty($this->despacho["detalle_adicional"])) {
            $this->generarDetalleAdicional();
        }
        
        // FOOTER
        $this->generarFooter();
    }
    
    private function generarHeader() {
        
        // Logo y título principal
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 10, 'DETALLE DEL DESPACHO', 0, 1, 'C');
        
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, 'Número: ' . $this->despacho["numero_despacho"], 0, 1, 'C');
        
        $this->Ln(5);
        
        // Línea separadora
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(5);
    }
    
    private function generarInformacionGeneral() {
        
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 8, 'INFORMACIÓN GENERAL', 0, 1, 'L');
        $this->Ln(2);
        
        // Crear tabla de información
        $this->SetFont('helvetica', '', 10);
        
        $info = [
            'Sucursal Origen' => $this->despacho["sucursal_origen"] ?? 'Sin especificar',
            'Creado por' => $this->despacho["nombre_usuario_creador"] ?? 'Sin especificar',
            'Fecha de Creación' => date('d/m/Y H:i', strtotime($this->despacho["fecha_creacion"])),
            'Estado' => strtoupper($this->despacho["estado"]),
            'Transportador' => $this->despacho["nombre_transportador"] ?? 'Sin asignar',
            'Total Productos' => $this->despacho["total_productos"] . ' productos',
            'Total Cantidad' => number_format($this->despacho["total_cantidad"]) . ' unidades'
        ];
        
        $this->SetFillColor(240, 240, 240);
        
        foreach ($info as $label => $value) {
            $this->Cell(60, 6, $label . ':', 1, 0, 'L', true);
            $this->Cell(120, 6, $value, 1, 1, 'L');
        }
        
        $this->Ln(5);
    }
    
    private function generarTablaProductos() {
        
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 8, 'PRODUCTOS A DESPACHAR', 0, 1, 'L');
        $this->Ln(2);
        
        // Headers de la tabla
        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(70, 130, 180);
        $this->SetTextColor(255, 255, 255);
        
        $this->Cell(15, 8, '#', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Código', 1, 0, 'C', true);
        $this->Cell(80, 8, 'Descripción', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Cantidad', 1, 0, 'C', true);
        $this->Cell(45, 8, 'Observaciones', 1, 1, 'C', true);
        
        // Restaurar color de texto
        $this->SetTextColor(0, 0, 0);
        
        // Datos de productos
        $this->SetFont('helvetica', '', 8);
        $this->SetFillColor(250, 250, 250);
        
        $contador = 1;
        $totalCantidad = 0;
        
        foreach ($this->productos as $producto) {
            $fill = ($contador % 2 == 0);
            
            $this->Cell(15, 6, $contador, 1, 0, 'C', $fill);
            $this->Cell(30, 6, $producto['codigo'], 1, 0, 'C', $fill);
            $this->Cell(80, 6, substr($producto['descripcion'], 0, 50) . (strlen($producto['descripcion']) > 50 ? '...' : ''), 1, 0, 'L', $fill);
            $this->Cell(25, 6, number_format($producto['cantidad']), 1, 0, 'C', $fill);
            $this->Cell(45, 6, substr($producto['observaciones'] ?? '', 0, 30) . (strlen($producto['observaciones'] ?? '') > 30 ? '...' : ''), 1, 1, 'L', $fill);
            
            $totalCantidad += $producto['cantidad'];
            $contador++;
        }
        
        // Fila de totales
        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(220, 220, 220);
        
        $this->Cell(125, 6, 'TOTAL:', 1, 0, 'R', true);
        $this->Cell(25, 6, number_format($totalCantidad), 1, 0, 'C', true);
        $this->Cell(45, 6, '', 1, 1, 'C', true);
        
        $this->Ln(5);
    }
    
    private function generarDetalleAdicional() {
        
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 8, 'DETALLE ADICIONAL', 0, 1, 'L');
        $this->Ln(2);
        
        $this->SetFont('helvetica', '', 10);
        $this->MultiCell(0, 6, $this->despacho["detalle_adicional"], 1, 'L', false);
        
        $this->Ln(5);
    }
    
    private function generarFooter() {
        
        // Línea separadora
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(5);
        
        // Información del documento
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 5, 'Documento generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $this->Cell(0, 5, 'Sistema de Gestión - Despacho ' . $this->despacho["numero_despacho"], 0, 1, 'C');
    }
    
    // Sobrescribir Header para personalizar
    public function Header() {
        // Header vacío - usamos nuestro propio header en el contenido
    }
    
    // Sobrescribir Footer para personalizar
    public function Footer() {
        // Footer vacío - usamos nuestro propio footer en el contenido
    }
}

// Función para generar PDF
function generarPDFDespacho($idDespacho) {
    
    try {
        $pdf = new PDFDespachoDetalle($idDespacho);
        
        // Nombre del archivo
        $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        $nombreArchivo = 'Despacho_' . $despacho["numero_despacho"] . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Descargar el PDF
        $pdf->Output($nombreArchivo, 'D');
        
    } catch (Exception $e) {
        echo "Error al generar PDF: " . $e->getMessage();
    }
}

// Si se llama directamente
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    generarPDFDespacho($_GET['id']);
}

?>
