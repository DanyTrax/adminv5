<?php
require_once "controladores/personalizacion-colores.controlador.php";

// Obtener configuración actual
$configuracionActual = ControladorPersonalizacionColores::ctrMostrarConfiguracionActiva();

// Obtener todas las configuraciones
$todasConfiguraciones = ControladorPersonalizacionColores::ctrObtenerTodasConfiguraciones();

// Procesar acciones
ControladorPersonalizacionColores::ctrActualizarConfiguracion();

if (isset($_GET['activar'])) {
    ControladorPersonalizacionColores::ctrActivarConfiguracion($_GET['activar']);
}

if (isset($_GET['eliminar'])) {
    ControladorPersonalizacionColores::ctrEliminarConfiguracion($_GET['eliminar']);
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Personalización de Colores
            <small>Personaliza la apariencia del sistema</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Personalización de Colores</li>
        </ol>
    </section>

    <section class="content">
        <!-- Configuración Actual -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-palette"></i> Configuración Actual
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalNuevaConfiguracion">
                                <i class="fa fa-plus"></i> Nueva Configuración
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><i class="fa fa-desktop"></i> Barra de Navegación</h4>
                                <div class="color-preview" style="background-color: <?= $configuracionActual['navbar_color'] ?>; color: <?= $configuracionActual['navbar_text_color'] ?>; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <strong>Navbar Color:</strong> <?= $configuracionActual['navbar_color'] ?>
                                </div>
                                <div class="color-preview" style="background-color: <?= $configuracionActual['navbar_hover_color'] ?>; color: <?= $configuracionActual['navbar_text_color'] ?>; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <strong>Navbar Hover:</strong> <?= $configuracionActual['navbar_hover_color'] ?>
                                </div>
                                
                                <h4><i class="fa fa-bars"></i> Barra Lateral</h4>
                                <div class="color-preview" style="background-color: <?= $configuracionActual['sidebar_color'] ?>; color: <?= $configuracionActual['sidebar_text_color'] ?>; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <strong>Sidebar Color:</strong> <?= $configuracionActual['sidebar_color'] ?>
                                </div>
                                <div class="color-preview" style="background-color: <?= $configuracionActual['sidebar_hover_color'] ?>; color: <?= $configuracionActual['sidebar_text_color'] ?>; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <strong>Sidebar Hover:</strong> <?= $configuracionActual['sidebar_hover_color'] ?>
                                </div>
                                <div class="color-preview" style="background-color: <?= $configuracionActual['active_menu_color'] ?>; color: <?= $configuracionActual['active_menu_text_color'] ?>; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <strong>Menú Activo:</strong> <?= $configuracionActual['active_menu_color'] ?>
                                </div>
                                
                                <h4><i class="fa fa-image"></i> Logos e Iconos</h4>
                                <div class="color-preview" style="background-color: #f5f5f5; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <div style="color: <?= $configuracionActual['logo_mini_color'] ?>; font-weight: bold;">Logo Mini: <?= $configuracionActual['logo_mini_color'] ?></div>
                                    <div style="color: <?= $configuracionActual['logo_lg_color'] ?>; font-weight: bold;">Logo LG: <?= $configuracionActual['logo_lg_color'] ?></div>
                                    <div style="background-color: <?= $configuracionActual['logo_background_color'] ?>; color: <?= $configuracionActual['logo_mini_color'] ?>; padding: 5px; border-radius: 3px; margin-top: 5px;">
                                        <strong>Fondo Logo:</strong> <?= $configuracionActual['logo_background_color'] ?>
                                    </div>
                                    <div style="color: <?= $configuracionActual['icon_color'] ?>; font-weight: bold;">Iconos: <?= $configuracionActual['icon_color'] ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h4><i class="fa fa-sign-in"></i> Página de Login</h4>
                                <div class="color-preview" style="background: linear-gradient(135deg, <?= $configuracionActual['login_gradient_start'] ?> 0%, <?= $configuracionActual['login_gradient_end'] ?> 100%); color: <?= $configuracionActual['login_logo_color'] ?>; padding: 20px; border-radius: 5px; margin-bottom: 10px; text-align: center;">
                                    <strong>Gradiente Login</strong><br>
                                    <small>Desde: <?= $configuracionActual['login_gradient_start'] ?></small><br>
                                    <small>Hasta: <?= $configuracionActual['login_gradient_end'] ?></small>
                                </div>
                                
                                <h4><i class="fa fa-mouse-pointer"></i> Efectos Hover</h4>
                                <div class="color-preview" style="background-color: #f5f5f5; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <div style="background-color: <?= $configuracionActual['sidebar_toggle_hover_color'] ?>; color: white; padding: 5px; border-radius: 3px; margin-bottom: 5px;">
                                        <strong>Toggle Hover:</strong> <?= $configuracionActual['sidebar_toggle_hover_color'] ?>
                                    </div>
                                    <div style="background-color: <?= $configuracionActual['dropdown_hover_color'] ?>; color: #333; padding: 5px; border-radius: 3px; margin-bottom: 5px;">
                                        <strong>Dropdown Hover:</strong> <?= $configuracionActual['dropdown_hover_color'] ?>
                                    </div>
                                    <div style="color: <?= $configuracionActual['link_hover_color'] ?>; font-weight: bold;">
                                        <strong>Enlaces Hover:</strong> <?= $configuracionActual['link_hover_color'] ?>
                                    </div>
                                </div>
                                
                                <h4><i class="fa fa-info-circle"></i> Información</h4>
                                <div class="alert alert-info">
                                    <strong>Configuración:</strong> <?= $configuracionActual['nombre_configuracion'] ?><br>
                                    <strong>Última actualización:</strong> <?= date('d/m/Y H:i:s', strtotime($configuracionActual['fecha_actualizacion'] ?? 'now')) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Configuraciones -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-history"></i> Historial de Configuraciones
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Navbar</th>
                                        <th>Sidebar</th>
                                        <th>Login Gradient</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todasConfiguraciones as $config): ?>
                                    <tr>
                                        <td><?= $config['nombre_configuracion'] ?></td>
                                        <td>
                                            <span class="badge" style="background-color: <?= $config['navbar_color'] ?>; color: <?= $config['navbar_text_color'] ?>;">
                                                <?= $config['navbar_color'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: <?= $config['sidebar_color'] ?>; color: <?= $config['sidebar_text_color'] ?>;">
                                                <?= $config['sidebar_color'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="background: linear-gradient(90deg, <?= $config['login_gradient_start'] ?>, <?= $config['login_gradient_end'] ?>); width: 100px; height: 20px; border-radius: 3px; display: inline-block;"></div>
                                        </td>
                                        <td>
                                            <?php if ($config['activo']): ?>
                                                <span class="label label-success">Activa</span>
                                            <?php else: ?>
                                                <span class="label label-default">Inactiva</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($config['fecha_actualizacion'])) ?></td>
                                        <td>
                                            <?php if (!$config['activo']): ?>
                                                <a href="personalizacion-colores?activar=<?= $config['id'] ?>" class="btn btn-success btn-xs">
                                                    <i class="fa fa-check"></i> Activar
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (count($todasConfiguraciones) > 1): ?>
                                                <a href="personalizacion-colores?eliminar=<?= $config['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('¿Estás seguro de eliminar esta configuración?')">
                                                    <i class="fa fa-trash"></i> Eliminar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Nueva Configuración -->
<div class="modal fade" id="modalNuevaConfiguracion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" id="formNuevaConfiguracion">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-palette"></i> Nueva Configuración de Colores
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_configuracion">Nombre de la Configuración</label>
                                <input type="text" class="form-control" id="nombre_configuracion" name="nombre_configuracion" value="Mi Configuración" required>
                            </div>
                            
                            <h4><i class="fa fa-desktop"></i> Barra de Navegación</h4>
                            <div class="form-group">
                                <label for="navbar_color">Color de Fondo</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="navbar_color" name="navbar_color" value="#3c8dbc" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#3c8dbc" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="navbar_text_color">Color del Texto</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="navbar_text_color" name="navbar_text_color" value="#ffffff" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#ffffff" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="navbar_hover_color">Color Hover</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="navbar_hover_color" name="navbar_hover_color" value="#2c3e50" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#2c3e50" readonly>
                                    </span>
                                </div>
                            </div>
                            
                            <h4><i class="fa fa-bars"></i> Barra Lateral</h4>
                            <div class="form-group">
                                <label for="sidebar_color">Color de Fondo</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="sidebar_color" name="sidebar_color" value="#222d32" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#222d32" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sidebar_text_color">Color del Texto</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="sidebar_text_color" name="sidebar_text_color" value="#b8c7ce" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#b8c7ce" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sidebar_hover_color">Color Hover</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="sidebar_hover_color" name="sidebar_hover_color" value="#1a252f" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#1a252f" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="active_menu_color">Color Menú Activo</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="active_menu_color" name="active_menu_color" value="#1a252f" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#1a252f" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="active_menu_text_color">Color Texto Menú Activo</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="active_menu_text_color" name="active_menu_text_color" value="#ffffff" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#ffffff" readonly>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h4><i class="fa fa-image"></i> Logos e Iconos</h4>
                            <div class="form-group">
                                <label for="logo_mini_color">Color Logo Mini</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="logo_mini_color" name="logo_mini_color" value="#ffffff" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#ffffff" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="logo_lg_color">Color Logo LG</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="logo_lg_color" name="logo_lg_color" value="#ffffff" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#ffffff" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="logo_background_color">Color Fondo Logo</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="logo_background_color" name="logo_background_color" value="#3c8dbc" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#3c8dbc" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="icon_color">Color de Iconos</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="icon_color" name="icon_color" value="#3c8dbc" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#3c8dbc" readonly>
                                    </span>
                                </div>
                            </div>
                            
                            <h4><i class="fa fa-mouse-pointer"></i> Efectos Hover</h4>
                            <div class="form-group">
                                <label for="sidebar_toggle_hover_color">Color Toggle Hover</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="sidebar_toggle_hover_color" name="sidebar_toggle_hover_color" value="#2c3e50" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#2c3e50" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dropdown_hover_color">Color Dropdown Hover</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="dropdown_hover_color" name="dropdown_hover_color" value="#f5f5f5" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#f5f5f5" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="link_hover_color">Color Enlaces Hover</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="link_hover_color" name="link_hover_color" value="#2c3e50" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#2c3e50" readonly>
                                    </span>
                                </div>
                            </div>
                            
                            <h4><i class="fa fa-square"></i> Botones</h4>
                            <div class="form-group">
                                <label for="button_primary_color">Color Botones Primarios</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="button_primary_color" name="button_primary_color" value="#3c8dbc" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#3c8dbc" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="button_primary_hover_color">Color Botones Primarios Hover</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="button_primary_hover_color" name="button_primary_hover_color" value="#2c3e50" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#2c3e50" readonly>
                                    </span>
                                </div>
                            </div>
                            
                            <h4><i class="fa fa-sign-in"></i> Página de Login</h4>
                            <div class="form-group">
                                <label for="login_gradient_start">Color Inicio Gradiente</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="login_gradient_start" name="login_gradient_start" value="#3c8dbc" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#3c8dbc" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="login_gradient_end">Color Fin Gradiente</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="login_gradient_end" name="login_gradient_end" value="#2c3e50" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#2c3e50" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="login_logo_color">Color Logo Login</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="login_logo_color" name="login_logo_color" value="#ffffff" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#ffffff" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="login_text_color">Color Texto Login</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="login_text_color" name="login_text_color" value="#ffffff" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#ffffff" readonly>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vista Previa -->
                    <div class="row">
                        <div class="col-md-12">
                            <h4><i class="fa fa-eye"></i> Vista Previa</h4>
                            <div id="vistaPrevia" class="preview-container" style="border: 1px solid #ddd; border-radius: 5px; padding: 20px; background: #f9f9f9;">
                                <div class="preview-navbar" style="background-color: #3c8dbc; color: #ffffff; padding: 10px; margin-bottom: 10px; border-radius: 3px;">
                                    <strong>Barra de Navegación</strong>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="preview-sidebar" style="background-color: #222d32; color: #b8c7ce; padding: 10px; border-radius: 3px; margin-bottom: 10px;">
                                            <strong>Barra Lateral</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="preview-login" style="background: linear-gradient(135deg, #3c8dbc 0%, #2c3e50 100%); color: #ffffff; padding: 20px; border-radius: 3px; text-align: center;">
                                            <strong>Página de Login</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" name="actualizarConfiguracion">
                        <i class="fa fa-save"></i> Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incluir estilos dinámicos -->
<?= ControladorPersonalizacionColores::ctrAplicarConfiguracionEstilos() ?>

<script>
$(document).ready(function() {
    // Actualizar vista previa cuando cambien los colores
    $('input[type="color"]').on('change', function() {
        actualizarVistaPrevia();
    });
    
    // Actualizar texto de color cuando cambie el input
    $('input[type="color"]').on('change', function() {
        var colorInput = $(this);
        var textInput = colorInput.closest('.input-group').find('.color-text');
        textInput.val(colorInput.val());
    });
    
    function actualizarVistaPrevia() {
        var navbarColor = $('#navbar_color').val();
        var navbarTextColor = $('#navbar_text_color').val();
        var sidebarColor = $('#sidebar_color').val();
        var sidebarTextColor = $('#sidebar_text_color').val();
        var gradientStart = $('#login_gradient_start').val();
        var gradientEnd = $('#login_gradient_end').val();
        var loginTextColor = $('#login_text_color').val();
        
        $('.preview-navbar').css({
            'background-color': navbarColor,
            'color': navbarTextColor
        });
        
        $('.preview-sidebar').css({
            'background-color': sidebarColor,
            'color': sidebarTextColor
        });
        
        $('.preview-login').css({
            'background': 'linear-gradient(135deg, ' + gradientStart + ' 0%, ' + gradientEnd + ' 100%)',
            'color': loginTextColor
        });
    }
    
    // Inicializar vista previa
    actualizarVistaPrevia();
});
</script>

<style>
.color-preview {
    border: 1px solid #ddd;
    margin-bottom: 10px;
}

.preview-container {
    min-height: 200px;
}

.input-group .color-text {
    background-color: #f5f5f5;
    border-left: none;
}

.input-group .form-control:first-child {
    border-right: none;
}

.input-group .form-control:last-child {
    border-left: none;
}
</style>
