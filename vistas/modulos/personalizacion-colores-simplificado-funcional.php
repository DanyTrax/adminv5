<?php
require_once "controladores/personalizacion-colores-simplificado-funcional.controlador.php";
require_once "modelos/sucursales.modelo.php";

// Obtener sucursal seleccionada (si existe)
$idSucursalSeleccionada = isset($_GET['sucursal']) ? (int)$_GET['sucursal'] : null;
if ($idSucursalSeleccionada === 0) {
    $idSucursalSeleccionada = null; // 0 significa "Global"
}

// Obtener todas las sucursales activas
$respuestaSucursales = ModeloSucursales::mdlObtenerSucursales(true);
$sucursales = $respuestaSucursales['success'] ? $respuestaSucursales['data'] : [];

// Obtener ID de sucursal actual (solo para mostrar en el selector)
$idSucursalActual = ModeloPersonalizacionColores::mdlObtenerIdSucursalActual();

// Obtener configuración actual (de la sucursal seleccionada, o null si es Global)
$configuracionActual = ControladorPersonalizacionColores::ctrMostrarConfiguracionActiva($idSucursalSeleccionada);

// Si no hay configuración, crear una por defecto para mostrar
if (!$configuracionActual) {
    $configuracionActual = [
        'nombre_configuracion' => 'Configuración por Defecto',
        'login_gradient_start' => '#3c8dbc',
        'login_gradient_end' => '#2c3e50',
        'navbar_color' => '#3c8dbc',
        'navbar_hover_color' => '#357ca5',
        'sidebar_color' => '#222d32',
        'sidebar_hover_color' => '#1e282c',
        'sidebar_text_color' => '#b8c7ce',
        'icono_pequeno' => 'vistas/img/plantilla/icono-blanco.png',
        'logo_menu' => 'vistas/img/plantilla/logo-blanco-lineal.png',
        'logo_login' => 'vistas/img/plantilla/Infinito1.png'
    ];
}

// Obtener todas las configuraciones (de la sucursal seleccionada o todas si es Global)
$todasConfiguraciones = ControladorPersonalizacionColores::ctrObtenerTodasConfiguraciones($idSucursalSeleccionada);

// Procesar acciones
ControladorPersonalizacionColores::ctrActualizarConfiguracion();

