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

		<!-- Nombre de la sucursal actual -->
		<div class="navbar-brand" style="color: white; font-size: 20px; font-weight: 500; margin-left: 0px; line-height: 30px; display: flex; align-items: center;">
			<?php
			// Obtener nombre de sucursal desde BD local
			require_once "modelos/conexion.php";
			$stmt = Conexion::conectar()->prepare("SELECT nombre FROM sucursal_local LIMIT 1");
			$stmt->execute();
			$sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
			
			echo '<i class="fa fa-building" style="margin-right: 8px; color: #fff;"></i>';
			echo $sucursal ? $sucursal["nombre"] : 'Sucursal Local';
			?>
		</div>

		<!-- perfil de usuario -->

		<div class="navbar-custom-menu">
				
			<ul class="nav navbar-nav">

				<?php 
				// MOSTRAR GESTIÓN CENTRAL SOLO PARA ADMINISTRADORES
				if($_SESSION["perfil"] == "Administrador"){ ?>
				
				<!-- MENÚ GESTIÓN CENTRAL -->
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Gestión Central">
						<i class="fa fa-cogs" style="font-size: 18px; color: #fff;"></i>
						<span class="hidden-xs" style="margin-left: 5px;">Gestión Central</span>
						<i class="fa fa-caret-down" style="margin-left: 5px;"></i>
					</a>
					<ul class="dropdown-menu" style="width: 200px; left: auto; right: 0;">
						<li>
							<a href="usuarios-central" style="padding: 10px 15px;">
								<i class="fa fa-users" style="margin-right: 8px; color: #337ab7;"></i>
								Usuarios Centrales
							</a>
						</li>
						<li>
							<a href="clientes-central" style="padding: 10px 15px;">
								<i class="fa fa-user-circle" style="margin-right: 8px; color: #f39c12;"></i>
								Clientes Centrales
							</a>
						</li>
						<li>
							<a href="categorias-central" style="padding: 10px 15px;">
								<i class="fa fa-tags" style="margin-right: 8px; color: #9c27b0;"></i>
								Categorías Centrales
							</a>
						</li>
						<li>
							<a href="personalizacion-colores" style="padding: 10px 15px;">
								<i class="fa fa-palette" style="margin-right: 8px; color: #e91e63;"></i>
								Personalización de Colores
							</a>
						</li>
						<li>
							<a href="sucursales" style="padding: 10px 15px;">
								<i class="fa fa-building" style="margin-right: 8px; color: #5cb85c;"></i>
								Sucursales
							</a>
						</li>
						<li>
							<a href="catalogo-maestro" style="padding: 10px 15px;">
								<i class="fa fa-database" style="margin-right: 8px; color: #17a2b8;"></i>
								Catálogo Maestro
							</a>
						</li>
					</ul>
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
						
						<span class="hidden-xs"><?php echo isset($_SESSION["nombre"]) ? $_SESSION["nombre"] : 'Usuario'; ?></span>

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

/* ✅ ESTILOS PARA NOTIFICACIONES - VERSIÓN CORREGIDA */
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

#contadorSolicitudes {
  background-color: #f39c12 !important;
  font-weight: 700 !important;
  font-size: 12px;
}

