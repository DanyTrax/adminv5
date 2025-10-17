<?php

class ControladorCotizaciones
{
	public static function all()
	{
		$respuesta = ModeloCotizaciones::all();
		return $respuesta;
	}

	public static function crear()
	{
		if (isset($_POST['nuevaCotizacion'])) {
			if ($_POST["listaProductos"] == "") {
				echo '<script>
							swal({
								type: "error",
								title: "La cotización no se ha ejecuta si no hay productos",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							}).then(function(result) {})
						</script>';
				return;
			}

			if ($_FILES['images']['tmp_name'][0] !== '') {
				$images = [];

				$directorio = "vistas/img/cotizacion/" . date('YmdHis');
				mkdir($directorio, 0755);

				foreach ($_FILES['images']['tmp_name'] as $tmp) {
					$name =  explode('/', $tmp);
					$name = array_pop($name);
					$target_file = $directorio . '/' . $name;
					move_uploaded_file($tmp, $target_file);

					$images[] = '/' . $target_file;
				}
			}

			$listaProductos = json_decode($_POST["listaProductos"], true);

			$totalProductosComprados = array();

			foreach ($listaProductos as $key => $value) {
				if ($value["id"] != "libre") {

					array_push($totalProductosComprados, $value["cantidad"]);

					$item = "id";
					$valor = $value["id"];
				}
			}


			date_default_timezone_set('America/Bogota');

			$datos = [
				'codigo' => 1,
				'id_vendedor' => $_POST['idVendedor'],
				'id_cliente' => $_POST['seleccionarCliente'],
				'productos' => $_POST['listaProductos'],
				'impuesto' => $_POST['nuevoPrecioImpuesto'],
				'descuento' => $_POST['nuevoPrecioDescuento'],
				'neto' => $_POST['nuevoPrecioNeto'],
				'total' => $_POST['totalVenta'],
				'detalle' => $_POST['detalle'],
				'metodo_pago' => '',
				'fecha_venta' => date('Y-m-d H:i:s'),
				'id_vend_abono' => $_POST['idVendedor'],
				'abono' => 0,
				'fecha_abono' => date('Y-m-d H:i:s'),
				'pago' => '',
				'Ult_abono' => 0,
				'images' => json_encode($images ?? [])
			];

			$respuesta = ModeloCotizaciones::save($datos);

			if ($respuesta == "ok") {
				echo '<script>
				swal({
					type: "success",
					title: "La cotización ha sido guardada correctamente",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				}).then(function(result) {
					if (result.value) {
						window.location = "cotizacion";
					}
				})
				</script>';
			}
		}
	}

	public static function findById($id)
	{
		$respuesta = ModeloCotizaciones::findById($id);
		return $respuesta;
	}

	public static function editarCotizacion()
	{
		if (isset($_POST['editarCotizacion'])) {
			if ($_POST["listaProductos"] == "") {
				echo '<script>
							swal({
								type: "error",
								title: "La cotización no se ha ejecuta si no hay productos",
								showConfirmButton: true,
								confirmButtonText: "Cerrar"
							}).then(function(result) {})
						</script>';
				return;
			}

			if ($_FILES['images']['tmp_name'][0] !== '') {
				$images = [];

				$directorio = "vistas/img/cotizacion/" . date('YmdHis');
				mkdir($directorio, 0755);

				foreach ($_FILES['images']['tmp_name'] as $tmp) {
					$name =  explode('/', $tmp);
					$name = array_pop($name);
					$target_file = $directorio . '/' . $name;
					move_uploaded_file($tmp, $target_file);

					$images[] = '/' . $target_file;
				}
			}

			$listaProductos = json_decode($_POST["listaProductos"], true);

			$totalProductosComprados = array();

			foreach ($listaProductos as $key => $value) {
				if ($value["id"] != "libre") {

					array_push($totalProductosComprados, $value["cantidad"]);

					$item = "id";
					$valor = $value["id"];
				}
			}


			date_default_timezone_set('America/Bogota');

			$datos = [
				'codigo' => 1,
				'id_vendedor' => $_POST['idVendedor'],
				'id_cliente' => $_POST['seleccionarCliente'],
				'productos' => $_POST['listaProductos'],
				'impuesto' => $_POST['nuevoPrecioImpuesto'],
				'descuento' => $_POST['nuevoPrecioDescuento'],
				'neto' => $_POST['nuevoPrecioNeto'],
				'total' => $_POST['totalVenta'],
				'detalle' => $_POST['detalle'],
				'metodo_pago' => '',
				'fecha_venta' => date('Y-m-d H:i:s'),
				'id_vend_abono' => $_POST['idVendedor'],
				'abono' => 0,
				'fecha_abono' => date('Y-m-d H:i:s'),
				'pago' => '',
				'Ult_abono' => 0,
				'images' => json_encode($images ?? [])
			];

			$respuesta = ModeloCotizaciones::update($_GET['idCotizacion'], $datos);

			if ($respuesta == "ok") {
				echo '<script>
				swal({
					type: "success",
					title: "La cotización ha sido guardada correctamente",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				}).then(function(result) {
					if (result.value) {
						window.location = "cotizacion";
					}
				})
				</script>';
			}
		}
	}

	public static function delete()
	{
		if (isset($_GET["idCotizacion"])) {
			$id = $_GET["idCotizacion"];

			$respuesta = ModeloCotizaciones::deleteById($id);

			if ($respuesta == "ok") {
				echo '<script>
				swal({
					type: "success",
					title: "La cotización ha sido eliminada correctamente",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
				}).then(function(result) {
					if (result.value) {
						window.location = "cotizacion";
					}
				})
				</script>';
			}
		}
	}
}
