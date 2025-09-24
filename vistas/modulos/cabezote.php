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

<style>
/* Estilos para notificaciones */
.notifications-menu .dropdown-menu {
    width: 280px;
    max-width: 280px;
    left: auto;
    right: 0;
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
}

.notifications-menu .dropdown-menu .menu li a:hover {
    background-color: #f5f5f5;
    text-decoration: none;
}

.notifications-menu .dropdown-menu .menu li:last-child a {
    border-bottom: none;
}

.notifications-menu .dropdown-menu .header {
    background-color: #3c8dbc;
    color: white;
    padding: 10px;
    text-align: center;
    font-weight: bold;
}

/* Animación para el contador */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.animated.pulse {
    animation: pulse 0.5s ease-in-out;
}

/* Responsive */
@media (max-width: 480px) {
    .notifications-menu .dropdown-menu {
        width: 250px;
    }
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
    console.log("🔔 Sistema de notificaciones iniciado");
    
    <?php if($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador"): ?>
    
    let contadorAnterior = 0;
    let notificacionesCache = [];
    
    // Cargar notificaciones al iniciar
    setTimeout(function() {
        console.log("🔄 Cargando notificaciones iniciales...");
        cargarNotificacionesCompletas();
    }, 2000);
    
    // Actualizar cada 30 segundos
    setInterval(cargarNotificacionesCompletas, 30000);
    
    // Marcar como vistas cuando se abre el dropdown
    $('#notificacionesSolicitudes').on('show.bs.dropdown', function () {
        console.log("👁️ Dropdown abierto - marcando como vistas");
        marcarNotificacionesComoVistas();
    });
    
    <?php else: ?>
    console.log("ℹ️ Usuario sin permisos para notificaciones");
    <?php endif; ?>
});

// Función principal completa
function cargarNotificacionesCompletas() {
    console.log("🔄 Cargando notificaciones completas...");
    
    $.ajax({
        url: 'ajax/notificaciones-solicitudes.ajax.php',
        method: 'POST',
        data: { accion: 'obtener_pendientes' },
        dataType: 'json',
        timeout: 15000,
        success: function(response) {
            console.log("📥 Respuesta completa recibida:", response);
            
            if(response.success && response.data) {
                const contador = parseInt(response.data.contador) || 0;
                const solicitudes = response.data.solicitudes || [];
                
                console.log("✅ Procesando datos:");
                console.log("- Contador:", contador);
                console.log("- Solicitudes:", solicitudes.length);
                
                // Detectar nuevas solicitudes
                if(contadorAnterior > 0 && contador > contadorAnterior) {
                    console.log("🔔 Nueva solicitud detectada!");
                    mostrarNotificacionNuevaSolicitud();
                }
                
                contadorAnterior = contador;
                notificacionesCache = solicitudes;
                
                // ✅ ACTUALIZAR TANTO CONTADOR COMO LISTA
                actualizarContadorNotificaciones(contador);
                actualizarListaNotificaciones(solicitudes);
                
            } else {
                console.warn("⚠️ Respuesta sin datos válidos");
                actualizarContadorNotificaciones(0);
                mostrarListaVacia();
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error AJAX completo:");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Response:", xhr.responseText);
            
            // Mostrar error en el dropdown
            mostrarErrorEnDropdown();
        }
    });
}

// Actualizar contador (ya funciona)
function actualizarContadorNotificaciones(contador) {
    console.log("🔢 Actualizando contador:", contador);
    
    const $contador = $('#contadorSolicitudes');
    
    if(!$contador.length) {
        console.warn("⚠️ Elemento #contadorSolicitudes no encontrado");
        return;
    }
    
    if(contador > 0) {
        $contador.text(contador).show();
        
        // Color según cantidad
        $contador.removeClass('label-success label-info label-danger label-warning');
        
        if(contador >= 10) {
            $contador.addClass('label-danger');
        } else if(contador >= 5) {
            $contador.addClass('label-warning');
        } else {
            $contador.addClass('label-info');
        }
        
        console.log("✅ Contador actualizado:", contador);
    } else {
        $contador.hide();
        console.log("👻 Contador oculto (sin solicitudes)");
    }
}

