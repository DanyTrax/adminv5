<?php

session_start();
date_default_timezone_set('America/Bogota');
require_once "config.php";
// =================================================
// DEFINIR URL BASE DINÁMICA
// =================================================
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace("index.php", "", $_SERVER['SCRIPT_NAME']);
$url = $protocol . $host . $script_name;

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <?php
    // --- LÓGICA PARA TÍTULO DINÁMICO ---
    $nombreSitio = "RICAURTE";
    $tituloPagina = "Inicio";
    if (isset($_GET["ruta"])) {
        $tituloAmigable = str_replace("-", " ", $_GET["ruta"]);
        $tituloPagina = ucwords($tituloAmigable);
    }
  ?>
  <title><?php echo $nombreSitio . " - " . $tituloPagina; ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="icon" href="<?php echo $url; ?>vistas/img/plantilla/icono-negro.png ">
  
  <!-- (Aquí van todos tus enlaces a CSS y scripts de librerías) -->
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/Ionicons/css/ionicons.min.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/dist/css/AdminLTE.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/datatables.net-bs/css/responsive.bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/plugins/iCheck/all.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/morris.js/morris.css">
  <script src="<?php echo $url; ?>vistas/bower_components/jquery/dist/jquery.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/fastclick/lib/fastclick.js"></script>
  <script src="<?php echo $url; ?>vistas/dist/js/adminlte.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/datatables.net-bs/js/dataTables.responsive.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/datatables.net-bs/js/responsive.bootstrap.min.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/sweetalert2/sweetalert2.all.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/iCheck/icheck.min.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/input-mask/jquery.inputmask.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/input-mask/jquery.inputmask.extensions.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/jqueryNumber/jquerynumber.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/moment/min/moment.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/raphael/raphael.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/morris.js/morris.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/Chart.js/Chart.js"></script>

  <!-- =============================================
  INICIO DE LA CORRECCIÓN
  ============================================== -->
  <script>
    <?php
      // Se define una variable de JavaScript con la ruta actual
      // para que todos los scripts puedan saber en qué página están.
      if (isset($_GET["ruta"])) {
        echo 'var RUTA_ACTUAL = "' . $_GET["ruta"] . '";';
      } else {
        // Si no hay ruta, asumimos que es la página de inicio
        echo 'var RUTA_ACTUAL = "inicio";';
      }
    ?>
  </script>
  <!-- =============================================
  FIN DE LA CORRECCIÓN
  ============================================== -->
<script>
<?php
// ✅ VERIFICAR SI HAY SESIÓN INICIADA
if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {
    // Usuario logueado - usar datos de sesión
    $nombreUsuario = isset($_SESSION["nombre"]) ? addslashes(trim($_SESSION["nombre"])) : 'Usuario';
    $nombreSucursal = defined('NOMBRE_SUCURSAL') ? addslashes(trim(NOMBRE_SUCURSAL)) : 'Sucursal Principal';
    $perfilUsuario = isset($_SESSION["perfil"]) ? addslashes(trim($_SESSION["perfil"])) : 'Usuario';
} else {
    // Usuario NO logueado - usar valores por defecto para login
    $nombreUsuario = 'Invitado';
    $nombreSucursal = 'Sistema';
    $perfilUsuario = 'Sin sesión';
}

