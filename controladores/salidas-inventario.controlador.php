<?php

class ControladorSalidasInventario{

	/*=============================================
	MOSTRAR SALIDAS DE INVENTARIO
	=============================================*/

	static public function ctrMostrarSalidasInventario($item, $valor){

		$tabla = "salidas_inventario";

		$respuesta = ModeloSalidasInventario::mdlMostrarSalidasInventario($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	CREAR SALIDA DE INVENTARIO
	=============================================*/

	static public function ctrCrearSalidaInventario(){

		if(isset($_POST["nuevaCantidad"])){

			if(preg_match('/^[0-9]+$/', $_POST["nuevaCantidad"]) &&
			   preg_match('/^[0-9]+$/', $_POST["nuevoProducto"]) &&
			   preg_match('/^[0-9]+$/', $_POST["nuevoUsuario"])){

				// Validar que la cantidad no sea mayor a 10
				if($_POST["nuevaCantidad"] > 10){
					echo'<script>

						swal({
							  type: "error",
							  title: "¡Error!",
							  text: "La cantidad no puede ser mayor a 10 unidades",
							  showConfirmButton: true,
							  confirmButtonText: "Cerrar"
							  }).then(function(result){
										if (result.value) {

										window.location = "salidas-inventario";

										}
									})

					</script>';
					return;
				}

				// Validar que el producto tenga stock suficiente
				$tablaProductos = "productos";
				$itemProducto = "id";
				$valorProducto = $_POST["nuevoProducto"];
				$ordenProducto = "id DESC";

				$producto = ModeloProductos::mdlMostrarProductos($tablaProductos, $itemProducto, $valorProducto, $ordenProducto);

				if($producto){

					if($producto["stock"] < $_POST["nuevaCantidad"]){
						echo'<script>

							swal({
								  type: "error",
								  title: "¡Error!",
								  text: "No hay suficiente stock. Stock disponible: '.$producto["stock"].' unidades",
								  showConfirmButton: true,
								  confirmButtonText: "Cerrar"
								  }).then(function(result){
											if (result.value) {

											window.location = "salidas-inventario";

											}
										})

						</script>';
						return;
					}

				}

				$tabla = "salidas_inventario";

				$datos = array("id_usuario" => $_POST["nuevoUsuario"],
							   "id_producto" => $_POST["nuevoProducto"],
							   "cantidad" => $_POST["nuevaCantidad"],
							   "descripcion" => $_POST["nuevaDescripcion"],
							   "numero_remision" => $_POST["nuevaRemision"]);

				$respuesta = ModeloSalidasInventario::mdlCrearSalidaInventario($tabla, $datos);

				if($respuesta == "ok"){

					// Actualizar stock del producto
					$nuevoStock = $producto["stock"] - $_POST["nuevaCantidad"];
					
					$tablaProductos = "productos";
					$campoStock = "stock";
					$valorStock = $nuevoStock;
					$idProducto = $_POST["nuevoProducto"];

					$actualizarStock = ModeloProductos::mdlActualizarCampo($tablaProductos, $campoStock, $valorStock, $idProducto);

					if($actualizarStock == "ok"){

						echo'<script>

						swal({
							  type: "success",
							  title: "¡Salida de Inventario creada!",
							  text: "La salida se ha registrado correctamente",
							  showConfirmButton: true,
							  confirmButtonText: "Cerrar"
							  }).then(function(result){
										if (result.value) {

										window.location = "salidas-inventario";

										}
									})

						</script>';

					}

				}

			}else{

				echo'<script>

					swal({
						  type: "error",
						  title: "¡Error!",
						  text: "¡No se puede guardar la salida de inventario!",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {

							window.location = "salidas-inventario";

							}
						})

			  	</script>';

			}

		}

	}

	/*=============================================
	ELIMINAR SALIDA DE INVENTARIO
	=============================================*/

	static public function ctrEliminarSalidaInventario(){

		if(isset($_GET["idSalida"])){

			// Solo Administrador puede eliminar salidas
			if($_SESSION["perfil"] != "Administrador"){
				echo'<script>

					swal({
						  type: "error",
						  title: "¡Sin permisos!",
						  text: "Solo los Administradores pueden eliminar salidas de inventario",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {

							window.location = "salidas-inventario";

							}
						})

				</script>';
				return;
			}

			$tabla ="salidas_inventario";
			$datos = $_GET["idSalida"];

			$respuesta = ModeloSalidasInventario::mdlEliminarSalidaInventario($tabla, $datos);

			if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "¡Salida de Inventario eliminada!",
					  text: "La salida se ha eliminado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
						if (result.value) {

						window.location = "salidas-inventario";

						}
					})

				</script>';

			}

		}

	}

}
