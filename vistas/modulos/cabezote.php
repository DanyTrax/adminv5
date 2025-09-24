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
				
				<!-- ✅ CAMPANA DE NOTIFICACIONES - ESTRUCTURA CORREGIDA -->
				<li class="dropdown notifications-menu" id="notificacionesSolicitudes">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						<i class="fa fa-bell-o"></i>
						<span class="label label-info" id="contadorSolicitudes" style="display: none;">0</span>
					</a>
					<ul class="dropdown-menu" style="width: 280px; left: auto; right: 0;">
						<li class="header" id="headerNotificaciones" style="background-color: #3c8dbc; color: white; padding: 10px; text-align: center;">
							<i class="fa fa-bell"></i> Cargando notificaciones...
						</li>
						<li style="height: auto; max-height: 300px; overflow-y: auto;">
							<ul class="menu" id="listaSolicitudesNotificaciones" style="padding: 0; margin: 0; list-style: none;">
								<li>
									<a href="#" style="text-align: center; padding: 15px; display: block; border-bottom: 1px solid #f0f0f0;">
										<i class="fa fa-spinner fa-spin"></i><br>
										<small>Cargando notificaciones...</small>
									</a>
								</li>
							</ul>
						</li>
						<li class="footer" style="background-color: #f4f4f4; text-align: center; padding: 5px;">
							<a href="solicitudes-stock" style="color: #337ab7; text-decoration: none;">
								Ver todas las solicitudes
							</a>
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

</header>

<!-- ✅ ESTILOS UNIFICADOS Y LIMPIOS -->
<style>
/* ESTILOS PARA SUCURSALES */
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

/* ✅ ESTILOS PARA NOTIFICACIONES - CORREGIDOS */
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
	width: 280px !important;
	max-width: 280px !important;
	left: auto !important;
	right: 0 !important;
	padding: 0;
}

.notifications-menu .dropdown-menu .menu {
	max-height: 300px;
	overflow-y: auto;
	padding: 0;
	margin: 0;
	list-style: none;
}

.notifications-menu .dropdown-menu .menu li a {
	display: block;
	padding: 10px;
	border-bottom: 1px solid #f0f0f0;
	text-decoration: none;
	color: #333;
	white-space: normal;
	word-wrap: break-word;
}

.notifications-menu .dropdown-menu .menu li a:hover {
	background-color: #f5f5f5;
	text-decoration: none;
	color: #333;
}

.notifications-menu .dropdown-menu .menu li:last-child a {
	border-bottom: none;
}

.notifications-menu .dropdown-menu .header {
	background-color: #3c8dbc !important;
	color: white !important;
	padding: 10px !important;
	text-align: center !important;
	font-weight: bold;
	border-radius: 0;
	margin: 0;
}

.notifications-menu .dropdown-menu .footer {
	background-color: #f4f4f4 !important;
	text-align: center !important;
	padding: 5px !important;
	border-top: 1px solid #ddd;
}

.notifications-menu .dropdown-menu .footer a {
	color: #337ab7;
	text-decoration: none;
	font-size: 12px;
}

.notifications-menu .dropdown-menu .footer a:hover {
	color: #23527c;
	text-decoration: underline;
}

/* ANIMACIÓN PARA EL CONTADOR */
@keyframes pulse {
	0% { transform: scale(1); }
	50% { transform: scale(1.1); }
	100% { transform: scale(1); }
}

