<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "src/Utils.php";

require_once "controladores/plantilla.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "controladores/categorias.controlador.php";
require_once "controladores/productos.controlador.php";
require_once "controladores/clientes.controlador.php";
require_once "controladores/ventas.controlador.php";
require_once "controladores/cotizaciones.controlador.php";
require_once "controladores/contabilidad.controlador.php";
require_once "controladores/medios-pago.controlador.php";
require_once "controladores/catalogo-maestro.controlador.php";
require_once "controladores/sucursales.controlador.php";

require_once "modelos/usuarios.modelo.php";
require_once "modelos/categorias.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/clientes.modelo.php";
require_once "modelos/ventas.modelo.php";
require_once "extensiones/vendor/autoload.php";
require_once "modelos/cotizaciones.modelo.php";
require_once "modelos/contabilidad.modelo.php";
require_once "modelos/medios-pago.modelo.php";
require_once "modelos/catalogo-maestro.modelo.php";
require_once "modelos/sucursales.modelo.php";

require_once "src/MedioPago.php";
require_once "src/FormaPago.php";

$plantilla = new ControladorPlantilla();
$plantilla->ctrPlantilla();

?>