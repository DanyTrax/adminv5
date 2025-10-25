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
		$neto = number_format($respuestaVenta["neto"] ?? 0, 2, ',', '.');
		$impuesto = number_format($respuestaVenta["impuesto"] ?? 0, 2, ',', '.');
		$total = number_format($respuestaVenta["total"] ?? 0, 2, ',', '.');
		$detalle = substr($respuestaVenta["detalle"], 0);
		$inabono = number_format($respuestaVenta["abono"] ?? 0, 2, ',', '.');
		$ultabono = number_format($respuestaVenta["Ult_abono"] ?? 0, 2, ',', '.');
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

		//INFORMACION EMPRESA - Obtener desde BD Central
		$tikempresa = "";
		$tiknumero = "";
		$tikdirecc = "";
		$tikcorreo = "NO HAY CORREO";
		
		// Obtener información de la sucursal desde BD Central
		if (isset($respuestaVendedor['empresa']) && !empty($respuestaVendedor['empresa'])) {
			try {
				// Buscar la sucursal por nombre en la BD central
				$sucursalInfo = ModeloSucursales::mdlMostrarSucursal("nombre", $respuestaVendedor['empresa']);
				
				if ($sucursalInfo) {
					$tikempresa = strtoupper($sucursalInfo['nombre']);
					$tiknumero = $sucursalInfo['telefono'] ?: "NO DISPONIBLE";
					$tikdirecc = $sucursalInfo['direccion'] ?: "NO DISPONIBLE";
					$tikcorreo = $sucursalInfo['email'] ?: "NO HAY CORREO";
				} else {
					// Fallback a datos por defecto si no se encuentra la sucursal
					$tikempresa = strtoupper($respuestaVendedor['empresa']);
					$tiknumero = "NO DISPONIBLE";
					$tikdirecc = "NO DISPONIBLE";
					$tikcorreo = "NO HAY CORREO";
				}
			} catch (Exception $e) {
				// En caso de error, usar datos por defecto
				error_log("Error obteniendo datos de sucursal: " . $e->getMessage());
				$tikempresa = strtoupper($respuestaVendedor['empresa']);
				$tiknumero = "NO DISPONIBLE";
				$tikdirecc = "NO DISPONIBLE";
				$tikcorreo = "NO HAY CORREO";
			}
		} else {
			// Si no hay empresa definida, usar datos por defecto
			$tikempresa = "SUCURSAL NO DEFINIDA";
			$tiknumero = "NO DISPONIBLE";
			$tikdirecc = "NO DISPONIBLE";
			$tikcorreo = "NO HAY CORREO";
		}
		
		// L�0�1NEA 94 ELIMINADA Y L�0�7GICA DE ABONO CORREGIDA
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

		// MARCADOR: Fin del resumen e inicio del footer
		$medidor->agregarLinea($bloque3);
		$lineasResumen = $medidor->marcarFin('resumen');
		
		// Agregar líneas fijas adicionales para el footer (espacios, márgenes)
		$medidor->agregarLineaFija(6); // 6 líneas fijas para footer
		
		$medidor->marcarInicio('footer');
		
		// MARCADOR: Fin del footer y total
		$lineasFooter = $medidor->marcarFin('footer');
		$totalLineas = $medidor->marcarFin('total');
		
		// Calcular altura basada en líneas reales medidas (ajustado)
		$interlineaBase = 4; // mm por interlínea (restaurado)
		$factorReduccion = 0.95; // Factor de reducción más conservador (solo 5% menos)
		$alturaCalculada = ($totalLineas * $interlineaBase) * $factorReduccion;
		
		// Factor de proporción: 5% más cuando se aproxima a 1 hoja (297mm)
		$alturaHoja = 297; // Altura aproximada de 1 hoja A4
		$umbralHoja = $alturaHoja * 0.8; // 80% de 1 hoja como umbral (237.6mm)
		
		if ($alturaCalculada > $umbralHoja) {
			$alturaCalculada = $alturaCalculada * 1.05; // 5% más grande
			error_log("Factura grande detectada: Aplicando factor de proporción 5% (aproximación a 1 hoja)");
		}
		
		// Escalado inteligente del margen según el tamaño de la factura
		$margenAdicional = 0;
		$tipoFactura = "";
		
		if ($alturaCalculada < 150) {
			// Facturas pequeñas (1 item): margen mínimo
			$margenAdicional = 5; // 0.5 cm
			$tipoFactura = "pequeña (1 item)";
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
		error_log("Factura {$tipoFactura}: Agregando margen de {$margenAdicional}mm");
		
		$alturaFinal = max(120, min($alturaCalculada, 600)); // Máximo restaurado a 600mm
		
		// Log detallado de la medición
		error_log("=== ESTUDIO COMPLETO FACTURA {$this->codigo} ===");
		error_log("📊 SECCIONES:");
		error_log("  🔹 Encabezado: {$lineasEncabezado} líneas + 3 fijas");
		error_log("  🔹 Productos: {$lineasProductos} líneas ({$cantidadProductos} productos)");
		error_log("  🔹 Resumen: {$lineasResumen} líneas");
		error_log("  🔹 Footer: {$lineasFooter} líneas + 6 fijas");
		error_log("📏 CÁLCULO:");
		error_log("  Total líneas: {$totalLineas} (incluye líneas fijas)");
		error_log("  Fórmula base: {$totalLineas} × {$interlineaBase}mm × {$factorReduccion}");
		
		// Mostrar si se aplicó factor de proporción
		if ($alturaCalculada > $umbralHoja) {
			error_log("  Factor de proporción: 5% aplicado (aproximación a 1 hoja)");
		} else {
			error_log("  Factor de proporción: No aplicado (factura normal)");
		}
		
		error_log("  Escalado inteligente: Factura {$tipoFactura} (+{$margenAdicional}mm)");
		error_log("  Altura calculada: {$alturaCalculada}mm");
		error_log("  Altura final: {$alturaFinal}mm");
		error_log("=== FIN ESTUDIO ===");

		// AHORA CREAMOS EL PDF CON LA ALTURA CORRECTA
		$pdf->AddPage('P', array(75, $alturaFinal));
		
		// Escribir el contenido en el PDF
		$pdf->writeHTML($bloque1, false, false, false, false, '');
		
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
		
		$pdf->writeHTML($bloque3, false, false, false, false, '');

		// ---------------------------------------------------------
		ob_end_clean(); // Limpia cualquier buffer de salida antes de generar el PDF
		$pdf->Output('factura.pdf', 'I'); // 'I' para mostrar en el navegador, 'D' para descargar
	}
}

$factura = new imprimirFactura();
$factura->codigo = $_GET["codigo"];
$factura->traerImpresionFactura();