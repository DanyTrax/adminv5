<?php

require_once __DIR__ . "/../src/Utils.php";

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";

require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";

require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/cotizaciones.controlador.php";
require_once "../modelos/cotizaciones.modelo.php";


$cotizacion = ControladorCotizaciones::findById($_GET['codigo']);
// Obtenemos el texto original de la base de datos
$productos_string = $cotizacion['productos'];

// LIMPIAMOS el texto de caracteres de control invisibles
$productos_string_limpio = preg_replace('/[\x00-\x1F\x7F]/u', '', $productos_string);

// Decodificamos el texto ya limpio
$productos = json_decode($productos_string_limpio);
$images = json_decode($cotizacion['images']);
$cliente = ControladorClientes::ctrMostrarClientes("id", $cotizacion['id_cliente']);
$vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $cotizacion['id_vendedor']);

$hostname = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
?>

<style>
	main {
		margin-top: 0;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}

	.header {
		background: #873173;
		padding: 5px;
		border-top-left-radius: 90px;
		border-bottom-left-radius: 90px;
		color: white;
		font-weight: 700;
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		justify-content: space-around;
		align-items: center;
		font-size:14px;
	}

	.header img {
		width: 80%;
	}

	footer {
		background: #873173;
		margin-top: 20px;
	}

	footer div:nth-child(1) {
		height: 5px;
	}

	footer div:nth-child(2) {
		width: 100%;
		height: 2px;
		background-color: white;
	}

	footer div:nth-child(3) {
		height: 2px;
	}

	footer div:nth-child(4) {
		width: 100%;
		height: 4px;
		background-color: white;
	}

	footer div:last-child {
		padding: 15px;
		color: white;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		font-size: 16px;
	}

	footer p {
		margin: 0;
	}

	.text-center {
		text-align: center;
	}

	.margin-bottom-0 {
		margin-bottom: 0;
	}

	.body {
		padding: 5px 25px;
	}

	.table-header {
		width: 97%;
		border-collapse: collapse;
		margin-top: 20px;
		margin-left: auto;
		margin-right: auto;
		font-size:13px;
	}

	.table-header tr {
		border-top: 1px solid black;
		border-left: 1px solid black;
		border-right: 1px solid black;
	}

	.table-header tr:last-child {
		border-bottom: 1px solid black;
	}

	.table-header td {
		padding: 2px;
	}

	.table-header td:first-child {
		width: 70%;
	}

	.table-products {
		width: 97%;
		border-collapse: collapse;
		margin-top: 20px;
		margin-left: auto;
		margin-right: auto;
		font-size:13px;
	}

	.table-products th,
	.table-products td {
		border: 1px solid;
	}

	.table-products td {
		padding: 2px;
		text-align: center;
	}

	.table-products tfoot.no-border,
	.table-products tfoot.no-border * {
		border: 0;
	}

	.table-products tfoot * {
		text-transform: uppercase;
		font-weight: 700;
		font-size: 14px;
	}

	.break-before {
		break-before: page;
	}

	.foot-img {
		padding-top: 25px;
		display: flex;
		justify-content: center;
		align-items: center;
		flex-wrap: wrap;
		gap: 20px;
	}

	.foot-img div {
		width: 40%;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.foot-img div img {
		max-height: 300px;
	}

	.firma {
		margin-top: 100px;
		display: flex;
		justify-content: space-around;
		align-items: center;
		gap: 50px;
	}

	.firma div {
		display: flex;
		justify-content: start;
		align-items: start;
		flex-direction: column;
	}
</style>
<main>
	<header class="header">
		<div class="text-center">
			<img src="<?= $hostname ?>/vistas/img/cotizacion/Infinito1.png" alt="" />
		</div>
		<div>
			<div>ACPLASTICOS</div>
			<div>NIT: 901.718.358-2</div>
			<div>IVA E ICA RÉGIMEN COMÚN</div>
		</div>
		
		<div>
			<div>AVISOS</div>
			<div>LETRAS EN 3D</div>
			<div>TOMA UNO</div>
			<div>TRABAJOS ESPECIALES</div>
		</div>
		<div class="text-center">
			<p style="margin-bottom: 5px;">Cotización</p>
			<?= $cotizacion['id'] + 1000 ?>
		</div>
	</header>
	<table class="table-header">
		<tr>
			<td>Cliente: <?= $cliente['nombre'] ?></td>
			<td>NIT: <?= $cliente["documento"] ?></td>
		</tr>
		<tr>
			<td>Datos de contacto: <?= $cliente['telefono'] ?></td>
			<td>PBX: <?= $cliente['telefono'] ?></td>
		</tr>
		<tr>
			<td>Dirección: <?= $cliente['direccion'] ?></td>
			<td>Móvil: <?= $cliente['telefono'] ?></td>
		</tr>
		<tr>
			<td>Correo: <?= $cliente['email'] ?></td>
			<td>Web:</td>
		</tr>
	</table>
	<div class="body">
		<p>Bogotá <?= date('d') ?> de <?= date('M') ?> del año <?= date('Y') ?></p>
		<p class="margin-bottom-0">Buenas tardes</p>
		<p>
			De acuerdo a su solicitud pongo en consideración la siguiente cotización:
		</p>
		<table class="table-products">
			<thead>
				<tr>
					<th>Item</th>
					<th>Cant.</th>
					<th>Descripcion</th>
					<th>Vr. Unidad</th>
					<th>Vr. Total</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$subtotal = 0;
				$iva = $cotizacion['impuesto'];
				$descuento = $cotizacion['descuento'];
				$total = $cotizacion['total'];
				?>
				<?php foreach ($productos as $k => $producto) : ?>
					<?php
					$subtotal += $producto->total;
					?>
					<tr>
						<td><?= $k + 1 ?></td>
						<td><?= $producto->cantidad ?></td>
						<td><?= $producto->descripcion ?></td>
						<td>$ <?= number_format($producto->precio, 2, ',', '.') ?></td>
						<td>$ <?= number_format($producto->total, 2, ',', '.') ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot class="no-border">
				<tr>
					<td colspan="3" rowspan="4" class="no-border"><?= $cotizacion['detalle'] ?></td>
					<td class="no-border">Subtotal</td>
					<td class="no-border">$ <?= number_format($subtotal, 2, ',', '.') ?></td>
				</tr>
				<?php if ($descuento > 1) : ?>
				<tr>
					<td>Descuento</td>
					<td>$ <?= number_format($descuento, 2, ',', '.') ?></td>
				</tr>
				<?php endif; ?>
				<?php if ($iva > 1) : ?>
				<tr>
					<td>Iva</td>
					<td>$ <?= number_format($iva, 2, ',', '.') ?></td>
				</tr>
				<?php endif; ?>
				
				<tr>
					<td>Total</td>
					<td>$ <?= number_format($total, 2, ',', '.') ?></td>
				</tr>
			</tfoot>
		</table>
	</div>

	<div class="firma">
		<div>
			<span>____________</span>
			<span><?= trim($vendedor['nombre']) ?></span>
			<span>Tel: <?= $vendedor['telefono'] ?></span>
		</div>
		<div>
			<span>____________</span>
			<span><?= trim($cliente['nombre']) ?></span>
			<span>Tel: <?= $cliente['telefono'] ?></span>
		</div>
	</div>

	<?php $first = true; ?>
	<?php for ($i = 0; $i < count($images) / 5; $i++) : ?>
		<?php if (!current($images)) continue; ?>

		<div class="foot-img break-before">
			<?php if ($first) : ?>
				<h2 style="width: 100%; text-align: center;">
					Imágenes de referencia
				</h2>
			<?php endif; ?>

			<div>
				<img src="<?= $hostname . ($first ? current($images) : next($images)) ?>" alt="" />
			</div>
			<?php if (isset($images[$key + 1])) : ?>
				<div>
					<img src="<?= $hostname . next($images) ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if (isset($images[$key + 2])) : ?>
				<div>
					<img src="<?= $hostname . next($images) ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if (isset($images[$key + 3])) : ?>
				<div>
					<img src="<?= $hostname . next($images) ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if (isset($images[$key + 4])) : ?>
				<div>
					<img src="<?= $hostname . next($images) ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if (isset($images[$key + 5])) : ?>
				<div>
					<img src="<?= $hostname . next($images) ?>" alt="" />
				</div>
			<?php endif; ?>
		</div>
		<?php $first = false; ?>
	<?php endfor; ?>
</main>
<footer>
	<div></div>
	<div></div>
	<div></div>
	<div></div>
	<div>
		<p>
			Carrera 27 # 10-65 Local 116 Tel: 601 569 9557 Móvil: 322 744 5631
		</p>
		<p>
			Correo: ventas1@acplasticos.com
		</p>
	</div>
</footer>

<style>
	#printInPdf {
		position: fixed;
		top: 10;
		left: 10;
	}
