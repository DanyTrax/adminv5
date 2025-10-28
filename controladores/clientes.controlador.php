<?php

require_once __DIR__ . "/../modelos/clientes.modelo.php";

class ControladorClientes
{

	/*=============================================
	CREAR CLIENTES
	=============================================*/

	static public function ctrCrearCliente()
	{

		if (isset($_POST["nuevoCliente"])) {

			// Debug: Log de los datos recibidos
			error_log("=== DEBUG CLIENTES - DATOS RECIBIDOS ===");
			error_log("nuevoCliente: " . $_POST["nuevoCliente"]);
			error_log("nuevoDocumentoId: " . $_POST["nuevoDocumentoId"]);
			error_log("nuevoEmail: " . $_POST["nuevoEmail"]);
			error_log("nuevoTelefono: " . $_POST["nuevoTelefono"]);
			error_log("nuevaDireccion: " . $_POST["nuevaDireccion"]);

			// Validaciones individuales con debug
			$validacionNombre = preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜçÇ\s\.\,\-\'\"\(\)]+$/', $_POST["nuevoCliente"]);
			$validacionDocumento = preg_match('/^[0-9]+$/', $_POST["nuevoDocumentoId"]);
			$validacionEmail = preg_match('/^[@\.\a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoEmail"]);
			$validacionTelefono = preg_match('/^[0-9]{7,10}$/', $_POST["nuevoTelefono"]);
			$validacionDireccion = preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜçÇ\s\.\,\-\'\"\(\)\#\/\:\;\@–—]+$/', $_POST["nuevaDireccion"]);

			error_log("=== DEBUG CLIENTES - RESULTADOS VALIDACIÓN ===");
			error_log("Nombre válido: " . ($validacionNombre ? "SÍ" : "NO"));
			error_log("Documento válido: " . ($validacionDocumento ? "SÍ" : "NO"));
			error_log("Email válido: " . ($validacionEmail ? "SÍ" : "NO"));
			error_log("Teléfono válido: " . ($validacionTelefono ? "SÍ" : "NO"));
			error_log("Dirección válida: " . ($validacionDireccion ? "SÍ" : "NO"));

			if (
				$validacionNombre &&
				$validacionDocumento &&
				$validacionEmail &&
				$validacionTelefono &&
				$validacionDireccion
			) {

				$tabla = "clientes";

				$datos = array(
					"nombre" => $_POST["nuevoCliente"],
					"documento" => $_POST["nuevoDocumentoId"],
					"email" => $_POST["nuevoEmail"],
					"telefono" => $_POST["nuevoTelefono"],
					"direccion" => $_POST["nuevaDireccion"]
				);

				$respuesta = ModeloClientes::mdlIngresarCliente($tabla, $datos);

				if ($respuesta == "ok") {

					echo '<script>

					swal({
						  type: "success",
						  title: "El cliente ha sido guardado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
									if (result.value) {

									window.location = "clientes";

									}
								})

					</script>';
				} else {
					echo '<script>

					swal({
						  type: "error",
						  title: "' . $respuesta . '",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						}).then(function(result){
							
						})

			  	</script>';
				}
			} else {

				echo '<script>

					swal({
						  type: "error",
						  title: "¡El cliente no puede ir vacío o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						}).then(function(result){
							
						})

			  	</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR CLIENTES
	=============================================*/

	static public function ctrMostrarClientes($item, $valor)
	{

		$tabla = "clientes";

		$respuesta = ModeloClientes::mdlMostrarClientes($tabla, $item, $valor);

		return $respuesta;
	}

	/*=============================================
	EDITAR CLIENTE
	=============================================*/

	static public function ctrEditarCliente()
	{

		if (isset($_POST["editarCliente"])) {

			if (
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜçÇ\s\.\,\-\'\"\(\)]+$/', $_POST["editarCliente"]) &&
				preg_match('/^[0-9]+$/', $_POST["editarDocumentoId"]) &&
				preg_match('/^[@\.\a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarEmail"]) &&
				preg_match('/^[0-9]{7,10}$/', $_POST["editarTelefono"]) &&
				preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜçÇ\s\.\,\-\'\"\(\)\#\/\:\;\@–—]+$/', $_POST["editarDireccion"])
			) {

				$tabla = "clientes";

				$datos = array(
					"id" => $_POST["idCliente"],
					"nombre" => $_POST["editarCliente"],
					"documento" => $_POST["editarDocumentoId"],
					"email" => $_POST["editarEmail"],
					"telefono" => $_POST["editarTelefono"],
					"direccion" => $_POST["editarDireccion"]
				);

				$respuesta = ModeloClientes::mdlEditarCliente($tabla, $datos);

				if ($respuesta == "ok") {

					echo '<script>

					swal({
						  type: "success",
						  title: "El cliente ha sido cambiado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
									if (result.value) {

									window.location = "clientes";

									}
								})

					</script>';
				} else {
					echo '<script>

					swal({
						  type: "error",
						  title: "' . $respuesta . '",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						}).then(function(result){
							
						})

			  	</script>';
				}
			} else {

				echo '<script>

					swal({
						  type: "error",
						  title: "¡El cliente no puede ir vacío o llevar caracteres especiales!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {

							window.location = "clientes";

							}
						})

			  	</script>';
			}
		}
	}

	/*=============================================
	ELIMINAR CLIENTE
	=============================================*/

	static public function ctrEliminarCliente()
	{

		if (isset($_GET["idCliente"])) {

			$tabla = "clientes";
			$datos = $_GET["idCliente"];

			$respuesta = ModeloClientes::mdlEliminarCliente($tabla, $datos);

			if ($respuesta == "ok") {

				echo '<script>

				swal({
					  type: "success",
					  title: "El cliente ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "clientes";

								}
							})

				</script>';
			}
		}
	}
}
