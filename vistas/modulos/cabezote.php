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
    console.log("🔔 Sistema de notificaciones iniciado");
    
    <?php if($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador"): ?>
    
    // TEST INICIAL
    setTimeout(function() {
        console.log("🧪 Ejecutando test de notificaciones...");
        testNotificaciones();
    }, 2000);
    
    <?php endif; ?>
});

// Función de test simplificada
function testNotificaciones() {
    console.log("📤 Enviando petición de prueba...");
    
    $.ajax({
        url: 'ajax/notificaciones-solicitudes.ajax.php',
        method: 'POST',
        data: { accion: 'obtener_pendientes' },
        dataType: 'text', // Cambiar a text temporalmente para debug
        success: function(response) {
            console.log("📥 Respuesta recibida (raw):");
            console.log(response);
            
            try {
                const jsonData = JSON.parse(response);
                console.log("✅ JSON parseado correctamente:");
                console.log(jsonData);
                
                if(jsonData.success && jsonData.data) {
                    const contador = jsonData.data.contador || 0;
                    console.log("🔢 Contador de solicitudes:", contador);
                    
                    // Actualizar contador
                    if(contador > 0) {
                        $('#contadorSolicitudes').text(contador).show().addClass('label-info');
                        console.log("✅ Contador mostrado en UI");
                    } else {
                        $('#contadorSolicitudes').hide();
                        console.log("👻 Sin solicitudes - contador oculto");
                    }
                }
                
            } catch(e) {
                console.error("❌ Error parseando JSON:", e);
                console.error("Respuesta que causó error:", response);
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error AJAX:");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Response:", xhr.responseText);
        }
    });
}

// Función manual para debug
function manualTestNotif() {
    console.log("🔄 Test manual iniciado");
    testNotificaciones();
}
</script>