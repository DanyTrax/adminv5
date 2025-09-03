<?php

class ControladorPlantilla{

	static public function ctrPlantilla(){

		include "vistas/plantilla.php";

	}	
else if($enlace == "sucursales"){
    include "vistas/modulos/".$enlace.".php";
}

}