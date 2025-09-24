<?php
session_start();

// Verificar que el usuario esté logueado
if(!isset($_SESSION['id'])) {
    die('Acceso denegado');
}

// Incluir conexiones y modelos necesarios
require_once "../../../modelos/conexion.php";
require_once "../../../api-transferencias/conexion-central.php";

class ImprimirSolicitudStock {

    public $idSolicitud;

    public function generarPDF() {
        
        try {
            // ✅ OBTENER DATOS DE LA SOLICITUD DESDE BASE CENTRAL
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT * FROM solicitudes_stock 
                WHERE id = :id
            ");
            $stmt->bindParam(":id", $this->idSolicitud, PDO::PARAM_INT);
            $stmt->execute();
            
            $solicitud = $stmt->fetch();
            
            if(!$solicitud) {
                die('Solicitud no encontrada');
            }

            // ✅ OBTENER INFORMACIÓN DEL USUARIO LOCAL
            $stmtUsuario = Conexion::conectar()->prepare("
                SELECT nombre, perfil FROM usuarios 
                WHERE id = :id
            ");
            $stmtUsuario->bindParam(":id", $_SESSION['id'], PDO::PARAM_INT);
            $stmtUsuario->execute();
            $usuarioActual = $stmtUsuario->fetch();

            // ✅ PROCESAR PRODUCTOS
            $productos = json_decode($solicitud["productos_solicitados"], true);
            
            // ✅ FORMATEAR FECHAS
            $fechaSolicitud = date('d/m/Y H:i', strtotime($solicitud["fecha_solicitud"]));
            $fechaAprobacion = $solicitud["fecha_aprobacion"] ? 
                              date('d/m/Y H:i', strtotime($solicitud["fecha_aprobacion"])) : 
                              'Sin aprobar';

            // ✅ INCLUIR TCPDF
            require_once('tcpdf_include.php');

            // ✅ CREAR INSTANCIA PDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // ✅ CONFIGURACIÓN DEL PDF
            $pdf->SetCreator('Sistema de Solicitudes Stock');
            $pdf->SetAuthor($usuarioActual['nombre']);
            $pdf->SetTitle('Solicitud de Stock - ' . $solicitud["numero_solicitud"]);
            $pdf->SetSubject('Reporte de Solicitud de Stock');

            // ✅ QUITAR HEADER Y FOOTER POR DEFECTO
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // ✅ CONFIGURAR MÁRGENES
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);

            // ✅ AGREGAR PÁGINA
            $pdf->AddPage();

            // ✅ DEFINIR COLORES
            $colorPrimario = array(60, 141, 188); // Azul
            $colorSecundario = array(243, 156, 18); // Naranja
            $colorExito = array(0, 166, 90); // Verde
            $colorPeligro = array(221, 75, 57); // Rojo

            // ✅ COLOR SEGÚN ESTADO
            $colorEstado = $colorSecundario; // Por defecto amarillo
            switch($solicitud["estado"]) {
                case 'aprobado':
                    $colorEstado = $colorExito;
                    break;
                case 'cancelado':
                    $colorEstado = $colorPeligro;
                    break;
            }

            // ✅ **ENCABEZADO PRINCIPAL**
            $pdf->SetFont('helvetica', 'B', 20);
            $pdf->SetTextColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
            $pdf->Cell(0, 15, 'SOLICITUD DE STOCK', 0, 1, 'C');

            // ✅ NÚMERO DE SOLICITUD
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor($colorEstado[0], $colorEstado[1], $colorEstado[2]);
            $pdf->Cell(0, 10, $solicitud["numero_solicitud"], 0, 1, 'C');

            $pdf->Ln(5);

            // ✅ **INFORMACIÓN GENERAL**
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 8, 'INFORMACIÓN GENERAL', 0, 1, 'L');
            
            // Línea debajo del título
            $pdf->SetDrawColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
            $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->Ln(5);

            // ✅ INFORMACIÓN EN DOS COLUMNAS
            $pdf->SetFont('helvetica', '', 10);
            $y_start = $pdf->GetY();

            // COLUMNA IZQUIERDA
            $pdf->SetXY(15, $y_start);
            $html_left = '
            <table cellpadding="3">
                <tr>
                    <td style="font-weight:bold; width:60px;">Sucursal:</td>
                    <td>' . htmlspecialchars($solicitud["nombre_sucursal_solicitante"]) . '</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Usuario:</td>
                    <td>' . htmlspecialchars($solicitud["nombre_usuario_solicitante"]) . '</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Fecha:</td>
                    <td>' . $fechaSolicitud . '</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Tipo:</td>
                    <td>' . strtoupper($solicitud["tipo_solicitud"]) . '</td>
                </tr>
            </table>';

            $pdf->writeHTML($html_left, true, false, true, false, '');

            // COLUMNA DERECHA
            $pdf->SetXY(105, $y_start);
            $html_right = '
            <table cellpadding="3">
                <tr>
                    <td style="font-weight:bold; width:80px;">Estado:</td>
                    <td style="color:rgb(' . $colorEstado[0] . ',' . $colorEstado[1] . ',' . $colorEstado[2] . '); font-weight:bold;">' . strtoupper($solicitud["estado"]) . '</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Total Productos:</td>
                    <td>' . $solicitud["total_productos"] . ' productos</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Total Cantidad:</td>
                    <td>' . $solicitud["total_cantidad"] . ' unidades</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Aprobado por:</td>
                    <td>' . ($solicitud["nombre_usuario_aprobacion"] ?: 'Sin aprobar') . '</td>
                </tr>
            </table>';

            $pdf->writeHTML($html_right, true, false, true, false, '');

            $pdf->Ln(15);

            // ✅ **INFORMACIÓN DE REMISIÓN** (si aplica)
            if($solicitud["tipo_solicitud"] == 'remision' && $solicitud["codigo_remision"]) {
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(0, 8, 'INFORMACIÓN DE REMISIÓN', 0, 1, 'L');
                
                $pdf->SetDrawColor($colorSecundario[0], $colorSecundario[1], $colorSecundario[2]);
                $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
                $pdf->Ln(5);

                $pdf->SetFont('helvetica', '', 10);
                $html_remision = '
                <table cellpadding="3" border="1" cellspacing="0">
                    <tr style="background-color:#f8f9fa;">
                        <td style="font-weight:bold; width:100px;">Código Remisión:</td>
                        <td>' . htmlspecialchars($solicitud["codigo_remision"]) . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Cliente:</td>
                        <td>' . htmlspecialchars($solicitud["nombre_cliente_remision"] ?: 'No especificado') . '</td>
                    </tr>
                </table>';

                $pdf->writeHTML($html_remision, true, false, true, false, '');
                $pdf->Ln(10);
            }

            // ✅ **DETALLE ADICIONAL** (si existe)
            if($solicitud["detalle_adicional"] && trim($solicitud["detalle_adicional"]) !== '') {
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(0, 8, 'DETALLE ADICIONAL', 0, 1, 'L');
                
                $pdf->SetDrawColor($colorExito[0], $colorExito[1], $colorExito[2]);
                $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
                $pdf->Ln(5);

                $pdf->SetFont('helvetica', '', 10);
                $html_detalle = '
                <div style="background-color:#f8f9fa; padding:8px; border:1px solid #dee2e6; border-radius:4px;">
                    ' . nl2br(htmlspecialchars($solicitud["detalle_adicional"])) . '
                </div>';

                $pdf->writeHTML($html_detalle, true, false, true, false, '');
                $pdf->Ln(10);
            }

            // ✅ **PRODUCTOS SOLICITADOS**
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'PRODUCTOS SOLICITADOS', 0, 1, 'L');
            
