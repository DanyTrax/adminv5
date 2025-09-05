<?php
require_once 'config-instalacion.php';

logInstalacion("Sesión de instalación cerrada");
cerrarSesionInstalacion();

header('Location: index.php?logout=success');
exit;
?>