<?php

// NOTA: Ya no necesitas estas 3 líneas si tu servidor está configurado para mostrar errores, pero no hacen daño.
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

class imprimirFactura
{

	public $codigo;

	public function traerImpresionFactura()
	{
		$itemVenta = "codigo";
		$valorVenta = $this->codigo;
		$respuestaVenta = ControladorVentas::ctrMostrarVentas($itemVenta, $valorVenta);

		$fechaVenta = substr($respuestaVenta["fecha_venta"], 0, -8);
		$fechaAbono = substr($respuestaVenta["fecha_abono"], 0, -8);
		$productos = json_decode($respuestaVenta["productos"], true);
		$neto = number_format($respuestaVenta["neto"] ?? 0, 2, ',', '.');
		$impuesto = number_format($respuestaVenta["impuesto"] ?? 0, 2, ',', '.');
		$total = number_format($respuestaVenta["total"] ?? 0, 2, ',', '.');
		$detalle = substr($respuestaVenta["detalle"], 0);
		$inabono = number_format($respuestaVenta["abono"] ?? 0, 2, ',', '.');
		$ultabono = number_format($respuestaVenta["Ult_abono"] ?? 0, 2, ',', '.');
        $mpago = substr($respuestaVenta["metodo_pago"], 0);

		//TRAEMOS LA INFORMACIÓN DEL CLIENTE
		$itemCliente = "id";
		$valorCliente = $respuestaVenta["id_cliente"];
		$respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

		//TRAEMOS LA INFORMACIÓN DEL VENDEDOR
		$itemVendedor = "id";
		$valorVendedor = $respuestaVenta["id_vendedor"];
		$desdetalle = $respuestaVenta["detalle"];
		$respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);

		// Pedimos la información del segundo vendedor
		$vendAbono = ControladorUsuarios::ctrMostrarUsuarios("id", $respuestaVenta["id_vend_abono"]);

		//INFORMACION EMPRESA
		$tikempresa = "";
		$tiknumero = "";
		$tikdirecc = "";
		$tikcorreo = "NO HAY CORREO";
		
        if (isset($respuestaVendedor['empresa'])) {
            if ($respuestaVendedor['empresa'] == "Infinito") {
                $tikempresa = "ACRILICOS INFINITO";
                $tiknumero = "322 9460 339 / 211 04 93";
                $tikdirecc = "CARRERA 20B # 73-43";
                $tikcorreo = "ventas2@acrilicosinfinito.com";
            } elseif ($respuestaVendedor['empresa'] == "Lema") {
                $tikempresa = "LEMA PUBLICIDAD";
                $tiknumero = "322 9460 339";
                $tikdirecc = "CARRERA 20B # 73-43";
                $tikcorreo = "ventas2@acrilicosinfinito.com";
            } elseif ($respuestaVendedor['empresa'] == "Epico") {
                $tikempresa = "EPICO SIEMPRE MAS";
                $tiknumero = "322 7445 631 / 621 24 21";
                $tikdirecc = "CARRERA 17 #71-63";
                $tikcorreo = "creativo@epicosiempremas.com";
            } else {
                $tikempresa = "ACPLASTICOS";
    			$tiknumero = "305 3177135 / 322 744 5631";
    			$tikdirecc = "CARRERA 27 # 10-65<br> LOCAL 116 BARRIO RICAURTE <br> CENTRO COMERCIAL C-KREA";
    			$tikcorreo = "ventas1@acplasticos.com";
            }
        }
		
		// LÍNEA 94 ELIMINADA Y LÓGICA DE ABONO CORREGIDA
		$sumab_tot = ($respuestaVenta["total"] - $respuestaVenta["abono"]);
        $restabono = "";
        $tikUl = "";

		if ($respuestaVenta["abono"] > 0 && $sumab_tot > 0) {
			$restabono = "$ " . number_format($sumab_tot, 2, ',', '.');
			$tikUl = "SE DEBE:";
		}

