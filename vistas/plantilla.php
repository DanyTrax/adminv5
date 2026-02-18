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
  <!-- Bloquear script de Cloudflare Insights (beacon.min.js) inyectado por hosting/proxy: evita errores MIME/CORS/integrity en consola. -->
  <meta http-equiv="Content-Security-Policy" content="script-src 'self' 'unsafe-inline' 'unsafe-eval' https://static.cloudflareinsights.com;">
  <?php
    // --- LÓGICA PARA TÍTULO DINÁMICO ---
    // Obtener nombre de sucursal desde BD local
    $nombreSitio = "Sistema";
    try {
        if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {
            require_once "modelos/conexion.php";
            $stmt = Conexion::conectar()->prepare("SELECT nombre FROM sucursal_local LIMIT 1");
            $stmt->execute();
            $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($sucursal && !empty($sucursal["nombre"])) {
                $nombreSitio = $sucursal["nombre"];
            }
        }
    } catch (Exception $e) {
        // Si hay error, mantener valor por defecto
        error_log("Error obteniendo nombre de sucursal para título: " . $e->getMessage());
    }
    
    $tituloPagina = "Inicio";
    if (isset($_GET["ruta"])) {
        $tituloAmigable = str_replace("-", " ", $_GET["ruta"]);
        $tituloPagina = ucwords($tituloAmigable);
    }
  ?>
  <title><?php echo $nombreSitio . " - " . $tituloPagina; ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="icon" href="<?php echo $url; ?>vistas/img/plantilla/icono-negro.png ">
  <!-- CDN reemplazados por vistas/lib (toastr, moment-timezone, xlsx) para evitar CORS. Beacon: lo inyecta Cloudflare/hosting; se bloquea con CSP más abajo. Para quitarlo del todo: Cloudflare Dashboard → Analytics → Web Analytics → desactivar. -->
  
  <!-- (Aquí van todos tus enlaces a CSS y scripts de librerías) -->
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/Ionicons/css/ionicons.min.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/dist/css/AdminLTE.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/datatables.net-bs/css/responsive.bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/plugins/iCheck/all.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/bower_components/morris.js/morris.css">
  <link rel="stylesheet" href="<?php echo $url; ?>vistas/lib/toastr.min.css">

  <!-- CSS DINÁMICO GLOBAL -->
  <?php include_once "css-dinamico-global.php"; ?>

  <script src="<?php echo $url; ?>vistas/bower_components/jquery/dist/jquery.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/fastclick/lib/fastclick.js"></script>
  <script src="<?php echo $url; ?>vistas/dist/js/adminlte.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/datatables.net-bs/js/dataTables.responsive.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/datatables.net-bs/js/responsive.bootstrap.min.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/sweetalert2/sweetalert2.all.js"></script>
  <!-- Shim compatibilidad swal/SweetAlert2: asegurar API swal() y swal.close() para popups/modales -->
  <script>
  (function() {
    var Sw = window.Sweetalert2 || window.Swal;
    if (Sw) {
      window.swal = function(opts) {
        if (opts && opts.type && !opts.icon) opts.icon = opts.type;
        return typeof Sw === 'function' ? Sw(opts) : (Sw.fire || Sw)(opts);
      };
      window.swal.close = Sw.close || Sw.closePopup || Sw.closeModal || function(){};
      if (typeof window.Swal === 'undefined') window.Swal = Sw;
    } else if (typeof window.swal === 'undefined') {
      window.swal = function(){ return Promise.resolve({value:true}); };
      window.swal.close = function(){};
    }
  })();
  </script>
  <script src="<?php echo $url; ?>vistas/lib/xlsx.full.min.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/iCheck/icheck.min.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/input-mask/jquery.inputmask.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/input-mask/jquery.inputmask.extensions.js"></script>
  <script src="<?php echo $url; ?>vistas/plugins/jqueryNumber/jquerynumber.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/moment/min/moment.min.js"></script>
  <script src="<?php echo $url; ?>vistas/lib/moment-timezone-with-data.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/raphael/raphael.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/morris.js/morris.min.js"></script>
  <script src="<?php echo $url; ?>vistas/bower_components/Chart.js/Chart.js"></script>
  <script src="<?php echo $url; ?>vistas/lib/toastr.min.js"></script>

  <!-- =============================================
  INICIO DE LA CORRECCIÓN
  ============================================== -->
  <script>
    <?php
      // URL base para que AJAX y enlaces relativos funcionen con rutas amigables (ej: /productos-stock-sucursales).
      echo 'var BASE_URL = "' . str_replace(['\\', '"'], ['\\\\', '\\"'], rtrim($url, '/')) . '/";';
      // Se define una variable de JavaScript con la ruta actual
      if (isset($_GET["ruta"])) {
        echo 'var RUTA_ACTUAL = "' . addslashes($_GET["ruta"]) . '";';
      } else {
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
        "inicio" => ["Administrador", "Especial", "Vendedor", "Contador", "Transportador"],
        "usuarios" => ["Administrador"],
        "categorias" => ["Administrador", "Especial", "Vendedor"],
        "catalogo-maestro" => ["Administrador", "Especial"],
        "productos" => ["Administrador", "Especial", "Vendedor"],
        "productos-stock-sucursales" => ["Administrador", "Especial", "Vendedor"],
        "clientes" => ["Administrador", "Vendedor", "Contador"],
        "ventas" => ["Administrador", "Vendedor", "Contador"],
        "crear-venta" => ["Administrador", "Vendedor", "Contador"],
        "editar-venta" => ["Administrador", "Vendedor", "Contador"],
        "reportes" => ["Administrador", "Especial", "Control"],
        "reporte-detallado" => ["Administrador", "Especial", "Control"],
        "contabilidad" => ["Administrador", "Especial"],
        "gastos" => ["Administrador", "Contador", "Vendedor"],
        "crear-gastos" => ["Administrador", "Contador", "Vendedor"],
        "editar-gasto" => ["Administrador", "Contador"],
        "entradas" => ["Administrador", "Contador"],
        "crear-entradas" => ["Administrador", "Contador"],
        "editar-entrada" => ["Administrador"],
        "borrar-entrada" => ["Administrador"],
        "cotizacion" => ["Administrador", "Vendedor", "Contador"],
        "crear-cotizacion" => ["Administrador", "Vendedor", "Contador"],
        "solicitudes-stock" => ["Administrador", "Vendedor", "Contador", "Transportador"],
        "crear-solicitud-stock" => ["Administrador", "Vendedor", "Contador"],
        "editar-cotizacion" => ["Administrador", "Vendedor", "Contador"],
        "medios-pago" => ["Administrador", "Especial"],
        "salidas-inventario" => ["Administrador", "Especial", "Vendedor"],
        "sucursales" => ["Administrador"], 
        "usuarios-central" => ["Administrador"],
        "clientes-central" => ["Administrador"],
        "categorias-central" => ["Administrador"],
        "consultar-usuarios-sucursales" => ["Administrador"],
        "despachos" => ["Administrador", "Vendedor", "Contador", "Transportador"],
        "crear-despacho" => ["Administrador", "Vendedor", "Contador"],
        "editar-despacho" => ["Administrador", "Vendedor", "Contador"],
        "eliminar-despacho" => ["Administrador", "Vendedor", "Contador"],
        "stock-transito" => ["Administrador", "Vendedor", "Contador", "Transportador"],
        "registro-descargas-funcional" => ["Administrador", "Vendedor", "Contador", "Transportador"],
        "personalizacion-colores-simplificado" => ["Administrador"],
        "personalizacion-cotizaciones" => ["Administrador"],
        "crear-tabla-personalizacion-cotizaciones" => ["Administrador"],
        "agregar-campos-logo-texto-cotizaciones" => ["Administrador"],
        "agregar-campo-nombre-sucursal-personalizacion" => ["Administrador"],
        "instalacion-sql-completa" => ["Administrador"],
        "corregir-tipo-documento-clientes" => ["Administrador"],
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
  <script src="<?php echo $url; ?>vistas/js/productos.js?v=<?php echo time(); ?>"></script>
  <script src="<?php echo $url; ?>vistas/js/productos-stock-sucursales.js"></script> 
  <script src="<?php echo $url; ?>vistas/js/clientes.js"></script>
  <script src="<?php echo $url; ?>vistas/js/ventas.js"></script>
  <script src="<?php echo $url; ?>vistas/js/contabilidad.js"></script>
  <script src="<?php echo $url; ?>vistas/js/medios-pago.js"></script>
  <script src="<?php echo $url; ?>vistas/js/timezone-bogota.js"></script>
  <script src="<?php echo $url; ?>vistas/js/salidas-inventario.js"></script>
  <script src="<?php echo $url; ?>vistas/js/filtros-fechas.js"></script>
  <script src="<?php echo $url; ?>vistas/js/sucursales.js"></script> 
  <script src="<?php echo $url; ?>vistas/js/solicitudes-stock.js"></script>
  <script src="<?php echo $url; ?>vistas/js/crear-solicitud-stock.js"></script>  
  <script src="<?php echo $url; ?>vistas/js/catalogo-maestro.js"></script>
  <script src="<?php echo $url; ?>vistas/js/clientes-central.js"></script>
  <?php if(isset($_GET["ruta"]) && $_GET["ruta"] == "despachos"): ?>
  <script>
  window.abrirModalCambiarEstado = function(idDespacho, numeroDespacho, estadoActual) {
    if (typeof jQuery !== 'undefined') {
      jQuery("#modalVerDespacho").modal("hide");
      jQuery("#idDespachoCambiarEstado").val(idDespacho);
      jQuery("#numeroDespachoCambiarEstado").text(numeroDespacho || '');
      jQuery("#estadoActualCambiar").text((estadoActual || "").toUpperCase());
      jQuery("#nuevoEstadoDespacho").val("");
      jQuery("#observacionesCambiarEstado").val("");
      jQuery("#modalCambiarEstadoDespacho").modal("show");
    }
  };
  </script>
  <?php endif; ?>
  <script src="<?php echo $url; ?>vistas/js/despachos.js"></script>
  <script src="<?php echo $url; ?>vistas/js/crear-despacho.js"></script>
  <!-- <script src="<?php echo $url; ?>vistas/js/stock-transito.js"></script> --> <!-- Comentado: se usa stock-transito-unificado.js -->
</html>
</body>

</html>