.notifications-menu .dropdown-menu {
	width: 320px !important;
	max-width: 320px !important;
	left: auto !important;
	right: 0 !important;
	padding: 0 !important;
	border-radius: 4px;
	box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.notifications-menu .dropdown-menu .menu {
	max-height: 350px;
	overflow-y: auto;
	padding: 0 !important;
	margin: 0 !important;
	list-style: none !important;
}

.notifications-menu .dropdown-menu .menu li {
	border-bottom: 1px solid #f0f0f0;
	margin: 0;
	padding: 0;
	list-style: none;
}

.notifications-menu .dropdown-menu .menu li:last-child {
	border-bottom: none;
}

.notifications-menu .dropdown-menu .menu li a {
	display: block !important;
	padding: 12px 15px !important;
	text-decoration: none !important;
	color: #333 !important;
	white-space: normal !important;
	word-wrap: break-word !important;
	font-size: 13px !important;
	line-height: 1.4 !important;
	border: none !important;
	background: white !important;
	position: relative;
	overflow: visible !important;
	text-overflow: initial !important;
}

.notifications-menu .dropdown-menu .menu li a:hover {
	background-color: #f8f9fa !important;
	text-decoration: none !important;
	color: #333 !important;
}

.notifications-menu .dropdown-menu .menu li a:focus,
.notifications-menu .dropdown-menu .menu li a:active {
	background-color: #f8f9fa !important;
	color: #333 !important;
	text-decoration: none !important;
}

.notifications-menu .dropdown-menu .header {
	background-color: #3c8dbc !important;
	color: white !important;
	padding: 12px 15px !important;
	text-align: center !important;
	font-weight: bold !important;
	font-size: 13px !important;
	border-radius: 4px 4px 0 0;
	margin: 0 !important;
	border: none !important;
}

.notifications-menu .dropdown-menu .footer {
	background-color: #f4f4f4 !important;
	text-align: center !important;
	padding: 8px 15px !important;
	border-top: 1px solid #ddd !important;
	border-radius: 0 0 4px 4px;
	margin: 0 !important;
}

.notifications-menu .dropdown-menu .footer a {
	color: #337ab7 !important;
	text-decoration: none !important;
	font-size: 12px !important;
	font-weight: normal !important;
}

.notifications-menu .dropdown-menu .footer a:hover {
	color: #23527c !important;
	text-decoration: underline !important;
}

/* ✅ ESTILOS ESPECÍFICOS PARA EL CONTENIDO DE NOTIFICACIONES */
.notif-solicitud-numero {
	font-weight: bold !important;
	color: #337ab7 !important;
	font-size: 14px !important;
	display: inline-block !important;
	margin-right: 5px !important;
}

.notif-productos-count {
	background-color: #f39c12 !important;
	color: white !important;
	font-size: 10px !important;
	padding: 2px 6px !important;
	border-radius: 10px !important;
	font-weight: bold !important;
	float: right !important;
	margin-top: 2px;
}

.notif-sucursal-nombre {
	display: block !important;
	color: #666 !important;
	font-size: 12px !important;
	margin: 3px 0 !important;
	font-weight: normal !important;
	overflow: hidden !important;
	text-overflow: ellipsis !important;
	white-space: nowrap !important;
	max-width: 200px !important;
}

.notif-tiempo-transcurrido {
	display: block !important;
	color: #999 !important;
	font-size: 11px !important;
	margin-top: 5px !important;
	font-weight: normal !important;
}

.notif-icono-tipo {
	font-size: 16px !important;
	color: #f39c12 !important;
	margin-right: 10px !important;
	float: left !important;
	margin-top: 5px;
}

.notif-contenido {
	margin-left: 30px !important;
	display: block !important;
	overflow: visible !important;
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

/* RESPONSIVE */
@media (max-width: 768px) {
	.notifications-menu .dropdown-menu {
		width: 280px !important;
		right: 10px !important;
	}
	
	.notif-sucursal-nombre {
		max-width: 160px !important;
	}
}

@media (max-width: 480px) {
	.notifications-menu .dropdown-menu {
		width: 250px !important;
		right: 5px !important;
	}
	
	.notif-sucursal-nombre {
		max-width: 140px !important;
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
// ✅ ACTUALIZAR LISTA DE FORMA SEGURA - VERSIÓN MEJORADA
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
				<a href="solicitudes-stock" style="text-align: center; color: #28a745; padding: 20px; display: block;">
					<i class="fa fa-check-circle" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
					<strong style="display: block; font-size: 14px;">¡Todo al día!</strong>
					<small style="display: block; font-size: 12px; color: #666;">No hay solicitudes pendientes</small>
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
	$header.html(`<i class="fa fa-bell"></i> ${texto}`);
	
	// Generar lista con estructura HTML mejorada
	let html = '';
	solicitudes.forEach(function(solicitud, index) {
		if(index < 8) {
			const tiempo = solicitud.fecha_relativa || 'Sin fecha';
			const icono = solicitud.tipo_solicitud === 'remision' ? 'fa-file-text' : 'fa-cubes';
			const sucursal = solicitud.nombre_sucursal_solicitante || 'Sin sucursal';
			const sucursalCorta = sucursal.length > 25 ? sucursal.substring(0, 22) + '...' : sucursal;
			const numeroSolicitud = solicitud.numero_solicitud || 'N/A';
			const totalProductos = solicitud.total_productos || 0;
			
			html += `
				<li>
					<a href="solicitudes-stock" title="Ver solicitud ${numeroSolicitud}">
						<i class="fa ${icono} notif-icono-tipo"></i>
						<div class="notif-contenido">
							<span class="notif-solicitud-numero">${numeroSolicitud}</span>
							<span class="notif-productos-count">${totalProductos}p</span>
							<span class="notif-sucursal-nombre" title="${sucursal}">${sucursalCorta}</span>
							<span class="notif-tiempo-transcurrido">
								<i class="fa fa-clock-o"></i> ${tiempo}
							</span>
						</div>
						<div style="clear: both;"></div>
					</a>
				</li>
			`;
		}
	});
	
	// Agregar link para ver más si hay más de 8
	if(solicitudes.length > 8) {
		html += `
			<li>
				<a href="solicitudes-stock" style="text-align: center; background-color: #f8f9fa; font-style: italic; padding: 10px; display: block;">
					<i class="fa fa-plus-circle" style="margin-right: 5px;"></i>
					<span style="font-size: 12px; color: #666;">Ver ${solicitudes.length - 8} solicitudes más...</span>
				</a>
			</li>
		`;
	}
	
	$lista.html(html);
	console.log("✅ Lista actualizada con estructura mejorada:", solicitudes.length, "solicitudes");
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