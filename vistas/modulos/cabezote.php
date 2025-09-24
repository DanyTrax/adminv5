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
				<!-- En el menú superior, la campana debería ser así: -->
				<li class="dropdown notifications-menu" id="notificacionesSolicitudes">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-bell-o"></i>
						<span class="label label-warning" id="contadorSolicitudes" style="display: none;">0</span>
					</a>
					<ul class="dropdown-menu">
						<li class="header" id="headerNotificaciones">
							<i class="fa fa-bell"></i> Sin notificaciones
						</li>
						<li>
							<ul class="menu" id="listaSolicitudesNotificaciones">
								<li>
									<a href="#" style="text-align: center; padding: 15px;">
										<i class="fa fa-spinner fa-spin"></i><br>
										<small>Cargando notificaciones...</small>
									</a>
								</li>
							</ul>
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
	/* ANIMACIONES PARA NOTIFICACIONES */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.animated.pulse {
    animation: pulse 0.5s ease-in-out;
}

/* EFECTO HOVER MEJORADO */
.notifications-menu .dropdown-toggle:hover {
    background-color: rgba(255,255,255,0.1);
}

/* INDICADOR DE NUEVA SOLICITUD */
.notif-item-nueva {
    background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%) !important;
    border-left: 4px solid #f39c12 !important;
    position: relative;
}

.notif-item-nueva::before {
    content: '';
    position: absolute;
    top: 50%;
    right: 8px;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    background-color: #f39c12;
    border-radius: 50%;
    box-shadow: 0 0 6px rgba(243, 156, 18, 0.6);
}

/* ESTILOS RESPONSIVOS MEJORADOS */
@media (max-width: 480px) {
    .notifications-menu .dropdown-menu {
        width: 220px;
        right: 0;
        left: auto;
    }
    
    .notifications-menu .dropdown-menu .menu li a {
        padding: 8px;
        font-size: 12px;
    }
    
    .notif-sucursal {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 140px;
    }
}
	</style>

</header>

