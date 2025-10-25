<?php

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";
require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";
require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";
require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";
require_once "../../../controladores/sucursales.controlador.php";
require_once "../../../modelos/sucursales.modelo.php";

class FacturaInteligente
{
    public $codigo;
    public $formato; // 'factura' o 'ticket'

    public function generarDocumento()
    {
        $itemVenta = "codigo";
        $valorVenta = $this->codigo;
        $respuestaVenta = ControladorVentas::ctrMostrarVentas($itemVenta, $valorVenta);

        $fechaVenta = substr($respuestaVenta["fecha_venta"], 0, -8);
        $productos = json_decode($respuestaVenta["productos"], true);
        $total = number_format($respuestaVenta["total"] ?? 0, 0, ',', '.');
        $detalle = substr($respuestaVenta["detalle"], 0);

        // Información del cliente
        $itemCliente = "id";
        $valorCliente = $respuestaVenta["id_cliente"];
        $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

        // Información del vendedor
        $itemVendedor = "id";
        $valorVendedor = $respuestaVenta["id_vendedor"];
        $respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);

        // Información de la empresa
        $itemEmpresa = "id";
        $valorEmpresa = 1;
        $respuestaEmpresa = ControladorSucursales::ctrMostrarSucursales($itemEmpresa, $valorEmpresa);

        $tikempresa = $respuestaEmpresa["nombre"];
        $tikcorreo = $respuestaEmpresa["email"];
        $tikdirecc = $respuestaEmpresa["direccion"];
        $tiknumero = $respuestaEmpresa["telefono"];

        require_once('tcpdf_include.php');

        // Configuración según formato
        if ($this->formato === 'ticket') {
            // Configuración para ticket térmico
            $ancho = 80;
            $margen = 2;
            $fuente = 8;
            $alturaBase = 50;
            $alturaPorProducto = 8;
        } else {
            // Configuración para factura normal
            $ancho = 75;
            $margen = 4;
            $fuente = 10;
            $alturaBase = 80;
            $alturaPorProducto = 12;
        }

        $pdf = new TCPDF('P', 'mm', array($ancho, 200), true, 'UTF-8', false);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins($margen, $margen, $margen);
        $pdf->SetAutoPageBreak(true, $margen);
        $pdf->SetFont('helvetica', '', $fuente);

        // Calcular altura dinámica
        $alturaCalculada = $alturaBase + (count($productos) * $alturaPorProducto);
        $alturaFinal = max(100, min($alturaCalculada, 400));

        $pdf->AddPage('P', array($ancho, $alturaFinal));

        // Generar contenido según formato
        if ($this->formato === 'ticket') {
            $this->generarContenidoTicket($pdf, $respuestaVenta, $respuestaCliente, $respuestaVendedor, $respuestaEmpresa, $productos);
        } else {
            $this->generarContenidoFactura($pdf, $respuestaVenta, $respuestaCliente, $respuestaVendedor, $respuestaEmpresa, $productos);
        }
        
