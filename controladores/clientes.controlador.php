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
			// Nombre: requerido, permite letras, números, espacios y caracteres comunes
			$validacionNombre = !empty($_POST["nuevoCliente"]) && preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜçÇ\s\.\,\-\'\"\(\)]+$/', $_POST["nuevoCliente"]);
			
			// Documento: requerido, solo números, 1-11 dígitos
			$validacionDocumento = !empty($_POST["nuevoDocumentoId"]) && preg_match('/^[0-9]{1,11}$/', $_POST["nuevoDocumentoId"]);
			
			// Email: opcional, si está presente debe ser válido
			$email = trim($_POST["nuevoEmail"] ?? '');
			$validacionEmail = empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/^[@\.\a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $email);
			
			// Teléfono: requerido, solo números, 7-15 dígitos (más flexible)
			$telefono = trim($_POST["nuevoTelefono"] ?? '');
			$telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono); // Limpiar caracteres no numéricos
			$validacionTelefono = !empty($telefonoLimpio) && strlen($telefonoLimpio) >= 7 && strlen($telefonoLimpio) <= 15;
			
			// Dirección: opcional, si está presente permite caracteres comunes
			$direccion = trim($_POST["nuevaDireccion"] ?? '');
			$validacionDireccion = empty($direccion) || preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜçÇ\s\.\,\-\'\"\(\)\#\/\:\;\@–—]+$/', $direccion);

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
					"nombre" => trim($_POST["nuevoCliente"]),
					"documento" => trim($_POST["nuevoDocumentoId"]),
					"email" => !empty(trim($_POST["nuevoEmail"] ?? '')) ? trim($_POST["nuevoEmail"]) : '',
					"telefono" => $telefonoLimpio, // Usar teléfono limpio
					"direccion" => !empty(trim($_POST["nuevaDireccion"] ?? '')) ? trim($_POST["nuevaDireccion"]) : ''
				);

				$respuesta = ModeloClientes::mdlIngresarCliente($tabla, $datos);

				// ✅ VERIFICAR RESPUESTAS DE DUPLICADOS
				if ($respuesta == "duplicado_documento") {
					echo '<script>
					swal({
						  type: "warning",
						  title: "¡Documento duplicado!",
						  text: "El documento ' . htmlspecialchars($_POST["nuevoDocumentoId"]) . ' ya existe en el sistema. Por favor, verifique e intente con otro documento.",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						}).then(function(result){
							
						})
			  	</script>';
					return;
				}

				if ($respuesta == "duplicado_nombre") {
					echo '<script>
					swal({
						  type: "warning",
						  title: "¡Nombre duplicado!",
						  text: "El nombre del cliente ya existe en el sistema. Por favor, verifique e intente con otro nombre.",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						}).then(function(result){
							
						})
			  	</script>';
					return;
				}

				if ($respuesta == "ok") {

					// Verificar si viene de crear venta o cotización
					if (isset($_POST["origen"]) && ($_POST["origen"] == "crear-venta" || $_POST["origen"] == "crear-cotizacion")) {
						
						// Obtener el ID del cliente recién creado
						// CAMBIO: Usar PDO::PARAM_STR para soportar documentos grandes
						$stmt = Conexion::conectar()->prepare("SELECT id, nombre, documento FROM clientes WHERE documento = :documento ORDER BY id DESC LIMIT 1");
						$stmt->bindParam(":documento", $_POST["nuevoDocumentoId"], PDO::PARAM_STR);
						$stmt->execute();
						$clienteCreado = $stmt->fetch();
						
						$textoBoton = $_POST["origen"] == "crear-venta" ? "Continuar con la venta" : "Continuar con la cotización";
						$textoMensaje = $_POST["origen"] == "crear-venta" ? "venta" : "cotización";
						
						echo '<script>
						
						swal({
							  type: "success",
							  title: "El cliente ha sido guardado correctamente",
							  showConfirmButton: true,
							  confirmButtonText: "' . $textoBoton . '"
							  }).then(function(result){
										if (result.value) {

										// Cerrar modal
										$("#modalAgregarCliente").modal("hide");
										
										// Actualizar lista de clientes y seleccionar el nuevo cliente
										actualizarListaClientes(' . json_encode($clienteCreado) . ');

										}
									})
						
						</script>';
						
					} else {
						
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
						
					}
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

			// Debug: Log de los datos recibidos
			error_log("=== DEBUG CLIENTES EDITAR - DATOS RECIBIDOS ===");
			error_log("editarCliente: " . $_POST["editarCliente"]);
			error_log("editarDocumentoId: " . $_POST["editarDocumentoId"]);
			error_log("editarEmail: " . $_POST["editarEmail"]);
			error_log("editarTelefono: " . $_POST["editarTelefono"]);
			error_log("editarDireccion: " . $_POST["editarDireccion"]);

			// Validaciones individuales con debug (iguales a crear cliente)
			// Nombre: requerido, permite letras, números, espacios y caracteres comunes
			$validacionNombre = !empty($_POST["editarCliente"]) && preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜçÇ\s\.\,\-\'\"\(\)]+$/', $_POST["editarCliente"]);
			
			// Documento: requerido, solo números, 1-11 dígitos
			$validacionDocumento = !empty($_POST["editarDocumentoId"]) && preg_match('/^[0-9]{1,11}$/', $_POST["editarDocumentoId"]);
			
			// Email: opcional, si está presente debe ser válido
			$email = trim($_POST["editarEmail"] ?? '');
			$validacionEmail = empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/^[@\.\a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $email);
			
			// Teléfono: requerido, solo números, 7-15 dígitos (más flexible)
			$telefono = trim($_POST["editarTelefono"] ?? '');
			$telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono); // Limpiar caracteres no numéricos
			$validacionTelefono = !empty($telefonoLimpio) && strlen($telefonoLimpio) >= 7 && strlen($telefonoLimpio) <= 15;
			
			// Dirección: opcional, si está presente permite caracteres comunes
			$direccion = trim($_POST["editarDireccion"] ?? '');
			$validacionDireccion = empty($direccion) || preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚüÜçÇ\s\.\,\-\'\"\(\)\#\/\:\;\@–—]+$/', $direccion);

			error_log("=== DEBUG CLIENTES EDITAR - RESULTADOS VALIDACIÓN ===");
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
					"id" => $_POST["idCliente"],
					"nombre" => trim($_POST["editarCliente"]),
					"documento" => trim($_POST["editarDocumentoId"]),
					"email" => !empty(trim($_POST["editarEmail"] ?? '')) ? trim($_POST["editarEmail"]) : '',
					"telefono" => $telefonoLimpio, // Usar teléfono limpio
					"direccion" => !empty(trim($_POST["editarDireccion"] ?? '')) ? trim($_POST["editarDireccion"]) : ''
				);

				$respuesta = ModeloClientes::mdlEditarCliente($tabla, $datos);

				// ✅ VERIFICAR RESPUESTAS DE DUPLICADOS
				if ($respuesta == "duplicado_documento") {
					echo '<script>
					swal({
						  type: "warning",
						  title: "¡Documento duplicado!",
						  text: "El documento ' . htmlspecialchars($_POST["editarDocumentoId"]) . ' ya existe en el sistema. Por favor, verifique e intente con otro documento.",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						}).then(function(result){
							
						})
			  	</script>';
					return;
				}

				if ($respuesta == "duplicado_nombre") {
					echo '<script>
					swal({
						  type: "warning",
						  title: "¡Nombre duplicado!",
						  text: "El nombre del cliente ya existe en el sistema. Por favor, verifique e intente con otro nombre.",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						}).then(function(result){
							
						})
			  	</script>';
					return;
				}

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
						  title: "Error al editar el cliente",
						  text: "Ocurrió un error inesperado. Por favor, intente nuevamente.",
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
