<?php
// Ejecutar ticket térmico directamente
$codigo = $_GET["codigo"] ?? "";
$formato = $_GET["formato"] ?? "ticket";

// Limpiar output buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Ejecutar directamente el código del ticket
require_once __DIR__ . "/../../controladores/ventas.controlador.php";
require_once __DIR__ . "/../../modelos/ventas.modelo.php";
require_once __DIR__ . "/../../controladores/clientes.controlador.php";
require_once __DIR__ . "/../../modelos/clientes.modelo.php";
require_once __DIR__ . "/../../controladores/usuarios.controlador.php";
require_once __DIR__ . "/../../modelos/usuarios.modelo.php";
require_once __DIR__ . "/../../controladores/sucursales.controlador.php";
require_once __DIR__ . "/../../modelos/sucursales.modelo.php";

// Obtener datos de la venta
$itemVenta = "codigo";
$valorVenta = $codigo;
$respuestaVenta = ControladorVentas::ctrMostrarVentas($itemVenta, $valorVenta);

if (!$respuestaVenta) {
    die("Venta no encontrada");
}

$fechaVenta = substr($respuestaVenta["fecha_venta"], 0, -8);
$productos = json_decode($respuestaVenta["productos"], true);
$total = number_format($respuestaVenta["total"] ?? 0, 0, ',', '.');

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

require_once(__DIR__ . '/../../extensiones/tcpdf/tcpdf_include.php');

// Configuración para ticket térmico (80mm de ancho)
$pdf = new TCPDF('P', 'mm', array(80, 200), true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(2, 2, 2);
$pdf->SetAutoPageBreak(true, 2); // AutoPageBreak automático
$pdf->SetFont('helvetica', '', 8);

// El PDF se ajustará automáticamente al contenido
$pdf->AddPage();

// Contenido del ticket
$html = '
<div style="text-align:center; font-size:10px;">
    <strong>' . $respuestaEmpresa["nombre"] . '</strong><br>
    ' . $respuestaEmpresa["email"] . '<br>
    ' . $respuestaEmpresa["direccion"] . '<br>
    Tel: ' . $respuestaEmpresa["telefono"] . '<br>
    <hr>
    <strong>FACTURA #' . $codigo . '</strong><br>
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
    ' . substr($respuestaVenta["detalle"], 0, 50) . '
</div>
';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('ticket.pdf', 'I');
?>