.animated.pulse {
	animation: pulse 0.5s ease-in-out;
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

/* RESPONSIVE */
@media (max-width: 768px) {
	.notifications-menu .dropdown-menu {
		width: 250px !important;
		right: 10px !important;
	}
	
	.notifications-menu .dropdown-menu .menu li a {
		padding: 8px;
		font-size: 12px;
	}
	
	.hidden-xs {
		display: none;
	}
}

@media (max-width: 480px) {
	.notifications-menu .dropdown-menu {
		width: 220px !important;
	}
}
</style>

<!-- ✅ JAVASCRIPT CORREGIDO Y SIMPLIFICADO -->
<script>
$(document).ready(function() {
	console.log("🔔 Iniciando sistema de notificaciones...");
	
	<?php if($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador"): ?>
	
	let contadorAnterior = 0;
	
	// ✅ VERIFICAR QUE LOS ELEMENTOS EXISTAN
	function verificarElementos() {
		const elementos = {
			contador: $('#contadorSolicitudes'),
			header: $('#headerNotificaciones'),
			lista: $('#listaSolicitudesNotificaciones'),
			dropdown: $('#notificacionesSolicitudes')
		};
		
		console.log("🔍 Verificando elementos del DOM:");
		console.log("- Contador:", elementos.contador.length > 0 ? "✅" : "❌");
		console.log("- Header:", elementos.header.length > 0 ? "✅" : "❌");
		console.log("- Lista:", elementos.lista.length > 0 ? "✅" : "❌");
		console.log("- Dropdown:", elementos.dropdown.length > 0 ? "✅" : "❌");
		
		return elementos.contador.length > 0 && elementos.header.length > 0 && 
			   elementos.lista.length > 0 && elementos.dropdown.length > 0;
	}
	
	// ✅ CARGAR NOTIFICACIONES SOLO SI LOS ELEMENTOS EXISTEN
	setTimeout(function() {
		if(verificarElementos()) {
			console.log("✅ Elementos verificados - iniciando carga de notificaciones");
			cargarNotificacionesSeguras();
		} else {
			console.error("❌ Elementos del DOM no encontrados - reintentando en 3 segundos");
			setTimeout(function() {
				if(verificarElementos()) {
					cargarNotificacionesSeguras();
				} else {
					console.error("❌ Elementos aún no disponibles - sistema de notificaciones deshabilitado");
				}
			}, 3000);
		}
	}, 2000);
	
	// Actualizar cada 30 segundos
	setInterval(function() {
		if(verificarElementos()) {
			cargarNotificacionesSeguras();
		}
	}, 30000);
	
	// Marcar como vistas cuando se abre el dropdown
	$(document).on('show.bs.dropdown', '#notificacionesSolicitudes', function () {
		console.log("👁️ Dropdown abierto");
		marcarComoVistas();
	});
	
	<?php endif; ?>
});

// ✅ FUNCIÓN PRINCIPAL SEGURA
function cargarNotificacionesSeguras() {
	console.log("🔄 Cargando notificaciones...");
	
	$.ajax({
		url: 'ajax/notificaciones-solicitudes.ajax.php',
		method: 'POST',
		data: { accion: 'obtener_pendientes' },
		dataType: 'json',
		timeout: 10000,
		success: function(response) {
			console.log("📥 Respuesta recibida:", response);
			
			if(response && response.success && response.data) {
				const contador = parseInt(response.data.contador) || 0;
				const solicitudes = response.data.solicitudes || [];
				
				console.log("✅ Datos:", { contador, solicitudes: solicitudes.length });
				
				actualizarContadorSeguro(contador);
				actualizarListaSegura(solicitudes);
			} else {
				console.warn("⚠️ Respuesta inválida");
				manejarError("Respuesta inválida del servidor");
			}
		},
		error: function(xhr, status, error) {
			console.error("❌ Error AJAX:", { status, error, response: xhr.responseText });
			manejarError("Error de conexión");
		}
	});
}

// ✅ ACTUALIZAR CONTADOR DE FORMA SEGURA
function actualizarContadorSeguro(contador) {
	const $contador = $('#contadorSolicitudes');
	
	if(!$contador.length) {
		console.error("❌ Contador no encontrado");
		return;
	}
	
	if(contador > 0) {
		$contador.text(contador).show();
		
		// Aplicar color
		$contador.removeClass('label-success label-info label-danger label-warning');
		if(contador >= 10) {
			$contador.addClass('label-danger');
		} else if(contador >= 5) {
			$contador.addClass('label-warning');
		} else {
			$contador.addClass('label-info');
		}
		
		// Animación
		$contador.addClass('animated pulse');
		setTimeout(() => $contador.removeClass('animated pulse'), 600);
		
		console.log("✅ Contador actualizado:", contador);
	} else {
		$contador.hide();
		console.log("👻 Sin solicitudes - contador oculto");
	}
}

// ✅ ACTUALIZAR LISTA DE FORMA SEGURA
function actualizarListaSegura(solicitudes) {
	const $header = $('#headerNotificaciones');
	const $lista = $('#listaSolicitudesNotificaciones');
	
	if(!$header.length || !$lista.length) {
		console.error("❌ Elementos de lista no encontrados");
		return;
	}
	
	// Actualizar header
	if(solicitudes.length === 0) {
		$header.html('<i class="fa fa-check text-success"></i> No hay solicitudes pendientes');
		$lista.html(`
			<li>
				<a href="#" style="text-align: center; color: #28a745; padding: 20px;">
					<i class="fa fa-check-circle" style="font-size: 20px;"></i><br>
					<strong>¡Todo al día!</strong><br>
					<small>No hay solicitudes pendientes</small>
				</a>
			</li>
		`);
		console.log("✅ Lista vacía mostrada");
		return;
	}
	
	// Header con conteo
	const texto = solicitudes.length === 1 ? 
		'Tienes 1 solicitud pendiente' : 
		`Tienes ${solicitudes.length} solicitudes pendientes`;
	$header.html(`<i class="fa fa-bell text-yellow"></i> ${texto}`);
	
	// Generar lista
	let html = '';
	solicitudes.forEach(function(solicitud, index) {
		if(index < 8) {
			const tiempo = solicitud.fecha_relativa || 'Sin fecha';
			const icono = solicitud.tipo_solicitud === 'remision' ? 'fa-file-text' : 'fa-cubes';
			const sucursal = solicitud.nombre_sucursal_solicitante || 'Sin sucursal';
			const sucursalCorta = sucursal.length > 25 ? sucursal.substring(0, 22) + '...' : sucursal;
			
			html += `
				<li>
					<a href="solicitudes-stock" title="Ver solicitud ${solicitud.numero_solicitud}">
						<i class="fa ${icono} text-yellow" style="margin-right: 8px;"></i>
						<div style="display: inline-block; width: calc(100% - 20px);">
							<strong style="color: #337ab7;">${solicitud.numero_solicitud}</strong>
							<span class="pull-right text-muted" style="font-size: 10px;">
								${solicitud.total_productos}p
							</span>
							<br>
							<span title="${sucursal}">${sucursalCorta}</span>
							<br>
							<small style="color: #999;">
								<i class="fa fa-clock-o"></i> ${tiempo}
							</small>
						</div>
					</a>
				</li>
			`;
		}
	});
	
	if(solicitudes.length > 8) {
		html += `
			<li>
				<a href="solicitudes-stock" style="text-align: center; background-color: #f8f9fa; font-style: italic;">
					<i class="fa fa-plus-circle"></i> 
					Ver ${solicitudes.length - 8} solicitudes más...
				</a>
			</li>
		`;
	}
	
	$lista.html(html);
	console.log("✅ Lista actualizada con", solicitudes.length, "solicitudes");
}

// ✅ MANEJAR ERRORES
function manejarError(mensaje) {
	const $header = $('#headerNotificaciones');
	const $lista = $('#listaSolicitudesNotificaciones');
	
	if($header.length) {
		$header.html('<i class="fa fa-exclamation-triangle text-danger"></i> Error');
	}
	
	if($lista.length) {
		$lista.html(`
			<li>
				<a href="#" style="text-align: center; color: #dc3545; padding: 15px;">
					<i class="fa fa-exclamation-triangle"></i><br>
					<strong>Error</strong><br>
					<small>${mensaje}</small>
				</a>
			</li>
		`);
	}
}

// ✅ MARCAR COMO VISTAS
function marcarComoVistas() {
	$.ajax({
		url: 'ajax/notificaciones-solicitudes.ajax.php',
		method: 'POST',
		data: { accion: 'marcar_como_vistas' },
		success: function() {
			console.log("👁️ Marcado como vistas");
		}
	});
}

// ✅ FUNCIÓN MANUAL PARA DEBUG
function debugNotificacionesManual() {
	console.log("🔍 DEBUG MANUAL");
	
	// Verificar elementos
	console.log("Elementos:");
	console.log("- #contadorSolicitudes:", $('#contadorSolicitudes').length);
	console.log("- #headerNotificaciones:", $('#headerNotificaciones').length);
	console.log("- #listaSolicitudesNotificaciones:", $('#listaSolicitudesNotificaciones').length);
	
	// Cargar notificaciones
	cargarNotificacionesSeguras();
}
</script>