            $pdf->SetDrawColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
            $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->Ln(5);

            // ✅ TABLA DE PRODUCTOS
            $pdf->SetFont('helvetica', '', 9);
            
            $html_productos = '
            <table cellpadding="5" cellspacing="0" border="1">
                <thead>
                    <tr style="background-color:rgb(' . $colorPrimario[0] . ',' . $colorPrimario[1] . ',' . $colorPrimario[2] . '); color:white; font-weight:bold;">
                        <td width="30" style="text-align:center;">#</td>
                        <td width="80">Código</td>
                        <td width="200">Descripción</td>
                        <td width="50" style="text-align:center;">Cant.</td>
                        <td width="120">Observaciones</td>
                    </tr>
                </thead>
                <tbody>';

            if($productos && count($productos) > 0) {
                foreach($productos as $index => $producto) {
                    $numeroItem = $index + 1;
                    $codigo = htmlspecialchars($producto['codigo'] ?? 'N/A');
                    $descripcion = htmlspecialchars($producto['descripcion'] ?? 'Sin descripción');
                    $cantidad = intval($producto['cantidad'] ?? 0);
                    $observacion = htmlspecialchars($producto['observacion'] ?? 'Sin observaciones');

                    // Color alternado para filas
                    $bgColor = ($index % 2 == 0) ? '#f8f9fa' : '#ffffff';

                    $html_productos .= '
                    <tr style="background-color:' . $bgColor . ';">
                        <td style="text-align:center; font-weight:bold;">' . $numeroItem . '</td>
                        <td style="font-family:monospace; font-size:8px;">' . $codigo . '</td>
                        <td>' . $descripcion . '</td>
                        <td style="text-align:center; font-weight:bold; color:rgb(0,166,90);">' . $cantidad . '</td>
                        <td style="font-size:8px; color:#666;">' . $observacion . '</td>
                    </tr>';
                }
            } else {
                $html_productos .= '
                <tr>
                    <td colspan="5" style="text-align:center; color:#999; font-style:italic;">No hay productos registrados</td>
                </tr>';
            }

