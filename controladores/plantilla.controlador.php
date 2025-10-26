<?php

class ControladorPlantilla{

	static public function ctrPlantilla(){

		include "vistas/plantilla.php";

	}
	
	/*=============================================
	INCLUIR MÓDULOS
	=============================================*/
	static public function ctrIncluirModulo($modulo) {
		$ruta = "vistas/modulos/" . $modulo . ".php";
		
		if (file_exists($ruta)) {
			include $ruta;
		} else {
			include "vistas/modulos/404.php";
		}
	}	


}