// Limpiar caracteres problemáticos
$nombreUsuario = str_replace(["\n", "\r", "\t", "'", '"'], ['', '', '', "\'", '\"'], $nombreUsuario);
$nombreSucursal = str_replace(["\n", "\r", "\t", "'", '"'], ['', '', '', "\'", '\"'], $nombreSucursal);
$perfilUsuario = str_replace(["\n", "\r", "\t", "'", '"'], ['', '', '', "\'", '\"'], $perfilUsuario);
?>
    // ✅ VARIABLES GLOBALES SEGURAS (FUNCIONAN EN LOGIN Y SISTEMA)
    const nombreUsuario = '<?php echo $nombreUsuario; ?>';
    const nombreSucursal = '<?php echo $nombreSucursal; ?>';
    const perfilUsuario = '<?php echo $perfilUsuario; ?>';
    const apiUrl = "https://pruebas2.acplasticos.com/api-transferencias/";
    const sesionActiva = <?php echo (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") ? 'true' : 'false'; ?>;
    
    // ✅ LOG SOLO SI HAY PROBLEMAS
    if (nombreUsuario === '' || nombreSucursal === '') {
        console.warn('Advertencia: Variables de usuario vacías');
    };
</script>

</head>
</head> <body class="hold-transition skin-blue sidebar-collapse sidebar-mini login-page <?php if(isset($_GET['ruta'])){ echo $_GET['ruta']; } ?>">

  <?php

  if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {

    echo '<div class="wrapper">';

    include "modulos/cabezote.php";
    include "modulos/menu.php";

    // --- LÓGICA DE RUTAS Y ROLES ORIGINAL (RESTAURADA) ---
    if (isset($_GET["ruta"])) {
      $routes = [
        "inicio" => ["Administrador"],
        "usuarios" => ["Administrador"],
        "categorias" => ["Administrador", "Especial"],
        "catalogo-maestro" => ["Administrador", "Especial"],
        "transferencias" => ["Administrador", "Vendedor", "Transportador", "Especial"],
        "crear-transferencia" => ["Administrador", "Vendedor", "Especial"],
        "cargues-pendientes" => ["Administrador", "Transportador", "Especial"],
        "despachar-a-transito" => ["Administrador", "Vendedor", "Especial"],
        "almacen-transito"      => ["Administrador", "Vendedor", "Transportador", "Especial"],
        "recepciones"           => ["Administrador", "Transportador"],
        "productos" => ["Administrador", "Especial", "Vendedor"],
        "clientes" => ["Administrador", "Vendedor", "Contador"],
        "ventas" => ["Administrador", "Vendedor", "Contador"],
        "crear-venta" => ["Administrador", "Vendedor", "Contador"],
        "editar-venta" => ["Administrador", "Vendedor", "Contador"],
        "reportes" => ["Administrador"],
        "reporte-detallado" => ["Administrador"],
        "contabilidad" => ["Administrador", "Contador", "Vendedor"],
        "gastos" => ["Administrador", "Contador", "Vendedor"],
        "crear-gastos" => ["Administrador", "Contador", "Vendedor"],
        "editar-gasto" => ["Administrador", "Contador"],
        "entradas" => ["Administrador", "Contador"],
        "crear-entradas" => ["Administrador", "Contador"],
        "editar-entrada" => ["Administrador", "Contador"],
        "cotizacion" => ["Administrador", "Vendedor", "Contador"],
        "crear-cotizacion" => ["Administrador", "Vendedor", "Contador"],
        "editar-cotizacion" => ["Administrador", "Vendedor", "Contador"],
        "medios-pago" => ["Administrador"],
        "sucursales" => ["Administrador"], 
        "salir" => ["Administrador", "Especial", "Vendedor", "Contador", "Transportador"]
      ];

      $route = $_GET["ruta"];
      $profile = $_SESSION['perfil'];

      if (array_key_exists($route, $routes)) {
        if (in_array($profile, $routes[$route])) {
          include "modulos/" . $route . ".php";
        } else {
          include "modulos/inicio.php";
        }
      } else {
        include "modulos/404.php";
      }
    } else {
      include "modulos/inicio.php";
    }

    include "modulos/footer.php";
    echo '</div>';

  } else {
    include "modulos/login.php";
  }

  ?>

  <script src="<?php echo $url; ?>vistas/js/reportes.js"></script>
  <script src="<?php echo $url; ?>vistas/js/plantilla.js"></script>
  <script src="<?php echo $url; ?>vistas/js/usuarios.js"></script>
  <script src="<?php echo $url; ?>vistas/js/categorias.js"></script>
  <script src="<?php echo $url; ?>vistas/js/productos.js?v=1.2"></script> 
  <script src="<?php echo $url; ?>vistas/js/clientes.js"></script>
  <script src="<?php echo $url; ?>vistas/js/ventas.js"></script>
  <script src="<?php echo $url; ?>vistas/js/contabilidad.js"></script>
  <script src="<?php echo $url; ?>vistas/js/medios-pago.js"></script>
  <script src="<?php echo $url; ?>vistas/js/filtros-fechas.js"></script>
  <script src="<?php echo $url; ?>vistas/js/transferencias.js"></script>
  <script src="<?php echo $url; ?>vistas/js/crear-transferencia.js"></script>
  <script src="<?php echo $url; ?>vistas/js/despachar-a-transito.js"></script>
  <script src="<?php echo $url; ?>vistas/js/cargues-pendientes.js"></script>
  <script src="<?php echo $url; ?>vistas/js/almacen-transito.js"></script>
  <script src="<?php echo $url; ?>vistas/js/recepciones.js"></script>
  <script src="<?php echo $url; ?>vistas/js/sucursales.js"></script> 
  <script src="<?php echo $url; ?>vistas/js/catalogo-maestro.js"></script>
</html>
</body>

</html>