		//CAMBIO DE ABONO
		$tikabono = "";
		$tiktipo = "";
		if ($mpago == "Abono") {
			$tikabono = "$ " . number_format($respuestaVenta["abono"], 2, ',', '.');
			$tiktipo = "ABONO";
			$totdebe = "TOTAL";
		} elseif ($mpago == "Se Debe") {
			$tikabono = "$ 0";
			$tiktipo = "ABONO";
			$totdebe = "PTE DE PAGO";
		} else {
			$tikabono = "CANCELADO";
			$tiktipo = "PAGO";
			$totdebe = "TOTAL";
		}

		require_once('tcpdf_include.php');

		$pdf = new TCPDF('P', 'mm', "h7", true, 'UTF-8', false);

		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(4, 0, 0, 0);
		$pdf->SetFooterMargin(0);
		$pdf->SetAutoPageBreak(true, 0); // Modificado para evitar saltos de página automáticos no deseados

		$pdf->AddPage('P', array(75, 280));
        $numVendedor = $respuestaVendedor['telefono'] ?? 'N/A';
		
        //---------------------------------------------------------
        // SINTAXIS DE ARRAY CORREGIDA: {$array['key']}
		$bloque1 = <<<EOF
<table style="font-size:10px; text-align:center">
	<tr>
		<td style="width:190px;">
			<div>
				<br style="font-size:9px">Fecha Venta: {$fechaVenta}
				<br style="font-size:12px; padding:2px">
				{$tikempresa}
				<br>
				<br>
				{$tikcorreo}
				<br>
				Direccion: {$tikdirecc}
				<br>
				Telefono: {$tiknumero}
				<br>
				<br>
				Orden N.{$valorVenta}
				<div style="font-size:9px"><br>					
				Cliente: {$respuestaCliente['nombre']}
				<br>
				Vendedor: {$vendAbono['nombre']}
				<br>
				Tel.Vendedor: {$numVendedor}				
				<br>
				Fecha Abono: {$fechaAbono}
				</div>
			</div>
		</td>
	</tr>
</table>
EOF;
		$pdf->writeHTML($bloque1, false, false, false, false, '');

		// ---------------------------------------------------------

		foreach ($productos as $key => $item) {

			$valorUnitario = number_format($item["precio"] ?? 0, 2, ',', '.');
			$precioTotal = number_format($item["total"] ?? 0, 2, ',', '.');
			$descripcionItem = $item["descripcion"];
			$cantidadItem = $item["cantidad"];

			$bloque2 = <<<EOF
<table style="font-size:10px;">
	<tr>
		<td style="width:160px; text-align:left; font-size:9px; padding-left:2px">
		 {$descripcionItem}
		</td>
	</tr>
	<tr>
		<td style="width:180px; text-align:right">
		$ {$valorUnitario} Und * {$cantidadItem}  = $ {$precioTotal}
		<br>
		</td>
	</tr>
</table>
EOF;
			$pdf->writeHTML($bloque2, false, false, false, false, '');
		}
		
		// ---------------------------------------------------------
		$bloque3 = <<<EOF
<table style="font-size:9px; text-align:right">
	<tr>
		<td style="width:180px; text-align:center">
		----------------------------------------------------------
		</td>
	</tr>
	<tr>
		<td style="width:90px;">
			 {$tikUl}
		</td>
		<td style="width:90px;">
			 {$restabono}
		</td>
	</tr>
	<tr>
		<td style="width:90px;">
			 {$tiktipo}:
		</td>
		<td style="width:90px;">
			 {$tikabono}
		</td>
	</tr>
	<tr>
		<td style="width:90px;">
			 {$totdebe}:
		</td>
		<td style="width:90px;">
			$ {$total}
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
                {$desdetalle}
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
EOF;

		$pdf->writeHTML($bloque3, false, false, false, false, '');

		// ---------------------------------------------------------
		ob_end_clean(); // Limpia cualquier buffer de salida antes de generar el PDF
		$pdf->Output('factura.pdf', 'I'); // 'I' para mostrar en el navegador, 'D' para descargar
	}
}

$factura = new imprimirFactura();
$factura->codigo = $_GET["codigo"];
$factura->traerImpresionFactura();