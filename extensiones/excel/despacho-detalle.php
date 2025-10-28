<?php

require_once __DIR__ . "/../../../modelos/conexion.php";
require_once __DIR__ . "/../../../api-transferencias/conexion-central.php";
require_once __DIR__ . "/../../../controladores/despachos.controlador.php";

class ExcelDespachoDetalle {
    
    private $despacho;
    private $productos;
    
    public function __construct($idDespacho) {
        // Obtener datos del despacho
        $this->despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if (!$this->despacho) {
            throw new Exception("Despacho no encontrado");
        }
        
        // Decodificar productos
        $this->productos = json_decode($this->despacho["productos_despacho"], true);
    }
    
    public function generarExcel() {
        
        // Configurar headers para Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Despacho_' . $this->despacho["numero_despacho"] . '_' . date('Y-m-d_H-i-s') . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Iniciar output
        echo '<html>';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<style>';
        echo 'table { border-collapse: collapse; width: 100%; }';
        echo 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
        echo 'th { background-color: #4682B4; color: white; font-weight: bold; }';
        echo '.header { background-color: #E6E6FA; font-weight: bold; }';
        echo '.total { background-color: #DCDCDC; font-weight: bold; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        
        // TÍTULO PRINCIPAL
        echo '<h2 style="text-align: center; color: #4682B4;">DETALLE DEL DESPACHO</h2>';
        echo '<h3 style="text-align: center;">Número: ' . htmlspecialchars($this->despacho["numero_despacho"]) . '</h3>';
        echo '<br>';
        
        // INFORMACIÓN GENERAL
        echo '<table>';
        echo '<tr><th colspan="4" style="text-align: center; background-color: #4682B4; color: white;">INFORMACIÓN GENERAL</th></tr>';
        
        $info = [
            'Sucursal Origen' => $this->despacho["sucursal_origen"] ?? 'Sin especificar',
            'Creado por' => $this->despacho["nombre_usuario_creador"] ?? 'Sin especificar',
            'Fecha de Creación' => date('d/m/Y H:i', strtotime($this->despacho["fecha_creacion"])),
            'Estado' => strtoupper($this->despacho["estado"]),
            'Transportador' => $this->despacho["nombre_transportador"] ?? 'Sin asignar',
            'Total Productos' => $this->despacho["total_productos"] . ' productos',
            'Total Cantidad' => number_format($this->despacho["total_cantidad"]) . ' unidades'
        ];
        
        foreach ($info as $label => $value) {
            echo '<tr>';
            echo '<td class="header" style="width: 30%;">' . htmlspecialchars($label) . '</td>';
            echo '<td style="width: 70%;">' . htmlspecialchars($value) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        echo '<br><br>';
        
        // TABLA DE PRODUCTOS
        echo '<table>';
        echo '<tr><th colspan="5" style="text-align: center; background-color: #4682B4; color: white;">PRODUCTOS A DESPACHAR</th></tr>';
        
        // Headers de productos
        echo '<tr>';
        echo '<th style="width: 8%;">#</th>';
        echo '<th style="width: 15%;">Código</th>';
        echo '<th style="width: 40%;">Descripción del Producto</th>';
        echo '<th style="width: 12%;">Cantidad</th>';
        echo '<th style="width: 25%;">Observaciones</th>';
        echo '</tr>';
        
        // Datos de productos
        $contador = 1;
        $totalCantidad = 0;
        
        foreach ($this->productos as $producto) {
            echo '<tr>';
            echo '<td style="text-align: center;">' . $contador . '</td>';
            echo '<td>' . htmlspecialchars($producto['codigo']) . '</td>';
            echo '<td>' . htmlspecialchars($producto['descripcion']) . '</td>';
            echo '<td style="text-align: center;">' . number_format($producto['cantidad']) . '</td>';
            echo '<td>' . htmlspecialchars($producto['observaciones'] ?? '') . '</td>';
            echo '</tr>';
            
            $totalCantidad += $producto['cantidad'];
            $contador++;
        }
        
        // Fila de totales
        echo '<tr class="total">';
        echo '<td colspan="3" style="text-align: right; font-weight: bold;">TOTAL:</td>';
        echo '<td style="text-align: center; font-weight: bold;">' . number_format($totalCantidad) . '</td>';
        echo '<td></td>';
        echo '</tr>';
        
        echo '</table>';
        
        // DETALLE ADICIONAL (si existe)
        if (!empty($this->despacho["detalle_adicional"])) {
            echo '<br><br>';
            echo '<table>';
            echo '<tr><th style="text-align: center; background-color: #4682B4; color: white;">DETALLE ADICIONAL</th></tr>';
            echo '<tr>';
            echo '<td>' . nl2br(htmlspecialchars($this->despacho["detalle_adicional"])) . '</td>';
            echo '</tr>';
            echo '</table>';
        }
        
        // INFORMACIÓN DEL DOCUMENTO
        echo '<br><br>';
        echo '<table>';
        echo '<tr>';
        echo '<td style="text-align: center; font-size: 10px; color: #666;">';
        echo 'Documento generado el: ' . date('d/m/Y H:i:s') . '<br>';
        echo 'Sistema de Gestión - Despacho ' . htmlspecialchars($this->despacho["numero_despacho"]);
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        
        echo '</body>';
        echo '</html>';
    }
}

// Función para generar Excel
function generarExcelDespacho($idDespacho) {
    
    try {
        $excel = new ExcelDespachoDetalle($idDespacho);
        $excel->generarExcel();
        
    } catch (Exception $e) {
        echo "Error al generar Excel: " . $e->getMessage();
    }
}

// Si se llama directamente
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    generarExcelDespacho($_GET['id']);
}

?>
