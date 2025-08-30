<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once '../../../vendor/autoload.php';

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

		//TRAEMOS LA INFORMACI脫N DE LA VENTA



		$itemVenta = "codigo";
		$valorVenta = $this->codigo;
		// $vendedor = "id_empresa";
		// $venta= $this -> id_empresa;


		$respuestaVenta = ControladorVentas::ctrMostrarVentas($itemVenta, $valorVenta);

		$fechaVenta = substr($respuestaVenta["fecha_venta"], 0, -8);
		$fechaAbono = substr($respuestaVenta["fecha_abono"], 0, -8);
		$productos = json_decode($respuestaVenta["productos"], true);
		$neto = number_format($respuestaVenta["neto"]);
		$impuesto = number_format($respuestaVenta["impuesto"]);
		$total = number_format($respuestaVenta["total"]);
		$detalle = substr($respuestaVenta["detalle"], 0);
		$inabono = number_format($respuestaVenta["abono"]);
		$ultabono = number_format($respuestaVenta["Ult_abono"]);
		$mpago = substr($respuestaVenta["metodo_pago"], 0);

		//TRAEMOS LA INFORMACI脫N DEL CLIENTE

		$itemCliente = "id";
		$valorCliente = $respuestaVenta["id_cliente"];

		$respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

		//TRAEMOS LA INFORMACI脫N DEL VENDEDOR
		$itemVendedor = "id";
		$valorVendedor = $respuestaVenta["id_vendedor"];
		$desdetalle = $respuestaVenta["detalle"];

		$respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);

		// Pedimos la informaci贸n del segundo vendedor
		$vendAbono = ControladorUsuarios::ctrMostrarUsuarios("id", $respuestaVenta["id_vend_abono"]);


		//INFORMACION EMPRESA
		$tikempresa = 0;
		$tiknumero = 0;
		$tikdirecc = 0;
		$tikcorreo = "NO HAY CORREO";
		//EMPRESA REQUERIMIENTO COMPLETO
		if ($respuestaVendedor['empresa'] == "Infinito") {
			$tikempresa = "ACRILICOS INFINITO";
			$tiknumero = "322 9460 339/ 211 04 93";
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
			$tikempresa = "NO NAME";
			$tiknumero = 0;
			$tikdirecc = 0;
		}
		//Abono final
		$subtotal = $total - $inabono;
		$sumab_tot = (($respuestaVenta["total"]) - ($respuestaVenta["Ult_abono"]));
		if ($respuestaVenta["Ult_abono"] == "0") {
			$restabono = "";
			$tikUl = "";
		} elseif ($sumab_tot == "1") {
			$uno = "1" + ($respuestaVenta["Ult_abono"]);
			$restabono = number_format($uno);
			$tikUl = "ULTIMO ABONO:";
		} else {
			//	$restabono = $subtotal;
			//	$tikUl = "SE DEBE:";
		}


		//CAMBIO DE ABONO
		$tikabono = "";
		$tiktipo = "";
		if ($mpago == "Abono") {
			$tikabono = "$ $inabono";
			$tiktipo = "ABONO";
			$totdebe = "TOTAL";
		} elseif ($mpago == "Se Debe") {
			$tikabono = "$ 0";
			$tiktipo = "ABONO";
			$totdebe = "TOTAL SE DEBE";
		} else {
			$tikabono = "CANCELADO";
			$tiktipo = "PAGO";
			$totdebe = "TOTAL";
		}
		//REQUERIMOS LA CLASE TCPDF

		require_once('tcpdf_include.php');

		$pdf = new TCPDF('P', 'mm', "h7", true, 'UTF-8', false);

		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(4, 0, 0, 0);
		$pdf->SetFooterMargin(0);
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

		$pdf->AddPage('P', array(75, 280));
		$numVendedor = $respuestaVendedor[telefono];
		//---------------------------------------------------------
		$bloque1 = <<<EOF
<table style="font-size:10px; text-align:center">
	<tr>
		<td style="width:190px;">
			<div>
				<br style="font-size:9px">Fecha Venta: $fechaVenta
				<br style="font-size:12px; padding:2px">
				$tikempresa
				<br>
				<br>
				$tikcorreo
				<br>
				Direccion: $tikdirecc
				<br>
				Telefono: $tiknumero<!--//$respuestaVendedor[telefono]-->
				<br>
				<br>
				Orden N.$valorVenta
				<div style="font-size:9px"><br>					
				Cliente: $respuestaCliente[nombre]
				<br>
				Vendedor: $vendAbono[nombre]
				<br>
				Tel.Vendedor: $numVendedor				
				<br>
				Fecha Abono: $fechaAbono
				</div>
			</div>
		</td>
	</tr>
