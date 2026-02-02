<?php

// NOTA: Ya no necesitas estas 3 l��neas si tu servidor est�� configurado para mostrar errores, pero no hacen da�0�9o.
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

require_once "../../../controladores/sucursales.controlador.php";
require_once "../../../modelos/sucursales.modelo.php";

// Clase para medir líneas reales durante la generación
class MedidorLineas {
	private $lineas = 0;
	private $marcadores = [];
	
	public function marcarInicio($nombre) {
		$this->marcadores[$nombre] = $this->lineas;
	}
	
	public function marcarFin($nombre) {
		if (isset($this->marcadores[$nombre])) {
			$lineasSeccion = $this->lineas - $this->marcadores[$nombre];
			error_log("Sección '{$nombre}': {$lineasSeccion} líneas");
			return $lineasSeccion;
		}
		return 0;
	}
	
	public function agregarLinea($contenido = '') {
		// Contar líneas de forma más precisa
		$brCount = substr_count($contenido, '<br');
		$brStyleCount = substr_count($contenido, '<br style');
		$trCount = substr_count($contenido, '<tr>');
		$tdCount = substr_count($contenido, '<td');
		
		// Calcular líneas basado en estructura HTML
		$lineasEnContenido = max(1, $brCount + $trCount + ($tdCount > 0 ? 1 : 0));
		
		// Log para debugging
		error_log("Contenido: BR={$brCount}, TR={$trCount}, TD={$tdCount} → Líneas={$lineasEnContenido}");
		
		$this->lineas += $lineasEnContenido;
	}
	
	public function agregarLineaFija($cantidad) {
		// Agregar líneas fijas (espacios, márgenes, etc.)
		$this->lineas += $cantidad;
		error_log("Líneas fijas agregadas: {$cantidad}");
	}
	