        ob_end_clean();
        $nombreArchivo = $this->formato === 'ticket' ? 'ticket.pdf' : 'factura.pdf';
        $pdf->Output($nombreArchivo, 'I');
    }

    private function generarContenidoTicket($pdf, $venta, $cliente, $vendedor, $empresa, $productos)
    {
        $html = '
        <div style="text-align:center; font-size:10px;">
            <strong>' . $empresa["nombre"] . '</strong><br>
            ' . $empresa["email"] . '<br>
            ' . $empresa["direccion"] . '<br>
            Tel: ' . $empresa["telefono"] . '<br>
            <hr>
            <strong>FACTURA #' . $this->codigo . '</strong><br>
            Fecha: ' . substr($venta["fecha_venta"], 0, -8) . '<br>
            Cliente: ' . $cliente['nombre'] . '<br>
            Vendedor: ' . $vendedor['nombre'] . '<br>
            <hr>
        </div>
        ';

        foreach ($productos as $producto) {
            $precio = number_format($producto["precio"] ?? 0, 0, ',', '.');
            $totalProducto = number_format($producto["total"] ?? 0, 0, ',', '.');
            $cantidad = $producto["cantidad"];
            $descripcion = substr($producto["descripcion"], 0, 30);
            
            $html .= '
            <div style="font-size:8px;">
                ' . $descripcion . '<br>
                $' . $precio . ' x ' . $cantidad . ' = $' . $totalProducto . '<br>
            </div>
            ';
        }

        $html .= '
        <hr>
        <div style="text-align:right; font-size:10px;">
            <strong>TOTAL: $' . number_format($venta["total"] ?? 0, 0, ',', '.') . '</strong><br>
        </div>
        <hr>
        <div style="text-align:center; font-size:7px;">
            Gracias por su compra
        </div>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');
    }

    private function generarContenidoFactura($pdf, $venta, $cliente, $vendedor, $empresa, $productos)
    {
        // Usar el contenido de la factura original pero con altura automática
        $html = '
        <table style="font-size:10px; text-align:center">
            <tr>
                <td style="width:190px;">
                    <div>
                        <br style="font-size:9px">Fecha Venta: ' . substr($venta["fecha_venta"], 0, -8) . '
                        <br style="font-size:12px; padding:2px">
                        ' . $empresa["nombre"] . '
                        <br>
                        <br>
                        ' . $empresa["email"] . '
                        <br>
                        Direccion: ' . $empresa["direccion"] . '
                        <br>
                        Telefono: ' . $empresa["telefono"] . '
                        <br>
                        <br>
                        Orden N.' . $this->codigo . '
                        <div style="font-size:9px"><br>					
                        Cliente: ' . $cliente['nombre'] . '
                        <br>
                        Vendedor: ' . $vendedor['nombre'] . '
                        <br>
                        Tel.Vendedor: ' . $vendedor['telefono'] . '				
                        <br>
                        Fecha Abono: ' . substr($venta["fecha_abono"], 0, -8) . '
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        ';

        foreach ($productos as $producto) {
            $valorUnitario = number_format($producto["precio"] ?? 0, 0, ',', '.');
            $precioTotal = number_format($producto["total"] ?? 0, 0, ',', '.');
            $descripcionItem = $producto["descripcion"];
            $cantidadItem = $producto["cantidad"];

            $html .= '
            <table style="font-size:10px;">
                <tr>
                    <td style="width:160px; text-align:left; font-size:9px; padding-left:2px">
                     ' . $descripcionItem . '
                    </td>
                </tr>
                <tr>
                    <td style="width:180px; text-align:right">
                    $ ' . $valorUnitario . ' Und * ' . $cantidadItem . '  = $ ' . $precioTotal . '
                    <br>
                    </td>
                </tr>
            </table>
            ';
        }

        $html .= '
        <table style="font-size:9px; text-align:right">
            <tr>
                <td style="width:180px; text-align:center">
                ----------------------------------------------------------
                </td>
            </tr>
            <tr>
                <td style="width:90px;">
                     TOTAL:
                </td>
                <td style="width:90px;">
                     $ ' . number_format($venta["total"] ?? 0, 0, ',', '.') . '
                </td>
            </tr>
        </table>
        <table style="font-size:8px; text-align:center">
            <tr>
                <td style="width:190px;">
                    <div>
                        <br style="font-size:8px">NOTA DETALLE<br>
                        ---------------------------------------------
                        <br>
                        ' . $venta["detalle"] . '
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width:190px;">
                    <br><br>
                    Despues de 30 dias no nos hacemos responsables por trabajos sin reclamar, los trabajos sin cancelar su totalidad no seran entregados. Se debe presentar este formato para la entrega de trabajos. Este documento no es valido para efectos contables.
                </td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');
    }
}

$documento = new FacturaInteligente();
$documento->codigo = $_GET["codigo"];
$documento->formato = $_GET["formato"] ?? 'factura'; // 'factura' o 'ticket'
$documento->generarDocumento();
