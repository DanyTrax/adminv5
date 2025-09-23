<header class="main-header">
	
	<!--=====================================
	LOGOTIPO
	======================================-->
	<a href="inicio" class="logo">
		
		<!-- logo mini -->
		<span class="logo-mini">
			
			<img src="vistas/img/plantilla/icono-blanco.png" class="img-responsive" style="padding-top:11px">

		</span>

		<!-- logo normal -->

		<span class="logo-lg">
			
			<img src="vistas/img/plantilla/logo-blanco-lineal.png" class="img-responsive" style="padding:2px 0px 0px 10px">

		</span>

	</a>

	<!--=====================================
	BARRA DE NAVEGACIÓN
	======================================-->
	<nav class="navbar navbar-static-top" role="navigation">
		
		<!-- Botón de navegación -->

	 	<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        	
        	<span class="sr-only">Toggle navigation</span>
      	
      	</a>

		<!-- perfil de usuario -->

		<div class="navbar-custom-menu">
				
			<ul class="nav navbar-nav">

				<?php 
				// MOSTRAR SUCURSALES SOLO PARA ADMINISTRADORES
				if($_SESSION["perfil"] == "Administrador"){ ?>
				
				<!-- ICONO SUCURSALES -->
				<li class="dropdown">
					<a href="sucursales" title="Administrar Sucursales" class="dropdown-toggle-sucursales">
						<i class="fa fa-building" style="font-size: 18px; color: #fff;"></i>
						<span class="hidden-xs" style="margin-left: 5px;">Sucursales</span>
					</a>
				</li>

				<?php } ?>

				<?php 
				// MOSTRAR NOTIFICACIONES DE SOLICITUDES PARA TRANSPORTADOR Y ADMINISTRADOR
				if($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador"){ ?>
				
				<!-- CAMPANA DE NOTIFICACIONES -->
				<li class="dropdown notifications-menu" id="notificacionesSolicitudes">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<i class="fa fa-bell-o" style="font-size: 18px;"></i>
						<span class="label label-warning" id="contadorSolicitudes" style="display: none;">0</span>
					</a>
					<ul class="dropdown-menu">
						<li class="header" id="headerNotificaciones">No hay solicitudes pendientes</li>
						<li>
							<!-- Lista interna de notificaciones -->
							<ul class="menu" id="listaSolicitudesNotificaciones">
								<!-- Aquí se cargan las notificaciones dinámicamente -->
							</ul>
						</li>
						<li class="footer">
							<a href="solicitudes-stock">Ver todas las solicitudes</a>
						</li>
					</ul>
				</li>

				<?php } ?>
				
				<li class="dropdown user user-menu">
					
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">

					<?php

					if($_SESSION["foto"] != ""){

						echo '<img src="'.$_SESSION["foto"].'" class="user-image">';

					}else{

						echo '<img src="vistas/img/usuarios/default/anonymous.png" class="user-image">';

					}

					?>
						
						<span class="hidden-xs"><?php echo $_SESSION["nombre"]; ?></span>

					</a>

					<!-- Dropdown-toggle -->

					<ul class="dropdown-menu">
						
						<li class="user-body">
							
							<div class="pull-right">
								
								<a href="salir" class="btn btn-default btn-flat">Salir</a>

							</div>

						</li>

					</ul>

				</li>

			</ul>

		</div>

	</nav>

	<!-- ESTILOS PERSONALIZADOS -->
	<style>
	.dropdown-toggle-sucursales {
		display: flex !important;
		align-items: center;
		padding: 15px 15px;
		color: #fff;
		text-decoration: none;
		transition: background-color 0.3s ease;
	}

	.dropdown-toggle-sucursales:hover {
		background-color: rgba(255,255,255,0.1);
		color: #fff;
		text-decoration: none;
	}

	.navbar-nav > li > .dropdown-toggle-sucursales {
		padding-top: 15px;
		padding-bottom: 15px;
		line-height: 20px;
	}

	/* ESTILOS PARA NOTIFICACIONES */
	.notifications-menu .label {
		position: absolute;
		top: 9px;
		right: 7px;
		font-size: 9px;
		font-weight: normal;
		min-width: 15px;
		height: 15px;
		line-height: 1.4;
		text-align: center;
		padding: 2px 4px;
		border-radius: 50%;
	}

	.notifications-menu .dropdown-menu {
		width: 280px;
		padding: 0;
		margin: 0;
		top: 100%;
	}

	.notifications-menu .dropdown-menu .header {
		padding: 7px 10px;
		border-bottom: 1px solid #f4f4f4;
		color: #444444;
		background-color: #ffffff;
		font-size: 14px;
		font-weight: 600;
	}

	.notifications-menu .dropdown-menu .menu {
		list-style: none;
		padding: 0;
		margin: 0;
		max-height: 250px;
		overflow-y: auto;
	}

	.notifications-menu .dropdown-menu .menu li a {
		color: #444;
		overflow: hidden;
		text-overflow: ellipsis;
		padding: 10px 10px;
		border-bottom: 1px solid #f4f4f4;
		text-decoration: none;
		font-size: 13px;
		display: block;
	}

	.notifications-menu .dropdown-menu .menu li a:hover {
		background-color: #f4f4f4;
		text-decoration: none;
	}

	.notifications-menu .dropdown-menu .footer {
		background-color: #f4f4f4;
		padding: 7px 10px;
		border-top: 1px solid #eeeeee;
		text-align: center;
		font-size: 12px;
	}

	.notifications-menu .dropdown-menu .footer a {
		color: #444;
		text-decoration: none;
		font-weight: 600;
	}

	.notifications-menu .dropdown-menu .footer a:hover {
		color: #337ab7;
	}

	.notif-item-nueva {
		background-color: #fff3cd !important;
		border-left: 3px solid #f39c12;
	}

	.notif-tiempo {
		color: #999;
		font-size: 11px;
	}

	.notif-sucursal {
		color: #666;
		font-weight: 500;
	}

	@media (max-width: 767px) {
		.dropdown-toggle-sucursales .hidden-xs {
			display: none !important;
		}
		.dropdown-toggle-sucursales {
			padding: 15px 10px;
		}
		.notifications-menu .dropdown-menu {
			width: 250px;
		}
	}
	</style>