</table>
EOF;
		$pdf->writeHTML($bloque1, false, false, false, false, '');

		// ---------------------------------------------------------


		foreach ($productos as $key => $item) {

			$valorUnitario = number_format($item["precio"]);

			$precioTotal = number_format($item["total"]);


			$bloque2 = <<<EOF

<table style="font-size:10px;">

	<tr>
	
		<td style="width:160px; text-align:left; font-size:9px; padding-left:2px">
		 $item[descripcion] 
		</td>

	</tr>

	<tr>
	
		<td style="width:180px; text-align:right">
		$ $valorUnitario Und * $item[cantidad]  = $ $precioTotal
		<br>
		</td>

	</tr>

</table>

EOF;

			$pdf->writeHTML($bloque2, false, false, false, false, '');
		}
		//foreach ($ventas as $key => $item) {
		//  $Traerdetalle = substr($respuestaVenta["detalle"],0,-8);
		//}

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
			 $tikUl 
		</td>

		<td style="width:90px;">
			 $restabono
		</td>

	</tr>
	<tr>
	
		<td style="width:90px;">
			 $tiktipo: 
		</td>

		<td style="width:90px;">
			 $tikabono
		</td>

	</tr>

	<tr>
	
		<td style="width:90px;">
			 $totdebe: 
		</td>

		<td style="width:90px;">
			$ $total
		</td>
        
	</tr>

<table style="font-size:8px; text-align:center">
	<tr>
		<td style="width:190px;">
			<div>
				<br style="font-size:8px">NOTA DETALLE<br>
                ---------------------------------------------
                <br>
                $desdetalle
            </div>
        </td>
    </tr>

	<tr>
	
		<td style="width:190px;">
			<br>		
			<br  style="font-size:7px">
			Despues de 30 dias no nos hacemos responsables por trabajos sin reclamar, los trabajos sin cancelar su totalidad no seran entregados. Se debe presentar este formato para la entrega de trabajos. Este documento no es valido para efectos contables.
		</td>

	</tr>

</table >


EOF;

		$pdf->writeHTML($bloque3, false, false, false, false, '');

		// ---------------------------------------------------------
		//SALIDA DEL ARCHIVO 

		$pdf->Output(__DIR__ . '/factura.pdf', 'F');
		ob_end_clean();

		//Create an instance; passing `true` enables exceptions
		$mail = new PHPMailer(true);

		try {
			//Server settings
			$mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
			$mail->isSMTP();                                            //Send using SMTP
			$mail->Host       = 'mail.epicosiempremas.com';                     //Set the SMTP server to send through
			$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
			$mail->Username   = 'no-reply@epicosiempremas.com';                     //SMTP username
			$mail->Password   = 'Ba4fU6S2pSfV';                               //SMTP password
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
			$mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

			//Recipients
			$mail->setFrom('no-reply@epicosiempremas.com', 'ACRILICOS INFINITO');
			$mail->addAddress('jonathan.cruz89@gmail.com', $respuestaCliente['nombre']);     //Add a recipient
			// $mail->addReplyTo('info@example.com', 'Information');
			$mail->addCC('Vidaultra777@gmail.com', $respuestaCliente['nombre']);
			// $mail->addBCC('bcc@example.com');

			//Attachments
			$mail->addAttachment(__DIR__ . '/factura.pdf', 'Factura.pdf');         //Add attachments
			// $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

			//Content
			$mail->isHTML(true);                                  //Set email format to HTML
			$mail->Subject = 'Factura de venta #' . $valorVenta;
			$mail->Body    = "
					<div style='width: 70%; margin: auto;'>
						<p>
							Factura de Venta <br>
							ACRILICOS INFINITO
						</p>
						<p>
							Hola <strong>{$respuestaCliente['nombre']}</strong> nos complace informarle que su factura ha sido enviada con éxito. Le agradecemos por confiar en nuestros servicios.
						</p>
						<p>
							Si necesita alguna aclaración sobre su factura, no dude en ponerse en contacto con nosotros. Estaremos encantados de ayudarle.
					
							Gracias de nuevo por su apoyo continuo.
						</p>
					</div>
			";
			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			$mail->send();
			echo 'Message has been sent';
		} catch (Exception $e) {
			// echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}
}

$factura = new imprimirFactura();
$factura->codigo = $_GET["codigo"];
$factura->traerImpresionFactura();
