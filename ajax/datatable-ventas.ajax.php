<?php

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

class TablaProductosVentas{

	public function mostrarTablaProductosVentas(){

		$item = null;
    	$valor = null;
    	$orden = "id";

  		$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
 		
  		if(count($productos) == 0){
  			echo '{"data": []}';
		 	return;
  		}	
		
  		// 1. CREAR UN ARRAY PARA ALMACENAR LOS DATOS
  		$datosJsonArray = array();

		for($i = 0; $i < count($productos); $i++){

		 	/*=============================================
	 		TRAEMOS LA IMAGEN
 			=============================================*/ 
		 	$imagen = "<img src='".$productos[$i]["imagen"]."' width='40px'>";

		 	/*=============================================
	 		STOCK
 			=============================================*/ 
 			if($productos[$i]["stock"] <= 10){
 				$stock = "<button class='btn btn-danger'>".$productos[$i]["stock"]."</button>";
 			}else if($productos[$i]["stock"] > 11 && $productos[$i]["stock"] <= 15){
 				$stock = "<button class='btn btn-warning'>".$productos[$i]["stock"]."</button>";
 			}else{
 				$stock = "<button class='btn btn-success'>".$productos[$i]["stock"]."</button>";
 			}

		 	/*=============================================
	 		TRAEMOS LAS ACCIONES
 			=============================================*/ 
		 	$botones = "<div class='btn-group'><button class='btn btn-primary agregarProducto recuperarBoton' idProducto='".$productos[$i]["id"]."'>Agregar</button></div>"; 

		 	// 2. AÑADIMOS LOS DATOS DE LA FILA AL ARRAY
		 	$datosJsonArray[] = array(
		 		($i+1),
		 		$imagen,
		 		$productos[$i]["codigo"],
		 		$productos[$i]["descripcion"],
		 		$stock,
		 		$botones
		 	);
		}

		// 3. CREAMOS LA RESPUESTA FINAL Y LA CONVERTIMOS A JSON
		$respuestaFinal = array("data" => $datosJsonArray);
		echo json_encode($respuestaFinal);
	}
}

/*=============================================
ACTIVAR TABLA DE PRODUCTOS
=============================================*/ 
$activarProductosVentas = new TablaProductosVentas();
$activarProductosVentas -> mostrarTablaProductosVentas();