</header>

<!-- SCRIPT PARA NOTIFICACIONES -->
<script>
$(document).ready(function() {
    // Solo cargar notificaciones para transportadores y administradores
    <?php if($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador"): ?>
    
    // Cargar notificaciones al iniciar
    cargarNotificacionesSolicitudes();
    
    // Actualizar cada 30 segundos
    setInterval(cargarNotificacionesSolicitudes, 30000);
    
    // Marcar como vistas cuando se abre el dropdown
    $('#notificacionesSolicitudes').on('show.bs.dropdown', function () {
        marcarNotificacionesComoVistas();
    });
    
    <?php endif; ?>
});

// Función para cargar notificaciones
function cargarNotificacionesSolicitudes() {
    $.ajax({
        url: 'ajax/notificaciones-solicitudes.ajax.php',
        method: 'POST',
        data: { accion: 'obtener_pendientes' },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                actualizarContadorNotificaciones(response.data.contador);
                actualizarListaNotificaciones(response.data.solicitudes);
            }
        },
        error: function() {
            console.log('Error al cargar notificaciones de solicitudes');
        }
    });
}

// Actualizar contador en la campana
function actualizarContadorNotificaciones(contador) {
    const $contador = $('#contadorSolicitudes');
    
    if(contador > 0) {
        $contador.text(contador).show();
        $contador.removeClass('label-success label-info').addClass('label-warning');
        
        // Animación de pulso para nuevas notificaciones
        $contador.addClass('animated pulse');
        setTimeout(() => $contador.removeClass('animated pulse'), 1000);
    } else {
        $contador.hide();
    }
}

// Actualizar lista de notificaciones
function actualizarListaNotificaciones(solicitudes) {
    const $header = $('#headerNotificaciones');
    const $lista = $('#listaSolicitudesNotificaciones');
    
    if(solicitudes.length === 0) {
        $header.text('No hay solicitudes pendientes');
        $lista.html('<li><a href="#" style="text-align: center; color: #999;"><i class="fa fa-check"></i> Todo al día</a></li>');
        return;
    }
    
    // Actualizar header
    const texto = solicitudes.length === 1 ? 
        '1 solicitud pendiente' : 
        solicitudes.length + ' solicitudes pendientes';
    $header.text(texto);
    
    // Actualizar lista
    let html = '';
    solicitudes.forEach(function(solicitud) {
        const tiempoTranscurrido = calcularTiempoTranscurrido(solicitud.fecha_solicitud);
        const esNueva = (new Date() - new Date(solicitud.fecha_solicitud)) < (30 * 60 * 1000); // 30 minutos
        
        html += `
            <li>
                <a href="solicitudes-stock" class="${esNueva ? 'notif-item-nueva' : ''}">
                    <i class="fa fa-cubes text-yellow"></i>
                    <strong>${solicitud.numero_solicitud}</strong><br>
                    <span class="notif-sucursal">${solicitud.nombre_sucursal_solicitante}</span><br>
                    <small class="notif-tiempo">
                        <i class="fa fa-clock-o"></i> ${tiempoTranscurrido}
                        <span class="pull-right">${solicitud.total_productos} prod.</span>
                    </small>
                </a>
            </li>
        `;
    });
    
    $lista.html(html);
}

// Marcar notificaciones como vistas
function marcarNotificacionesComoVistas() {
    $.ajax({
        url: 'ajax/notificaciones-solicitudes.ajax.php',
        method: 'POST',
        data: { accion: 'marcar_como_vistas' },
        success: function() {
            // Remover clases de "nueva" después de un momento
            setTimeout(() => {
                $('.notif-item-nueva').removeClass('notif-item-nueva');
            }, 2000);
        }
    });
}

// Calcular tiempo transcurrido
function calcularTiempoTranscurrido(fechaSolicitud) {
    const ahora = new Date();
    const fecha = new Date(fechaSolicitud);
    const diff = Math.floor((ahora - fecha) / 1000); // diferencia en segundos
    
    if (diff < 60) return 'Hace un momento';
    if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
    return `Hace ${Math.floor(diff / 86400)} días`;
}

// Función para mostrar notificación cuando llega una nueva solicitud
function mostrarNotificacionNuevaSolicitud(solicitud) {
    // Mostrar notificación toast o similar
    if (typeof swal !== 'undefined') {
        swal({
            title: 'Nueva Solicitud',
            text: `Solicitud ${solicitud.numero_solicitud} de ${solicitud.nombre_sucursal_solicitante}`,
            type: 'info',
            timer: 3000,
            showConfirmButton: false,
            position: 'top-right',
            toast: true
        });
    }
    
    // Actualizar contador inmediatamente
    cargarNotificacionesSolicitudes();
}
</script>