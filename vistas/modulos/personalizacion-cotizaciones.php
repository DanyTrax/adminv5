<?php
require_once __DIR__ . "/../../controladores/personalizacion-cotizaciones.controlador.php";
require_once __DIR__ . "/../../modelos/sucursales.modelo.php";

// Obtener sucursal seleccionada (si existe)
$idSucursalSeleccionada = isset($_GET['sucursal']) ? (int)$_GET['sucursal'] : null;
if ($idSucursalSeleccionada === 0) {
    $idSucursalSeleccionada = null; // 0 significa "Global"
}

// Obtener todas las sucursales activas
$respuestaSucursales = ModeloSucursales::mdlObtenerSucursales(true);
$sucursales = $respuestaSucursales['success'] ? $respuestaSucursales['data'] : [];

// Obtener configuración actual
$configuracionActual = ControladorPersonalizacionCotizaciones::ctrMostrarConfiguracionActiva($idSucursalSeleccionada);

// Si no hay configuración, crear una por defecto
if (!$configuracionActual) {
    $configuracionActual = [
        'header_logo' => 'vistas/img/cotizacion/Infinito1.png',
        'header_nombre_empresa' => 'ACPLASTICOS',
        'header_nit' => 'NIT: 901.718.358-2',
        'header_regimen' => 'IVA E ICA RÉGIMEN COMÚN',
        'header_servicios' => "AVISOS\nLETRAS EN 3D\nTOMA UNO\nTRABAJOS ESPECIALES",
        'header_color_fondo' => '#873173',
        'header_color_texto' => '#FFFFFF',
        'footer_direccion' => 'Carrera 27 # 10-65 Local 116',
        'footer_telefono' => 'Tel: 601 569 9557',
        'footer_movil' => 'Móvil: 322 744 5631',
        'footer_correo' => 'Correo: ventas1@acplasticos.com',
        'footer_color_fondo' => '#873173',
        'footer_color_texto' => '#FFFFFF'
    ];
}

// Obtener todas las configuraciones
$todasConfiguraciones = ControladorPersonalizacionCotizaciones::ctrObtenerTodasConfiguraciones($idSucursalSeleccionada);

// Procesar acciones
if (isset($_POST['nuevoHeaderNombreEmpresa'])) {
    $resultado = ControladorPersonalizacionCotizaciones::ctrCrearConfiguracion();
    if ($resultado['success']) {
        echo "<script>
            swal({
                type: 'success',
                title: '¡Configuración creada!',
                text: '" . $resultado['mensaje'] . "',
                showConfirmButton: true,
                confirmButtonText: 'Aceptar'
            }).then(function() {
                window.location.href = 'personalizacion-cotizaciones" . ($idSucursalSeleccionada ? '?sucursal=' . $idSucursalSeleccionada : '') . "';
            });
        </script>";
    } else {
        echo "<script>
            swal({
                type: 'error',
                title: 'Error',
                text: '" . ($resultado['mensaje'] ?? 'Error al crear la configuración') . "',
                showConfirmButton: true,
                confirmButtonText: 'Cerrar'
            });
        </script>";
    }
}

if (isset($_POST['editarId'])) {
    $resultado = ControladorPersonalizacionCotizaciones::ctrActualizarConfiguracion();
    if ($resultado['success']) {
        echo "<script>
            swal({
                type: 'success',
                title: '¡Configuración actualizada!',
                text: '" . $resultado['mensaje'] . "',
                showConfirmButton: true,
                confirmButtonText: 'Aceptar'
            }).then(function() {
                window.location.href = 'personalizacion-cotizaciones" . ($idSucursalSeleccionada ? '?sucursal=' . $idSucursalSeleccionada : '') . "';
            });
        </script>";
    } else {
        echo "<script>
            swal({
                type: 'error',
                title: 'Error',
                text: '" . ($resultado['mensaje'] ?? 'Error al actualizar la configuración') . "',
                showConfirmButton: true,
                confirmButtonText: 'Cerrar'
            });
        </script>";
    }
}

if (isset($_GET['activar'])) {
    ControladorPersonalizacionCotizaciones::ctrActivarConfiguracion($_GET['activar'], $idSucursalSeleccionada);
    echo "<script>window.location.href = 'personalizacion-cotizaciones" . ($idSucursalSeleccionada ? '?sucursal=' . $idSucursalSeleccionada : '') . "';</script>";
}