            $html_productos .= '
                </tbody>
                <tfoot>
                    <tr style="background-color:#e9ecef; font-weight:bold;">
                        <td colspan="3" style="text-align:right;">TOTAL:</td>
                        <td style="text-align:center; color:rgb(0,166,90);">' . $solicitud["total_cantidad"] . '</td>
                        <td>' . $solicitud["total_productos"] . ' productos</td>
                    </tr>
                </tfoot>
            </table>';

            $pdf->writeHTML($html_productos, true, false, true, false, '');

            // ✅ **INFORMACIÓN DE CANCELACIÓN** (si aplica)
            if($solicitud["estado"] == 'cancelado' && $solicitud["motivo_cancelacion"]) {
                $pdf->Ln(10);
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->SetTextColor($colorPeligro[0], $colorPeligro[1], $colorPeligro[2]);
                $pdf->Cell(0, 8, 'MOTIVO DE CANCELACIÓN', 0, 1, 'L');
                
                $pdf->SetDrawColor($colorPeligro[0], $colorPeligro[1], $colorPeligro[2]);
                $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
                $pdf->Ln(5);

                $pdf->SetFont('helvetica', '', 10);
                $pdf->SetTextColor(0, 0, 0);
                $html_cancelacion = '
                <div style="background-color:#f8d7da; padding:8px; border:1px solid #f5c6cb; border-radius:4px; color:#721c24;">
                    ' . nl2br(htmlspecialchars($solicitud["motivo_cancelacion"])) . '
                </div>';

                $pdf->writeHTML($html_cancelacion, true, false, true, false, '');
            }

            // ✅ **PIE DE PÁGINA**
            $pdf->Ln(15);
            
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(128, 128, 128);
            
            $fechaGeneracion = date('d/m/Y H:i:s');
            $generadoPor = htmlspecialchars($usuarioActual['nombre']);
            
            $html_footer = '
            <hr style="color:#ddd;">
            <table width="100%">
                <tr>
                    <td style="text-align:left;">
                        <strong>Generado por:</strong> ' . $generadoPor . '<br>
                        <strong>Fecha de generación:</strong> ' . $fechaGeneracion . '
                    </td>
                    <td style="text-align:right;">
                        <strong>Sistema de Gestión de Stock</strong><br>
                        Este documento es un reporte automatizado
                    </td>
                </tr>
            </table>';

            $pdf->writeHTML($html_footer, true, false, true, false, '');

            // ✅ **GENERAR Y MOSTRAR PDF**
            ob_end_clean();
            
            $nombreArchivo = 'Solicitud_' . $solicitud["numero_solicitud"] . '.pdf';
            $pdf->Output($nombreArchivo, 'I'); // 'I' para mostrar en navegador, 'D' para descargar

        } catch(Exception $e) {
            die('Error generando PDF: ' . $e->getMessage());
        }
    }
}

// ✅ **PROCESAR SOLICITUD**
if(isset($_GET["id"]) && !empty($_GET["id"])) {
    $solicitudPDF = new ImprimirSolicitudStock();
    $solicitudPDF->idSolicitud = intval($_GET["id"]);
    $solicitudPDF->generarPDF();
} else {
    die('ID de solicitud no especificado');
}

?>