// ✅ NUEVA FUNCIÓN: Actualizar lista de notificaciones en el dropdown
function actualizarListaNotificaciones(solicitudes) {
    console.log("📝 Actualizando lista del dropdown con", solicitudes.length, "solicitudes");
    
    const $header = $('#headerNotificaciones');
    const $lista = $('#listaSolicitudesNotificaciones');
    
    if(!$header.length || !$lista.length) {
        console.error("❌ Elementos del dropdown no encontrados");
        console.log("Header encontrado:", $header.length > 0);
        console.log("Lista encontrada:", $lista.length > 0);
        return;
    }
    
    // ✅ ACTUALIZAR HEADER
    if(solicitudes.length === 0) {
        $header.html('<i class="fa fa-check text-success"></i> No hay solicitudes pendientes');
        mostrarListaVacia();
        return;
    }
    
    const textoHeader = solicitudes.length === 1 ? 
        'Tienes 1 solicitud pendiente' : 
        `Tienes ${solicitudes.length} solicitudes pendientes`;
    $header.html(`<i class="fa fa-bell text-yellow"></i> ${textoHeader}`);
    
    // ✅ GENERAR HTML PARA LA LISTA
    let html = '';
    
    solicitudes.forEach(function(solicitud, index) {
        if(index < 8) { // Máximo 8 solicitudes
            
            const tiempoTranscurrido = solicitud.fecha_relativa || 'Sin fecha';
            const tipoIcon = solicitud.tipo_solicitud === 'remision' ? 'fa-file-text' : 'fa-cubes';
            const sucursalCorta = truncarTexto(solicitud.nombre_sucursal_solicitante, 25);
            const tieneObservaciones = solicitud.detalle_adicional && 
                                     solicitud.detalle_adicional.trim() !== '' && 
                                     solicitud.detalle_adicional.toLowerCase() !== 'null';
            
            html += `
                <li>
                    <a href="solicitudes-stock" title="Ver solicitud ${solicitud.numero_solicitud}">
                        <i class="fa ${tipoIcon} text-yellow" style="margin-right: 8px;"></i>
                        <div style="display: inline-block; width: calc(100% - 20px);">
                            <strong style="color: #337ab7;">${solicitud.numero_solicitud}</strong>
                            <span class="pull-right text-muted" style="font-size: 10px;">
                                ${solicitud.total_productos}p
                            </span>
                            <br>
                            <span title="${solicitud.nombre_sucursal_solicitante}">
                                ${sucursalCorta}
                            </span>
                            <br>
                            <small style="color: #999;">
                                <i class="fa fa-clock-o"></i> ${tiempoTranscurrido}
                                ${tieneObservaciones ? '<i class="fa fa-comment text-info" title="Con observaciones"></i>' : ''}
                            </small>
                        </div>
                    </a>
                </li>
            `;
        }
    });
    
    // ✅ AGREGAR INDICADOR SI HAY MÁS DE 8
    if(solicitudes.length > 8) {
        html += `
            <li>
                <a href="solicitudes-stock" style="text-align: center; background-color: #f0f0f0; font-style: italic; color: #666;">
                    <i class="fa fa-plus-circle"></i> 
                    Ver ${solicitudes.length - 8} solicitudes más...
                </a>
            </li>
        `;
    }
    
    // ✅ ACTUALIZAR EL DOM
    $lista.html(html);
    console.log("✅ Lista del dropdown actualizada correctamente");
}

// ✅ FUNCIÓN: Mostrar lista vacía
function mostrarListaVacia() {
    $('#listaSolicitudesNotificaciones').html(`
        <li>
            <a href="#" style="text-align: center; color: #28a745; padding: 20px;">
                <i class="fa fa-check-circle" style="font-size: 24px;"></i><br>
                <strong>¡Todo al día!</strong><br>
                <small>No hay solicitudes pendientes</small>
            </a>
        </li>
    `);
}

// ✅ FUNCIÓN: Mostrar error en dropdown
function mostrarErrorEnDropdown() {
    $('#headerNotificaciones').html('<i class="fa fa-exclamation-triangle text-danger"></i> Error cargando');
    $('#listaSolicitudesNotificaciones').html(`
        <li>
            <a href="#" style="text-align: center; color: #dc3545; padding: 15px;">
                <i class="fa fa-exclamation-triangle"></i><br>
                <strong>Error de conexión</strong><br>
                <small>No se pudieron cargar las notificaciones</small>
            </a>
        </li>
    `);
}

// ✅ FUNCIÓN: Truncar texto
function truncarTexto(texto, longitud) {
    if (!texto || texto.length <= longitud) return texto || 'Sin nombre';
    return texto.substring(0, longitud - 3) + '...';
}

// ✅ FUNCIÓN: Marcar como vistas
function marcarNotificacionesComoVistas() {
    $.ajax({
        url: 'ajax/notificaciones-solicitudes.ajax.php',
        method: 'POST',
        data: { accion: 'marcar_como_vistas' },
        success: function() {
            console.log("👁️ Notificaciones marcadas como vistas");
        },
        error: function() {
            console.warn("⚠️ Error marcando como vistas (no crítico)");
        }
    });
}

// ✅ FUNCIÓN: Notificación de nueva solicitud
function mostrarNotificacionNuevaSolicitud() {
    console.log("🔔 Nueva solicitud detectada!");
    
    if (typeof swal !== 'undefined') {
        swal({
            title: '¡Nueva Solicitud!',
            text: 'Se ha recibido una nueva solicitud de stock',
            type: 'info',
            timer: 4000,
            showConfirmButton: false
        });
    }
    
    // Efecto visual en la campana
    const $campana = $('.fa-bell-o');
    if($campana.length) {
        $campana.addClass('fa-spin');
        setTimeout(() => $campana.removeClass('fa-spin'), 1000);
    }
}

// ✅ FUNCIÓN PÚBLICA: Actualizar después de crear solicitud
function actualizarNotificacionesDespuesDeCrear() {
    console.log("🔄 Actualizando notificaciones después de crear solicitud");
    setTimeout(() => {
        cargarNotificacionesCompletas();
    }, 2000);
}

// ✅ FUNCIÓN DE DEBUG MANUAL
function debugNotificacionesCompleto() {
    console.log("🔍 DEBUG MANUAL COMPLETO");
    console.log("Elementos del DOM:");
    console.log("- Contador:", $('#contadorSolicitudes').length > 0 ? "✅" : "❌");
    console.log("- Header:", $('#headerNotificaciones').length > 0 ? "✅" : "❌");
    console.log("- Lista:", $('#listaSolicitudesNotificaciones').length > 0 ? "✅" : "❌");
    console.log("- Dropdown:", $('#notificacionesSolicitudes').length > 0 ? "✅" : "❌");
    
    cargarNotificacionesCompletas();
}
</script>