if (isset($_GET['eliminar'])) {
    ControladorPersonalizacionCotizaciones::ctrEliminarConfiguracion($_GET['eliminar']);
    echo "<script>window.location.href = 'personalizacion-cotizaciones" . ($idSucursalSeleccionada ? '?sucursal=' . $idSucursalSeleccionada : '') . "';</script>";
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Personalización de Cotizaciones
            <small>Personaliza el header y footer de las cotizaciones</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li class="active">Personalización de Cotizaciones</li>
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
                        <form method="get" action="personalizacion-cotizaciones" id="formSelectorSucursal">
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
                                        <strong>Información:</strong> Selecciona una sucursal para ver y editar su personalización de cotizaciones.
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
                            <i class="fa fa-file-text"></i> Configuración Actual
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalNuevaConfiguracion">
                                <i class="fa fa-plus"></i> Nueva Configuración
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <!-- Vista Previa Header -->
                        <div class="row">
                            <div class="col-md-12">
                                <h4><i class="fa fa-header"></i> Vista Previa del Header</h4>
                                <div class="header-preview" style="background: <?= $configuracionActual['header_color_fondo'] ?>; padding: 15px; border-radius: 5px; color: <?= $configuracionActual['header_color_texto'] ?>; margin-bottom: 20px;">
                                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; align-items: center;">
                                        <div style="text-align: center;">
                                            <img src="<?= $configuracionActual['header_logo'] ?>" style="max-width: 80px; max-height: 60px;" alt="Logo">
                                        </div>
                                        <div>
                                            <div><strong><?= htmlspecialchars($configuracionActual['header_nombre_empresa']) ?></strong></div>
                                            <div><?= htmlspecialchars($configuracionActual['header_nit']) ?></div>
                                            <div><?= htmlspecialchars($configuracionActual['header_regimen']) ?></div>
                                        </div>
                                        <div>
                                            <?php 
                                            $servicios = explode("\n", $configuracionActual['header_servicios']);
                                            foreach ($servicios as $servicio): 
                                                if (trim($servicio)): ?>
                                                    <div><?= htmlspecialchars(trim($servicio)) ?></div>
                                                <?php endif;
                                            endforeach; 
                                            ?>
                                        </div>
                                        <div style="text-align: center;">
                                            <p style="margin: 0;"><strong>Cotización</strong></p>
                                            <p style="margin: 0;">1001</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vista Previa Footer -->
                        <div class="row">
                            <div class="col-md-12">
                                <h4><i class="fa fa-footer"></i> Vista Previa del Footer</h4>
                                <div class="footer-preview" style="background: <?= $configuracionActual['footer_color_fondo'] ?>; padding: 15px; border-radius: 5px; color: <?= $configuracionActual['footer_color_texto'] ?>; margin-bottom: 20px; text-align: center;">
                                    <p style="margin: 5px 0;"><strong><?= htmlspecialchars($configuracionActual['footer_direccion']) ?></strong></p>
                                    <p style="margin: 5px 0;"><?= htmlspecialchars($configuracionActual['footer_telefono']) ?> <?= htmlspecialchars($configuracionActual['footer_movil']) ?></p>
                                    <p style="margin: 5px 0;"><?= htmlspecialchars($configuracionActual['footer_correo']) ?></p>
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
                                        <th>Sucursal</th>
                                        <th>Empresa</th>
                                        <th>Header Color</th>
                                        <th>Footer Color</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($todasConfiguraciones)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No hay configuraciones guardadas</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($todasConfiguraciones as $config): ?>
                                        <tr>
                                            <td>
                                                <?php if ($config['id_sucursal'] === null): ?>
                                                    <span class="label label-info">Global</span>
                                                <?php else: ?>
                                                    <span class="label label-primary">
                                                        <?= htmlspecialchars($config['nombre_sucursal'] ?? 'Sucursal #' . $config['id_sucursal']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($config['header_nombre_empresa']) ?></td>
                                            <td>
                                                <span class="badge" style="background-color: <?= $config['header_color_fondo'] ?>; color: <?= $config['header_color_texto'] ?>;">
                                                    <?= $config['header_color_fondo'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: <?= $config['footer_color_fondo'] ?>; color: <?= $config['footer_color_texto'] ?>;">
                                                    <?= $config['footer_color_fondo'] ?>
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
                                                    
                                                    <a href="personalizacion-cotizaciones?activar=<?= $config['id'] ?><?= $idSucursalSeleccionada !== null ? '&sucursal=' . $idSucursalSeleccionada : '' ?>" 
                                                       class="btn <?= $config['activo'] ? 'btn-info' : 'btn-success' ?> btn-xs"
                                                       title="<?= $config['activo'] ? 'Esta configuración ya está aplicada' : 'Aplicar esta configuración' ?>">
                                                        <i class="fa <?= $config['activo'] ? 'fa-check-circle' : 'fa-check' ?>"></i> 
                                                        <?= $config['activo'] ? 'Aplicada' : 'Aplicar' ?>
                                                    </a>
                                                    
                                                    <?php if (count($todasConfiguraciones) > 1): ?>
                                                        <a href="personalizacion-cotizaciones?eliminar=<?= $config['id'] ?><?= $idSucursalSeleccionada !== null ? '&sucursal=' . $idSucursalSeleccionada : '' ?>" 
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
                                    <?php endif; ?>
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
            <form method="post" action="personalizacion-cotizaciones<?= $idSucursalSeleccionada ? '?sucursal=' . $idSucursalSeleccionada : '' ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus"></i> Nueva Configuración</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoIdSucursal">Sucursal:</label>
                                <select class="form-control" id="nuevoIdSucursal" name="nuevoIdSucursal">
                                    <option value="">Global (Todas las sucursales)</option>
                                    <?php foreach ($sucursales as $sucursal): ?>
                                        <option value="<?= $sucursal['id'] ?>" <?= $idSucursalSeleccionada == $sucursal['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sucursal['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <h4><i class="fa fa-header"></i> Header</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoHeaderLogo">Logo (Ruta):</label>
                                <input type="text" class="form-control" id="nuevoHeaderLogo" name="nuevoHeaderLogo" value="vistas/img/cotizacion/Infinito1.png" required>
                                <small class="help-block">Ruta de la imagen del logo</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoHeaderNombreEmpresa">Nombre de Empresa:</label>
                                <input type="text" class="form-control" id="nuevoHeaderNombreEmpresa" name="nuevoHeaderNombreEmpresa" value="ACPLASTICOS" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoHeaderNit">NIT:</label>
                                <input type="text" class="form-control" id="nuevoHeaderNit" name="nuevoHeaderNit" value="NIT: 901.718.358-2">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoHeaderRegimen">Régimen:</label>
                                <input type="text" class="form-control" id="nuevoHeaderRegimen" name="nuevoHeaderRegimen" value="IVA E ICA RÉGIMEN COMÚN">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nuevoHeaderServicios">Servicios (uno por línea):</label>
                                <textarea class="form-control" id="nuevoHeaderServicios" name="nuevoHeaderServicios" rows="4">AVISOS
LETRAS EN 3D
TOMA UNO
TRABAJOS ESPECIALES</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoHeaderColorFondo">Color de Fondo Header:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="nuevoHeaderColorFondo" name="nuevoHeaderColorFondo" value="#873173" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#873173" readonly style="width: 100px;">
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoHeaderColorTexto">Color de Texto Header:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="nuevoHeaderColorTexto" name="nuevoHeaderColorTexto" value="#FFFFFF" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#FFFFFF" readonly style="width: 100px;">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h4><i class="fa fa-footer"></i> Footer</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nuevoFooterDireccion">Dirección:</label>
                                <input type="text" class="form-control" id="nuevoFooterDireccion" name="nuevoFooterDireccion" value="Carrera 27 # 10-65 Local 116">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoFooterTelefono">Teléfono:</label>
                                <input type="text" class="form-control" id="nuevoFooterTelefono" name="nuevoFooterTelefono" value="Tel: 601 569 9557">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoFooterMovil">Móvil:</label>
                                <input type="text" class="form-control" id="nuevoFooterMovil" name="nuevoFooterMovil" value="Móvil: 322 744 5631">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nuevoFooterCorreo">Correo:</label>
                                <input type="text" class="form-control" id="nuevoFooterCorreo" name="nuevoFooterCorreo" value="Correo: ventas1@acplasticos.com">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoFooterColorFondo">Color de Fondo Footer:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="nuevoFooterColorFondo" name="nuevoFooterColorFondo" value="#873173" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#873173" readonly style="width: 100px;">
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nuevoFooterColorTexto">Color de Texto Footer:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="nuevoFooterColorTexto" name="nuevoFooterColorTexto" value="#FFFFFF" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" value="#FFFFFF" readonly style="width: 100px;">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Configuración -->
<div class="modal fade" id="modalEditarConfiguracion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="personalizacion-cotizaciones<?= $idSucursalSeleccionada ? '?sucursal=' . $idSucursalSeleccionada : '' ?>">
                <input type="hidden" id="editarId" name="editarId">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil"></i> Editar Configuración</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarIdSucursal">Sucursal:</label>
                                <select class="form-control" id="editarIdSucursal" name="editarIdSucursal">
                                    <option value="">Global (Todas las sucursales)</option>
                                    <?php foreach ($sucursales as $sucursal): ?>
                                        <option value="<?= $sucursal['id'] ?>">
                                            <?= htmlspecialchars($sucursal['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <h4><i class="fa fa-header"></i> Header</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarHeaderLogo">Logo (Ruta):</label>
                                <input type="text" class="form-control" id="editarHeaderLogo" name="editarHeaderLogo" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarHeaderNombreEmpresa">Nombre de Empresa:</label>
                                <input type="text" class="form-control" id="editarHeaderNombreEmpresa" name="editarHeaderNombreEmpresa" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarHeaderNit">NIT:</label>
                                <input type="text" class="form-control" id="editarHeaderNit" name="editarHeaderNit">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarHeaderRegimen">Régimen:</label>
                                <input type="text" class="form-control" id="editarHeaderRegimen" name="editarHeaderRegimen">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="editarHeaderServicios">Servicios (uno por línea):</label>
                                <textarea class="form-control" id="editarHeaderServicios" name="editarHeaderServicios" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarHeaderColorFondo">Color de Fondo Header:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editarHeaderColorFondo" name="editarHeaderColorFondo" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly style="width: 100px;">
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarHeaderColorTexto">Color de Texto Header:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editarHeaderColorTexto" name="editarHeaderColorTexto" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly style="width: 100px;">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h4><i class="fa fa-footer"></i> Footer</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="editarFooterDireccion">Dirección:</label>
                                <input type="text" class="form-control" id="editarFooterDireccion" name="editarFooterDireccion">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarFooterTelefono">Teléfono:</label>
                                <input type="text" class="form-control" id="editarFooterTelefono" name="editarFooterTelefono">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarFooterMovil">Móvil:</label>
                                <input type="text" class="form-control" id="editarFooterMovil" name="editarFooterMovil">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="editarFooterCorreo">Correo:</label>
                                <input type="text" class="form-control" id="editarFooterCorreo" name="editarFooterCorreo">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarFooterColorFondo">Color de Fondo Footer:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editarFooterColorFondo" name="editarFooterColorFondo" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly style="width: 100px;">
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarFooterColorTexto">Color de Texto Footer:</label>
                                <div class="input-group">
                                    <input type="color" class="form-control" id="editarFooterColorTexto" name="editarFooterColorTexto" required>
                                    <span class="input-group-addon">
                                        <input type="text" class="form-control color-text" readonly style="width: 100px;">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Configuración</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Actualizar texto de color cuando cambie el input
    $('input[type="color"]').on('change', function() {
        var colorInput = $(this);
        var textInput = colorInput.closest('.input-group').find('.color-text');
        textInput.val(colorInput.val());
    });
    
    // Manejar edición de configuración
    $('.btnEditarConfiguracion').on('click', function() {
        var id = $(this).data('id');
        editarConfiguracion(id);
    });
    
    // Actualizar texto de color en modal de edición
    $('#modalEditarConfiguracion input[type="color"]').on('change', function() {
        var colorInput = $(this);
        var textInput = colorInput.closest('.input-group').find('.color-text');
        textInput.val(colorInput.val());
    });
});

// Función para editar configuración
function editarConfiguracion(id) {
    $.ajax({
        url: 'ajax/personalizacion-cotizaciones.ajax.php',
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
                $('#editarId').val(config.id);
                $('#editarIdSucursal').val(config.id_sucursal || '');
                $('#editarHeaderLogo').val(config.header_logo);
                $('#editarHeaderNombreEmpresa').val(config.header_nombre_empresa);
                $('#editarHeaderNit').val(config.header_nit);
                $('#editarHeaderRegimen').val(config.header_regimen);
                $('#editarHeaderServicios').val(config.header_servicios);
                $('#editarHeaderColorFondo').val(config.header_color_fondo);
                $('#editarHeaderColorTexto').val(config.header_color_texto);
                $('#editarFooterDireccion').val(config.footer_direccion);
                $('#editarFooterTelefono').val(config.footer_telefono);
                $('#editarFooterMovil').val(config.footer_movil);
                $('#editarFooterCorreo').val(config.footer_correo);
                $('#editarFooterColorFondo').val(config.footer_color_fondo);
                $('#editarFooterColorTexto').val(config.footer_color_texto);
                
                // Actualizar textos de color
                $('#editarHeaderColorFondo').closest('.input-group').find('.color-text').val(config.header_color_fondo);
                $('#editarHeaderColorTexto').closest('.input-group').find('.color-text').val(config.header_color_texto);
                $('#editarFooterColorFondo').closest('.input-group').find('.color-text').val(config.footer_color_fondo);
                $('#editarFooterColorTexto').closest('.input-group').find('.color-text').val(config.footer_color_texto);
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
</script>