<!-- SCRIPT PARA NOTIFICACIONES -->
<script>
$(document).ready(function() {
    
    console.log("🔔 Iniciando sistema de notificaciones...");
    
    // Solo cargar notificaciones para transportadores y administradores
    <?php if($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador"): ?>
    
    let contadorAnterior = 0;
    let notificacionesCache = [];
    
    // Cargar notificaciones al iniciar (después de un pequeño delay)
    setTimeout(function() {
        console.log("🔄 Cargando notificaciones iniciales...");
        cargarNotificacionesSolicitudes();
    }, 2000);
    
    // Actualizar cada 30 segundos
    setInterval(cargarNotificacionesSolicitudes, 30000);
    
    // Marcar como vistas cuando se abre el dropdown
    $('#notificacionesSolicitudes').on('show.bs.dropdown', function () {
        console.log("👁️ Dropdown abierto - marcando como vistas");
        marcarNotificacionesComoVistas();
    });
    
    <?php else: ?>
    console.log("ℹ️ Usuario sin permisos para notificaciones - Perfil: <?php echo $_SESSION['perfil']; ?>");
    <?php endif; ?>
});

// Función principal para cargar notificaciones
function cargarNotificacionesSolicitudes() {
    
    console.log("🔄 Iniciando carga de notificaciones...");
    
    $.ajax({
        url: 'ajax/notificaciones-solicitudes.ajax.php',
        method: 'POST',
        data: { accion: 'obtener_pendientes' },
        dataType: 'json',
        timeout: 15000,
        beforeSend: function() {
            console.log("📤 Enviando petición AJAX...");
        },
        success: function(response, textStatus, xhr) {
            
            console.log("📥 Respuesta recibida:");
            console.log("Status:", xhr.status);
            console.log("Response:", response);
            
            // Verificar que la respuesta sea válida
            if(typeof response !== 'object' || response === null) {
                console.error("❌ Respuesta no es un objeto JSON válido");
                return;
            }
            
            if(response.success && response.data) {
                const nuevoContador = parseInt(response.data.contador) || 0;
                const solicitudes = response.data.solicitudes || [];
                
                console.log("✅ Datos procesados:");
                console.log("- Contador:", nuevoContador);
                console.log("- Solicitudes:", solicitudes.length);
                
                // Detectar nuevas solicitudes
                if(contadorAnterior > 0 && nuevoContador > contadorAnterior) {
                    console.log("🔔 Nueva solicitud detectada!");
                    mostrarNotificacionNuevaSolicitud();
                }
                
                contadorAnterior = nuevoContador;
                notificacionesCache = solicitudes;
                
                actualizarContadorNotificaciones(nuevoContador);
                actualizarListaNotificaciones(solicitudes);
                
            } else {
                console.warn("⚠️ Respuesta exitosa pero sin datos válidos:", response);
                actualizarContadorNotificaciones(0);
                actualizarListaNotificaciones([]);
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error en AJAX:");
            console.error("- Status:", status);
            console.error("- Error:", error);
            console.error("- Response Text:", xhr.responseText);
            console.error("- Status Code:", xhr.status);
            
            // En caso de error, ocultar contador
            $('#contadorSolicitudes').hide();
            
            // Mostrar mensaje de error en el dropdown si está visible
            if($('#notificacionesSolicitudes').hasClass('open')) {
                $('#listaSolicitudesNotificaciones').html(`
                    <li>
                        <a href="#" style="text-align: center; color: #dc3545; padding: 15px;">
                            <i class="fa fa-exclamation-triangle"></i><br>
                            <small>Error cargando notificaciones</small>
                        </a>
                    </li>
                `);
            }
        }
    });
}

// Actualizar contador en la campana
function actualizarContadorNotificaciones(contador) {
    
    console.log("🔢 Actualizando contador:", contador);
    
    const $contador = $('#contadorSolicitudes');
    
    if(!$contador.length) {
        console.warn("⚠️ Elemento #contadorSolicitudes no encontrado en el DOM");
        return;
    }
    
    if(contador > 0) {
        $contador.text(contador).show();
        
        // Color según cantidad
        $contador.removeClass('label-success label-info label-danger label-warning');
        
        if(contador >= 10) {
            $contador.addClass('label-danger');
            console.log("🔴 Contador crítico (≥10)");
        } else if(contador >= 5) {
            $contador.addClass('label-warning');
            console.log("🟡 Contador alto (≥5)");
        } else {
            $contador.addClass('label-info');
            console.log("🔵 Contador normal (<5)");
        }
        
        // Animación de pulso
        $contador.addClass('animated pulse');
        setTimeout(() => $contador.removeClass('animated pulse'), 600);
        
        console.log("✅ Contador actualizado correctamente");
        
    } else {
        $contador.hide();
        console.log("👻 Contador oculto (sin solicitudes)");
    }
}

// Actualizar lista de notificaciones
function actualizarListaNotificaciones(solicitudes) {
    
    console.log("📝 Actualizando lista con", solicitudes.length, "solicitudes");
    
    const $header = $('#headerNotificaciones');
    const $lista = $('#listaSolicitudesNotificaciones');
    
    if(!$header.length || !$lista.length) {
        console.warn("⚠️ Elementos del dropdown no encontrados en el DOM");
        return;
    }
    
    if(solicitudes.length === 0) {
        $header.html('<i class="fa fa-check text-success"></i> No hay solicitudes pendientes');
        $lista.html(`
            <li>
                <a href="#" style="text-align: center; color: #28a745; padding: 20px;">
                    <i class="fa fa-check-circle" style="font-size: 24px;"></i><br>
                    <strong>¡Todo al día!</strong><br>
                    <small>No hay solicitudes pendientes</small>
                </a>
            </li>
        `);
        console.log("✅ Lista vacía renderizada");
        return;
    }
    
    // Actualizar header
    const texto = solicitudes.length === 1 ? 
        'Tienes 1 solicitud pendiente' : 
        `Tienes ${solicitudes.length} solicitudes pendientes`;
    $header.html(`<i class="fa fa-bell text-yellow"></i> ${texto}`);
    
    // Actualizar lista
    let html = '';
    solicitudes.forEach(function(solicitud, index) {
        
        // Solo mostrar máximo 8 notificaciones
        if(index < 8) {
            const tiempoTranscurrido = solicitud.fecha_relativa || 'Sin fecha';
            const esNueva = (new Date() - new Date(solicitud.fecha_solicitud)) < (30 * 60 * 1000); // 30 minutos
            const tipoIcon = solicitud.tipo_solicitud === 'remision' ? 'fa-file-text' : 'fa-cubes';
            const sucursalCorta = truncarTexto(solicitud.nombre_sucursal_solicitante, 25);
            
            html += `
                <li>
                    <a href="solicitudes-stock" class="${esNueva ? 'notif-item-nueva' : ''}" 
                       title="Ver solicitud ${solicitud.numero_solicitud}">
                        <i class="fa ${tipoIcon} text-yellow" style="margin-right: 8px;"></i>
                        <div style="display: inline-block; width: calc(100% - 20px);">
                            <strong style="color: #337ab7;">${solicitud.numero_solicitud}</strong>
                            <span class="pull-right text-muted" style="font-size: 10px;">
                                ${solicitud.total_productos}p
                            </span>
                            <br>
                            <span class="notif-sucursal" title="${solicitud.nombre_sucursal_solicitante}">
                                ${sucursalCorta}
                            </span>
                            <br>
                            <small class="notif-tiempo">
                                <i class="fa fa-clock-o"></i> ${tiempoTranscurrido}
                                ${solicitud.detalle_adicional && solicitud.detalle_adicional.trim() ? '<i class="fa fa-comment text-info" title="Con observaciones"></i>' : ''}
                            </small>
                        </div>
                    </a>
                </li>
            `;
        }
    });
    
    // Si hay más de 8, agregar indicador
    if(solicitudes.length > 8) {
        html += `
            <li>
                <a href="solicitudes-stock" style="text-align: center; background-color: #f0f0f0; font-style: italic;">
                    <i class="fa fa-plus-circle"></i> 
                    Ver ${solicitudes.length - 8} solicitudes más...
                </a>
            </li>
        `;
    }
    
    $lista.html(html);
    console.log("✅ Lista de notificaciones renderizada correctamente");
}

// Marcar notificaciones como vistas
function marcarNotificacionesComoVistas() {
    $.ajax({
        url: 'ajax/notificaciones-solicitudes.ajax.php',
        method: 'POST',
        data: { accion: 'marcar_como_vistas' },
        success: function(response) {
            console.log("👁️ Notificaciones marcadas como vistas");
            
            // Remover clases de "nueva" después de un momento
            setTimeout(() => {
                $('.notif-item-nueva').removeClass('notif-item-nueva');
            }, 2000);
        },
        error: function() {
            console.warn("⚠️ Error marcando notificaciones como vistas (no crítico)");
        }
    });
}

// Truncar texto para evitar desbordamiento
function truncarTexto(texto, longitud) {
    if (!texto || texto.length <= longitud) return texto || 'Sin nombre';
    return texto.substring(0, longitud - 3) + '...';
}

// Función para mostrar notificación cuando llega una nueva solicitud
function mostrarNotificacionNuevaSolicitud() {
    console.log("🔔 Mostrando notificación de nueva solicitud");
    
    // Notificación toast si SweetAlert está disponible
    if (typeof swal !== 'undefined') {
        swal({
            title: '¡Nueva Solicitud!',
            text: 'Se ha recibido una nueva solicitud de stock',
            type: 'info',
            timer: 4000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    } else {
        console.log("ℹ️ SweetAlert no disponible - notificación omitida");
    }
    
    // Efecto visual en la campana
    const $campana = $('.fa-bell-o');
    if($campana.length) {
        $campana.addClass('fa-spin');
        setTimeout(() => $campana.removeClass('fa-spin'), 1000);
    }
}

// Función pública para refrescar notificaciones después de crear solicitud
function actualizarNotificacionesDespuesDeCrear() {
    console.log("🔄 Actualizando notificaciones después de crear solicitud");
    setTimeout(() => {
        cargarNotificacionesSolicitudes();
    }, 2000);
}

// Función para debug manual
function debugNotificaciones() {
    console.log("🔍 DEBUG MANUAL DE NOTIFICACIONES");
    console.log("Contador anterior:", contadorAnterior);
    console.log("Cache de notificaciones:", notificacionesCache);
    cargarNotificacionesSolicitudes();
}
</script>