</style>
<button id="printInPdf" onClick="handleGeneratePdf();">Imprimir en PDF</button>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
	const handleGeneratePdf = () => {
		let element = document.querySelector("main");
		let opt = {
			margin: [1, 1, 30, 1],
			filename: "cotizacion-<?= $cotizacion['id'] + 1000 ?>.pdf",
			image: {
				type: "jpeg",
				quality: 0.99
			},
			html2canvas: {
				scale: 3,
				useCORS: true
			},
			// jsPDF: {
			// 	unit: "in",
			// 	format: "letter",
			// 	orientation: "portrait",
			// 	putTotalPages: true
			// },
		};

		html2pdf()
			.from(element)
			.set(opt)
			.toContainer()
			.toCanvas()
			.toImg()
			.toPdf()
			.get('pdf')
			.then((pdf) => {
				var totalPages = pdf.internal.getNumberOfPages();
				let pageSize = pdf.internal.pageSize;

				for (let i = 1; i <= totalPages; i++) {
					pdf.setPage(i);

					pdf.setFontSize(10);
					pdf.setTextColor(150);
					pdf.text('Page ' + i + ' of ' + totalPages, pageSize.getWidth() / 2.25, pageSize.getHeight() - 1);


					pdf.setDrawColor(0);
					pdf.setFillColor('#873173');
					pdf.rect(2, pageSize.getHeight() - 20, pageSize.getWidth() - 5, 1, "F");

					pdf.rect(2, pageSize.getHeight() - 18.5, pageSize.getWidth() - 5, 0.5, "F");

					pdf.rect(2, pageSize.getHeight() - 17, pageSize.getWidth() - 5, 16, "F");

					pdf.setFontSize(10);
					pdf.setTextColor(255, 255, 255);
					pdf.text('Carrera 27 # 10-65 Local 116 Tel: 601 569 9557 Móvil: 322 744 5631', pageSize.getWidth() / 4, pageSize.getHeight() - 10);
					pdf.text('Correo: ventas1@acplastico.com', pageSize.getWidth() / 2.6, pageSize.getHeight() - 5)
				}

			}).save();
	};
</script>