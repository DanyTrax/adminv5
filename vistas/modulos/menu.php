<aside class="main-sidebar">

    <section class="sidebar">

        <ul class="sidebar-menu">
<li style="background-color: #f39c12; color: white; font-weight: bold; padding: 10px;">
    <?php echo "Perfil Detectado: " . $_SESSION["perfil"]; ?>
</li>
            <?php

            if ($_SESSION["perfil"] == "Administrador") {

                echo '<li class="active">

                    <a href="inicio">

                        <i class="fa fa-home"></i>
                        <span>Inicio</span>

                    </a>

                </li>

                <li>

                    <a href="usuarios">

                        <i class="fa fa-user"></i>
                        <span>Usuarios</span>

                    </a>

                </li>';
            }

            // El enlace a Categorías solo lo ven Administrador y Especial
            if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Especial") {

                echo '<li>

                    <a href="categorias">

                        <i class="fa fa-th"></i>
                        <span>Categorías</span>

                    </a>

                </li>';
            }
            if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Especial") {

                echo '<li>

                    <a href="catalogo-maestro">

                        <i class="fa fa-database"></i>
                        <span>Catálogo Maestro</span>

                    </a>

                </li>';
            }
                       // El enlace a Productos ahora también lo ve el Vendedor
            if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Especial" || $_SESSION["perfil"] == "Vendedor") {
                
                echo '<li>

                    <a href="productos">

                        <i class="fa fa-product-hunt"></i>
                        <span>Productos</span>

                    </a>

                </li>';
            }
            
            // ✅ NUEVO MENÚ DE SOLICITUDES DE STOCK
            if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Especial" || $_SESSION["perfil"] == "Vendedor" || $_SESSION["perfil"] == "Contador" || $_SESSION["perfil"] == "Transportador") {
                
                echo '<li class="treeview">
                  <a href="#">
                    <i class="fa fa-cubes"></i>
                    <span>Solicitudes de Stock</span>';
                
                // Mostrar contador de notificaciones para Transportador y Administrador
                if ($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador") {
                    echo '<small class="label pull-right bg-red" id="contadorMenuSolicitudes" style="display: none;">0</small>';
                }
                
                echo '<span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                  </a>
                  <ul class="treeview-menu">';
                
                // Opción "Ver Solicitudes" - Para todos los perfiles
                echo '<li>
                        <a href="solicitudes-stock">
                          <i class="fa fa-list"></i>
                          <span>Ver Solicitudes</span>';
                          
                // Agregar badge de notificación para transportadores
                if ($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador") {
                    echo '<small class="label pull-right bg-yellow" id="badgeSolicitudesPendientes" style="display: none;">0</small>';
                }
                          
                echo '    </a>
                      </li>';
                
            // Opción "Nueva Solicitud" - Solo para NO transportadores
            if ($_SESSION["perfil"] != "Transportador") {
                echo '<li>
                        <a href="crear-solicitud-stock">
                        <i class="fa fa-plus"></i>
                        <span>Nueva Solicitud</span>
                        </a>
                    </li>';
            }
                
                echo '</ul>
                </li>';
            }            

            if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor" || $_SESSION["perfil"] == "Contador") {

                echo '<li>

                    <a href="clientes">

                        <i class="fa fa-users"></i>
                        <span>Clientes</span>

                    </a>

                </li>';
            }

            if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor" || $_SESSION["perfil"] == "Contador") {

                echo '<li class="treeview">

                <a href="#">

                    <i class="fa fa-list-ul"></i>
                    
                    <span>Ventas</span>
                    
                    <span class="pull-right-container">
                    
                        <i class="fa fa-angle-left pull-right"></i>

                    </span>

                </a>

                <ul class="treeview-menu">
                    
                    <li>

                        <a href="ventas">
                            
                            <i class="fa fa-circle-o"></i>
                            <span>Administrar ventas</span>

                        </a>

                    </li>

                    <li>

                        <a href="crear-venta">
                            
                            <i class="fa fa-circle-o"></i>
                            <span>Crear venta</span>

                        </a>

                    </li>';

                if ($_SESSION["perfil"] == "Administrador") {

                    echo '<li>

                        <a href="reportes">
                            
                            <i class="fa fa-circle-o"></i>
                            <span>Reporte de ventas</span>

                        </a>

                    </li>';
                    echo '<li><a href="reporte-detallado"><span>Reporte Detallado</span></a></li>';
                }



                echo '</ul>

            </li>';
            }

            /*=============================================
MENÚ CONTABILIDAD CON PERMISOS DETALLADOS
=============================================*/
// El menú desplegable de Contabilidad será visible para Administrador, Contador y Vendedor.
if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Contador" || $_SESSION["perfil"] == "Vendedor") {

    echo '<li class="treeview">
            <a href="#">
                <i class="fa fa-calculator"></i>
                <span>Contabilidad</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">';

    // El enlace a "Contabilidad" y "Entradas" solo lo ven Administrador y Contador.
    if($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Contador"){
        echo '
         <li>
                <a href="reportes">
                    <i class="fa fa-line-chart"></i>
                    <span>Reportes</span>
                </a>
              </li>
        
        <li>
                        <a href="reporte-detallado">
                    <i class="fa fa-line-chart"></i>
                    <span>Reporte detallado</span>
                </a>
              </li>
        
        <li>
                <a href="contabilidad">
                    <i class="fa fa-circle-o"></i>
                    <span>Contabilidad</span>
                </a>
              </li>';
    }

    // El enlace a "Gastos" y "Crear gastos" lo ven los tres perfiles.
    echo '<li>
            <a href="gastos">
                <i class="fa fa-circle-o"></i>
                <span>Gastos</span>
            </a>
          </li>
          <li>
            <a href="crear-gastos">
                <i class="fa fa-circle-o"></i>
                <span>Crear gastos</span>
            </a>
          </li>';

    // El enlace a "Entradas" y "Crear entradas" solo lo ven Administrador y Contador.
    if($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Contador"){
        echo '<li>
                <a href="entradas">
                    <i class="fa fa-circle-o"></i>
                    <span>Entradas</span>
                </a>
              </li>
              <li>
                <a href="crear-entradas">
                    <i class="fa fa-circle-o"></i>
                    <span>Crear entradas</span>
                </a>
              </li>
                <li class="">
                <a href="medios-pago">
                    <i class="fa fa-credit-card"></i>
                    <span>Medios de Pago</span>
                </a>
            </li>';
    }

    echo '  </ul>
          </li>';
}

            if ($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Contador" || $_SESSION["perfil"] == "Vendedor") {
                echo '<li class="treeview">

                        <a href="#">

                            <i class="fa fa-clipboard"></i>
                            
                            <span>Cotizaciones</span>
                            
                            <span class="pull-right-container">
                            
                                <i class="fa fa-angle-left pull-right"></i>

                            </span>

                        </a>
                        
                        <ul class="treeview-menu">
                            
                            <li>

                                <a href="cotizacion">
                                    
                                    <i class="fa fa-circle-o"></i>
                                    <span>Administrar COTIZ.</span>

                                </a>

                            </li>

                            <li>

                                <a href="crear-cotizacion">
                                    
                                    <i class="fa fa-circle-o"></i>
                                    <span>Crear cotizacion</span>

                                </a>

                            </li>
                            
                        </ul>
                    </li>';
            }

            ?>

        </ul>

    </section>

</aside>

<!-- SCRIPT PARA ACTUALIZAR CONTADORES DEL MENÚ -->
<script>
$(document).ready(function() {
    
    // Solo para transportadores y administradores
    <?php if($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador"): ?>
    
    // Función para actualizar contadores del menú
    function actualizarContadoresMenu() {
        $.ajax({
            url: 'ajax/notificaciones-solicitudes.ajax.php',
            method: 'POST',
            data: { accion: 'obtener_pendientes' },
            dataType: 'json',
            success: function(response) {
                if(response.success && response.data.contador > 0) {
                    // Actualizar contador en el menú principal
                    $('#contadorMenuSolicitudes').text(response.data.contador).show();
                    
                    // Actualizar badge en submenu
                    $('#badgeSolicitudesPendientes').text(response.data.contador).show();
                } else {
                    $('#contadorMenuSolicitudes').hide();
                    $('#badgeSolicitudesPendientes').hide();
                }
            },
            error: function() {
                console.log('Error al actualizar contadores del menú');
            }
        });
    }
    
    // Actualizar al cargar
    actualizarContadoresMenu();
    
    // Actualizar cada 30 segundos
    setInterval(actualizarContadoresMenu, 30000);
    
    <?php endif; ?>
    
});
</script>