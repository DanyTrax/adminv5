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

class TicketTermico
{
    public $codigo;

    public function generarTicket()
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

        // Configuración para ticket térmico (80mm de ancho)
        $pdf = new TCPDF('P', 'mm', array(80, 200), true, 'UTF-8', false);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(2, 2, 2);
        $pdf->SetAutoPageBreak(true, 2); // AutoPageBreak con 2mm de margen
        
        // Fuente más pequeña para ticket
        $pdf->SetFont('helvetica', '', 8);

        // Calcular altura dinámica basada en contenido
        $alturaBase = 50; // Altura base
        $alturaPorProducto = 8; // Altura por producto
        $alturaCalculada = $alturaBase + (count($productos) * $alturaPorProducto);
        $alturaFinal = max(100, min($alturaCalculada, 400));

        $pdf->AddPage('P', array(80, $alturaFinal));

        // Contenido del ticket
        $html = '
        <div style="text-align:center; font-size:10px;">
            <strong>' . $tikempresa . '</strong><br>
            ' . $tikcorreo . '<br>
            ' . $tikdirecc . '<br>
            Tel: ' . $tiknumero . '<br>
            <hr>
            <strong>FACTURA #' . $valorVenta . '</strong><br>
            Fecha: ' . $fechaVenta . '<br>
            Cliente: ' . $respuestaCliente['nombre'] . '<br>
            Vendedor: ' . $respuestaVendedor['nombre'] . '<br>
            <hr>
        </div>
        ';

        // Productos
        foreach ($productos as $producto) {
            $precio = number_format($producto["precio"] ?? 0, 0, ',', '.');
            $totalProducto = number_format($producto["total"] ?? 0, 0, ',', '.');
            $cantidad = $producto["cantidad"];
            $descripcion = substr($producto["descripcion"], 0, 30); // Limitar descripción
            
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
            <strong>TOTAL: $' . $total . '</strong><br>
        </div>
        <hr>
        <div style="text-align:center; font-size:7px;">
            Gracias por su compra<br>
            ' . $detalle . '
        </div>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');
        
        ob_end_clean();
        $pdf->Output('ticket.pdf', 'I');
    }
}

$ticket = new TicketTermico();
$ticket->codigo = $_GET["codigo"];
$ticket->generarTicket();