	public function getTotalLineas() {
		return $this->lineas;
	}
}

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
		$neto = number_format($respuestaVenta["neto"] ?? 0, 0, ',', '.');
		$impuesto = number_format($respuestaVenta["impuesto"] ?? 0, 0, ',', '.');
		$total = number_format($respuestaVenta["total"] ?? 0, 0, ',', '.');
		$detalle = substr($respuestaVenta["detalle"], 0);
		$inabono = number_format($respuestaVenta["abono"] ?? 0, 0, ',', '.');
		$ultabono = number_format($respuestaVenta["Ult_abono"] ?? 0, 0, ',', '.');
        $mpago = substr($respuestaVenta["metodo_pago"], 0);

		//TRAEMOS LA INFORMACI�0�7N DEL CLIENTE
		$itemCliente = "id";
		$valorCliente = $respuestaVenta["id_cliente"];
		$respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

		//TRAEMOS LA INFORMACI�0�7N DEL VENDEDOR
		$itemVendedor = "id";
		$valorVendedor = $respuestaVenta["id_vendedor"];
		$desdetalle = $respuestaVenta["detalle"];
		$respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);

		// Pedimos la informaci��n del segundo vendedor
		$vendAbono = ControladorUsuarios::ctrMostrarUsuarios("id", $respuestaVenta["id_vend_abono"]);

		//INFORMACION EMPRESA - Obtener desde tabla sucursal_local (configuración local)
		$tikempresa = "";
		$tiknumero = "";
		$tikdirecc = "";
		$tikcorreo = "NO HAY CORREO";
		
		// Obtener información de la sucursal desde la tabla local sucursal_local
		try {
			$sucursalLocal = ModeloSucursales::mdlObtenerConfiguracionLocal();
			
			if ($sucursalLocal && !empty($sucursalLocal)) {
				$tikempresa = !empty($sucursalLocal['nombre']) ? strtoupper($sucursalLocal['nombre']) : "SUCURSAL NO DEFINIDA";
				$tiknumero = !empty($sucursalLocal['telefono']) ? $sucursalLocal['telefono'] : "NO DISPONIBLE";
				$tikdirecc = !empty($sucursalLocal['direccion']) ? $sucursalLocal['direccion'] : "NO DISPONIBLE";
				$tikcorreo = !empty($sucursalLocal['email']) ? $sucursalLocal['email'] : "NO HAY CORREO";
			} else {
				// Fallback a datos por defecto si no se encuentra la configuración
				$tikempresa = "SUCURSAL NO CONFIGURADA";
				$tiknumero = "NO DISPONIBLE";
				$tikdirecc = "NO DISPONIBLE";
				$tikcorreo = "NO HAY CORREO";
			}
		} catch (Exception $e) {
			// En caso de error, usar datos por defecto
			error_log("Error obteniendo datos de sucursal local: " . $e->getMessage());
			$tikempresa = "SUCURSAL NO CONFIGURADA";
			$tiknumero = "NO DISPONIBLE";
			$tikdirecc = "NO DISPONIBLE";
			$tikcorreo = "NO HAY CORREO";
		}
		
		// L�0�1NEA 94 ELIMINADA Y L�0�7GICA DE ABONO CORREGIDA
		$sumab_tot = ($respuestaVenta["total"] - $respuestaVenta["abono"]);
        $restabono = "";
        $tikUl = "";

		if ($respuestaVenta["abono"] > 0 && $sumab_tot > 0) {
			$restabono = "$ " . number_format($sumab_tot, 0, ',', '.');
			$tikUl = "SE DEBE:";
		}

		//CAMBIO DE ABONO
		$tikabono = "";
		$tiktipo = "";
		if ($mpago == "Abono") {
			$tikabono = "$ " . number_format($respuestaVenta["abono"], 0, ',', '.');
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
		$pdf->SetAutoPageBreak(false, 0); // Modificado para evitar saltos de p��gina autom��ticos no deseados

		$medidor = new MedidorLineas();

		// Primero generamos el contenido para medir las líneas reales
		$medidor->marcarInicio('total');
        $numVendedor = $respuestaVendedor['telefono'] ?? 'N/A';
		
        //---------------------------------------------------------
        // SINTAXIS DE ARRAY CORREGIDA: {$array['key']}
		
		// MARCADOR: Inicio del encabezado
		$medidor->marcarInicio('encabezado');
		
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
		
		// MARCADOR: Fin del encabezado e inicio de productos
		$medidor->agregarLinea($bloque1);
		$lineasEncabezado = $medidor->marcarFin('encabezado');
		
		// Agregar líneas fijas adicionales para el header (espacios, márgenes)
		$medidor->agregarLineaFija(3); // 3 líneas fijas para header
		
		$medidor->marcarInicio('productos');

		// ---------------------------------------------------------

		foreach ($productos as $key => $item) {

			$valorUnitario = number_format($item["precio"] ?? 0, 0, ',', '.');
			$precioTotal = number_format($item["total"] ?? 0, 0, ',', '.');
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
			
			// MARCADOR: Agregar líneas de este producto
			$medidor->agregarLinea($bloque2);
		}
		
		// MARCADOR: Fin de productos e inicio de resumen
		$lineasProductos = $medidor->marcarFin('productos');
		$medidor->marcarInicio('resumen');
		
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

		// MARCADOR: Fin del resumen e inicio de la nota
		$medidor->agregarLinea($bloque3);
		$lineasResumen = $medidor->marcarFin('resumen');
		
		// Calcular líneas adicionales para la nota detalle
		$medidor->marcarInicio('nota');
		$lineasNota = 0;
		
		if (!empty($detalle)) {
			// Calcular líneas de la nota basado en caracteres (ajustado)
			$caracteresPorLinea = 45; // Aumentado a 45 caracteres por línea (más realista)
			$longitudNota = strlen($detalle);
			$lineasNota = max(1, ceil($longitudNota / $caracteresPorLinea));
			
			// Agregar líneas fijas mínimas para el formato de la nota
			$lineasNota += 2; // Solo "NOTA DETALLE" y línea separadora (reducido de 3 a 2)
			
			$medidor->agregarLineaFija($lineasNota);
			error_log("Nota detalle: {$longitudNota} caracteres → {$lineasNota} líneas (ajustado)");
		} else {
			// Si no hay nota, solo agregar líneas fijas mínimas
			$medidor->agregarLineaFija(2); // Reducido de 3 a 2
			error_log("Sin nota detalle: líneas fijas mínimas (ajustado)");
		}
		
		$lineasNotaCalculada = $medidor->marcarFin('nota');
		
		// Agregar líneas fijas adicionales para el footer (espacios, márgenes)
		$medidor->agregarLineaFija(6); // 6 líneas fijas para footer
		
		$medidor->marcarInicio('footer');
		
		// MARCADOR: Fin del footer y total
		$lineasFooter = $medidor->marcarFin('footer');
		$totalLineas = $medidor->marcarFin('total');
		
		// Calcular altura dinámica basada en contenido real
		$interlineaBase = 4; // mm por interlínea
		$factorReduccion = 0.95; // Factor de reducción conservador
		$alturaCalculada = ($totalLineas * $interlineaBase) * $factorReduccion;
		
		// Escalado inteligente del margen según el tamaño de la factura
		$margenAdicional = 0;
		$tipoFactura = "";
		
		if ($alturaCalculada < 150) {
			// Facturas pequeñas: margen mínimo
			$margenAdicional = 5; // 0.5 cm
			$tipoFactura = "pequeña";
		} elseif ($alturaCalculada < 250) {
			// Facturas medianas: margen moderado
			$margenAdicional = 10; // 1 cm
			$tipoFactura = "mediana";
		} else {
			// Facturas grandes: margen exacto
			$margenAdicional = 15; // 1.5 cm
			$tipoFactura = "grande";
		}
		
		$alturaCalculada = $alturaCalculada + $margenAdicional;
		
		// Establecer límites mínimos y máximos
		$alturaFinal = max(120, min($alturaCalculada, 600));
		
		error_log("=== FACTURA CON ALTURA DINÁMICA ===");
		error_log("Factura código: {$this->codigo}");
		error_log("  🔹 Total líneas: {$totalLineas}");
		error_log("  🔹 Altura base: " . ($totalLineas * $interlineaBase) . "mm");
		error_log("  🔹 Con factor: " . (($totalLineas * $interlineaBase) * $factorReduccion) . "mm");
		error_log("  🔹 Tipo: {$tipoFactura} (+{$margenAdicional}mm)");
		error_log("  🔹 Altura final: {$alturaFinal}mm");
		error_log("=== FIN CÁLCULO ===");
		
		// Log simplificado de secciones
		error_log("📊 SECCIONES:");
		error_log("  🔹 Encabezado: {$lineasEncabezado} líneas");
		error_log("  🔹 Productos: {$lineasProductos} líneas ({$cantidadProductos} productos)");
		error_log("  🔹 Resumen: {$lineasResumen} líneas");
		error_log("  🔹 Nota: {$lineasNotaCalculada} líneas" . (!empty($detalle) ? " (con detalle)" : " (sin detalle)"));
		error_log("  🔹 Footer: {$lineasFooter} líneas");

		// AHORA CREAMOS EL PDF CON LA ALTURA CORRECTA
		$pdf->AddPage('P', array(75, $alturaFinal));
		
		// Escribir el contenido en el PDF
		$pdf->writeHTML($bloque1, false, false, false, false, '');
		
		foreach ($productos as $key => $item) {
			$valorUnitario = number_format($item["precio"] ?? 0, 0, ',', '.');
			$precioTotal = number_format($item["total"] ?? 0, 0, ',', '.');
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
		
		$pdf->writeHTML($bloque3, false, false, false, false, '');

		// ---------------------------------------------------------
		ob_end_clean(); // Limpia cualquier buffer de salida antes de generar el PDF
		$pdf->Output('factura.pdf', 'I'); // 'I' para mostrar en el navegador, 'D' para descargar
	}
}

$factura = new imprimirFactura();
$factura->codigo = $_GET["codigo"];
$factura->traerImpresionFactura();