if (isset($_GET['activar'])) {
    // Usar la sucursal seleccionada en el filtro, o la de la URL si existe
    if ($idSucursalSeleccionada !== null) {
        $idSucursalParaActivar = $idSucursalSeleccionada;
    } elseif (isset($_GET['sucursal']) && $_GET['sucursal'] != '0') {
        $idSucursalParaActivar = (int)$_GET['sucursal'];
    } else {
        // Si es Global, aplicar sin sucursal específica
        $idSucursalParaActivar = null;
    }
    ControladorPersonalizacionColores::ctrActivarConfiguracion($_GET['activar'], $idSucursalParaActivar);
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
        <!-- Selector de Sucursal -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-building"></i> Seleccionar Sucursal
                        </h3>
                    </div>
                    <div class="box-body">
                        <form method="get" action="personalizacion-colores-simplificado" id="formSelectorSucursal">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sucursal">Sucursal:</label>
                                        <select class="form-control" id="sucursal" name="sucursal" onchange="this.form.submit()">
                                            <option value="0" <?= $idSucursalSeleccionada === null ? 'selected' : '' ?>>Global (Todas las sucursales)</option>
                                            <?php foreach ($sucursales as $sucursal): ?>
                                                <option value="<?= $sucursal['id'] ?>" <?= $idSucursalSeleccionada == $sucursal['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($sucursal['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info" style="margin-top: 25px; margin-bottom: 0;">
                                        <i class="fa fa-info-circle"></i> 
                                        <strong>Información:</strong> Selecciona una sucursal para ver y editar su personalización de colores e imágenes.
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Configuración Actual -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-palette"></i> Configuración Actual 
                            <?php if ($idSucursalSeleccionada !== null): ?>
                                - <?= htmlspecialchars($sucursales[array_search($idSucursalSeleccionada, array_column($sucursales, 'id'))]['nombre'] ?? 'Sucursal') ?>
                            <?php else: ?>
                                - Global
                            <?php endif; ?>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalNuevaConfiguracion">
                                <i class="fa fa-plus"></i> Nueva Configuración
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h4><i class="fa fa-sign-in"></i> Gradiente del Login</h4>
                                <div class="color-preview" style="background: linear-gradient(135deg, <?= $configuracionActual['login_gradient_start'] ?> 0%, <?= $configuracionActual['login_gradient_end'] ?> 100%); color: white; padding: 20px; border-radius: 5px; margin-bottom: 10px; text-align: center;">
                                    <strong>Gradiente Login</strong><br>
                                    <small>Desde: <?= $configuracionActual['login_gradient_start'] ?></small><br>
                                    <small>Hasta: <?= $configuracionActual['login_gradient_end'] ?></small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <h4><i class="fa fa-desktop"></i> Barra Principal</h4>
                                <div class="color-preview" style="background-color: <?= $configuracionActual['navbar_color'] ?>; color: white; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <strong>Barra Principal:</strong> <?= $configuracionActual['navbar_color'] ?>
                                </div>
                                <div class="color-preview" style="background-color: <?= $configuracionActual['navbar_hover_color'] ?>; color: white; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <strong>Hover:</strong> <?= $configuracionActual['navbar_hover_color'] ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <h4><i class="fa fa-bars"></i> Barra Lateral</h4>
                                <div class="color-preview" style="background-color: <?= $configuracionActual['sidebar_color'] ?>; color: <?= $configuracionActual['sidebar_text_color'] ?>; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <strong>Barra Lateral:</strong> <?= $configuracionActual['sidebar_color'] ?>
                                </div>
                                <div class="color-preview" style="background-color: <?= $configuracionActual['sidebar_hover_color'] ?>; color: <?= $configuracionActual['sidebar_text_color'] ?>; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <strong>Hover:</strong> <?= $configuracionActual['sidebar_hover_color'] ?>
                                </div>
                                <div class="color-preview" style="background-color: #f5f5f5; color: <?= $configuracionActual['sidebar_text_color'] ?>; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                    <strong>Texto:</strong> <?= $configuracionActual['sidebar_text_color'] ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h4><i class="fa fa-image"></i> Imágenes del Sistema</h4>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> <strong>Instrucciones:</strong> Haz clic en cualquier imagen para cambiarla desde tu equipo.
                                    <?php if ($idSucursalSeleccionada !== null): ?>
                                        <br><strong>Nota:</strong> Los cambios se aplicarán a la sucursal seleccionada: <strong><?= htmlspecialchars($sucursales[array_search($idSucursalSeleccionada, array_column($sucursales, 'id'))]['nombre'] ?? 'Sucursal #' . $idSucursalSeleccionada) ?></strong>
                                    <?php else: ?>
                                        <br><strong>Nota:</strong> Los cambios se aplicarán globalmente a todas las sucursales.
                                    <?php endif; ?>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="image-upload-container" style="text-align: center; margin-bottom: 20px;">
                                            <div class="image-preview-box" id="preview-icono-pequeno" style="border: 2px dashed #ddd; padding: 20px; border-radius: 5px; cursor: pointer; background-color: #f9f9f9; min-height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: all 0.3s ease;" onclick="abrirSelectorImagen('icono-pequeno')">
                                                <img src="<?= $configuracionActual['icono_pequeno'] ?>" style="max-width: 50px; max-height: 50px; margin-bottom: 10px;">
                                                <div style="color: #666; font-size: 12px;">
                                                    <i class="fa fa-camera" style="font-size: 16px; margin-bottom: 5px; display: block;"></i>
                                                    <strong>Icono Pequeño</strong><br>
                                                    <small>Haz clic para cambiar</small>
                                                </div>
                                            </div>
                                            <input type="file" id="file-icono-pequeno" accept="image/*" style="display: none;" onchange="subirImagen('icono-pequeno', this)">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="image-upload-container" style="text-align: center; margin-bottom: 20px;">
                                            <div class="image-preview-box" id="preview-logo-menu" style="border: 2px dashed #ddd; padding: 20px; border-radius: 5px; cursor: pointer; background-color: #f9f9f9; min-height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: all 0.3s ease;" onclick="abrirSelectorImagen('logo-menu')">
                                                <img src="<?= $configuracionActual['logo_menu'] ?>" style="max-width: 100px; max-height: 50px; margin-bottom: 10px;">
                                                <div style="color: #666; font-size: 12px;">
                                                    <i class="fa fa-camera" style="font-size: 16px; margin-bottom: 5px; display: block;"></i>
                                                    <strong>Logo Menú</strong><br>
                                                    <small>Haz clic para cambiar</small>
                                                </div>
                                            </div>
                                            <input type="file" id="file-logo-menu" accept="image/*" style="display: none;" onchange="subirImagen('logo-menu', this)">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="image-upload-container" style="text-align: center; margin-bottom: 20px;">
                                            <div class="image-preview-box" id="preview-logo-login" style="border: 2px dashed #ddd; padding: 20px; border-radius: 5px; cursor: pointer; background-color: #f9f9f9; min-height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: all 0.3s ease;" onclick="abrirSelectorImagen('logo-login')">
                                                <img src="<?= $configuracionActual['logo_login'] ?>" style="max-width: 100px; max-height: 50px; margin-bottom: 10px;">
                                                <div style="color: #666; font-size: 12px;">
                                                    <i class="fa fa-camera" style="font-size: 16px; margin-bottom: 5px; display: block;"></i>
                                                    <strong>Logo Login</strong><br>
                                                    <small>Haz clic para cambiar</small>
                                                </div>
                                            </div>
                                            <input type="file" id="file-logo-login" accept="image/*" style="display: none;" onchange="subirImagen('logo-login', this)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Configuración:</strong> <?= $configuracionActual['nombre_configuracion'] ?><br>
                            <strong>Última actualización:</strong> <?= date('d/m/Y H:i:s', strtotime($configuracionActual['fecha_actualizacion'] ?? 'now')) ?>
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
                                        <th>Sucursal</th>
                                        <th>Gradiente Login</th>
                                        <th>Barra Principal</th>
                                        <th>Barra Lateral</th>
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
                                            <?php if ($config['id_sucursal'] === null): ?>
                                                <span class="label label-info">Global</span>
                                            <?php else: ?>
                                                <span class="label label-primary">
                                                    <?= htmlspecialchars($config['nombre_sucursal'] ?? 'Sucursal #' . $config['id_sucursal']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="background: linear-gradient(90deg, <?= $config['login_gradient_start'] ?>, <?= $config['login_gradient_end'] ?>); width: 100px; height: 20px; border-radius: 3px; display: inline-block;"></div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: <?= $config['navbar_color'] ?>; color: white;">
                                                <?= $config['navbar_color'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: <?= $config['sidebar_color'] ?>; color: <?= $config['sidebar_text_color'] ?>;">
                                                <?= $config['sidebar_color'] ?>
                                            </span>
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
                                            <div class="btn-group">
                                                <button class="btn btn-warning btn-xs btnEditarConfiguracion" 
                                                        data-id="<?= $config['id'] ?>"
                                                        data-toggle="modal" 
                                                        data-target="#modalEditarConfiguracion"
                                                        title="Editar configuración">
                                                    <i class="fa fa-pencil"></i> Editar
                                                </button>
                                                
                                                <a href="personalizacion-colores-simplificado?activar=<?= $config['id'] ?><?= $idSucursalSeleccionada !== null ? '&sucursal=' . $idSucursalSeleccionada : '' ?>" 
                                                   class="btn btn-success btn-xs"
                                                   title="Aplicar esta configuración a la sucursal seleccionada">
                                                    <i class="fa fa-check"></i> Aplicar
                                                </a>
                                                
                                                <?php if (count($todasConfiguraciones) > 1): ?>
                                                    <a href="personalizacion-colores-simplificado?eliminar=<?= $config['id'] ?><?= $idSucursalSeleccionada !== null ? '&sucursal=' . $idSucursalSeleccionada : '' ?>" 
                                                       class="btn btn-danger btn-xs" 
                                                       onclick="return confirm('¿Estás seguro de eliminar esta configuración?')"
                                                       title="Eliminar configuración">
                                                        <i class="fa fa-trash"></i> Eliminar
                                                    </a>
                                                <?php endif; ?>
                                            </div>
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
            <form method="post" id="formNuevaConfiguracion" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-palette"></i> Nueva Configuración de Colores
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nombre_configuracion">Nombre de la Configuración</label>
                                <input type="text" class="form-control" id="nombre_configuracion" name="nombre_configuracion" value="Mi Configuración" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_sucursal_modal">Sucursal:</label>
                                <select class="form-control" id="id_sucursal_modal" name="id_sucursal">
                                    <option value="" <?= $idSucursalSeleccionada === null ? 'selected' : '' ?>>Global (Todas las sucursales)</option>
                                    <?php foreach ($sucursales as $sucursal): ?>
                                        <option value="<?= $sucursal['id'] ?>" <?= $idSucursalSeleccionada == $sucursal['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sucursal['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="help-block">Selecciona una sucursal específica o deja en "Global" para aplicar a todas. La configuración se aplicará a la sucursal seleccionada.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            
                            <h4><i class="fa fa-sign-in"></i> Gradiente del Login</h4>
                            <div class="form-group">
                                <label for="login_gradient_start">Color Inicio</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="login_gradient_start" name="login_gradient_start" value="#3c8dbc" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#3c8dbc" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="login_gradient_end">Color Fin</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="login_gradient_end" name="login_gradient_end" value="#2c3e50" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#2c3e50" readonly>
                                    </span>
                                </div>
                            </div>
                            
                            <h4><i class="fa fa-desktop"></i> Barra Principal</h4>
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
                                <label for="navbar_hover_color">Color Hover</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="navbar_hover_color" name="navbar_hover_color" value="#2c3e50" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#2c3e50" readonly>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
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
                                <label for="sidebar_hover_color">Color Hover</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="sidebar_hover_color" name="sidebar_hover_color" value="#1a252f" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#1a252f" readonly>
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
                        </div>
                    </div>
                    
                    <!-- Vista Previa -->
                    <div class="row">
                        <div class="col-md-12">
                            <h4><i class="fa fa-eye"></i> Vista Previa</h4>
                            <div id="vistaPrevia" class="preview-container" style="border: 1px solid #ddd; border-radius: 5px; padding: 20px; background: #f9f9f9;">
                                <div class="preview-navbar" style="background-color: #3c8dbc; color: white; padding: 10px; margin-bottom: 10px; border-radius: 3px;">
                                    <strong>Barra Principal</strong>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="preview-sidebar" style="background-color: #222d32; color: #b8c7ce; padding: 10px; border-radius: 3px; margin-bottom: 10px;">
                                            <strong>Barra Lateral</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="preview-login" style="background: linear-gradient(135deg, #3c8dbc 0%, #2c3e50 100%); color: white; padding: 20px; border-radius: 3px; text-align: center;">
                                            <strong>Gradiente Login</strong>
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

<!-- Modal Editar Configuración -->
<div class="modal fade" id="modalEditarConfiguracion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" id="formEditarConfiguracion" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-pencil"></i> Editar Configuración de Colores
                    </h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editar_configuracion" name="editar_configuracion" value="">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="editar_nombre_configuracion">Nombre de la Configuración</label>
                                <input type="text" class="form-control" id="editar_nombre_configuracion" name="nombre_configuracion" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="editar_id_sucursal_modal">Sucursal:</label>
                                <select class="form-control" id="editar_id_sucursal_modal" name="id_sucursal">
                                    <option value="" <?= $idSucursalSeleccionada === null ? 'selected' : '' ?>>Global (Todas las sucursales)</option>
                                    <?php foreach ($sucursales as $sucursal): ?>
                                        <option value="<?= $sucursal['id'] ?>" <?= $idSucursalSeleccionada == $sucursal['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sucursal['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="help-block">Selecciona una sucursal específica o deja en "Global" para aplicar a todas</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h4><i class="fa fa-sign-in"></i> Gradiente del Login</h4>
                            <div class="form-group">
                                <label for="editar_login_gradient_start">Color Inicio</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editar_login_gradient_start" name="login_gradient_start" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="editar_login_gradient_end">Color Fin</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editar_login_gradient_end" name="login_gradient_end" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly>
                                    </span>
                                </div>
                            </div>
                            
                            <h4><i class="fa fa-desktop"></i> Barra Principal</h4>
                            <div class="form-group">
                                <label for="editar_navbar_color">Color de Fondo</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editar_navbar_color" name="navbar_color" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="editar_navbar_hover_color">Color Hover</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editar_navbar_hover_color" name="navbar_hover_color" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h4><i class="fa fa-bars"></i> Barra Lateral</h4>
                            <div class="form-group">
                                <label for="editar_sidebar_color">Color de Fondo</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editar_sidebar_color" name="sidebar_color" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="editar_sidebar_hover_color">Color Hover</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editar_sidebar_hover_color" name="sidebar_hover_color" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="editar_sidebar_text_color">Color del Texto</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editar_sidebar_text_color" name="sidebar_text_color" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vista Previa -->
                    <div class="row">
                        <div class="col-md-12">
                            <h4><i class="fa fa-eye"></i> Vista Previa</h4>
                            <div id="vistaPreviaEditar" class="preview-container" style="border: 1px solid #ddd; border-radius: 5px; padding: 20px; background: #f9f9f9;">
                                <div class="preview-navbar-editar" style="background-color: #3c8dbc; color: white; padding: 10px; margin-bottom: 10px; border-radius: 3px;">
                                    <strong>Barra Principal</strong>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="preview-sidebar-editar" style="background-color: #222d32; color: #b8c7ce; padding: 10px; border-radius: 3px; margin-bottom: 10px;">
                                            <strong>Barra Lateral</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="preview-login-editar" style="background: linear-gradient(135deg, #3c8dbc 0%, #2c3e50 100%); color: white; padding: 20px; border-radius: 3px; text-align: center;">
                                            <strong>Gradiente Login</strong>
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
                        <i class="fa fa-save"></i> Guardar Cambios
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
    
    // Inicializar vista previa
    actualizarVistaPrevia();
    
    // Manejar edición de configuración
    $('.btnEditarConfiguracion').on('click', function() {
        var id = $(this).data('id');
        editarConfiguracion(id);
    });
    
    // Actualizar vista previa en modal de edición
    $('#modalEditarConfiguracion input[type="color"]').on('change', function() {
        actualizarVistaPreviaEditar();
        var colorInput = $(this);
        var textInput = colorInput.closest('.input-group').find('.color-text');
        textInput.val(colorInput.val());
    });
});

// Función para editar configuración
function editarConfiguracion(id) {
    $.ajax({
        url: 'ajax/personalizacion-colores.ajax.php',
        type: 'POST',
        data: {
            accion: 'obtener_configuracion',
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.configuracion) {
                var config = response.configuracion;
                
                // Llenar campos del modal
                $('#editar_configuracion').val(config.id);
                $('#editar_nombre_configuracion').val(config.nombre_configuracion);
                $('#editar_id_sucursal_modal').val(config.id_sucursal || '');
                
                // Colores
                $('#editar_login_gradient_start').val(config.login_gradient_start);
                $('#editar_login_gradient_end').val(config.login_gradient_end);
                $('#editar_navbar_color').val(config.navbar_color);
                $('#editar_navbar_hover_color').val(config.navbar_hover_color);
                $('#editar_sidebar_color').val(config.sidebar_color);
                $('#editar_sidebar_hover_color').val(config.sidebar_hover_color);
                $('#editar_sidebar_text_color').val(config.sidebar_text_color);
                
                // Actualizar textos de color
                $('#editar_login_gradient_start').closest('.input-group').find('.color-text').val(config.login_gradient_start);
                $('#editar_login_gradient_end').closest('.input-group').find('.color-text').val(config.login_gradient_end);
                $('#editar_navbar_color').closest('.input-group').find('.color-text').val(config.navbar_color);
                $('#editar_navbar_hover_color').closest('.input-group').find('.color-text').val(config.navbar_hover_color);
                $('#editar_sidebar_color').closest('.input-group').find('.color-text').val(config.sidebar_color);
                $('#editar_sidebar_hover_color').closest('.input-group').find('.color-text').val(config.sidebar_hover_color);
                $('#editar_sidebar_text_color').closest('.input-group').find('.color-text').val(config.sidebar_text_color);
                
                // Actualizar vista previa
                actualizarVistaPreviaEditar();
            }
        },
        error: function() {
            swal({
                type: "error",
                title: "Error",
                text: "No se pudo cargar la configuración",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        }
    });
}

// Función para actualizar vista previa en modal de edición
function actualizarVistaPreviaEditar() {
    var navbarColor = $('#editar_navbar_color').val();
    var sidebarColor = $('#editar_sidebar_color').val();
    var sidebarTextColor = $('#editar_sidebar_text_color').val();
    var gradientStart = $('#editar_login_gradient_start').val();
    var gradientEnd = $('#editar_login_gradient_end').val();
    
    $('.preview-navbar-editar').css({
        'background-color': navbarColor
    });
    
    $('.preview-sidebar-editar').css({
        'background-color': sidebarColor,
        'color': sidebarTextColor
    });
    
    $('.preview-login-editar').css({
        'background': 'linear-gradient(135deg, ' + gradientStart + ' 0%, ' + gradientEnd + ' 100%)'
    });
}

// Función para abrir el selector de imágenes
function abrirSelectorImagen(tipo) {
    document.getElementById('file-' + tipo).click();
}

// Función para subir imagen
function subirImagen(tipo, input) {
    var file = input.files[0];
    if (!file) return;
    
    // Mostrar indicador de carga
    var previewBox = document.getElementById('preview-' + tipo);
    previewBox.innerHTML = '<div style="text-align: center; color: #666;"><i class="fa fa-spinner fa-spin" style="font-size: 24px;"></i><br><small>Subiendo imagen...</small></div>';
    
    // Obtener la sucursal seleccionada
    var sucursalSeleccionada = $('#sucursal').val();
    var idSucursal = sucursalSeleccionada && sucursalSeleccionada != '0' ? sucursalSeleccionada : null;
    
    // Crear FormData para enviar la imagen
    var formData = new FormData();
    formData.append('accion', 'subir_imagen');
    formData.append('tipo', tipo);
    formData.append('imagen', file);
    if (idSucursal) {
        formData.append('id_sucursal', idSucursal);
    }
    
    // Enviar imagen al servidor
    $.ajax({
        url: 'ajax/personalizacion-colores.ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                var data = JSON.parse(response);
                if (data.success) {
                    // Actualizar la imagen en la vista previa
                    var rutaImagen = data.ruta_imagen + '?t=' + new Date().getTime();
                    var img = previewBox.querySelector('img');
                    if (img) {
                        img.src = rutaImagen;
                    } else {
                        previewBox.innerHTML = '<img src="' + rutaImagen + '" style="max-width: ' + (tipo === 'icono-pequeno' ? '50px' : '100px') + '; max-height: ' + (tipo === 'icono-pequeno' ? '50px' : '50px') + '; margin-bottom: 10px;"><div style="color: #666; font-size: 12px;"><i class="fa fa-check text-success" style="font-size: 16px; margin-bottom: 5px; display: block;"></i><strong>' + (tipo === 'icono-pequeno' ? 'Icono Pequeño' : tipo === 'logo-menu' ? 'Logo Menú' : 'Logo Login') + '</strong><br><small>Imagen actualizada</small></div>';
                    }
                    
                    // Actualizar imágenes en la página inmediatamente
                    if (tipo === 'icono-pequeno') {
                        $('.logo-mini img').attr('src', rutaImagen);
                    } else if (tipo === 'logo-menu') {
                        $('.logo-lg img').attr('src', rutaImagen);
                    } else if (tipo === 'logo-login') {
                        $('.login-logo img').attr('src', rutaImagen);
                    }
                    
                    // Mostrar mensaje de éxito con información de la sucursal
                    var mensaje = data.mensaje || "La imagen se ha subido y aplicado correctamente";
                    swal({
                        type: "success",
                        title: "¡Imagen actualizada!",
                        text: mensaje,
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                    
                    // Recargar la página después de 2 segundos para aplicar los cambios completamente
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                mostrarError('Error al procesar la respuesta del servidor');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            mostrarError('Error de conexión: ' + error);
        }
    });
}

// Función para mostrar error
function mostrarError(mensaje) {
    swal({
        type: "error",
        title: "Error",
        text: mensaje,
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
    });
    
    // Restaurar la vista previa original
    setTimeout(function() {
        window.location.reload();
    }, 2000);
}

function actualizarVistaPrevia() {
    var navbarColor = $('#navbar_color').val();
    var sidebarColor = $('#sidebar_color').val();
    var sidebarTextColor = $('#sidebar_text_color').val();
    var gradientStart = $('#login_gradient_start').val();
    var gradientEnd = $('#login_gradient_end').val();
    
    $('.preview-navbar').css({
        'background-color': navbarColor
    });
    
    $('.preview-sidebar').css({
        'background-color': sidebarColor,
        'color': sidebarTextColor
    });
    
    $('.preview-login').css({
        'background': 'linear-gradient(135deg, ' + gradientStart + ' 0%, ' + gradientEnd + ' 100%)'
    });
}
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

.image-preview-box:hover {
    border-color: #3c8dbc !important;
    background-color: #f0f8ff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.image-preview-box:hover .fa-camera {
    color: #3c8dbc !important;
}

.image-preview-box:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
