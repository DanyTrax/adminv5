<?php

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

require_once "../../../controladores/cotizaciones.controlador.php";
require_once "../../../modelos/cotizaciones.modelo.php";


$cotizacion = ControladorCotizaciones::findById($_GET['codigo']);
$productos = json_decode($cotizacion['productos']);
$images = json_decode($cotizacion['images']);
$cliente = ControladorClientes::ctrMostrarClientes("id", $cotizacion['id_cliente']);

$sql = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
?>

<style>
	main {
		margin-top: 0;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		/* page-break-after: always; */
	}

	.header {
		background: #00afef;
		padding: 15px;
		border-top-left-radius: 90px;
		border-bottom-left-radius: 90px;
		color: white;
		font-weight: 700;
		display: grid;
		grid-template-columns: repeat(5, 1fr);
		justify-content: space-around;
		align-items: center;
	}

	.header img {
		width: 50%;
	}

	footer {
		background: #00afef;
		margin-top: 20px;
		break-after: page;
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
		font-size: 22px;
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
		font-size: 16px;
	}

	.foot-img {
		margin-top: 25px;
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
		width: 100%;
	}
</style>
<main>
	<header class="header">
		<div class="text-center">
			<img src="<?= $sql ?>/vistas/img/cotizacion/logo.svg" alt="" />
		</div>
		<div>
			<div>ACRÍLICOS INFINITO S.A.A</div>
			<div>NIT: 901.158.612-5</div>
			<div>IVA E ICA RÉGIMEN COMÚN</div>
		</div>
		<div>
			<div>ACRÍLICOS</div>
			<div>HABLADORES</div>
			<div>BUZONES</div>
			<div>SEÑALIZACIÓN</div>
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
				$iva = 0;
				$total = 0;
				?>
				<?php foreach ($productos as $producto) : ?>
					<?php
					$subtotal += $producto->total;
					$iva += $producto->total * 0.19;
					?>
					<tr>
						<td><?= $producto->id ?></td>
						<td><?= $producto->cantidad ?></td>
						<td><?= $producto->descripcion ?></td>
						<td><?= $producto->precio ?></td>
						<td><?= $producto->total ?></td>
					</tr>
				<?php endforeach; ?>
				<?php
				$total = $subtotal + $iva;
				?>
			</tbody>
			<tfoot class="no-border">
				<tr>
					<td colspan="3" class="no-border"></td>
					<td class="no-border">Subtotal</td>
					<td class="no-border"><?= $subtotal ?></td>
				</tr>
				<tr>
					<td colspan="3"></td>
					<td>Iva</td>
					<td><?= $iva ?></td>
				</tr>
				<tr>
					<td colspan="3"></td>
					<td>Total</td>
					<td><?= $total ?></td>
				</tr>
			</tfoot>
		</table>
	</div>
	<div class="foot-img">
		<?php foreach ($images as $image) : ?>
			<div>
				<img src="<?= $sql . $image ?>" alt="" />
			</div>
		<?php endforeach; ?>
	</div>

	<footer>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
		<div>
			<p>
				Carrera 20B No. 73-43 Tel: 211 04 93 Móvil: 322 946 0339
			</p>
			<p>
				Correo: ventas1@acrilicosinfinito.com
			</p>
		</div>
	</footer>
</main>

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
			margin: 1,
			filename: "cotizacion.pdf",
			image: {
				type: "jpeg",
				quality: 0.98
			},
			html2canvas: {
				scale: 2,
				useCORS: true
			},
			// jsPDF: { unit: "in", format: "letter", orientation: "portrait" },
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

				for (let i = 1; i <= totalPages; i++) {
					pdf.setPage(i);
					pdf.setFontSize(10);
					pdf.setTextColor(150);
					pdf.text('Calle 72 No. 20A-47 Tel: 211 04 93 Móvil: 322 946 0339 Correo: ventas1@acrilicosinfinito.com ', pdf.internal.pageSize.getWidth() - 180, pdf.internal.pageSize.getHeight() - 10);
				}

			}).save();
	};

	fetch('https://upload.wikimedia.org/wikipedia/en/0/0c/Garfield-comparison.png')
		.then(res => res.blob())
		.then(res => new Promise(callback => {
			let reader = new FileReader();
			reader.onload = () => callback(reader.result);
			reader.readAsDataURL(res);
		}))
		.then(res => console.log(res))

	fetch('/vistas/img/cotizacion/logo.svg')
		.then(res => res.blob())
		.then(res => new Promise(callback => {
			let reader = new FileReader();
			reader.onload = () => callback(reader.result);
			reader.readAsDataURL(res);
		}))
		.then(res => console.log(res))
</script>