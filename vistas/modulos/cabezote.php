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
21				// MOSTRAR SUCURSALES SOLO PARA ADMINISTRADORES
22				if($_SESSION["perfil"] == "Administrador"){ ?>
23				
24				<!-- ICONO SUCURSALES -->
25				<li class="dropdown">
26					<a href="sucursales" title="Administrar Sucursales" class="dropdown-toggle-sucursales">
27						<i class="fa fa-building" style="font-size: 18px; color: #fff;"></i>
28						<span class="hidden-xs" style="margin-left: 5px;">Sucursales</span>
29					</a>
30				</li>
31
32				<?php } ?>
				
				<li class="dropdown user user-menu">
					
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">

					<?php

					if($_SESSION["foto"] != ""){

						echo '<img src="'.$_SESSION["foto"].'" class="user-image">';

					}else{


						echo '<img src="vistas/img/usuarios/default/anonymous.png" class="user-image">';

					}


					?>
						
						<span class="hidden-xs"><?php  echo $_SESSION["nombre"]; ?></span>

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

	<!-- ESTILOS PERSONALIZADOS PARA SUCURSALES -->
4	<style>
5	.dropdown-toggle-sucursales {
6		display: flex !important;
7		align-items: center;
8		padding: 15px 15px;
9		color: #fff;
10		text-decoration: none;
11		transition: background-color 0.3s ease;
12	}
13
14	.dropdown-toggle-sucursales:hover {
15		background-color: rgba(255,255,255,0.1);
16		color: #fff;
17		text-decoration: none;
18	}
19
20	.navbar-nav > li > .dropdown-toggle-sucursales {
21		padding-top: 15px;
22		padding-bottom: 15px;
23		line-height: 20px;
24	}
25
26	@media (max-width: 767px) {
27		.dropdown-toggle-sucursales .hidden-xs {
28			display: none !important;
29		}
30		.dropdown-toggle-sucursales {
31			padding: 15px 10px;
32		}
33	}
34	</style>

 </header>